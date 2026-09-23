<?php

namespace AC\CloudflareSecurityRuleSync\Services;

use AC\CloudflareSecurityRuleSync\Http\RulesetClient;

class SecurityRuleSync
{
    public function __construct(
        private readonly RulesetClient $client,
        private readonly string $ruleAction,
    ) {}

    public function sync(string $expression, string $description): void
    {
        $rules = $this->client->getEntrypoint();
        $matchIndex = null;

        foreach ($rules as $i => $rule) {
            if (($rule['description'] ?? null) === $description) {
                $matchIndex = $i;
                break;
            }
        }

        if ($matchIndex !== null) {
            $rules[$matchIndex]['expression'] = $expression;
            $rules[$matchIndex]['action'] = $this->ruleAction;
        } else {
            $rules[] = [
                'action' => $this->ruleAction,
                'description' => $description,
                'enabled' => true,
                'expression' => $expression,
            ];
        }

        $this->client->putEntrypoint($rules);
    }
}
