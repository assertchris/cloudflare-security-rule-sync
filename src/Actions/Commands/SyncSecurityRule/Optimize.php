<?php

namespace AC\CloudflareSecurityRuleSync\Actions\Commands\SyncSecurityRule;

use AC\CloudflareSecurityRuleSync\Actions\Commands\SyncSecurityRule\Concerns\NormalizesAndMatchesPathsConcern;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Optimize
{
    use NormalizesAndMatchesPathsConcern;

    public function __invoke(Collection $paths): Collection
    {
        $paths = $this->normalizeAndSort($paths);

        $optimized = collect();

        foreach ($paths as $path) {
            if ($optimized->contains($path)) {
                continue;
            }

            if ($this->isCoveredByExistingWildcards($optimized, $path)) {
                continue;
            }

            if ($this->containsWildcard($path)) {
                $optimized = $this->removeEntriesCoveredByWildcard($optimized, $path);
            }

            $optimized->push($path);
        }

        return $optimized->values();
    }

    protected function isCoveredByExistingWildcards(Collection $optimized, string $path): bool
    {
        return $optimized->contains(function ($existing) use ($path) {
            return $this->containsWildcard($existing) && $this->pathMatchesWildcard($path, $existing);
        });
    }

    protected function removeEntriesCoveredByWildcard(Collection $optimized, string $wildcard): Collection
    {
        return $optimized->reject(function ($existing) use ($wildcard) {
            return $this->pathMatchesWildcard($existing, $wildcard);
        })->values();
    }

    protected function containsWildcard(string $path): bool
    {
        return Str::contains($path, '*');
    }
}
