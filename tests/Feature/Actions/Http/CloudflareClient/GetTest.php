<?php

use AC\CloudflareSecurityRuleSync\Actions\Http\CloudflareClient\Get;
use AC\CloudflareSecurityRuleSync\Exceptions\CloudflareApiException;
use AC\CloudflareSecurityRuleSync\Http\CloudflareClient;
use Illuminate\Support\Facades\Http;

it('returns the response on a successful GET', function () {
    Http::fake(['*' => Http::response(['result' => ['id' => 'abc']], 200)]);
    $client = new CloudflareClient('token', 'zone');
    $response = (new Get)($client, '/endpoint');
    expect($response->json('result.id'))->toBe('abc');
});

it('sends query parameters with the GET request', function () {
    Http::fake(['*' => Http::response([], 200)]);
    $client = new CloudflareClient('token', 'zone');
    (new Get)($client, '/endpoint', ['page' => 2]);
    Http::assertSent(fn ($req) => str_contains($req->url(), 'page=2'));
});

it('throws CloudflareApiException on 401', function () {
    Http::fake(['*' => Http::response([], 401)]);
    $client = new CloudflareClient('token', 'zone');
    expect(fn () => (new Get)($client, '/endpoint'))->toThrow(CloudflareApiException::class);
});

it('throws CloudflareApiException on 403', function () {
    Http::fake(['*' => Http::response([], 403)]);
    $client = new CloudflareClient('token', 'zone');
    expect(fn () => (new Get)($client, '/endpoint'))->toThrow(CloudflareApiException::class);
});

it('throws CloudflareApiException on 429', function () {
    Http::fake(['*' => Http::response([], 429)]);
    $client = new CloudflareClient('token', 'zone');
    expect(fn () => (new Get)($client, '/endpoint'))->toThrow(CloudflareApiException::class);
});

it('throws CloudflareApiException on 500', function () {
    Http::fake(['*' => Http::response([], 500)]);
    $client = new CloudflareClient('token', 'zone');
    expect(fn () => (new Get)($client, '/endpoint'))->toThrow(CloudflareApiException::class);
});
