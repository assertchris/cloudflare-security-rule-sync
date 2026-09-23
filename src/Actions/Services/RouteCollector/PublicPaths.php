<?php

namespace AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PublicPaths
{
    public function __invoke(): array
    {
        $glob = File::glob(base_path('public/{,.}*'), GLOB_BRACE);

        return collect($glob)
            ->map(fn ($path) => Str::replace('\\', '/', $path))
            ->map(fn ($path) => Str::after($path, '/public/'))
            ->reject(fn ($path) => in_array($path, ['.', '..', '.htaccess', 'index.php']))
            ->map(fn ($path) => File::isDirectory(public_path($path)) ? $path.'/*' : $path)
            ->all();
    }
}
