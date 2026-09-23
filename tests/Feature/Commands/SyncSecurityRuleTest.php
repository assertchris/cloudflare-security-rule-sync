<?php

use AC\CloudflareSecurityRuleSync\Services\RouteCollector;

it('fails when no routes are collected', function () {
    $this->mock(RouteCollector::class)
        ->shouldReceive('collect')
        ->andReturn(collect());

    $this->artisan('cloudflare:sync-security-rule')
        ->assertFailed()
        ->expectsOutput('No routes found. Refusing to push a blank expression that would block all traffic.');
});

it('succeeds and prints the expression when routes are found', function () {
    $this->mock(RouteCollector::class)
        ->shouldReceive('collect')
        ->andReturn(collect(['/health', '/api/*']));

    $this->artisan('cloudflare:sync-security-rule')
        ->assertSuccessful()
        ->expectsOutputToContain('Expression');
});

it('fails when --sync is passed but api_token is not configured', function () {
    $this->mock(RouteCollector::class)
        ->shouldReceive('collect')
        ->andReturn(collect(['/health']));

    config()->set('cloudflare-security-rule-sync.api_token', '');
    config()->set('cloudflare-security-rule-sync.zone_id', '');

    $this->artisan('cloudflare:sync-security-rule --sync')
        ->assertFailed()
        ->expectsOutput('CF_API_TOKEN and CF_ZONE_ID must be set to use --sync.');
});
