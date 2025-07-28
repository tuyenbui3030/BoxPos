<?php

namespace Packages\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\User\Models\User;
use Packages\User\Models\UserDevice;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\User\Models\UserDevice>
 */
class UserDeviceFactory extends Factory
{
    protected $model = UserDevice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'];
        $platforms = ['Windows', 'macOS', 'Linux', 'Android', 'iOS'];
        $deviceTypes = ['desktop', 'mobile', 'tablet'];

        $browser = fake()->randomElement($browsers);
        $platform = fake()->randomElement($platforms);
        $deviceType = fake()->randomElement($deviceTypes);

        return [
            'user_id' => User::factory(),
            'device_name' => $this->generateDeviceName($platform, $browser),
            'device_type' => $deviceType,
            'browser' => $browser,
            'platform' => $platform,
            'ip_address' => fake()->ipv4(),
            'user_agent' => $this->generateUserAgent($browser, $platform),
            'remember_token' => fake()->sha256(),
            'last_activity' => fake()->dateTimeBetween('-30 days', 'now'),
            'last_login_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'is_trusted' => fake()->boolean(30), // 30% chance of being trusted
        ];
    }

    /**
     * Generate a realistic device name
     */
    private function generateDeviceName(string $platform, string $browser): string
    {
        $deviceNames = [
            'Windows' => ['Windows PC', 'Work Computer', 'Home Desktop'],
            'macOS' => ['MacBook Pro', 'MacBook Air', 'iMac', 'Mac Studio'],
            'Linux' => ['Ubuntu Desktop', 'Linux Workstation', 'Development Machine'],
            'Android' => ['Samsung Galaxy', 'Google Pixel', 'OnePlus', 'Xiaomi'],
            'iOS' => ['iPhone', 'iPad', 'iPad Pro', 'iPad Air'],
        ];

        $names = $deviceNames[$platform] ?? ['Unknown Device'];
        $deviceName = fake()->randomElement($names);

        return "{$deviceName} - {$browser}";
    }

    /**
     * Generate a realistic user agent string
     */
    private function generateUserAgent(string $browser, string $platform): string
    {
        $userAgents = [
            'Chrome' => [
                'Windows' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'macOS' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Linux' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Android' => 'Mozilla/5.0 (Linux; Android 10; SM-G973F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
                'iOS' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.0.0 Mobile/15E148 Safari/604.1',
            ],
            'Firefox' => [
                'Windows' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/121.0',
                'macOS' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/121.0',
                'Linux' => 'Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/121.0',
                'Android' => 'Mozilla/5.0 (Mobile; rv:109.0) Gecko/121.0 Firefox/121.0',
                'iOS' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/121.0.0 Mobile/15E148 Safari/605.1.15',
            ],
            'Safari' => [
                'macOS' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
                'iOS' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            ],
            'Edge' => [
                'Windows' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
                'macOS' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
            ],
        ];

        return $userAgents[$browser][$platform] ?? 'Mozilla/5.0 (Unknown) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    }

    /**
     * Indicate that the device should be trusted
     */
    public function trusted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_trusted' => true,
        ]);
    }

    /**
     * Indicate that the device should be active (recent activity)
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_activity' => fake()->dateTimeBetween('-7 days', 'now'),
            'last_login_at' => fake()->dateTimeBetween('-3 days', 'now'),
        ]);
    }

    /**
     * Indicate that the device should be inactive (old activity)
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_activity' => fake()->dateTimeBetween('-90 days', '-31 days'),
            'last_login_at' => fake()->dateTimeBetween('-90 days', '-31 days'),
        ]);
    }

    /**
     * Create a mobile device
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'mobile',
            'platform' => fake()->randomElement(['Android', 'iOS']),
        ]);
    }

    /**
     * Create a desktop device
     */
    public function desktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'desktop',
            'platform' => fake()->randomElement(['Windows', 'macOS', 'Linux']),
        ]);
    }
}