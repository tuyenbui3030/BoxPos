<?php

namespace App\Traits;

trait HandlesLocaleUpdates
{
    /**
     * Handle locale update event
     */
    public function handleLocaleUpdate($locale)
    {
        // Update the application locale
        app()->setLocale($locale);
        
        // Force re-render of this component by calling $refresh
        $this->js('$wire.$refresh()');
    }

    /**
     * Boot method to ensure the listener is added
     */
    public function bootHandlesLocaleUpdates()
    {
        // This method will be called automatically when the trait is used
        // We ensure the listener is added to the component's listeners
        if (!isset($this->listeners)) {
            $this->listeners = [];
        }
        
        if (is_array($this->listeners)) {
            $this->listeners['locale-updated'] = 'handleLocaleUpdate';
        }
    }
}
