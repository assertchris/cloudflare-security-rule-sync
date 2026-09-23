<?php

use AC\CloudflareSecurityRuleSync\Http\RulesetClient;
use Illuminate\Support\Facades\Http;

it('fetches rules from the correct endpoint', function () {
    Http::fake(['*' => Http::response(['result' => ['rules' => []]], 200)]);
    $client = new RulesetClient('token', 'my-zone');
    $client->getEntrypoint();
    Http::assertSent(
        fn ($req) => str_contains($req->url(), 'zones/my-zone/rulesets/phases/http_request_firewall_custom/entrypoint')
    );
});

it('returns the rules array from getEntrypoint', function () {
    $rules = [['id' => 'r1', 'action' => 'block']];
    Http::fake(['*' => Http::response(['result' => ['rules' => $rules]], 200)]);
    $client = new RulesetClient('token', 'zone');
    expect($client->getEntrypoint())->toBe($rules);
});

it('sends rules to the entrypoint via PUT', function () {
    $rules = [['action' => 'block', 'expression' => 'true']];
    Http::fake(['*' => Http::response(['result' => ['rules' => $rules]], 200)]);
    $client = new RulesetClient('token', 'my-zone');
    $client->putEntrypoint($rules);
    Http::assertSent(
        fn ($req) => $req->method() === 'PUT' &&
            str_contains($req->url(), 'zones/my-zone/rulesets/phases/http_request_firewall_custom/entrypoint') &&
            $req->data()['rules'] === $rules
    );
});

it('returns the updated rules from putEntrypoint', function () {
    $rules = [['id' => 'r1', 'action' => 'block']];
    Http::fake(['*' => Http::response(['result' => ['rules' => $rules]], 200)]);
    $client = new RulesetClient('token', 'zone');
    expect($client->putEntrypoint($rules))->toBe($rules);
});
