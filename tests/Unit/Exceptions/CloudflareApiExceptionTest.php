<?php

use AC\CloudflareSecurityRuleSync\Exceptions\CloudflareApiException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Client\Response;

function makeResponse(int $status, array $body = [], array $headers = []): Response
{
    return new Response(new GuzzleResponse($status, $headers, json_encode($body)));
}

it('stores and returns the endpoint', function () {
    $e = new CloudflareApiException(endpoint: '/zones/abc/rulesets');

    expect($e->getEndpoint())->toBe('/zones/abc/rulesets');
});

it('stores and returns the method', function () {
    $e = new CloudflareApiException(method: 'PUT');

    expect($e->getMethod())->toBe('PUT');
});

it('builds from a response with error details', function () {
    $response = makeResponse(422, ['errors' => [['message' => 'Invalid expression', 'code' => 10001]]]);
    $e = CloudflareApiException::fromResponse($response, 'Rule update failed', '/zones/abc', 'PUT');

    expect($e->getMessage())->toBe('Rule update failed: Invalid expression (Code: 10001)');
    expect($e->getCode())->toBe(422);
    expect($e->getEndpoint())->toBe('/zones/abc');
    expect($e->getMethod())->toBe('PUT');
});

it('builds from a response without an error code', function () {
    $response = makeResponse(500, ['errors' => [['message' => 'Internal error']]]);
    $e = CloudflareApiException::fromResponse($response, 'Something broke');

    expect($e->getMessage())->toBe('Something broke: Internal error');
});

it('builds an authentication failure exception', function () {
    $response = makeResponse(401, ['errors' => [['message' => 'Invalid token']]]);
    $e = CloudflareApiException::authenticationFailed($response);

    expect($e->getMessage())->toContain('Invalid token');
    expect($e->getCode())->toBe(401);
    expect($e->isAuthenticationError())->toBeTrue();
});

it('builds a rate limit exceeded exception without retry header', function () {
    $response = makeResponse(429, []);
    $e = CloudflareApiException::rateLimitExceeded($response);

    expect($e->getMessage())->toBe('Cloudflare API rate limit exceeded');
    expect($e->isRateLimitError())->toBeTrue();
});

it('builds a rate limit exceeded exception with retry header', function () {
    $response = makeResponse(429, [], ['Retry-After' => ['60']]);
    $e = CloudflareApiException::rateLimitExceeded($response);

    expect($e->getMessage())->toBe('Cloudflare API rate limit exceeded. Retry after 60 seconds');
});
