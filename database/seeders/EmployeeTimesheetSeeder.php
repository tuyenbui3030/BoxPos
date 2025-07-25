<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\Employees\Models\Employee;
use Packages\Employees\Models\EmployeeTimesheet;
use Carbon\Carbon;

class EmployeeTimesheetSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('⏰ Seeding Employee Timesheets...');

        $stores = Store::all();

        foreach ($stores as $store) {
            $this->createTimesheetsForStore($store);
        }

        $this->command->info('✅ Employee Timesheets seeded successfully!');
    }

    private function createTimesheetsForStore(Store $store): void
    {
        $employees = Employee::where('store_id', $store->id)
            ->where('status', 'active')
            ->get();

        if ($employees->isEmpty()) {
            return;
        }

        // Create timesheets for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        foreach ($employees as $employee) {
            $this->createTimesheetsForEmployee($employee, $startDate, $endDate);
        }
    }

    private function createTimesheetsForEmployee(Employee $employee, Carbon $startDate, Carbon $endDate): void
    {
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Skip Sundays (assuming Sunday is day off)
            if ($currentDate->dayOfWeek !== Carbon::SUNDAY) {
                $this->createDailyTimesheet($employee, $currentDate);
            }
            
            $currentDate->addDay();
        }
    }

    private function createDailyTimesheet(Employee $employee, Carbon $date): void
    {
        // Check if timesheet already exists for this employee and date
        $existingTimesheet = EmployeeTimesheet::where('employee_id', $employee->id)
            ->where('work_date', $date->format('Y-m-d'))
            ->first();

        if ($existingTimesheet) {
            return; // Skip if already exists
        }

        // Determine if employee worked this day (90% chance)
        $worked = rand(1, 100) <= 90;
        
        if (!$worked) {
            // Create absent record
            EmployeeTimesheet::create([
                'store_id' => $employee->store_id,
                'employee_id' => $employee->id,
                'work_date' => $date->format('Y-m-d'),
                'status' => 'absent',
                'absence_reason' => $this->getRandomAbsenceReason(),
                'is_approved' => true,
                'approved_by' => $employee->manager_id,
                'approved_at' => $date->copy()->addHours(rand(8, 10)),
                'notes' => 'Nghỉ có phép',
            ]);
            return;
        }

        // Generate work schedule
        $schedule = $this->generateWorkSchedule($employee, $date);
        
        EmployeeTimesheet::create([
            'store_id' => $employee->store_id,
            'employee_id' => $employee->id,
            'work_date' => $date->format('Y-m-d'),
            'check_in_time' => $schedule['clock_in'],
            'check_out_time' => $schedule['clock_out'],
            'break_start_time' => $schedule['break_start'],
            'break_end_time' => $schedule['break_end'],
            'total_hours' => $schedule['total_hours'],
            'regular_hours' => $schedule['regular_hours'],
            'overtime_hours' => $schedule['overtime_hours'],
            'break_hours' => $schedule['break_hours'],
            'status' => $schedule['status'],
            'check_in_location' => $this->getWorkLocation($employee),
            'check_out_location' => $this->getWorkLocation($employee),
            'check_in_ip' => $this->generateRandomIP(),
            'check_out_ip' => $this->generateRandomIP(),
            'check_in_device' => $this->getRandomDeviceType(),
            'check_out_device' => $this->getRandomDeviceType(),
            'is_approved' => $date->lt(Carbon::now()->subDays(1)), // Auto-approve past days
            'approved_by' => $date->lt(Carbon::now()->subDays(1)) ? $employee->manager_id : null,
            'approved_at' => $date->lt(Carbon::now()->subDays(1)) ?
                $date->copy()->addHours(rand(18, 20)) : null,
            'notes' => $this->generateTimesheetNotes($schedule),
        ]);
    }

    private function generateWorkSchedule(Employee $employee, Carbon $date): array
    {
        // Base schedule: 8:00 AM - 5:00 PM with 1 hour lunch
        $baseClockIn = $date->copy()->setTime(8, 0, 0);
        $baseClockOut = $date->copy()->setTime(17, 0, 0);
        
        // Add some variation (-30 to +60 minutes for clock in)
        $clockIn = $baseClockIn->copy()->addMinutes(rand(-30, 60));
        
        // Clock out variation (-60 to +120 minutes)
        $clockOut = $baseClockOut->copy()->addMinutes(rand(-60, 120));
        
        // Lunch break (usually 12:00-13:00 with some variation)
        $breakStart = $date->copy()->setTime(12, 0, 0)->addMinutes(rand(-30, 30));
        $breakEnd = $breakStart->copy()->addMinutes(rand(45, 75)); // 45-75 minutes lunch
        
        // Calculate hours
        $totalMinutes = $clockOut->diffInMinutes($clockIn);
        $breakMinutes = $breakEnd->diffInMinutes($breakStart);
        $workMinutes = $totalMinutes - $breakMinutes;
        
        $totalHours = round($workMinutes / 60, 2);
        $regularHours = min($totalHours, 8); // Max 8 regular hours
        $overtimeHours = max(0, $totalHours - 8);
        $breakHours = round($breakMinutes / 60, 2);
        
        // Determine status
        $status = $this->determineTimesheetStatus($clockIn, $clockOut, $totalHours);
        
        return [
            'clock_in' => $clockIn->format('H:i:s'),
            'clock_out' => $clockOut->format('H:i:s'),
            'break_start' => $breakStart->format('H:i:s'),
            'break_end' => $breakEnd->format('H:i:s'),
            'total_hours' => $totalHours,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'break_hours' => $breakHours,
            'status' => $status,
        ];
    }

    private function determineTimesheetStatus(Carbon $clockIn, Carbon $clockOut, float $totalHours): string
    {
        $standardClockIn = $clockIn->copy()->setTime(8, 0, 0);
        $standardClockOut = $clockIn->copy()->setTime(17, 0, 0);
        
        // Late if clock in after 8:30 AM
        if ($clockIn->gt($standardClockIn->copy()->addMinutes(30))) {
            return 'late';
        }
        
        // Early leave if clock out before 4:30 PM
        if ($clockOut->lt($standardClockOut->copy()->subMinutes(30))) {
            return 'early_leave';
        }
        
        // Overtime if more than 8.5 hours
        if ($totalHours > 8.5) {
            return 'overtime';
        }
        
        return 'present';
    }

    private function getRandomAbsenceReason(): string
    {
        $reasons = [
            'Nghỉ phép năm',
            'Nghỉ ốm',
            'Nghỉ việc riêng',
            'Nghỉ thai sản',
            'Nghỉ lễ bù',
            'Nghỉ không phép',
            'Công tác',
            'Đào tạo',
        ];
        
        return $reasons[array_rand($reasons)];
    }

    private function getWorkLocation(Employee $employee): string
    {
        $locations = [
            'Văn phòng chính',
            'Chi nhánh 1',
            'Chi nhánh 2',
            'Công trường A',
            'Công trường B',
            'Làm việc từ xa',
            'Khách hàng',
        ];
        
        return $locations[array_rand($locations)];
    }

    private function generateRandomIP(): string
    {
        return rand(192, 203) . '.' . rand(168, 255) . '.' . rand(1, 255) . '.' . rand(1, 254);
    }

    private function getRandomDeviceType(): string
    {
        $devices = ['desktop', 'mobile', 'tablet', 'kiosk'];
        return $devices[array_rand($devices)];
    }

    private function getRandomBrowser(): string
    {
        $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'];
        return $browsers[array_rand($browsers)];
    }

    private function getRandomOS(): string
    {
        $os = ['Windows 10', 'Windows 11', 'macOS', 'iOS', 'Android', 'Linux'];
        return $os[array_rand($os)];
    }

    private function generateTimesheetNotes(array $schedule): ?string
    {
        $notes = [];
        
        if ($schedule['overtime_hours'] > 0) {
            $notes[] = "Tăng ca {$schedule['overtime_hours']} giờ";
        }
        
        if ($schedule['status'] === 'late') {
            $notes[] = 'Đi muộn';
        }
        
        if ($schedule['status'] === 'early_leave') {
            $notes[] = 'Về sớm';
        }
        
        if ($schedule['break_hours'] > 1.5) {
            $notes[] = 'Nghỉ trưa dài';
        }
        
        return empty($notes) ? null : implode(', ', $notes);
    }

    private function getWorkType(Employee $employee): string
    {
        $workTypes = [
            'office_work' => 'Công việc văn phòng',
            'field_work' => 'Công việc ngoài hiện trường',
            'construction' => 'Thi công xây dựng',
            'sales' => 'Bán hàng',
            'management' => 'Quản lý',
            'technical' => 'Kỹ thuật',
        ];
        
        $keys = array_keys($workTypes);
        return $keys[array_rand($keys)];
    }

    private function getRandomProjectCodes(): array
    {
        $projects = [
            'PRJ001 - Dự án A',
            'PRJ002 - Dự án B',
            'PRJ003 - Dự án C',
            'MAINT - Bảo trì',
            'ADMIN - Hành chính',
            'SALES - Bán hàng',
        ];
        
        // Return 1-3 random projects
        $count = rand(1, 3);
        return array_slice($projects, 0, $count);
    }
}
