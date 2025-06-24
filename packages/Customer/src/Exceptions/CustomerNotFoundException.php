<?php

namespace Packages\Customer\Exceptions;

use Exception;

/**
 * Customer Not Found Exception
 * 
 * Thrown when a requested customer cannot be found.
 */
class CustomerNotFoundException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = 'Customer not found', int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
