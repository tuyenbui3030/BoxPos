<?php

namespace Packages\Appearance\Traits;

use Packages\Appearance\Services\ConfirmationBuilder;

trait HasConfirmationModal
{
    /**
     * Create a new confirmation builder instance
     * 
     * @return ConfirmationBuilder
     */
    public function confirmation(): ConfirmationBuilder
    {
        return new ConfirmationBuilder($this);
    }
}
