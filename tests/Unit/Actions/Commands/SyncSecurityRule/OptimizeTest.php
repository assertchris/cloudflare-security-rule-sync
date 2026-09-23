<?php

use AC\CloudflareSecurityRuleSync\Actions\Commands\SyncSecurityRule\Optimize;

it('deduplicates identical paths', function () {
    $result = (new Optimize)(collect(['/foo', '/foo', '/bar']));

    expect($result->values()->all())->toBe(['/bar', '/foo']);
});

it('adds a leading slash to paths missing one', function () {
    $result = (new Optimize)(collect(['foo', 'bar']));

    expect($result->contains('/foo'))->toBeTrue();
    expect($result->contains('/bar'))->toBeTrue();
});

it('returns paths sorted', function () {
    $result = (new Optimize)(collect(['/z', '/a', '/m']));

    expect($result->values()->all())->toBe(['/a', '/m', '/z']);
});

it('skips a path already covered by an existing wildcard', function () {
    $result = (new Optimize)(collect(['/storage/*', '/storage/images/photo.jpg']));

    expect($result->contains('/storage/images/photo.jpg'))->toBeFalse();
    expect($result->contains('/storage/*'))->toBeTrue();
});

it('removes specific paths when a covering wildcard is added later', function () {
    $result = (new Optimize)(collect(['/assets/a.js', '/assets/b.css', '/assets/*']));

    expect($result->contains('/assets/a.js'))->toBeFalse();
    expect($result->contains('/assets/b.css'))->toBeFalse();
    expect($result->contains('/assets/*'))->toBeTrue();
});

it('keeps paths not covered by any wildcard', function () {
    $result = (new Optimize)(collect(['/storage/*', '/health']));

    expect($result->contains('/health'))->toBeTrue();
});

it('handles an empty collection', function () {
    $result = (new Optimize)(collect());

    expect($result->isEmpty())->toBeTrue();
});
