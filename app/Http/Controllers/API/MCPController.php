<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\DiagnosticFlow;
use App\Services\ComponentMapper;
use App\Services\CodeResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MCPController extends Controller
{
    public function __construct(
        protected DiagnosticFlow $diagnosticFlow,
        protected ComponentMapper $componentMapper,
        protected CodeResolver $codeResolver,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'jsonrpc' => 'required|string',
            'method' => 'required|string',
            'params' => 'nullable|array',
            'id' => 'nullable|string',
            'server' => 'nullable|string',
        ]);

        $method = $validated['method'];
        $params = $validated['params'] ?? [];
        $id = $validated['id'] ?? null;

        $availableMethods = [
            'diagnostic.analyze',
            'servers.list',
            'component.map',
            'codesecret.resolve',
        ];

        if (!in_array($method, $availableMethods)) {
            return response()->json([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32601,
                    'message' => 'Method not found: ' . $method,
                ],
                'id' => $id,
            ], 500);
        }

        try {
            $result = match ($method) {
                'diagnostic.analyze' => $this->handleDiagnosticAnalyze($params),
                'servers.list' => $this->handleServersList(),
                'component.map' => $this->handleComponentMap($params),
                'codesecret.resolve' => $this->handleCodesecretResolve($params),
                default => throw new \Exception('Method not implemented'),
            };

            return response()->json([
                'jsonrpc' => '2.0',
                'result' => $result,
                'id' => $id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32603,
                    'message' => $e->getMessage(),
                ],
                'id' => $id,
            ], 500);
        }
    }

    protected function handleDiagnosticAnalyze(array $params): array
    {
        $symptoms = $params['symptoms'] ?? [];
        $device = $params['device'] ?? [];
        
        return $this->diagnosticFlow->analyze($symptoms);
    }

    protected function handleServersList(): array
    {
        return [
            ['name' => 'diagnostic', 'enabled' => true],
            ['name' => 'component', 'enabled' => true],
            ['name' => 'codesecret', 'enabled' => true],
            ['name' => 'evolution', 'enabled' => true],
            ['name' => 'tool', 'enabled' => true],
        ];
    }

    protected function handleComponentMap(array $params): array
    {
        $symptomIds = $params['symptom_ids'] ?? [];
        
        // Implémente la logique de mapping
        return [
            'symptom_ids' => $symptomIds,
            'components' => [],
        ];
    }

    protected function handleCodesecretResolve(array $params): array
    {
        $input = $params['input'] ?? '';
        
        return $this->codeResolver->resolve($input)->toArray();
    }

    public function servers(): JsonResponse
    {
        return response()->json([
            'servers' => $this->handleServersList(),
        ]);
    }

    public function info(): JsonResponse
    {
        return response()->json([
            'name' => 'Aide Phone Réparation MCP',
            'version' => '1.0.0',
            'protocol_version' => '2024-11-05',
            'capabilities' => [
                'diagnostic',
                'component_mapping',
                'code_resolution',
                'evolution_tracking',
                'tool_recommendation',
            ],
        ]);
    }
}