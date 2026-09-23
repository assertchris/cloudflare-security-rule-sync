<?php

use AC\CloudflareSecurityRuleSync\Actions\Commands\SyncSecurityRule\BuildRuleExpression;
use AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector\AppRoutes;
use AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector\FolioRoutes;
use AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector\PublicPaths;
use AC\CloudflareSecurityRuleSync\Http\RulesetClient;
use AC\CloudflareSecurityRuleSync\Services\RouteCollector;
use AC\CloudflareSecurityRuleSync\Services\SecurityRuleSync;

it('registers RulesetClient as a singleton', function () {
    expect(app(RulesetClient::class))->toBe(app(RulesetClient::class));
});

it('registers SecurityRuleSync as a singleton', function () {
    expect(app(SecurityRuleSync::class))->toBe(app(SecurityRuleSync::class));
});

it('registers RouteCollector as a singleton', function () {
    expect(app(RouteCollector::class))->toBe(app(RouteCollector::class));
});

it('registers BuildRuleExpression as a singleton', function () {
    expect(app(BuildRuleExpression::class))->toBe(app(BuildRuleExpression::class));
});

it('registers AppRoutes as a singleton', function () {
    expect(app(AppRoutes::class))->toBe(app(AppRoutes::class));
});

it('registers FolioRoutes as a singleton', function () {
    expect(app(FolioRoutes::class))->toBe(app(FolioRoutes::class));
});

it('registers PublicPaths as a singleton', function () {
    expect(app(PublicPaths::class))->toBe(app(PublicPaths::class));
});

it('merges the package configuration', function () {
    expect(config('cloudflare-security-rule-sync'))->toBeArray();
    expect(config('cloudflare-security-rule-sync.rule.action'))->toBe('block');
});
