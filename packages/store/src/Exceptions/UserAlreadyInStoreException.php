<?php

namespace Packages\Store\Exceptions;

use Exception;

class UserAlreadyInStoreException extends Exception
{
    public function __construct(string $message = 'User is already in this store')
    {
        parent::__construct($message);
    }
}
