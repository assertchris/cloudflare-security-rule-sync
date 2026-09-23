<?php

namespace AC\CloudflareSecurityRuleSync\Actions\Commands\SyncSecurityRule\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait NormalizesAndMatchesPathsConcern
{
    protected function normalizeAndSort(Collection $paths): Collection
    {
        return $paths->map(function (string $path) {
            return Str::startsWith($path, '/') ? $path : '/'.$path;
        })->sort()->values();
    }

    protected function pathMatchesWildcard(string $concretePath, string $wildcardRule): bool
    {
        $concretePath = Str::startsWith($concretePath, '/') ? $concretePath : '/'.$concretePath;
        $wildcardRule = Str::startsWith($wildcardRule, '/') ? $wildcardRule : '/'.$wildcardRule;

        $segments = explode('/', ltrim($wildcardRule, '/'));
        $regex = '^/';
        $count = count($segments);

        foreach ($segments as $index => $seg) {
            $isLast = ($index === $count - 1);

            if ($seg === '*') {
                if ($isLast) {
                    $regex .= '.+';
                    break;
                } else {
                    $regex .= '[^/]+';
                }
            } else {
                $regex .= preg_quote($seg, '/');
            }

            if (! $isLast) {
                $regex .= '/';
            }
        }

        $regex .= '$';

        return (bool) preg_match('~'.$regex.'~', $concretePath);
    }
}
