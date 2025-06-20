<?php

namespace Packages\User\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Packages\User\Models\UserDevice;
use Packages\Appearance\Traits\HasConfirmationModal;

class ManageDevices extends Component
{
    use HasConfirmationModal;

    protected $listeners = ['revokeAllOtherDevices' => 'revokeAllOtherDevices'];

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
        $device = auth()->user()->devices()->findOrFail($deviceId);
        
        $this->showDangerConfirmation(
            'Remove Device',
            "Are you sure you want to remove the device '{$device->device_name}'? This action cannot be undone.",
            'removeDevice',
            [$deviceId],
            [
                'confirmText' => 'Remove Device',
                'confirmButtonClass' => 'btn-danger'
            ]
        );
    }

    public function removeDevice($deviceId)
    {
        $device = auth()->user()->devices()->findOrFail($deviceId);
        
        // Don't allow removing the current device
        if ($device->is_current_device) {
            session()->flash('error', 'You cannot remove the current device.');
            return;
        }
        
        $device->delete();
        session()->flash('success', 'Device removed successfully.');
    }

    public function confirmRevokeAllOtherDevices()
    {
        $this->showWarningConfirmation(
            'Revoke All Other Devices',
            'Are you sure you want to revoke access for all other devices? This will log out all other sessions.',
            'revokeAllOtherDevices',
            [],
            [
                'confirmText' => 'Revoke All',
                'confirmButtonClass' => 'btn-danger'
            ]
        );
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

        return view('user::livewire.manage-devices', compact('devices'))
            ->layout('layouts.app', [
                'header' => 'Manage Devices'
            ]);
    }
}
