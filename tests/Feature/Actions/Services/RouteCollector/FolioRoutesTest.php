<?php

use AC\CloudflareSecurityRuleSync\Actions\Services\RouteCollector\FolioRoutes;

it('returns an empty collection when FolioManager is not installed', function () {
    expect(class_exists('Laravel\\Folio\\FolioManager'))->toBeFalse();
    expect((new FolioRoutes)()->isEmpty())->toBeTrue();
});
