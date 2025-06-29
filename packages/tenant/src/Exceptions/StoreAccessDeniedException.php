<?php

namespace Packages\Tenant\Exceptions;

use Exception;

class StoreAccessDeniedException extends Exception
{
    public function __construct(string $message = 'Access to store is denied')
    {
        parent::__construct($message);
    }
}
