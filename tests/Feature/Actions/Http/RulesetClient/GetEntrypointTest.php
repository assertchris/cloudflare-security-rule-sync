<?php

use AC\CloudflareSecurityRuleSync\Actions\Http\RulesetClient\GetEntrypoint;
use AC\CloudflareSecurityRuleSync\Exceptions\CloudflareApiException;
use AC\CloudflareSecurityRuleSync\Http\RulesetClient;
use Illuminate\Support\Facades\Http;

it('returns rules from a successful GET', function () {
    $rules = [['id' => 'rule1', 'action' => 'block']];
    Http::fake(['*' => Http::response(['result' => ['rules' => $rules]], 200)]);
    $client = new RulesetClient('token', 'zone');
    expect((new GetEntrypoint)($client, '/endpoint'))->toBe($rules);
});

it('returns an empty array when result has no rules key', function () {
    Http::fake(['*' => Http::response(['result' => []], 200)]);
    $client = new RulesetClient('token', 'zone');
    expect((new GetEntrypoint)($client, '/endpoint'))->toBe([]);
});

it('throws CloudflareApiException on a server error', function () {
    Http::fake(['*' => Http::response([], 500)]);
    $client = new RulesetClient('token', 'zone');
    expect(fn () => (new GetEntrypoint)($client, '/endpoint'))->toThrow(CloudflareApiException::class);
});

it('throws CloudflareApiException on 401', function () {
    Http::fake(['*' => Http::response([], 401)]);
    $client = new RulesetClient('token', 'zone');
    expect(fn () => (new GetEntrypoint)($client, '/endpoint'))->toThrow(CloudflareApiException::class);
});
