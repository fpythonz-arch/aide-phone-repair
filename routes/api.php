<?php

use App\Http\Controllers\API\MCPController;
use App\Http\Controllers\API\DiagnosticController;
use App\Http\Controllers\API\ComponentController;
use App\Http\Controllers\API\CodeController;
use App\Http\Controllers\API\EvolutionController;
use App\Http\Controllers\API\ToolController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\DeviceController;
use App\Http\Controllers\API\SymptomController;
use App\Http\Controllers\API\DepannageController;

/*
|--------------------------------------------------------------------------
| API Routes - Aide Phone Réparation
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'aide-phone-reparation-api',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
        'environment' => app()->environment(),
    ]);
});

Route::get('/ping', function () {
    return response()->json(['pong' => true]);
});

/*
|--------------------------------------------------------------------------
| MCP Protocol Routes
|--------------------------------------------------------------------------
*/
Route::prefix('mcp')->group(function () {
    Route::get('/info', [MCPController::class, 'info']);
    Route::get('/servers', [MCPController::class, 'servers']);
    Route::post('/', [MCPController::class, 'handle'])
        ->middleware('mcp.auth');
});

/*
|--------------------------------------------------------------------------
| Diagnostic Routes
|--------------------------------------------------------------------------
*/
Route::prefix('diagnostic')->group(function () {
    Route::post('/initialize', [DiagnosticController::class, 'initialize']);
    Route::post('/analyze', [DiagnosticController::class, 'analyze']);
    Route::post('/validate', [DiagnosticController::class, 'validateResults']); // CORRIGÉ ICI
    Route::get('/next-steps', [DiagnosticController::class, 'nextSteps']);
    Route::get('/history', [DiagnosticController::class, 'history']);
});

/*
|--------------------------------------------------------------------------
| Component Routes
|--------------------------------------------------------------------------
*/
Route::prefix('components')->group(function () {
    Route::get('/', [ComponentController::class, 'index']);
    Route::get('/categories', [ComponentController::class, 'categories']);
    Route::post('/map', [ComponentController::class, 'mapBySymptoms']);
    Route::get('/by-category/{category}', [ComponentController::class, 'byCategory']);
    Route::get('/compatible', [ComponentController::class, 'compatible']);
    Route::get('/{component}', [ComponentController::class, 'show']);
    Route::get('/{component}/feasibility', [ComponentController::class, 'feasibility']);
    Route::get('/{component}/alternatives', [ComponentController::class, 'alternatives']);
    Route::get('/components', [ComponentController::class, 'index']);
    Route::get('/components/{id}', [ComponentController::class, 'show']);
    Route::get('/components/{slug}', [ComponentController::class, 'showBySlug']);
});

/*
|--------------------------------------------------------------------------
| Secret Code Routes
|--------------------------------------------------------------------------
*/
Route::prefix('codes')->group(function () {
    Route::get('/', [CodeController::class, 'index']);
    Route::get('/categories', [CodeController::class, 'categories']);
    Route::get('/popular', [CodeController::class, 'popular']);
    Route::get('/statistics', [CodeController::class, 'statistics']);
    Route::post('/resolve', [CodeController::class, 'resolve']);
    Route::post('/validate', [CodeController::class, 'validateSafety']);
    Route::get('/by-brand/{brand}', [CodeController::class, 'byBrand']);
    Route::get('/by-category/{category}', [CodeController::class, 'byCategory']);
    Route::get('/{code}', [CodeController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Evolution Routes
|--------------------------------------------------------------------------
*/
Route::prefix('evolution')->group(function () {
    Route::get('/', [EvolutionController::class, 'index']);
    Route::post('/', [EvolutionController::class, 'store']);
    Route::get('/trends', [EvolutionController::class, 'trends']);
    Route::get('/timeline', [EvolutionController::class, 'timeline']);
    Route::get('/symptom/{symptomId}/stats', [EvolutionController::class, 'symptomStats']);
    Route::get('/{event}', [EvolutionController::class, 'show']);
    Route::put('/{event}', [EvolutionController::class, 'update']);
    Route::delete('/{event}', [EvolutionController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Tool Routes
|--------------------------------------------------------------------------
*/
Route::prefix('tools')->group(function () {
    Route::get('/for-repair', [ToolController::class, 'forRepair']);
    Route::post('/check-inventory', [ToolController::class, 'checkInventory']);
    Route::get('/starter-kit', [ToolController::class, 'starterKit']);
    Route::get('/tools', [ToolController::class, 'index']);
    Route::post('/tools/{slug}/execute', [ToolController::class, 'execute']);
});

/*
|--------------------------------------------------------------------------
| Device Routes
|--------------------------------------------------------------------------
*/
Route::prefix('devices')->group(function () {
    Route::get('/brands', [DeviceController::class, 'brands']);
    Route::get('/search', [DeviceController::class, 'search']);
    Route::get('/by-brand/{brand}', [DeviceController::class, 'byBrand']);
    Route::get('/{slug}', [DeviceController::class, 'show']);
    Route::get('/', [DeviceController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| Symptom Routes
|--------------------------------------------------------------------------
*/
Route::prefix('symptoms')->group(function () {
    Route::get('/', [SymptomController::class, 'index']);
    Route::get('/by-device/{deviceId}', [SymptomController::class, 'getByDevice']);
});

/*
|--------------------------------------------------------------------------
| Depannage Routes
|--------------------------------------------------------------------------
*/
Route::prefix('depannage')->group(function () {
    Route::get('/categories', [DepannageController::class, 'categories']);  // ← D'ABORD les routes spécifiques
    Route::get('/{type}', [DepannageController::class, 'show']);            // ← ENSUITE le paramètre générique
});