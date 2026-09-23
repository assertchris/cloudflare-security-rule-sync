<?php

namespace AC\CloudflareSecurityRuleSync\Commands;

use AC\CloudflareSecurityRuleSync\Actions\Commands\SyncSecurityRule\BuildRuleExpression;
use AC\CloudflareSecurityRuleSync\Exceptions\CloudflareApiException;
use AC\CloudflareSecurityRuleSync\Exceptions\CloudflareException;
use AC\CloudflareSecurityRuleSync\Services\RouteCollector;
use AC\CloudflareSecurityRuleSync\Services\SecurityRuleSync;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncSecurityRule extends Command
{
    protected $signature = 'cloudflare:sync-security-rule
                            {--sync : Push the generated rule to Cloudflare via the Rulesets API}';

    protected $description = 'Generate a Cloudflare security rule from your application routes';

    public function handle(
        RouteCollector $collector,
        BuildRuleExpression $buildExpression,
    ): int {
        $paths = $collector->collect();

        if ($paths->isEmpty()) {
            $this->error('No routes found. Refusing to push a blank expression that would block all traffic.');

            return self::FAILURE;
        }

        $expression = $buildExpression($paths);

        if (! $buildExpression->fitsWithinLimit($expression)) {
            $this->error('Unable to condense the expression below 4000 characters. Review your routes manually.');

            return self::FAILURE;
        }

        $description = $buildExpression->description();
        $length = Str::length($expression);

        $this->line("Description: {$description}");
        $this->line("Expression ({$length} chars):");
        $this->line($expression);

        if ($this->option('sync')) {
            $apiToken = config('cloudflare-security-rule-sync.api_token');
            $zoneId = config('cloudflare-security-rule-sync.zone_id');

            if (! $apiToken || ! $zoneId) {
                $this->error('CF_API_TOKEN and CF_ZONE_ID must be set to use --sync.');

                return self::FAILURE;
            }

            try {
                /** @var SecurityRuleSync $service */
                $service = app(SecurityRuleSync::class);
                $service->sync($expression, $description);

                $this->newLine();
                $this->info('Rule synced successfully.');
            } catch (CloudflareApiException $e) {
                $this->error('API error: '.$e->getMessage());

                if ($e->isAuthenticationError()) {
                    $this->warn('Check your CF_API_TOKEN has the correct zone permissions and the CF_ZONE_ID is correct.');
                } elseif ($e->isRateLimitError()) {
                    $this->warn('Rate limit exceeded. Wait a moment and try again.');
                }

                return self::FAILURE;
            } catch (CloudflareException $e) {
                $this->error('Cloudflare error: '.$e->getMessage());

                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
