<?php

use AC\CloudflareSecurityRuleSync\Actions\Http\CloudflareClient\Put;
use AC\CloudflareSecurityRuleSync\Exceptions\CloudflareApiException;
use AC\CloudflareSecurityRuleSync\Http\CloudflareClient;
use Illuminate\Support\Facades\Http;

it('returns the response on a successful PUT', function () {
    Http::fake(['*' => Http::response(['result' => ['id' => 'xyz']], 200)]);
    $client = new CloudflareClient('token', 'zone');
    $response = (new Put)($client, '/endpoint', ['key' => 'val']);
    expect($response->json('result.id'))->toBe('xyz');
});

it('sends data in the PUT request body', function () {
    Http::fake(['*' => Http::response([], 200)]);
    $client = new CloudflareClient('token', 'zone');
    (new Put)($client, '/endpoint', ['rules' => []]);
    Http::assertSent(fn ($req) => $req->data()['rules'] === []);
});

it('throws CloudflareApiException on 401', function () {
    Http::fake(['*' => Http::response([], 401)]);
    $client = new CloudflareClient('token', 'zone');
    expect(fn () => (new Put)($client, '/endpoint', []))->toThrow(CloudflareApiException::class);
});

it('throws CloudflareApiException on 403', function () {
    Http::fake(['*' => Http::response([], 403)]);
    $client = new CloudflareClient('token', 'zone');
    expect(fn () => (new Put)($client, '/endpoint', []))->toThrow(CloudflareApiException::class);
});

it('throws CloudflareApiException on 500', function () {
    Http::fake(['*' => Http::response([], 500)]);
    $client = new CloudflareClient('token', 'zone');
    expect(fn () => (new Put)($client, '/endpoint', []))->toThrow(CloudflareApiException::class);
});
