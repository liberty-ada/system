<?php

declare(strict_types=1);

namespace Liberty\System\Exception;

use Throwable;

/**
 * Class ErrorException
 */
class ErrorException extends SystemException
{
    private readonly array $errors;

    /**
     * Constructs ErrorException
     */
    public function __construct(string $message = '', array $errors = [], int $code = 0, Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Retrieves the errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
