<?php

namespace AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;

class AppRoutes
{
    public function __invoke(): Collection
    {
        Artisan::call('route:list', ['--json' => true]);
        $json = Artisan::output();

        return collect(json_decode($json, true) ?: [])
            ->reject(fn (array $route) => (
                ($route['name'] ?? null) === 'laravel-folio' &&
                ($route['uri'] ?? null) === '{fallbackPlaceholder}'
            ))
            ->pluck('uri')
            ->values();
    }
}
