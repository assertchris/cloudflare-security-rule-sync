<?php

use AC\CloudflareSecurityRuleSync\Actions\Commands\SyncSecurityRule\Condense;

it('handles an empty collection', function () {
    $result = (new Condense)(collect());

    expect($result->isEmpty())->toBeTrue();
});

it('passes through a single path unchanged', function () {
    $result = (new Condense)(collect(['/health']));

    expect($result->values()->all())->toBe(['/health']);
});

it('condenses deep paths sharing a two-segment prefix to a wildcard', function () {
    $result = (new Condense)(collect(['/api/users/active', '/api/users/inactive', '/api/users/pending']));

    expect($result->contains('/api/users/*'))->toBeTrue();
    expect($result->contains('/api/users/active'))->toBeFalse();
});

it('keeps a path that is the only one in its prefix group', function () {
    $result = (new Condense)(collect(['/health', '/api/users', '/api/posts']));

    expect($result->contains('/health'))->toBeTrue();
});

it('condenses ending wildcard groups to their longest common prefix', function () {
    $result = (new Condense)(collect(['/storage/images/*', '/storage/videos/*']));

    expect($result->contains('/storage/*'))->toBeTrue();
});

it('removes paths already covered by a condensed ending wildcard', function () {
    $result = (new Condense)(collect(['/storage/*', '/storage/images/photo.jpg']));

    expect($result->contains('/storage/images/photo.jpg'))->toBeFalse();
    expect($result->contains('/storage/*'))->toBeTrue();
});

it('deduplicates and sorts the result', function () {
    $result = (new Condense)(collect(['/b', '/a', '/b']));

    expect($result->values()->all())->toBe(['/a', '/b']);
});
