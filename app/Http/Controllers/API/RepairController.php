<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRepairsRequest;
use App\Http\Requests\StoreRepairRequest;
use App\Http\Requests\UpdateRepairRequest;
use App\Http\Requests\UpdateRepairStatusRequest;
use App\Http\Resources\RepairResource;
use App\Models\Device;
use App\Models\Repair;
use App\Services\ClientResolver;
use App\Services\RepairNumberGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RepairController extends Controller
{
    public function __construct(
        protected RepairNumberGenerator $numbers,
        protected ClientResolver $clients,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Repair::query();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'LIKE', "%{$search}%")
                    ->orWhere('client_phone', 'LIKE', "%{$search}%")
                    ->orWhere('device_brand', 'LIKE', "%{$search}%")
                    ->orWhere('device_model', 'LIKE', "%{$search}%")
                    ->orWhere('number', 'LIKE', "%{$search}%");
            });
        }

        $repairs = $query->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 500));

        return response()->json([
            'success' => true,
            'data' => RepairResource::collection($repairs),
            'meta' => [
                'total' => $repairs->total(),
                'per_page' => $repairs->perPage(),
                'current_page' => $repairs->currentPage(),
                'last_page' => $repairs->lastPage(),
            ],
        ]);
    }

    public function stats(): JsonResponse
    {
        $active = ['in_progress', 'diagnosing', 'testing'];
        $pending = ['new', 'received', 'waiting_quote', 'waiting_parts'];

        return response()->json([
            'success' => true,
            'data' => [
                'total' => Repair::query()->count(),
                'active' => Repair::query()->whereIn('status', $active)->count(),
                'pending' => Repair::query()->whereIn('status', $pending)->count(),
                'ready' => Repair::query()->where('status', 'ready')->count(),
                'completed' => Repair::query()->where('status', 'delivered')->count(),
                'urgent' => Repair::query()->where('priority', 'urgent')->count(),
            ],
        ]);
    }

    public function store(StoreRepairRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $client = $this->clients->resolve(
            $validated['client_name'],
            $validated['client_phone'],
            $validated['client_email'] ?? null
        );

        $repair = Repair::query()->create([
            ...$validated,
            'number' => $this->numbers->next(),
            'client_id' => $client->id,
            'device_id' => $this->matchDevice($validated['device_brand'], $validated['device_model'])?->id,
            'technician_id' => $request->user()?->id,
            'status' => $validated['status'] ?? 'new',
            'priority' => $validated['priority'] ?? 'normal',
            'currency' => $validated['currency'] ?? 'FCFA',
        ]);

        return response()->json([
            'success' => true,
            'data' => new RepairResource($repair),
        ], 201);
    }

    public function show(Repair $repair): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new RepairResource($repair),
        ]);
    }

    public function update(UpdateRepairRequest $request, Repair $repair): JsonResponse
    {
        $validated = $request->validated();

        if (! empty($validated['client_name']) || ! empty($validated['client_phone'])) {
            $client = $this->clients->resolve(
                $validated['client_name'] ?? $repair->client_name,
                $validated['client_phone'] ?? $repair->client_phone,
                $validated['client_email'] ?? $repair->client_email
            );
            $validated['client_id'] = $client->id;
        }

        $repair->update($validated);

        return response()->json([
            'success' => true,
            'data' => new RepairResource($repair),
        ]);
    }

    public function updateStatus(UpdateRepairStatusRequest $request, Repair $repair): JsonResponse
    {
        $repair->update(['status' => $request->validated('status')]);

        return response()->json([
            'success' => true,
            'data' => new RepairResource($repair),
        ]);
    }

    public function destroy(Repair $repair): JsonResponse
    {
        $repair->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Importe en une fois les réparations créées côté client avant la migration
     * vers l'API (localStorage). Idempotent : rejouer le même lot ne duplique rien,
     * car chaque ligne est dédupliquée par son identifiant client d'origine (legacy_id).
     */
    public function import(ImportRepairsRequest $request): JsonResponse
    {
        $items = $request->validated('repairs');
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($items as $index => $item) {
            try {
                $legacyId = $item['id'] ?? null;

                if ($legacyId && Repair::query()->where('legacy_id', $legacyId)->exists()) {
                    $skipped++;
                    continue;
                }

                DB::transaction(function () use ($item, $legacyId) {
                    $client = $this->clients->resolve(
                        $item['client_name'],
                        $item['client_phone'],
                        $item['client_email'] ?? null
                    );

                    $repair = new Repair([
                        'number' => $this->numbers->next(),
                        'legacy_number' => $item['number'] ?? null,
                        'legacy_id' => $legacyId,
                        'client_id' => $client->id,
                        'device_id' => $this->matchDevice($item['device_brand'], $item['device_model'])?->id,
                        'technician_id' => null,
                        'client_name' => $item['client_name'],
                        'client_phone' => $item['client_phone'],
                        'client_email' => $item['client_email'] ?? null,
                        'device_brand' => $item['device_brand'],
                        'device_model' => $item['device_model'],
                        'device_imei' => $item['device_imei'] ?? null,
                        'problem_description' => $item['problem_description'],
                        'diagnosis' => $item['diagnosis'] ?? null,
                        'technician' => $item['technician'] ?? null,
                        'status' => $item['status'] ?? 'new',
                        'priority' => $item['priority'] ?? 'normal',
                        'cost_estimate' => $item['cost_estimate'] ?? null,
                        'cost_final' => $item['cost_final'] ?? null,
                        'currency' => $item['currency'] ?? 'FCFA',
                        'parts_used' => $item['parts_used'] ?? [],
                        'notes' => $item['notes'] ?? null,
                        'estimated_ready' => $item['estimated_ready'] ?? null,
                        'warranty_days' => $item['warranty_days'] ?? null,
                    ]);

                    $repair->timestamps = false;
                    $repair->created_at = isset($item['created_at']) ? Carbon::parse($item['created_at']) : now();
                    $repair->updated_at = isset($item['updated_at']) ? Carbon::parse($item['updated_at']) : now();
                    $repair->save();
                });

                $imported++;
            } catch (\Throwable $e) {
                $errors[] = ['index' => $index, 'message' => $e->getMessage()];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'imported' => $imported,
                'skipped' => $skipped,
                'failed' => count($errors),
                'errors' => $errors,
            ],
        ]);
    }

    protected function matchDevice(string $brand, string $model): ?Device
    {
        return Device::query()
            ->whereRaw('lower(brand) = ?', [mb_strtolower($brand)])
            ->whereRaw('lower(model) = ?', [mb_strtolower($model)])
            ->first();
    }
}
