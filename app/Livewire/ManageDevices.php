<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Packages\User\Models\UserDevice;

class ManageDevices extends Component
{
    public $showConfirmModal = false;
    public $deviceToRemove = null;

    public function trustDevice($deviceId)
    {
        $device = auth()->user()->devices()->findOrFail($deviceId);
        $device->update(['is_trusted' => true]);
        
        session()->flash('success', 'Device marked as trusted.');
    }

    public function untrustDevice($deviceId)
    {
        $device = auth()->user()->devices()->findOrFail($deviceId);
        $device->update(['is_trusted' => false]);
        
        session()->flash('success', 'Device untrusted.');
    }

    public function confirmRemoveDevice($deviceId)
    {
        $this->deviceToRemove = $deviceId;
        $this->showConfirmModal = true;
    }

    public function removeDevice()
    {
        if ($this->deviceToRemove) {
            $device = auth()->user()->devices()->findOrFail($this->deviceToRemove);
            
            // Don't allow removing the current device
            if ($device->is_current_device) {
                session()->flash('error', 'You cannot remove the current device.');
                $this->closeModal();
                return;
            }
            
            $device->delete();
            session()->flash('success', 'Device removed successfully.');
        }
        
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showConfirmModal = false;
        $this->deviceToRemove = null;
    }

    public function revokeAllOtherDevices()
    {
        $currentDevice = auth()->user()->devices()
            ->where('ip_address', request()->ip())
            ->where('user_agent', request()->userAgent())
            ->first();

        $count = auth()->user()->devices()
            ->when($currentDevice, fn($query) => $query->where('id', '!=', $currentDevice->id))
            ->delete();

        session()->flash('success', "Revoked access for {$count} other devices.");
    }

    #[Title('Manage Devices')]
    public function render()
    {
        $devices = auth()->user()->devices()
            ->orderBy('last_activity', 'desc')
            ->get();

        return view('livewire.manage-devices', compact('devices'))
            ->layout('layouts.app', [
                'header' => 'Manage Devices'
            ]);
    }
}
