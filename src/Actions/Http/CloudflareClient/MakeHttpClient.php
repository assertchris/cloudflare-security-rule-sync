<?php

namespace AC\CloudflareSecurityRuleSync\Actions\Http\CloudflareClient;

use AC\CloudflareSecurityRuleSync\Http\CloudflareClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class MakeHttpClient
{
    public function __invoke(CloudflareClient $client): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer '.$client->getApiToken(),
            'Content-Type' => 'application/json',
        ])
            ->baseUrl($client->getBaseUrl())
            ->timeout($client->getOption('timeout', 30))
            ->retry(
                $client->getOption('retry_attempts', 3),
                $client->getOption('retry_delay', 1000)
            );
    }
}
