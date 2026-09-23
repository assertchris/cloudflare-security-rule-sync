<?php

namespace AC\CloudflareSecurityRuleSync\Services;

use AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector\AppRoutes;
use AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector\FolioRoutes;
use AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector\PublicPaths;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RouteCollector
{
    public function __construct(
        private readonly AppRoutes $appRoutes,
        private readonly FolioRoutes $folioRoutes,
        private readonly PublicPaths $publicPaths,
    ) {}

    public function collect(): Collection
    {
        return ($this->appRoutes)()
            ->merge(($this->folioRoutes)())
            ->merge(($this->publicPaths)())
            ->merge($this->forcedAllowPaths())
            ->unique()
            ->map(function (string $path) {
                if (Str::contains($path, '{')) {
                    $path = preg_replace('/\{[^}]+\}/', '*', $path);
                }

                return Str::startsWith($path, '/') ? $path : '/'.$path;
            })
            ->reject(fn (string $path) => $path === '*' || $path === '/*')
            ->reject(fn (string $path) => $this->isIgnorable($path))
            ->values();
    }

    public function forcedAllowPaths(): array
    {
        $paths = config('cloudflare-security-rule-sync.rule.forced_allow_paths') ?: [];

        return collect($paths)
            ->map(fn (string $path) => Str::startsWith($path, '/') ? $path : '/'.$path)
            ->reject(fn (string $path) => $path === '/' || $path === '*' || $path === '/*')
            ->values()
            ->all();
    }

    public function isIgnorable(string $path): bool
    {
        $ignorable = config('cloudflare-security-rule-sync.rule.ignorable_paths') ?: ['/_dusk/*'];

        $path = Str::startsWith($path, '/') ? $path : '/'.$path;

        return collect($ignorable)->contains(fn ($pattern) => Str::is($pattern, $path));
    }
}
