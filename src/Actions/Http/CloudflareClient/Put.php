<?php

namespace AC\CloudflareSecurityRuleSync\Actions\Http\CloudflareClient;

use AC\CloudflareSecurityRuleSync\Exceptions\CloudflareApiException;
use AC\CloudflareSecurityRuleSync\Http\CloudflareClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

class Put
{
    public function __invoke(CloudflareClient $client, string $endpoint, array $data = []): Response
    {
        try {
            $response = (new MakeHttpClient)($client)->put($endpoint, $data);
        } catch (RequestException $e) {
            if ($e->response) {
                $client->handleApiError($e->response, 'PUT request failed', $endpoint, 'PUT');
            }

            throw CloudflareApiException::fromResponse($e->response, 'PUT request failed', $endpoint, 'PUT');
        }

        if (! $response->successful()) {
            $client->handleApiError($response, 'PUT request failed', $endpoint, 'PUT');
        }

        return $response;
    }
}
