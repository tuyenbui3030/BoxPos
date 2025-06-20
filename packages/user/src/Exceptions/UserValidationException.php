<?php

namespace Packages\User\Exceptions;

use Exception;

/**
 * User Validation Exception
 * 
 * Thrown when user data validation fails.
 */
class UserValidationException extends Exception
{
    /**
     * The validation errors
     *
     * @var array
     */
    protected array $errors;

    /**
     * Create a new exception instance.
     *
     * @param array $errors
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(array $errors = [], string $message = 'User validation failed', int $code = 422, \Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the validation errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
