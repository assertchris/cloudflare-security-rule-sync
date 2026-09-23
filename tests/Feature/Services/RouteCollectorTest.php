<?php

use AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector\AppRoutes;
use AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector\FolioRoutes;
use AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector\PublicPaths;
use AC\CloudflareSecurityRuleSync\Services\RouteCollector;

function makeCollector(array $app = [], array $folio = [], array $public = []): RouteCollector
{
    $appRoutes = Mockery::mock(AppRoutes::class);
    $appRoutes->shouldReceive('__invoke')->andReturn(collect($app));

    $folioRoutes = Mockery::mock(FolioRoutes::class);
    $folioRoutes->shouldReceive('__invoke')->andReturn(collect($folio));

    $publicPaths = Mockery::mock(PublicPaths::class);
    $publicPaths->shouldReceive('__invoke')->andReturn($public);

    return new RouteCollector(
        appRoutes: $appRoutes,
        folioRoutes: $folioRoutes,
        publicPaths: $publicPaths,
    );
}

it('merges routes from all three sources', function () {
    $paths = makeCollector(
        app: ['/health'],
        folio: ['/about'],
        public: ['robots.txt'],
    )->collect();

    expect($paths->contains('/health'))->toBeTrue();
    expect($paths->contains('/about'))->toBeTrue();
    expect($paths->contains('/robots.txt'))->toBeTrue();
});

it('normalizes paths to have a leading slash', function () {
    expect(makeCollector(app: ['health'])->collect()->contains('/health'))->toBeTrue();
});

it('replaces route parameters with wildcards', function () {
    expect(makeCollector(app: ['/users/{id}/posts/{postId}'])->collect()->contains('/users/*/posts/*'))->toBeTrue();
});

it('deduplicates paths from multiple sources', function () {
    $paths = makeCollector(app: ['/health'], folio: ['/health'])->collect();

    expect($paths->filter(fn ($p) => $p === '/health')->count())->toBe(1);
});

it('rejects bare wildcard paths including the normalised form', function () {
    expect(makeCollector(app: ['*'])->collect()->isEmpty())->toBeTrue();
});

it('rejects ignorable paths based on config', function () {
    config()->set('cloudflare-security-rule-sync.rule.ignorable_paths', ['/_dusk/*']);

    expect(makeCollector(app: ['/_dusk/browser-test'])->collect()->contains('/_dusk/browser-test'))->toBeFalse();
});

it('includes forced allow paths from config in the collection', function () {
    config()->set('cloudflare-security-rule-sync.rule.forced_allow_paths', ['/forced']);

    expect(makeCollector()->collect()->contains('/forced'))->toBeTrue();
});

it('returns forced allow paths normalised', function () {
    config()->set('cloudflare-security-rule-sync.rule.forced_allow_paths', ['/forced', 'also-forced']);

    expect(makeCollector()->forcedAllowPaths())->toContain('/forced');
    expect(makeCollector()->forcedAllowPaths())->toContain('/also-forced');
});

it('excludes bare slash and wildcard from forced allow paths', function () {
    config()->set('cloudflare-security-rule-sync.rule.forced_allow_paths', ['/', '*']);

    expect(makeCollector()->forcedAllowPaths())->toBeEmpty();
});

it('correctly identifies ignorable paths using wildcards', function () {
    config()->set('cloudflare-security-rule-sync.rule.ignorable_paths', ['/_dusk/*']);

    expect(makeCollector()->isIgnorable('/_dusk/anything'))->toBeTrue();
    expect(makeCollector()->isIgnorable('/health'))->toBeFalse();
});
