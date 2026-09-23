<?php

use AC\CloudflareSecurityRuleSync\Actions\Commands\SyncSecurityRule\Build;

it('returns empty string for empty collection', function () {
    expect((new Build)(collect()))->toBe('');
});

it('builds expression for exact paths', function () {
    $expression = (new Build)(collect(['/foo', '/bar']));

    expect($expression)->toBe('not (http.request.uri.path in {"/foo" "/bar"})');
});

it('builds expression for a single exact path', function () {
    $expression = (new Build)(collect(['/health']));

    expect($expression)->toBe('not (http.request.uri.path in {"/health"})');
});

it('builds expression for wildcard paths', function () {
    $expression = (new Build)(collect(['/storage/*', '/assets/*']));

    expect($expression)->toBe(
        'not (http.request.uri.path wildcard "/storage/*" or http.request.uri.path wildcard "/assets/*")'
    );
});

it('strips everything after the first wildcard segment', function () {
    $expression = (new Build)(collect(['/foo/*/bar/baz']));

    expect($expression)->toBe('not (http.request.uri.path wildcard "/foo/*")');
});

it('builds combined expression for exact and wildcard paths', function () {
    $expression = (new Build)(collect(['/health', '/storage/*']));

    expect($expression)->toBe(
        'not (http.request.uri.path in {"/health"} or http.request.uri.path wildcard "/storage/*")'
    );
});
