<?php

namespace Packages\Appearance\Services;

/**
 * Confirmation Builder - Fluent interface for building confirmation modals
 * 
 * Usage example:
 * $this->confirmation()
 *     ->title('Remove Device')
 *     ->message("Are you sure you want to remove the device '{$device->device_name}'?")
 *     ->danger()
 *     ->action('removeDevice', [$deviceId])
 *     ->confirmText('Remove Device')
 *     ->show();
 */
class ConfirmationBuilder
{
    protected $component;
    protected $title = '';
    protected $message = '';
    protected $action = '';
    protected $actionParams = [];
    protected $confirmText = 'Confirm';
    protected $cancelText = 'Cancel';
    protected $confirmButtonClass = 'btn-primary';
    protected $icon = 'info';
    protected $size = 'modal-sm';

    public function __construct($component)
    {
        $this->component = $component;
    }

    /**
     * Set the modal title
     */
    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Set the modal message
     */
    public function message(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Set the action to be called when confirmed
     */
    public function action(string $action, array $params = []): self
    {
        $this->action = $action;
        $this->actionParams = $params;
        return $this;
    }

    /**
     * Set custom confirm button text
     */
    public function confirmText(string $text): self
    {
        $this->confirmText = $text;
        return $this;
    }

    /**
     * Set custom cancel button text
     */
    public function cancelText(string $text): self
    {
        $this->cancelText = $text;
        return $this;
    }

    /**
     * Make this a danger confirmation (red button, danger icon)
     */
    public function danger(): self
    {
        $this->icon = 'danger';
        $this->confirmButtonClass = 'btn-danger';
        if ($this->confirmText === 'Confirm') {
            $this->confirmText = 'Delete';
        }
        return $this;
    }

    /**
     * Make this a logout confirmation (red button, logout icon)
     */
    public function logout(): self
    {
        $this->icon = 'logout';
        $this->confirmButtonClass = 'btn-danger';
        $this->confirmText = 'Logout';
        return $this;
    }

    /**
     * Make this a warning confirmation (yellow button, warning icon)
     */
    public function warning(): self
    {
        $this->icon = 'warning';
        $this->confirmButtonClass = 'btn-warning';
        if ($this->confirmText === 'Confirm') {
            $this->confirmText = 'Continue';
        }
        return $this;
    }

    /**
     * Make this an info confirmation (blue button, info icon)
     */
    public function info(): self
    {
        $this->icon = 'info';
        $this->confirmButtonClass = 'btn-primary';
        $this->confirmText = 'Confirm';
        return $this;
    }

    /**
     * Set modal size
     */
    public function size(string $size): self
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Make this a small modal
     */
    public function small(): self
    {
        $this->size = 'modal-sm';
        return $this;
    }

    /**
     * Make this a large modal
     */
    public function large(): self
    {
        $this->size = 'modal-lg';
        return $this;
    }

    /**
     * Make this an extra large modal
     */
    public function extraLarge(): self
    {
        $this->size = 'modal-xl';
        return $this;
    }

    /**
     * Set custom button class
     */
    public function buttonClass(string $class): self
    {
        $this->confirmButtonClass = $class;
        return $this;
    }

    /**
     * Set custom icon
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Show the confirmation modal
     */
    public function show(): void
    {
        $data = [
            'title' => $this->title,
            'message' => $this->message,
            'action' => $this->action,
            'actionParams' => $this->actionParams,
            'componentId' => get_class($this->component),
            'confirmText' => $this->confirmText,
            'cancelText' => $this->cancelText,
            'confirmButtonClass' => $this->confirmButtonClass,
            'icon' => $this->icon,
            'size' => $this->size,
        ];

        $this->component->dispatch('show-confirmation', $data);
    }

    /**
     * Quick danger confirmation shortcut
     */
    public static function dangerConfirmation($component, string $title, string $message, string $action, array $params = []): self
    {
        return (new static($component))
            ->title($title)
            ->message($message)
            ->action($action, $params)
            ->danger();
    }

    /**
     * Quick warning confirmation shortcut
     */
    public static function warningConfirmation($component, string $title, string $message, string $action, array $params = []): self
    {
        return (new static($component))
            ->title($title)
            ->message($message)
            ->action($action, $params)
            ->warning();
    }

    /**
     * Quick info confirmation shortcut
     */
    public static function infoConfirmation($component, string $title, string $message, string $action, array $params = []): self
    {
        return (new static($component))
            ->title($title)
            ->message($message)
            ->action($action, $params)
            ->info();
    }
}
