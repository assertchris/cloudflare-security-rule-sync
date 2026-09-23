<?php

namespace AC\CloudflareSecurityRuleSync\Actions\Http\CloudflareClient;

use AC\CloudflareSecurityRuleSync\Exceptions\CloudflareApiException;
use AC\CloudflareSecurityRuleSync\Http\CloudflareClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

class Get
{
    public function __invoke(CloudflareClient $client, string $endpoint, array $query = []): Response
    {
        try {
            $response = (new MakeHttpClient)($client)->get($endpoint, $query);
        } catch (RequestException $e) {
            if ($e->response) {
                $client->handleApiError($e->response, 'GET request failed', $endpoint, 'GET');
            }

            throw CloudflareApiException::fromResponse($e->response, 'GET request failed', $endpoint, 'GET');
        }

        if (! $response->successful()) {
            $client->handleApiError($response, 'GET request failed', $endpoint, 'GET');
        }

        return $response;
    }
}
