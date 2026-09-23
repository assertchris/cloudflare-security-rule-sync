<?php

namespace AC\CloudflareSecurityRuleSync\Exceptions;

use RuntimeException;

class CloudflareException extends RuntimeException
{
    protected ?int $statusCode;

    protected ?array $errorResponse;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        ?int $statusCode = null,
        ?array $errorResponse = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
        $this->errorResponse = $errorResponse;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getErrorResponse(): ?array
    {
        return $this->errorResponse;
    }

    public function isAuthenticationError(): bool
    {
        return in_array($this->statusCode, [401, 403]);
    }

    public function isRateLimitError(): bool
    {
        return $this->statusCode === 429;
    }
}
