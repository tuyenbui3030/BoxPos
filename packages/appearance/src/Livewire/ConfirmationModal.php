<?php

namespace Packages\Appearance\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class ConfirmationModal extends Component
{
    public $show = false;
    public $title = '';
    public $message = '';
    public $action = '';
    public $actionParams = [];
    public $componentId = '';
    public $confirmText = 'Confirm';
    public $cancelText = 'Cancel';
    public $confirmButtonClass = 'btn-danger';
    public $icon = 'warning';
    public $size = 'modal-sm';

    #[On('show-confirmation')]
    public function showConfirmation($data)
    {
        $this->title = $data['title'] ?? '';
        $this->message = $data['message'] ?? '';
        $this->action = $data['action'] ?? '';
        $this->actionParams = $data['actionParams'] ?? [];
        $this->componentId = $data['componentId'] ?? '';
        $this->confirmText = $data['confirmText'] ?? 'Confirm';
        $this->cancelText = $data['cancelText'] ?? 'Cancel';
        $this->confirmButtonClass = $data['confirmButtonClass'] ?? 'btn-danger';
        $this->icon = $data['icon'] ?? 'warning';
        $this->size = $data['size'] ?? 'modal-sm';
        
        $this->show = true;
    }

    public function confirm()
    {
        if ($this->action && $this->componentId) {
            // Dispatch to the specific component that triggered the confirmation
            $this->dispatch($this->action, ...$this->actionParams)->to($this->componentId);
        }
        
        $this->hide();
    }

    public function cancel()
    {
        $this->hide();
    }

    public function hide()
    {
        $this->show = false;
        $this->reset();
    }

    public function render()
    {
        return view('appearance::livewire.confirmation-modal');
    }
}
