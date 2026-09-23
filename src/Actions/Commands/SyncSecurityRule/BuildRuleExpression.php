<?php

namespace AC\CloudflareSecurityRuleSync\Actions\Commands\SyncSecurityRule;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BuildRuleExpression
{
    public function __invoke(Collection $paths): string
    {
        $optimizer = new Optimize;
        $routes = $optimizer($paths);
        $expression = (new Build)($routes);

        if (Str::length($expression) > 4000) {
            $condense = new Condense;
            do {
                $previous = $expression;
                $routes = $condense($routes);
                $expression = (new Build)($routes);

                if (Str::length($expression) <= 4000) {
                    break;
                }
            } while ($expression !== $previous);
        }

        $hostnames = config('cloudflare-security-rule-sync.rule.hostnames', []);

        if (is_array($hostnames) && $hostnames !== []) {
            $list = implode(' ', array_map(
                fn (string $h) => '"'.str_replace('"', '\\"', $h).'"',
                $hostnames
            ));

            $expression = '(http.host in {'.$list.'}) and ('.$expression.')';
        }

        return $expression;
    }

    public function description(): string
    {
        $base = config('cloudflare-security-rule-sync.rule.description', 'cloudflare-security-rule-sync');
        $hostnames = config('cloudflare-security-rule-sync.rule.hostnames', []);

        return (is_array($hostnames) && $hostnames !== [])
            ? $base.':'.reset($hostnames)
            : $base;
    }

    public function fitsWithinLimit(string $expression): bool
    {
        return Str::length($expression) <= 4000;
    }
}
