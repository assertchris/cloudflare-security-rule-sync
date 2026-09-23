<?php

return [
    'api_token' => env('CF_API_TOKEN'), // required for --sync
    'zone_id' => env('CF_ZONE_ID'),     // required for --sync

    'rule' => [
        // Must match the `description` field on the Cloudflare rule object.
        // Used to find-and-update vs append-new.
        'description' => env('CF_RULE_DESCRIPTION', 'cloudflare-security-rule-sync'),

        // Valid values: block | challenge | js_challenge | managed_challenge | log | bypass
        'action' => env('CF_RULE_ACTION', 'block'),

        // Paths excluded from the allowlist even if present in routes. Supports wildcards.
        'ignorable_paths' => ['/_dusk/*', '/_boost/*'],

        // Paths always added to the allowlist even if not in routes. Supports wildcards.
        'forced_allow_paths' => [],

        // When non-empty, wraps the expression with an http.host check.
        'hostnames' => [],
    ],
];
