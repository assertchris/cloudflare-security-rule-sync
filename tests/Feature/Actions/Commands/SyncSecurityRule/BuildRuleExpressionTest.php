<?php

use AC\CloudflareSecurityRuleSync\Actions\Commands\SyncSecurityRule\BuildRuleExpression;

it('returns a plain expression when no hostnames are configured', function () {
    $expression = (new BuildRuleExpression)(collect(['/health', '/api/*']));

    expect($expression)->toStartWith('not (');
    expect($expression)->not->toContain('http.host');
});

it('wraps the expression with a hostname check when hostnames are configured', function () {
    config()->set('cloudflare-security-rule-sync.rule.hostnames', ['api.example.com']);

    $expression = (new BuildRuleExpression)(collect(['/health']));

    expect($expression)->toStartWith('(http.host in {"api.example.com"}) and (not (');
});

it('wraps multiple hostnames as a space-separated set', function () {
    config()->set('cloudflare-security-rule-sync.rule.hostnames', ['a.example.com', 'b.example.com']);

    $expression = (new BuildRuleExpression)(collect(['/health']));

    expect($expression)->toContain('http.host in {"a.example.com" "b.example.com"}');
});

it('returns the base description when no hostnames are set', function () {
    config()->set('cloudflare-security-rule-sync.rule.description', 'my-rule');

    expect((new BuildRuleExpression)->description())->toBe('my-rule');
});

it('appends the first hostname to the description when hostnames are set', function () {
    config()->set('cloudflare-security-rule-sync.rule.description', 'my-rule');
    config()->set('cloudflare-security-rule-sync.rule.hostnames', ['api.example.com']);

    expect((new BuildRuleExpression)->description())->toBe('my-rule:api.example.com');
});

it('reports an expression within the limit as fitting', function () {
    expect((new BuildRuleExpression)->fitsWithinLimit(str_repeat('x', 4000)))->toBeTrue();
});

it('reports an expression over the limit as not fitting', function () {
    expect((new BuildRuleExpression)->fitsWithinLimit(str_repeat('x', 4001)))->toBeFalse();
});
