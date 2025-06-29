<?php

namespace Packages\Store\Exceptions;

use Exception;

class StoreNotFoundException extends Exception
{
    public function __construct(string $message = 'Store not found')
    {
        parent::__construct($message);
    }
}
