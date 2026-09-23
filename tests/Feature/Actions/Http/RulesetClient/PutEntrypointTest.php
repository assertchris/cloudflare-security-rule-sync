<?php

use AC\CloudflareSecurityRuleSync\Actions\Http\RulesetClient\PutEntrypoint;
use AC\CloudflareSecurityRuleSync\Exceptions\CloudflareApiException;
use AC\CloudflareSecurityRuleSync\Http\RulesetClient;
use Illuminate\Support\Facades\Http;

it('returns the updated rules from a successful PUT', function () {
    $rules = [['id' => 'rule1', 'action' => 'block']];
    Http::fake(['*' => Http::response(['result' => ['rules' => $rules]], 200)]);
    $client = new RulesetClient('token', 'zone');
    expect((new PutEntrypoint)($client, '/endpoint', $rules))->toBe($rules);
});

it('sends the rules array in the request body', function () {
    Http::fake(['*' => Http::response(['result' => ['rules' => []]], 200)]);
    $client = new RulesetClient('token', 'zone');
    (new PutEntrypoint)($client, '/endpoint', [['action' => 'block']]);
    Http::assertSent(fn ($req) => $req->data()['rules'][0]['action'] === 'block');
});

it('throws CloudflareApiException on a server error', function () {
    Http::fake(['*' => Http::response([], 500)]);
    $client = new RulesetClient('token', 'zone');
    expect(fn () => (new PutEntrypoint)($client, '/endpoint', []))->toThrow(CloudflareApiException::class);
});

it('throws CloudflareApiException on 401', function () {
    Http::fake(['*' => Http::response([], 401)]);
    $client = new RulesetClient('token', 'zone');
    expect(fn () => (new PutEntrypoint)($client, '/endpoint', []))->toThrow(CloudflareApiException::class);
});
