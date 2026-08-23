<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEvolutionEventRequest;
use App\Http\Resources\EvolutionEventResource;
use App\Models\EvolutionEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EvolutionController extends Controller
{
    /**
     * Liste les événements d'évolution avec filtres.
     */
    public function index(Request $request): JsonResponse
    {
        $query = EvolutionEvent::with(['symptom', 'component']);

        if ($request->has('symptom_id')) {
            $query->where('symptom_id', $request->symptom_id);
        }

        if ($request->has('component_id')) {
            $query->where('component_id', $request->component_id);
        }

        if ($request->has('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->has('device_brand')) {
            $query->where('device_brand', $request->device_brand);
        }

        if ($request->has('device_model')) {
            $query->where('device_model', $request->device_model);
        }

        if ($request->has('repair_successful')) {
            $query->where('repair_successful', filter_var($request->repair_successful, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        // Filtre pour les réparations réussies récentes
        if ($request->boolean('recent_successful')) {
            $query->successfulRepairs()->recent();
        }

        $events = $query->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => EvolutionEventResource::collection($events),
            'meta' => [
                'total' => $events->total(),
                'per_page' => $events->perPage(),
                'current_page' => $events->currentPage(),
            ],
        ]);
    }

    /**
     * Affiche un événement spécifique.
     */
    public function show(EvolutionEvent $event): JsonResponse
    {
        $event->load(['symptom', 'component']);

        return response()->json([
            'data' => new EvolutionEventResource($event),
        ]);
    }

    /**
     * Crée un nouvel événement d'évolution.
     */
    public function store(StoreEvolutionEventRequest $request): JsonResponse
    {
        $event = EvolutionEvent::create($request->validated());

        return response()->json([
            'message' => 'Événement d\'évolution enregistré avec succès.',
            'data' => new EvolutionEventResource($event),
        ], 201);
    }

    /**
     * Met à jour un événement d'évolution.
     */
    public function update(StoreEvolutionEventRequest $request, EvolutionEvent $event): JsonResponse
    {
        $event->update($request->validated());

        return response()->json([
            'message' => 'Événement mis à jour avec succès.',
            'data' => new EvolutionEventResource($event),
        ]);
    }

    /**
     * Supprime un événement d'évolution.
     */
    public function destroy(EvolutionEvent $event): JsonResponse
    {
        $event->delete();

        return response()->json([
            'message' => 'Événement supprimé avec succès.',
        ]);
    }

    /**
     * Statistiques d'évolution pour un symptôme.
     */
    public function symptomStats(int $symptomId): JsonResponse
    {
        $stats = EvolutionEvent::where('symptom_id', $symptomId)
            ->selectRaw('
                COUNT(*) as total_events,
                AVG(severity_before) as avg_severity_before,
                AVG(severity_after) as avg_severity_after,
                SUM(CASE WHEN repair_attempted = 1 THEN 1 ELSE 0 END) as repairs_attempted,
                SUM(CASE WHEN repair_successful = 1 THEN 1 ELSE 0 END) as repairs_successful,
                AVG(time_elapsed_days) as avg_time_elapsed
            ')
            ->first();

        $successRate = $stats->repairs_attempted > 0
            ? round(($stats->repairs_successful / $stats->repairs_attempted) * 100, 1)
            : 0;

        return response()->json([
            'symptom_id' => $symptomId,
            'statistics' => [
                'total_events' => (int) $stats->total_events,
                'avg_severity_before' => round($stats->avg_severity_before, 1),
                'avg_severity_after' => round($stats->avg_severity_after, 1),
                'repairs_attempted' => (int) $stats->repairs_attempted,
                'repairs_successful' => (int) $stats->repairs_successful,
                'success_rate_percent' => $successRate,
                'avg_time_elapsed_days' => round($stats->avg_time_elapsed, 1),
            ],
        ]);
    }

    /**
     * Timeline d'évolution pour un appareil spécifique.
     */
    public function timeline(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_brand' => 'required|string|max:50',
            'device_model' => 'required|string|max:100',
        ]);

        $events = EvolutionEvent::with(['symptom', 'component'])
            ->where('device_brand', $validated['device_brand'])
            ->where('device_model', $validated['device_model'])
            ->orderBy('created_at')
            ->get();

        $timeline = $events->groupBy(function ($event) {
            return $event->created_at->format('Y-m');
        })->map(function ($monthEvents, $month) {
            return [
                'month' => $month,
                'events_count' => $monthEvents->count(),
                'events' => EvolutionEventResource::collection($monthEvents),
                'avg_severity_change' => round(
                    $monthEvents->avg('severity_after') - $monthEvents->avg('severity_before'),
                    1
                ),
            ];
        })->values();

        return response()->json([
            'device' => [
                'brand' => $validated['device_brand'],
                'model' => $validated['device_model'],
            ],
            'timeline' => $timeline,
            'total_events' => $events->count(),
        ]);
    }

    /**
     * Tendances globales des pannes.
     */
    public function trends(Request $request): JsonResponse
    {
        $days = (int) $request->input('days', 30); // CAST EN INT

        $trends = EvolutionEvent::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('
                event_type,
                COUNT(*) as count,
                AVG(severity_after) as avg_severity
            ')
            ->groupBy('event_type')
            ->orderByDesc('count')
            ->get();

        $brandTrends = EvolutionEvent::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('
                device_brand,
                COUNT(*) as count,
                SUM(CASE WHEN repair_successful = 1 THEN 1 ELSE 0 END) as successful_repairs
            ')
            ->groupBy('device_brand')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return response()->json([
            'period_days' => $days,
            'event_type_trends' => $trends,
            'brand_trends' => $brandTrends,
        ]);
    }
}