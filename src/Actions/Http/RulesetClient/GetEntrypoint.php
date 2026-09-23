<?php

namespace AC\CloudflareSecurityRuleSync\Actions\Http\RulesetClient;

use AC\CloudflareSecurityRuleSync\Exceptions\CloudflareApiException;
use AC\CloudflareSecurityRuleSync\Exceptions\CloudflareException;
use AC\CloudflareSecurityRuleSync\Http\RulesetClient;
use Illuminate\Http\Client\RequestException;

class GetEntrypoint
{
    public function __invoke(RulesetClient $client, string $endpoint): array
    {
        try {
            $response = $client->get($endpoint);
        } catch (RequestException $e) {
            if ($e->response && $e->response->status() === 404) {
                throw new CloudflareException(
                    'No custom ruleset found on this zone. Create at least one rule manually in the Cloudflare dashboard before syncing.'
                );
            }

            if ($e->response) {
                $client->handleApiError($e->response, 'Failed to fetch ruleset entrypoint', $endpoint, 'GET');
            }

            throw CloudflareApiException::fromResponse($e->response, 'Failed to fetch ruleset entrypoint', $endpoint, 'GET');
        }

        if ($response->status() === 404) {
            throw new CloudflareException(
                'No custom ruleset found on this zone. Create at least one rule manually in the Cloudflare dashboard before syncing.'
            );
        }

        return $response->json('result.rules', []);
    }
}
