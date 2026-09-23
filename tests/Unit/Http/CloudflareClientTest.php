<?php

use AC\CloudflareSecurityRuleSync\Http\CloudflareClient;

it('returns the api token', function () {
    $client = new CloudflareClient('token-abc', 'zone-xyz');

    expect($client->getApiToken())->toBe('token-abc');
});

it('returns the default base url', function () {
    $client = new CloudflareClient('token-abc', 'zone-xyz');

    expect($client->getBaseUrl())->toBe('https://api.cloudflare.com/client/v4');
});

it('returns a custom base url from options', function () {
    $client = new CloudflareClient('token-abc', 'zone-xyz', [
        'base_url' => 'https://custom.example.com',
    ]);

    expect($client->getBaseUrl())->toBe('https://custom.example.com');
});

it('returns an option value', function () {
    $client = new CloudflareClient('token-abc', 'zone-xyz', [
        'timeout' => 30,
    ]);

    expect($client->getOption('timeout'))->toBe(30);
});

it('returns the default when an option is missing', function () {
    $client = new CloudflareClient('token-abc', 'zone-xyz');

    expect($client->getOption('missing', 'fallback'))->toBe('fallback');
});
