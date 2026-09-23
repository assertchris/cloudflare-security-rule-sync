<?php

namespace AC\CloudflareSecurityRuleSync\Http;

use AC\CloudflareSecurityRuleSync\Actions\Http\RulesetClient\GetEntrypoint;
use AC\CloudflareSecurityRuleSync\Actions\Http\RulesetClient\PutEntrypoint;

class RulesetClient extends CloudflareClient
{
    private const PHASE = 'http_request_firewall_custom';

    private function endpoint(): string
    {
        return "zones/{$this->zoneId}/rulesets/phases/".self::PHASE.'/entrypoint';
    }

    public function getEntrypoint(): array
    {
        return (new GetEntrypoint)($this, $this->endpoint());
    }

    public function putEntrypoint(array $rules): array
    {
        return (new PutEntrypoint)($this, $this->endpoint(), $rules);
    }
}
