<?php

namespace AC\CloudflareSecurityRuleSync\Tests;

use AC\CloudflareSecurityRuleSync\CloudflareSecurityRuleSyncServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            CloudflareSecurityRuleSyncServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cloudflare-security-rule-sync', [
            'api_token' => '',
            'zone_id' => '',
            'rule' => [
                'description' => 'cloudflare-security-rule-sync',
                'action' => 'block',
                'ignorable_paths' => ['/_dusk/*'],
                'forced_allow_paths' => [],
                'hostnames' => [],
            ],
        ]);
    }
}
