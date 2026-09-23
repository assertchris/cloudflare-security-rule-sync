<?php

use AC\CloudflareSecurityRuleSync\Actions\Http\CloudflareClient\MakeHttpClient;
use AC\CloudflareSecurityRuleSync\Http\CloudflareClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

it('returns a PendingRequest', function () {
    $client = new CloudflareClient('test-token', 'test-zone');
    expect((new MakeHttpClient)($client))->toBeInstanceOf(PendingRequest::class);
});

it('attaches the Bearer token as an authorization header', function () {
    Http::fake(['*' => Http::response([], 200)]);
    $client = new CloudflareClient('my-api-token', 'zone-id');
    (new MakeHttpClient)($client)->get('/test');
    Http::assertSent(fn ($req) => $req->header('Authorization')[0] === 'Bearer my-api-token');
});

it('uses the client base URL', function () {
    Http::fake(['https://example.com/*' => Http::response([], 200)]);
    $client = new CloudflareClient('token', 'zone', ['base_url' => 'https://example.com']);
    (new MakeHttpClient)($client)->get('/endpoint');
    Http::assertSent(fn ($req) => str_starts_with($req->url(), 'https://example.com'));
});
