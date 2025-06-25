<?php

namespace Packages\User\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Packages\User\Models\UserDevice;
use Packages\Appearance\Traits\HasConfirmationModal;
use App\Traits\HandlesLocaleUpdates;

class ManageDevices extends Component
{
    use HasConfirmationModal, HandlesLocaleUpdates;

    protected $listeners = [
        'revokeAllOtherDevices' => 'revokeAllOtherDevices',
        'locale-updated' => 'handleLocaleUpdate'
    ];

    public function trustDevice($deviceId)
    {
        $device = auth()->user()->devices()->findOrFail($deviceId);
        $device->update(['is_trusted' => true]);
        
        session()->flash('success', __('app.device_trusted'));
    }

    public function untrustDevice($deviceId)
    {
        $device = auth()->user()->devices()->findOrFail($deviceId);
        $device->update(['is_trusted' => false]);
        
        session()->flash('success', __('app.device_untrusted'));
    }

    public function confirmRemoveDevice($deviceId)
    {
        $device = auth()->user()->devices()->findOrFail($deviceId);
        
        $this->confirmation()
            ->title(__('app.revoke_device'))
            ->message(__('app.are_you_sure_remove_device', ['device' => $device->device_name]))
            ->danger()
            ->action('removeDevice', [$deviceId])
            ->confirmText(__('app.revoke_device'))
            ->show();
    }

    public function removeDevice($deviceId)
    {
        $device = auth()->user()->devices()->findOrFail($deviceId);
        
        // Don't allow removing the current device
        if ($device->is_current_device) {
            session()->flash('error', __('app.you_cannot_remove_current_device'));
            return;
        }
        
        $device->delete();
        session()->flash('success', __('app.device_revoked'));
    }

    public function confirmRevokeAllOtherDevices()
    {
        $this->confirmation()
            ->title(__('app.revoke_all_other_devices'))
            ->message(__('app.are_you_sure_revoke_all'))
            ->warning()
            ->action('revokeAllOtherDevices')
            ->confirmText(__('app.revoke_all'))
            ->buttonClass('btn-danger')
            ->show();
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

        session()->flash('success', __('app.devices_revoked', ['count' => $count]));
    }

    #[Title('Manage Devices')]
    public function render()
    {
        $devices = auth()->user()->devices()
            ->orderBy('last_activity', 'desc')
            ->get();

        return view('user::livewire.manage-devices', compact('devices'))
            ->layout('layouts.app', [
                'header' => __('app.manage_devices')
            ]);
    }
}
