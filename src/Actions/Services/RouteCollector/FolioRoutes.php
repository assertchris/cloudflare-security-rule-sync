<?php

namespace AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class FolioRoutes
{
    public function __invoke(): Collection
    {
        if (! class_exists('Laravel\\Folio\\FolioManager')) {
            return collect();
        }

        try {
            Artisan::call('folio:list', ['--json' => true]);
        } catch (Throwable) {
            return collect();
        }

        return collect(json_decode(Artisan::output(), true) ?: [])
            ->pluck('uri')
            ->values();
    }
}
