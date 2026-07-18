<?php

namespace App\Services\Groq;

use RuntimeException;

class GroqRateLimitException extends RuntimeException
{
    public function __construct(string $message, public readonly int $retryAfter = 30)
    {
        parent::__construct($message, 429);
    }
}
