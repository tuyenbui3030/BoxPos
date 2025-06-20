<?php

namespace Packages\User\Exceptions;

use Exception;

/**
 * User Not Found Exception
 * 
 * Thrown when a requested user cannot be found.
 */
class UserNotFoundException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = 'User not found', int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
