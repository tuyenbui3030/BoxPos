<?php

namespace Packages\Store\Exceptions;

use Exception;

class UserNotInStoreException extends Exception
{
    public function __construct(string $message = 'User is not in this store')
    {
        parent::__construct($message);
    }
}
