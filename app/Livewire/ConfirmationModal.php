<?php

namespace App\Livewire;

use Livewire\Component;
use Packages\Log\Traits\Loggable;

class ConfirmationModal extends Component
{
    use Loggable; // ⚠️ MANDATORY: Use Loggable trait
    
    // Modal properties
    public $show = false;
    public $title = 'Confirm Action';
    public $message = 'Are you sure you want to perform this action?';
    public $confirmText = 'Confirm';
    public $cancelText = 'Cancel';
    public $confirmButtonClass = 'btn-danger';
    public $icon = 'warning'; // warning, danger, info, success, question
    public $size = 'modal-sm'; // modal-sm, modal-lg, modal-xl
    
    // Action properties
    public $action = null;
    public $actionParams = [];
    public $componentId = null;
    
    protected $listeners = [
        'show-confirmation' => 'showConfirmation'
    ];

    public function showConfirmation($data)
    {
        $this->show = true;
        $this->title = $data['title'] ?? $this->title;
        $this->message = $data['message'] ?? $this->message;
        $this->confirmText = $data['confirmText'] ?? $this->confirmText;
        $this->cancelText = $data['cancelText'] ?? $this->cancelText;
        $this->confirmButtonClass = $data['confirmButtonClass'] ?? $this->confirmButtonClass;
        $this->icon = $data['icon'] ?? $this->icon;
        $this->size = $data['size'] ?? $this->size;
        $this->action = $data['action'] ?? null;
        $this->actionParams = $data['actionParams'] ?? [];
        $this->componentId = $data['componentId'] ?? null;
        
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('confirmation_modal_shown', [
            'user_id' => auth()->user()?->id,
            'ip_address' => request()->ip(),
            'title' => $this->title,
            'action' => $this->action,
            'component' => 'ConfirmationModal',
        ]);
    }

    public function confirm()
    {
        $this->show = false;
        
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('confirmation_modal_confirmed', [
            'user_id' => auth()->user()?->id,
            'ip_address' => request()->ip(),
            'title' => $this->title,
            'action' => $this->action,
            'component' => 'ConfirmationModal',
        ]);
        
        if ($this->action && $this->componentId) {
            // Dispatch the action to the target component
            $this->dispatch($this->action, ...$this->actionParams)->to($this->componentId);
        } elseif ($this->action) {
            // Dispatch globally if no specific component
            $this->dispatch($this->action, ...$this->actionParams);
        }
        
        $this->reset(['action', 'actionParams', 'componentId']);
    }

    public function cancel()
    {
        $this->show = false;
        
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('confirmation_modal_cancelled', [
            'user_id' => auth()->user()?->id,
            'ip_address' => request()->ip(),
            'title' => $this->title,
            'action' => $this->action,
            'component' => 'ConfirmationModal',
        ]);
        
        $this->reset(['action', 'actionParams', 'componentId']);
    }

    public function getIconSvg()
    {
        return match ($this->icon) {
            'warning' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-warning icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M12 9v2m0 4v.01"/>
                <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.48 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/>
            </svg>',
            'danger' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M12 9v2m0 4v.01"/>
                <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.48 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/>
            </svg>',
            'info' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-info icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <circle cx="12" cy="12" r="9"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
                <polyline points="11,12 12,12 12,16 13,16"/>
            </svg>',
            'success' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-success icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <circle cx="12" cy="12" r="9"/>
                <path d="M9 12l2 2l4 -4"/>
            </svg>',
            'question' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-primary icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <circle cx="12" cy="12" r="9"/>
                <line x1="9.09" y1="9" x2="15" y2="15"/>
                <line x1="15" y1="9" x2="9.09" y2="15"/>
            </svg>',
            default => '<svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-warning icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M12 9v2m0 4v.01"/>
                <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.48 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/>
            </svg>'
        };
    }

    public function getModalStatusClass()
    {
        return match ($this->icon) {
            'warning' => 'bg-warning',
            'danger' => 'bg-danger',
            'info' => 'bg-info',
            'success' => 'bg-success',
            'question' => 'bg-primary',
            default => 'bg-warning'
        };
    }

    public function render()
    {
        return view('livewire.confirmation-modal');
    }
}
