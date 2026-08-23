<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Liste des exceptions qui ne doivent pas être reportées.
     */
    protected $dontReport = [
        //
    ];

    /**
     * Liste des inputs qui ne doivent pas être flashés en cas de validation.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Enregistre les callbacks de gestion des exceptions.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Gestion personnalisée des exceptions MCP
        $this->renderable(function (MCPException $e, $request) {
            return $this->renderMCPError($e);
        });

        // Gestion personnalisée des exceptions de diagnostic
        $this->renderable(function (DiagnosticException $e, $request) {
            return $this->renderDiagnosticError($e);
        });

        // Gestion personnalisée des composants non trouvés
        $this->renderable(function (ComponentNotFoundException $e, $request) {
            return $this->renderComponentError($e);
        });

        // Gestion générique des erreurs 404 pour l'API
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RESOURCE_NOT_FOUND',
                        'message' => 'La ressource demandée n\'existe pas.',
                        'details' => $e->getMessage(),
                    ],
                ], 404);
            }
        });

        // Gestion des méthodes HTTP non autorisées
        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'METHOD_NOT_ALLOWED',
                        'message' => 'Méthode HTTP non autorisée pour cette route.',
                    ],
                ], 405);
            }
        });

        // Gestion des erreurs de validation
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Les données fournies sont invalides.',
                        'errors' => $e->errors(),
                    ],
                ], 422);
            }
        });
    }

    /**
     * Rendu d'une erreur MCP.
     */
    protected function renderMCPError(MCPException $e): JsonResponse
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $e->getErrorCode(),
                'message' => $e->getMessage(),
                'data' => $e->getErrorData(),
            ],
            'id' => $e->getRequestId(),
        ], $e->getStatusCode());
    }

    /**
     * Rendu d'une erreur de diagnostic.
     */
    protected function renderDiagnosticError(DiagnosticException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'DIAGNOSTIC_ERROR',
                'message' => $e->getMessage(),
                'step' => $e->getStep(),
                'suggestion' => $e->getSuggestion(),
            ],
        ], $e->getStatusCode());
    }

    /**
     * Rendu d'une erreur de composant.
     */
    protected function renderComponentError(ComponentNotFoundException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'COMPONENT_NOT_FOUND',
                'message' => $e->getMessage(),
                'component_id' => $e->getComponentId(),
                'alternatives' => $e->getAlternatives(),
            ],
        ], 404);
    }

    /**
     * Rendu JSON générique pour les erreurs non gérées en API.
     */
    public function render($request, Throwable $e): \Symfony\Component\HttpFoundation\Response
    {
        if ($request->is('api/*') && !($e instanceof ValidationException)) {
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => app()->environment('production')
                        ? 'Une erreur interne est survenue.'
                        : $e->getMessage(),
                    'trace' => app()->environment('production') ? null : $e->getTrace(),
                ],
            ], $statusCode);
        }

        return parent::render($request, $e);
    }
}