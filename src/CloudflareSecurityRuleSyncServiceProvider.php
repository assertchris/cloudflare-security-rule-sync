<?php

namespace AC\CloudflareSecurityRuleSync;

use AC\CloudflareSecurityRuleSync\Actions\Commands\SyncSecurityRule\BuildRuleExpression;
use AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector\AppRoutes;
use AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector\FolioRoutes;
use AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector\PublicPaths;
use AC\CloudflareSecurityRuleSync\Commands\SyncSecurityRule;
use AC\CloudflareSecurityRuleSync\Http\RulesetClient;
use AC\CloudflareSecurityRuleSync\Services\RouteCollector;
use AC\CloudflareSecurityRuleSync\Services\SecurityRuleSync;
use Illuminate\Support\ServiceProvider;

class CloudflareSecurityRuleSyncServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cloudflare-security-rule-sync.php', 'cloudflare-security-rule-sync');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/cloudflare-security-rule-sync.php' => config_path('cloudflare-security-rule-sync.php'),
            ], 'cloudflare-security-rule-sync-config');
        }

        $this->app->singleton(RulesetClient::class, function ($app) {
            return new RulesetClient(
                apiToken: config('cloudflare-security-rule-sync.api_token', ''),
                zoneId: config('cloudflare-security-rule-sync.zone_id', ''),
            );
        });

        $this->app->singleton(SecurityRuleSync::class, function ($app) {
            return new SecurityRuleSync(
                client: $app->make(RulesetClient::class),
                ruleAction: config('cloudflare-security-rule-sync.rule.action', 'block'),
            );
        });

        $this->app->singleton(BuildRuleExpression::class);
        $this->app->singleton(AppRoutes::class);
        $this->app->singleton(FolioRoutes::class);
        $this->app->singleton(PublicPaths::class);
        $this->app->singleton(RouteCollector::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([SyncSecurityRule::class]);
        }
    }
}
