<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Packages\User\Services\DeviceDetectionService;

class TestDeviceService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:device-service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test DeviceDetectionService';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Test 1: Check if class exists
            $this->info('Testing DeviceDetectionService...');
            
            if (class_exists(DeviceDetectionService::class)) {
                $this->info('✓ DeviceDetectionService class exists');
            } else {
                $this->error('✗ DeviceDetectionService class not found');
                return 1;
            }

            // Test 2: Try to resolve from container
            $service = app(DeviceDetectionService::class);
            $this->info('✓ DeviceDetectionService resolved from container');
            
            // Test 3: Test location info method
            $locationInfo = $service->getLocationInfo();
            $this->info('✓ getLocationInfo() method works');
            $this->line('Location info: ' . json_encode($locationInfo));
            
            $this->info('All tests passed!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
            return 1;
        }
    }
}
