<?php

namespace AC\CloudflareSecurityRuleSync\Actions\Http\RulesetClient;

use AC\CloudflareSecurityRuleSync\Exceptions\CloudflareApiException;
use AC\CloudflareSecurityRuleSync\Http\RulesetClient;
use Illuminate\Http\Client\RequestException;

class PutEntrypoint
{
    public function __invoke(RulesetClient $client, string $endpoint, array $rules): array
    {
        try {
            $response = $client->put($endpoint, ['rules' => $rules]);
        } catch (RequestException $e) {
            if ($e->response) {
                $client->handleApiError($e->response, 'Failed to update ruleset entrypoint', $endpoint, 'PUT');
            }

            throw CloudflareApiException::fromResponse($e->response, 'Failed to update ruleset entrypoint', $endpoint, 'PUT');
        }

        return $response->json('result.rules', []);
    }
}
