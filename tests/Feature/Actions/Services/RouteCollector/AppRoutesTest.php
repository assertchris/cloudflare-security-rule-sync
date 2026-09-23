<?php

use AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector\AppRoutes;
use Illuminate\Support\Collection;

it('returns a collection of route URIs from the registered routes', function () {
    $this->app['router']->get('/test-health', fn () => 'ok')->name('test.health');
    $result = (new AppRoutes)();
    expect($result)->toBeInstanceOf(Collection::class);
    expect($result->contains('test-health'))->toBeTrue();
});

it('excludes the laravel-folio fallback route', function () {
    $this->app['router']->get('{fallbackPlaceholder}', fn () => 'ok')->name('laravel-folio');
    $result = (new AppRoutes)();
    expect($result->contains('{fallbackPlaceholder}'))->toBeFalse();
});

it('returns a collection even when no routes are registered', function () {
    expect((new AppRoutes)())->toBeInstanceOf(Collection::class);
});
