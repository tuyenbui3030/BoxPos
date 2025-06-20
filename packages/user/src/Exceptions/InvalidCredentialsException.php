<?php

namespace Packages\User\Exceptions;

use Exception;

/**
 * Invalid Credentials Exception
 * 
 * Thrown when authentication fails due to invalid credentials.
 */
class InvalidCredentialsException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = 'Invalid credentials', int $code = 401, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
