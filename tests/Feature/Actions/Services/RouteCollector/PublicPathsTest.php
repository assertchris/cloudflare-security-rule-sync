<?php

use AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector\PublicPaths;
use Illuminate\Support\Facades\File;

it('returns file paths relative to the public directory', function () {
    File::shouldReceive('glob')->andReturn([
        '/var/app/public/robots.txt',
        '/var/app/public/favicon.ico',
    ]);
    File::shouldReceive('isDirectory')->andReturn(false);

    $result = (new PublicPaths)();
    expect($result)->toContain('robots.txt');
    expect($result)->toContain('favicon.ico');
});

it('appends a wildcard to directory entries', function () {
    File::shouldReceive('glob')->andReturn(['/var/app/public/assets']);
    File::shouldReceive('isDirectory')->andReturn(true);

    expect((new PublicPaths)())->toContain('assets/*');
});

it('excludes .htaccess from results', function () {
    File::shouldReceive('glob')->andReturn(['/var/app/public/.htaccess']);
    File::shouldReceive('isDirectory')->andReturn(false);

    expect((new PublicPaths)())->not->toContain('.htaccess');
});

it('excludes index.php from results', function () {
    File::shouldReceive('glob')->andReturn(['/var/app/public/index.php']);
    File::shouldReceive('isDirectory')->andReturn(false);

    expect((new PublicPaths)())->not->toContain('index.php');
});

it('returns an empty array when the public directory is empty', function () {
    File::shouldReceive('glob')->andReturn([]);

    expect((new PublicPaths)())->toBeEmpty();
});
