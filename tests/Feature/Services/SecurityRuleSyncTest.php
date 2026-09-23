<?php

use AC\CloudflareSecurityRuleSync\Http\RulesetClient;
use AC\CloudflareSecurityRuleSync\Services\SecurityRuleSync;

it('adds a new rule when no matching description exists', function () {
    $client = Mockery::mock(RulesetClient::class);
    $client->shouldReceive('getEntrypoint')->andReturn([]);
    $client->shouldReceive('putEntrypoint')->once()->with([
        [
            'action' => 'block',
            'description' => 'my-rule',
            'enabled' => true,
            'expression' => 'http.request.uri.path in {"/health"}',
        ],
    ]);

    $sync = new SecurityRuleSync($client, 'block');
    $sync->sync('http.request.uri.path in {"/health"}', 'my-rule');
});

it('updates the expression on an existing rule that matches the description', function () {
    $existing = [
        ['action' => 'block', 'description' => 'my-rule', 'expression' => 'old expression'],
        ['action' => 'block', 'description' => 'other-rule', 'expression' => 'other'],
    ];
    $client = Mockery::mock(RulesetClient::class);
    $client->shouldReceive('getEntrypoint')->andReturn($existing);
    $client->shouldReceive('putEntrypoint')->once()->with([
        ['action' => 'block', 'description' => 'my-rule', 'expression' => 'new expression'],
        ['action' => 'block', 'description' => 'other-rule', 'expression' => 'other'],
    ]);

    (new SecurityRuleSync($client, 'block'))->sync('new expression', 'my-rule');
});

it('updates the action on an existing rule', function () {
    $existing = [['action' => 'log', 'description' => 'my-rule', 'expression' => 'expr']];
    $client = Mockery::mock(RulesetClient::class);
    $client->shouldReceive('getEntrypoint')->andReturn($existing);
    $client->shouldReceive('putEntrypoint')->once()->with([
        ['action' => 'block', 'description' => 'my-rule', 'expression' => 'expr'],
    ]);

    (new SecurityRuleSync($client, 'block'))->sync('expr', 'my-rule');
});

it('preserves unrelated rules when updating', function () {
    $existing = [
        ['action' => 'block', 'description' => 'keep-me', 'expression' => 'keep'],
        ['action' => 'block', 'description' => 'target', 'expression' => 'old'],
    ];
    $client = Mockery::mock(RulesetClient::class);
    $client->shouldReceive('getEntrypoint')->andReturn($existing);
    $client->shouldReceive('putEntrypoint')->once()->with(Mockery::on(function ($rules) {
        return count($rules) === 2 &&
            $rules[0]['description'] === 'keep-me' &&
            $rules[0]['expression'] === 'keep';
    }));

    (new SecurityRuleSync($client, 'block'))->sync('new', 'target');
});
