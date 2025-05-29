<?php

namespace App\Services;

use Packages\User\Models\User;
use Packages\User\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceDetectionService
{
    public function __construct(private Request $request)
    {
    }

    public function detectAndRecordDevice(User $user, ?string $rememberToken = null): UserDevice
    {
        $deviceInfo = $this->parseUserAgent();
        
        // Check if this device already exists
        $existingDevice = $user->devices()
            ->where('ip_address', $this->request->ip())
            ->where('user_agent', $this->request->userAgent())
            ->first();

        if ($existingDevice) {
            // Update existing device
            $existingDevice->update([
                'last_activity' => now(),
                'last_login_at' => now(),
                'remember_token' => $rememberToken,
            ]);
            
            return $existingDevice;
        }

        // Create new device record
        return $user->devices()->create([
            'device_name' => $this->generateDeviceName($deviceInfo),
            'device_type' => $deviceInfo['device_type'],
            'browser' => $deviceInfo['browser'],
            'platform' => $deviceInfo['platform'],
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'remember_token' => $rememberToken,
            'last_activity' => now(),
            'last_login_at' => now(),
            'is_trusted' => false,
        ]);
    }

    private function parseUserAgent(): array
    {
        $userAgent = $this->request->userAgent();
        
        // Basic device detection
        $deviceType = 'desktop';
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            if (preg_match('/iPad/', $userAgent)) {
                $deviceType = 'tablet';
            } else {
                $deviceType = 'mobile';
            }
        }

        // Browser detection
        $browser = 'Unknown';
        if (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches)) {
            $browser = 'Chrome ' . explode('.', $matches[1])[0];
        } elseif (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
            $browser = 'Firefox ' . explode('.', $matches[1])[0];
        } elseif (preg_match('/Safari\/([0-9.]+)/', $userAgent, $matches)) {
            if (!preg_match('/Chrome/', $userAgent)) {
                $browser = 'Safari';
            }
        } elseif (preg_match('/Edge\/([0-9.]+)/', $userAgent, $matches)) {
            $browser = 'Edge ' . explode('.', $matches[1])[0];
        }

        // Platform detection
        $platform = 'Unknown';
        if (preg_match('/Windows NT ([0-9.]+)/', $userAgent, $matches)) {
            $platform = 'Windows';
        } elseif (preg_match('/Mac OS X ([0-9_]+)/', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/Android ([0-9.]+)/', $userAgent, $matches)) {
            $platform = 'Android ' . explode('.', $matches[1])[0];
        } elseif (preg_match('/iPhone OS ([0-9_]+)/', $userAgent, $matches)) {
            $platform = 'iOS';
        } elseif (preg_match('/iPad.*OS ([0-9_]+)/', $userAgent, $matches)) {
            $platform = 'iPadOS';
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'platform' => $platform,
        ];
    }

    private function generateDeviceName(array $deviceInfo): string
    {
        $parts = [];
        
        if ($deviceInfo['platform'] !== 'Unknown') {
            $parts[] = $deviceInfo['platform'];
        }
        
        if ($deviceInfo['browser'] !== 'Unknown') {
            $parts[] = $deviceInfo['browser'];
        }

        if (empty($parts)) {
            $parts[] = ucfirst($deviceInfo['device_type']);
        }

        return implode(' - ', $parts);
    }

    public function getLocationInfo(): array
    {
        // This is a basic implementation. 
        // In production, you might want to use a service like GeoIP
        $ip = $this->request->ip();
        
        // For demo purposes, return basic info
        return [
            'ip' => $ip,
            'country' => 'Unknown',
            'city' => 'Unknown',
            'is_local' => in_array($ip, ['127.0.0.1', '::1']) || 
                         str_starts_with($ip, '192.168.') || 
                         str_starts_with($ip, '10.') ||
                         str_starts_with($ip, '172.'),
        ];
    }
}
