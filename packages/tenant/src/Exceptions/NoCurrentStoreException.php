<?php

namespace Packages\Tenant\Exceptions;

use Exception;

class NoCurrentStoreException extends Exception
{
    public function __construct(string $message = 'No current store is set for the user')
    {
        parent::__construct($message);
    }
}
