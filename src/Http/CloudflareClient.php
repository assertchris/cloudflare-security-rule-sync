<?php

namespace AC\CloudflareSecurityRuleSync\Http;

use AC\CloudflareSecurityRuleSync\Actions\Http\CloudflareClient\Get;
use AC\CloudflareSecurityRuleSync\Actions\Http\CloudflareClient\MakeHttpClient;
use AC\CloudflareSecurityRuleSync\Actions\Http\CloudflareClient\Put;
use AC\CloudflareSecurityRuleSync\Exceptions\CloudflareApiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class CloudflareClient
{
    protected string $apiToken;

    protected string $zoneId;

    protected string $baseUrl;

    protected array $options;

    public function __construct(string $apiToken, string $zoneId, array $options = [])
    {
        $this->apiToken = $apiToken;
        $this->zoneId = $zoneId;
        $this->baseUrl = $options['base_url'] ?? 'https://api.cloudflare.com/client/v4';
        $this->options = $options;
    }

    public function getApiToken(): string
    {
        return $this->apiToken;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    public function pendingRequest(): PendingRequest
    {
        return (new MakeHttpClient)($this);
    }

    public function get(string $endpoint, array $query = []): Response
    {
        return (new Get)($this, $endpoint, $query);
    }

    public function put(string $endpoint, array $data = []): Response
    {
        return (new Put)($this, $endpoint, $data);
    }

    public function handleApiError(Response $response, string $context, string $endpoint = '', string $method = ''): never
    {
        $statusCode = $response->status();

        if ($statusCode === 401 || $statusCode === 403) {
            throw CloudflareApiException::authenticationFailed($response);
        }

        if ($statusCode === 429) {
            throw CloudflareApiException::rateLimitExceeded($response);
        }

        throw CloudflareApiException::fromResponse($response, $context, $endpoint, $method);
    }
}
