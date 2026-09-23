<?php

namespace AC\CloudflareSecurityRuleSync\Actions\Commands\SyncSecurityRule;

use AC\CloudflareSecurityRuleSync\Actions\Commands\SyncSecurityRule\Concerns\NormalizesAndMatchesPathsConcern;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Condense
{
    use NormalizesAndMatchesPathsConcern;

    public function __invoke(Collection $routes): Collection
    {
        $working = $this->normalizeAndSort($routes);

        [$endingWildcards, $nonEnding] = $this->splitByEndingWildcard($working);

        $condensed = collect();
        $grouped = $this->groupNonEndingWildcardPaths($nonEnding);

        foreach ($grouped as $prefix => $group) {
            if ($this->prefixCoveredByEndingWildcard($endingWildcards, $prefix)) {
                continue;
            }

            $condensed->push($this->condenseNonEndingGroup($prefix, $group));
        }

        $wildcardGrouped = $this->groupEndingWildcardsByTopLevel($endingWildcards);

        foreach ($wildcardGrouped as $prefix => $group) {
            if ($prefix === '/' && ($nonEnding->contains('/') || $condensed->contains('/'))) {
                continue;
            }

            $condensed->push($this->condenseEndingWildcardGroup($prefix, $group));
        }

        $final = $condensed->values();
        $wildcards = $final->filter(fn ($route) => Str::endsWith($route, '/*'))->values();

        foreach ($wildcards as $wc) {
            $final = $final->filter(function ($existing) use ($wc) {
                if ($existing === $wc) {
                    return true;
                }

                return ! $this->pathMatchesWildcard($existing, $wc);
            })->values();
        }

        return $final->unique()->sort()->values();
    }

    private function splitByEndingWildcard(Collection $paths): array
    {
        $ending = $paths->filter(fn ($path) => Str::endsWith($path, '/*'));
        $nonEnding = $paths->reject(fn ($path) => Str::endsWith($path, '/*'));

        return [$ending, $nonEnding];
    }

    private function groupNonEndingWildcardPaths(Collection $nonEnding): Collection
    {
        return $nonEnding->groupBy(function ($path) {
            $beforeWildcard = Str::contains($path, '/*') ? Str::before($path, '/*') : $path;
            $segments = explode('/', trim($beforeWildcard, '/'));

            if (count($segments) === 1) {
                return '/'.$segments[0];
            }

            return '/'.$segments[0].'/'.$segments[1];
        });
    }

    private function prefixCoveredByEndingWildcard(Collection $endingWildcards, string $prefix): bool
    {
        return $endingWildcards->contains(function ($wildcard) use ($prefix) {
            $wildcardPrefix = Str::before($wildcard, '/*');

            if ($wildcardPrefix === '' && $prefix === '/') {
                return false;
            }

            return ($wildcardPrefix !== '' && (Str::startsWith($prefix, $wildcardPrefix.'/') || $prefix === $wildcardPrefix))
                || ($wildcardPrefix === '' && $prefix !== '/');
        });
    }

    private function condenseNonEndingGroup(string $prefix, Collection $group): string
    {
        if ($group->count() === 1) {
            return $group->first();
        }

        $allExactMatch = $group->every(fn ($path) => $path === $prefix);

        return $allExactMatch ? $prefix : $prefix.'/*';
    }

    private function groupEndingWildcardsByTopLevel(Collection $endingWildcards): Collection
    {
        return $endingWildcards->groupBy(function ($path) {
            $cleanPath = Str::before($path, '/*');
            $segments = explode('/', trim($cleanPath, '/'));

            return isset($segments[0]) && $segments[0] !== '' ? '/'.$segments[0] : '/';
        });
    }

    private function condenseEndingWildcardGroup(string $prefix, Collection $group): string
    {
        if ($group->count() === 1) {
            return $group->first();
        }

        $segmentLists = $group->map(function ($path) {
            $clean = Str::before($path, '/*');

            return explode('/', trim($clean, '/'));
        })->values();

        $lcp = [];
        $first = $segmentLists->first();
        foreach ($first as $i => $seg) {
            $allMatch = $segmentLists->every(function ($segments) use ($i, $seg) {
                return isset($segments[$i]) && $segments[$i] === $seg;
            });

            if (! $allMatch) {
                break;
            }

            $lcp[] = $seg;
        }

        if (count($lcp) === 0) {
            return rtrim($prefix, '/').'/*';
        }

        return '/'.implode('/', $lcp).'/*';
    }
}
