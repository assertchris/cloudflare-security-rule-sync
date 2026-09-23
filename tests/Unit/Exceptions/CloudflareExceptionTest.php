<?php

use AC\CloudflareSecurityRuleSync\Exceptions\CloudflareException;

it('stores and returns the status code', function () {
    $e = new CloudflareException(statusCode: 503);

    expect($e->getStatusCode())->toBe(503);
});

it('stores and returns the error response', function () {
    $payload = ['errors' => [['message' => 'bad']]];
    $e = new CloudflareException(errorResponse: $payload);

    expect($e->getErrorResponse())->toBe($payload);
});

it('identifies a 401 as an authentication error', function () {
    expect((new CloudflareException(statusCode: 401))->isAuthenticationError())->toBeTrue();
});

it('identifies a 403 as an authentication error', function () {
    expect((new CloudflareException(statusCode: 403))->isAuthenticationError())->toBeTrue();
});

it('does not treat other status codes as authentication errors', function () {
    expect((new CloudflareException(statusCode: 500))->isAuthenticationError())->toBeFalse();
});

it('identifies a 429 as a rate limit error', function () {
    expect((new CloudflareException(statusCode: 429))->isRateLimitError())->toBeTrue();
});

it('does not treat other status codes as rate limit errors', function () {
    expect((new CloudflareException(statusCode: 503))->isRateLimitError())->toBeFalse();
});
