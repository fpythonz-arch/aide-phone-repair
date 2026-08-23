<?php

namespace App\Providers;

use App\MCP\Core\Engine;
use App\MCP\Protocol\MCPHandler;
use App\Services\DiagnosticFlow;
use App\Services\ComponentMapper;
use App\Services\CodeResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class MCPServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Singleton du moteur MCP - PAS de config('mcp') dans le constructeur
        $this->app->singleton(Engine::class, function ($app) {
            return new Engine(
                $app->make(DiagnosticFlow::class),
                $app->make(ComponentMapper::class),
                $app->make(CodeResolver::class)
            );
        });

        // Singleton du handler MCP
        $this->app->singleton(MCPHandler::class, function ($app) {
            return new MCPHandler(
                $app->make(Engine::class)
            );
        });

        // Services de diagnostic en singleton
        $this->app->singleton(DiagnosticFlow::class, function ($app) {
            return new DiagnosticFlow();
        });

        $this->app->singleton(ComponentMapper::class, function ($app) {
            return new ComponentMapper();
        });

        $this->app->singleton(CodeResolver::class, function ($app) {
            return new CodeResolver();
        });
    }

    public function boot(): void
    {
        $this->validateMCPConfig();
        $this->registerCacheMacros();

        $this->publishes([
            __DIR__ . '/../../config/mcp.php' => config_path('mcp.php'),
        ], 'mcp-config');
    }

    protected function validateMCPConfig(): void
    {
        $config = config('mcp');

        if (empty($config)) {
            \Log::warning('Configuration MCP non trouvée. Utilisation des valeurs par défaut.');
            return;
        }

        if (empty($config['servers'])) {
            \Log::error('Aucun serveur MCP configuré.');
        }

        if (app()->environment('production') && !($config['auth']['enabled'] ?? false)) {
            \Log::critical('L\'authentification MCP est désactivée en production !');
        }
    }

    protected function registerCacheMacros(): void
    {
        Cache::macro('mcpRemember', function (string $key, int $ttlMinutes, callable $callback) {
            $ttl = now()->addMinutes($ttlMinutes);
            return Cache::remember("mcp:{$key}", $ttl, $callback);
        });

        Cache::macro('mcpForget', function (string $pattern) {
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                return true;
            }
            Cache::forget("mcp:{$pattern}");
            return true;
        });
    }
}