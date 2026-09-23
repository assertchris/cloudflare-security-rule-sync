<?php

namespace AC\CloudflareSecurityRuleSync\Actions\Commands\SyncSecurityRule;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Build
{
    public function __invoke(Collection $paths): string
    {
        if ($paths->isEmpty()) {
            return '';
        }

        $wildcards = $paths->filter(fn ($route) => Str::contains($route, '*'));
        $exact = $paths->filter(fn ($route) => ! Str::contains($route, '*'));

        $expression = 'not (';

        if ($exact->isNotEmpty()) {
            $expression .= 'http.request.uri.path in {"';
            $expression .= implode('" "', $exact->toArray());
            $expression .= '"}';

            if ($wildcards->isNotEmpty()) {
                $expression .= ' or ';
            }
        }

        if ($wildcards->isNotEmpty()) {
            $expression .= $wildcards->map(function ($route) {
                if (Str::contains($route, '*')) {
                    $route = Str::before($route, '*').'*';
                }

                return sprintf('http.request.uri.path wildcard "%s"', $route);
            })->join(' or ');
        }

        $expression .= ')';

        return $expression;
    }
}
