<?php

namespace Packages\Employees\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Illuminate\Support\Facades\DB;
use Packages\Employees\Models\Department;
use Packages\Employees\Models\Employee;
use Packages\Employees\Models\EmployeeCommission;
use Packages\Employees\Models\EmployeePayroll;
use Packages\Employees\Models\EmployeeSchedule;
use Packages\Employees\Models\EmployeeTimesheet;
use Packages\Employees\Models\Position;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class EmployeeSeeder extends BasePackageSeeder
{
    /**
     * Standard departments for construction material stores
     */
    private array $standardDepartments = [
        [
            'code' => 'SALES',
            'name' => 'Bán hàng',
            'description' => 'Phòng ban phụ trách bán hàng và chăm sóc khách hàng',
            'location' => 'Tầng trệt',
            'budget' => 50000000,
            'max_employees' => 10,
        ],
        [
            'code' => 'WAREHOUSE',
            'name' => 'Kho',
            'description' => 'Phòng ban quản lý kho bãi và vận chuyển',
            'location' => 'Khu kho',
            'budget' => 30000000,
            'max_employees' => 8,
        ],
        [
            'code' => 'ACCOUNTING',
            'name' => 'Kế toán',
            'description' => 'Phòng ban kế toán và tài chính',
            'location' => 'Tầng 2',
            'budget' => 25000000,
            'max_employees' => 5,
        ],
        [
            'code' => 'MANAGEMENT',
            'name' => 'Quản lý',
            'description' => 'Ban giám đốc và quản lý cấp cao',
            'location' => 'Tầng 3',
            'budget' => 100000000,
            'max_employees' => 3,
        ],
    ];

    /**
     * Standard positions for each department
     */
    private array $standardPositions = [
        'SALES' => [
            [
                'code' => 'SALES_MANAGER',
                'title' => 'Trưởng phòng Bán hàng',
                'level' => 'manager',
                'min_salary' => 15000000,
                'max_salary' => 25000000,
                'responsibilities' => 'Quản lý đội ngũ bán hàng, lập kế hoạch kinh doanh',
                'requirements' => 'Kinh nghiệm 3+ năm trong lĩnh vực bán hàng vật liệu xây dựng',
            ],
            [
                'code' => 'SALES_EXEC',
                'title' => 'Nhân viên Bán hàng',
                'level' => 'entry',
                'min_salary' => 8000000,
                'max_salary' => 15000000,
                'responsibilities' => 'Tư vấn và bán hàng cho khách hàng',
                'requirements' => 'Giao tiếp tốt, hiểu biết về vật liệu xây dựng',
            ],
            [
                'code' => 'SALES_SUPPORT',
                'title' => 'Nhân viên Hỗ trợ Bán hàng',
                'level' => 'junior',
                'min_salary' => 6000000,
                'max_salary' => 10000000,
                'responsibilities' => 'Hỗ trợ nhân viên bán hàng, xử lý đơn hàng',
                'requirements' => 'Tốt nghiệp THPT, có thể đào tạo',
            ],
        ],
        'WAREHOUSE' => [
            [
                'code' => 'WAREHOUSE_MANAGER',
                'title' => 'Trưởng kho',
                'level' => 'manager',
                'min_salary' => 12000000,
                'max_salary' => 20000000,
                'responsibilities' => 'Quản lý kho bãi, kiểm soát tồn kho',
                'requirements' => 'Kinh nghiệm 2+ năm quản lý kho',
            ],
            [
                'code' => 'WAREHOUSE_STAFF',
                'title' => 'Nhân viên Kho',
                'level' => 'entry',
                'min_salary' => 7000000,
                'max_salary' => 12000000,
                'responsibilities' => 'Nhập xuất kho, kiểm kê hàng hóa',
                'requirements' => 'Sức khỏe tốt, có thể làm việc ngoài trời',
            ],
            [
                'code' => 'FORKLIFT_OPERATOR',
                'title' => 'Lái xe nâng',
                'level' => 'entry',
                'min_salary' => 8000000,
                'max_salary' => 13000000,
                'responsibilities' => 'Vận hành xe nâng, di chuyển hàng hóa',
                'requirements' => 'Có bằng lái xe nâng, kinh nghiệm 1+ năm',
            ],
        ],
        'ACCOUNTING' => [
            [
                'code' => 'CHIEF_ACCOUNTANT',
                'title' => 'Kế toán trưởng',
                'level' => 'manager',
                'min_salary' => 18000000,
                'max_salary' => 30000000,
                'responsibilities' => 'Quản lý tài chính, lập báo cáo tài chính',
                'requirements' => 'Bằng cử nhân Kế toán, chứng chỉ kế toán trưởng',
            ],
            [
                'code' => 'ACCOUNTANT',
                'title' => 'Kế toán viên',
                'level' => 'entry',
                'min_salary' => 10000000,
                'max_salary' => 18000000,
                'responsibilities' => 'Ghi sổ kế toán, lập báo cáo',
                'requirements' => 'Bằng cử nhân Kế toán hoặc tương đương',
            ],
            [
                'code' => 'CASHIER',
                'title' => 'Thu ngân',
                'level' => 'entry',
                'min_salary' => 7000000,
                'max_salary' => 12000000,
                'responsibilities' => 'Thu tiền, xuất hóa đơn',
                'requirements' => 'Tính toán nhanh, trung thực',
            ],
        ],
        'MANAGEMENT' => [
            [
                'code' => 'GENERAL_MANAGER',
                'title' => 'Giám đốc',
                'level' => 'executive',
                'min_salary' => 40000000,
                'max_salary' => 80000000,
                'responsibilities' => 'Điều hành toàn bộ hoạt động của cửa hàng',
                'requirements' => 'Kinh nghiệm 5+ năm quản lý, hiểu biết sâu về ngành',
            ],
            [
                'code' => 'DEPUTY_MANAGER',
                'title' => 'Phó Giám đốc',
                'level' => 'director',
                'min_salary' => 25000000,
                'max_salary' => 45000000,
                'responsibilities' => 'Hỗ trợ giám đốc, quản lý các phòng ban',
                'requirements' => 'Kinh nghiệm 3+ năm quản lý cấp trung',
            ],
        ],
    ];

    public function run(): void
    {
        $this->ensureSeedingAllowed();
        
        $this->executeWithTransaction(function () {
            $this->seedForAllStores(function (Store $store) {
                $this->seedDepartmentsForStore($store);
                $this->seedPositionsForStore($store);
                $this->seedEmployeesForStore($store);
                $this->seedEmployeeSchedulesForStore($store);
                $this->seedEmployeeTimesheetsForStore($store);
                $this->seedEmployeeCommissionsForStore($store);
                $this->seedEmployeePayrollsForStore($store);
            });
        });
    }

    /**
     * Seed departments for a store
     */
    private function seedDepartmentsForStore(Store $store): void
    {
        $this->logSeedingProgress('seeding_departments_for_store', [
            'store_id' => $store->id,
            'store_name' => $store->name
        ]);

        foreach ($this->standardDepartments as $departmentData) {
            // Check if department already exists
            $existingDepartment = Department::where('store_id', $store->id)
                ->where('code', $departmentData['code'])
                ->first();

            if ($existingDepartment) {
                continue; // Skip if department already exists
            }

            Department::create(array_merge($departmentData, [
                'store_id' => $store->id,
                'is_active' => true,
                'sort_order' => array_search($departmentData['code'], array_column($this->standardDepartments, 'code')) + 1,
            ]));
        }
    }

    /**
     * Seed positions for a store
     */
    private function seedPositionsForStore(Store $store): void
    {
        $this->logSeedingProgress('seeding_positions_for_store', [
            'store_id' => $store->id,
            'store_name' => $store->name
        ]);

        $departments = Department::where('store_id', $store->id)->get()->keyBy('code');

        foreach ($this->standardPositions as $departmentCode => $positions) {
            $department = $departments[$departmentCode] ?? null;
            if (!$department) continue;

            foreach ($positions as $index => $positionData) {
                // Check if position already exists
                $existingPosition = Position::where('store_id', $store->id)
                    ->where('code', $positionData['code'])
                    ->first();

                if ($existingPosition) {
                    continue; // Skip if position already exists
                }

                Position::create(array_merge($positionData, [
                    'store_id' => $store->id,
                    'department_id' => $department->id,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]));
            }
        }
    }

    /**
     * Seed employees for a store
     */
    private function seedEmployeesForStore(Store $store): void
    {
        $this->logSeedingProgress('seeding_employees_for_store', [
            'store_id' => $store->id,
            'store_name' => $store->name
        ]);

        $departments = Department::where('store_id', $store->id)->with('positions')->get();
        $vietnameseNames = $this->getVietnameseNames();
        $employeeCount = $this->getRecordCount(15, 5); // 15 for dev, 5 for testing

        $createdEmployees = [];
        $employeeIndex = 0;

        foreach ($departments as $department) {
            $employeesPerDept = $this->getEmployeesPerDepartment($department->code);
            
            for ($i = 0; $i < $employeesPerDept && $employeeIndex < $employeeCount; $i++) {
                $position = $department->positions->random();
                $name = $vietnameseNames[$employeeIndex % count($vietnameseNames)];
                $nameParts = explode(' ', $name);
                
                $employee = Employee::create([
                    'store_id' => $store->id,
                    'department_id' => $department->id,
                    'position_id' => $position->id,
                    'employee_code' => Employee::generateEmployeeCode($store->id),
                    'full_name' => $name,
                    'first_name' => end($nameParts),
                    'last_name' => implode(' ', array_slice($nameParts, 0, -1)),
                    'id_number' => $this->generateIdNumber(),
                    'date_of_birth' => now()->subYears(rand(22, 55))->subDays(rand(1, 365)),
                    'gender' => rand(0, 1) ? 'male' : 'female',
                    'phone' => $this->generatePhoneNumber(),
                    'email' => $this->generateEmail($name),
                    'address' => $this->getVietnameseAddresses()[array_rand($this->getVietnameseAddresses())],
                    'emergency_contact_name' => $vietnameseNames[array_rand($vietnameseNames)],
                    'emergency_contact_phone' => $this->generatePhoneNumber(),
                    'hire_date' => now()->subDays(rand(30, 1095)), // Hired 1 month to 3 years ago
                    'probation_end_date' => rand(0, 1) ? now()->subDays(rand(1, 60)) : null,
                    'employment_type' => $this->getEmploymentType($position->level),
                    'contract_type' => rand(0, 1) ? 'permanent' : 'fixed_term',
                    'contract_start_date' => now()->subDays(rand(30, 1095)),
                    'contract_end_date' => rand(0, 1) ? now()->addYears(rand(1, 3)) : null,
                    'work_location' => $department->location,
                    'basic_salary' => rand($position->min_salary, $position->max_salary),
                    'salary_grade' => $this->getSalaryGrade($position->level),
                    'pay_frequency' => 'monthly',
                    'currency' => 'VND',
                    'salary_effective_date' => now()->subDays(rand(30, 365)),
                    'working_hours' => $this->getWorkingHours(),
                    'weekly_hours' => 48,
                    'break_time' => ['start' => '12:00', 'end' => '13:00'],
                    'health_insurance' => true,
                    'social_insurance' => true,
                    'vacation_days' => 12,
                    'sick_leave_days' => 30,
                    'tax_id' => $this->generateTaxId(),
                    'dependents' => rand(0, 3),
                    'status' => $this->getEmployeeStatus(),
                    'skills' => $this->getSkillsForPosition($position->code),
                    'certifications' => $this->getCertificationsForPosition($position->code),
                ]);

                $createdEmployees[] = $employee;
                $employeeIndex++;
            }
        }

        // Assign managers
        $this->assignManagers($store, $createdEmployees);
    }

    /**
     * Seed employee schedules for a store
     */
    private function seedEmployeeSchedulesForStore(Store $store): void
    {
        $this->logSeedingProgress('seeding_employee_schedules_for_store', [
            'store_id' => $store->id,
            'store_name' => $store->name
        ]);

        $employees = Employee::where('store_id', $store->id)->get();
        $scheduleCount = $this->getRecordCount(30, 10); // 30 days for dev, 10 for testing
        
        // Get a user from this store for approved_by
        $storeUser = $store->users()->first();
        $approvedBy = $storeUser ? $storeUser->id : null;

        foreach ($employees as $employee) {
            // Create schedules for the next month
            for ($i = 0; $i < $scheduleCount; $i++) {
                $scheduleDate = now()->addDays($i);
                
                // Skip weekends for most employees (except some warehouse staff)
                if ($scheduleDate->isWeekend() && rand(0, 3) > 0) {
                    continue;
                }

                // Check if schedule already exists for this date (prevent duplicates)
                $existingSchedule = EmployeeSchedule::where('store_id', $store->id)
                    ->where('employee_id', $employee->id)
                    ->where('schedule_date', $scheduleDate)
                    ->first();

                if ($existingSchedule) {
                    continue; // Skip if schedule already exists
                }

                $shiftType = $this->getShiftType($employee->department->code);
                $workingHours = $this->getWorkingHoursForShift($shiftType);

                EmployeeSchedule::create([
                    'store_id' => $store->id,
                    'employee_id' => $employee->id,
                    'department_id' => $employee->department_id,
                    'schedule_date' => $scheduleDate,
                    'shift_name' => $shiftType,
                    'start_time' => $scheduleDate->copy()->setTimeFromTimeString($workingHours['start']),
                    'end_time' => $scheduleDate->copy()->setTimeFromTimeString($workingHours['end']),
                    'break_start_time' => $scheduleDate->copy()->setTimeFromTimeString('12:00:00'),
                    'break_end_time' => $scheduleDate->copy()->setTimeFromTimeString('13:00:00'),
                    'scheduled_hours' => $workingHours['hours'],
                    'status' => rand(0, 10) > 1 ? 'scheduled' : 'cancelled',
                    'schedule_type' => rand(0, 20) > 18 ? 'overtime' : 'regular',
                    'work_location' => $employee->department->location,
                    'role_for_shift' => $employee->position->title,
                    'is_available' => true,
                    'is_approved' => true,
                    'approved_by' => $approvedBy,
                    'approved_at' => now(),
                    'notes' => rand(0, 10) > 8 ? 'Lịch làm việc đặc biệt' : null,
                ]);
            }
        }
    }

    /**
     * Seed employee timesheets for a store
     */
    private function seedEmployeeTimesheetsForStore(Store $store): void
    {
        $this->logSeedingProgress('seeding_employee_timesheets_for_store', [
            'store_id' => $store->id,
            'store_name' => $store->name
        ]);

        $employees = Employee::where('store_id', $store->id)->get();
        $timesheetDays = $this->getRecordCount(30, 10); // Last 30 days for dev, 10 for testing
        
        // Get a user from this store for approved_by
        $storeUser = $store->users()->first();
        $approvedBy = $storeUser ? $storeUser->id : null;

        foreach ($employees as $employee) {
            for ($i = 1; $i <= $timesheetDays; $i++) {
                $workDate = now()->subDays($i);
                
                // Skip weekends for most employees
                if ($workDate->isWeekend() && rand(0, 3) > 0) {
                    continue;
                }

                // Check if timesheet already exists for this date
                $existingTimesheet = EmployeeTimesheet::where('store_id', $store->id)
                    ->where('employee_id', $employee->id)
                    ->where('work_date', $workDate)
                    ->first();

                if ($existingTimesheet) {
                    continue; // Skip if timesheet already exists
                }

                // Some employees might be absent
                if (rand(0, 20) === 0) {
                    EmployeeTimesheet::create([
                        'store_id' => $store->id,
                        'employee_id' => $employee->id,
                        'work_date' => $workDate,
                        'status' => 'absent',
                        'absence_reason' => $this->getAbsenceReason(),
                        'is_approved' => true,
                        'approved_by' => $approvedBy,
                        'approved_at' => now(),
                    ]);
                    continue;
                }

                $shiftHours = $this->getWorkingHoursForShift('morning');
                $checkInTime = $workDate->copy()->setTimeFromTimeString($shiftHours['start'])
                    ->addMinutes(rand(-15, 30)); // Some variation in check-in time
                $checkOutTime = $workDate->copy()->setTimeFromTimeString($shiftHours['end'])
                    ->addMinutes(rand(-30, 60)); // Some variation in check-out time

                $timesheet = EmployeeTimesheet::create([
                    'store_id' => $store->id,
                    'employee_id' => $employee->id,
                    'work_date' => $workDate,
                    'check_in_time' => $checkInTime,
                    'check_out_time' => $checkOutTime,
                    'break_start_time' => $workDate->copy()->setTimeFromTimeString('12:00:00'),
                    'break_end_time' => $workDate->copy()->setTimeFromTimeString('13:00:00'),
                    'status' => 'present',
                    'is_approved' => true,
                    'approved_by' => $approvedBy,
                    'approved_at' => now(),
                    'check_in_location' => $employee->department->location,
                    'check_out_location' => $employee->department->location,
                    'notes' => rand(0, 10) > 8 ? 'Làm thêm giờ' : null,
                ]);

                // Calculate hours
                $timesheet->calculateHours();
                $timesheet->save();
            }
        }
    }

    /**
     * Seed employee commissions for a store
     */
    private function seedEmployeeCommissionsForStore(Store $store): void
    {
        $this->logSeedingProgress('seeding_employee_commissions_for_store', [
            'store_id' => $store->id,
            'store_name' => $store->name
        ]);
        
        // Get a user from this store for approved_by
        $storeUser = $store->users()->first();
        $approvedBy = $storeUser ? $storeUser->id : null;

        // Only sales employees get commissions
        $salesEmployees = Employee::where('store_id', $store->id)
            ->whereHas('department', function ($query) {
                $query->where('code', 'SALES');
            })
            ->get();

        $commissionMonths = $this->getRecordCount(6, 3); // Last 6 months for dev, 3 for testing

        foreach ($salesEmployees as $employee) {
            for ($i = 1; $i <= $commissionMonths; $i++) {
                $periodStart = now()->subMonths($i)->startOfMonth();
                $periodEnd = now()->subMonths($i)->endOfMonth();

                // Check if commission already exists for this period
                $existingCommission = EmployeeCommission::where('store_id', $store->id)
                    ->where('employee_id', $employee->id)
                    ->where('period_start_date', $periodStart)
                    ->where('period_end_date', $periodEnd)
                    ->first();

                if ($existingCommission) {
                    continue; // Skip if commission already exists
                }

                $totalSales = rand(50000000, 200000000); // 50M to 200M VND
                $targetSales = rand(80000000, 150000000); // 80M to 150M VND
                $totalOrders = rand(20, 100);
                $targetOrders = rand(30, 80);

                try {
                    $commission = EmployeeCommission::create([
                        'store_id' => $store->id,
                        'employee_id' => $employee->id,
                        'commission_period' => 'monthly',
                        'period_start_date' => $periodStart,
                        'period_end_date' => $periodEnd,
                        'total_sales' => $totalSales,
                        'target_sales' => $targetSales,
                        'total_orders' => $totalOrders,
                        'target_orders' => $targetOrders,
                        'base_commission_rate' => 2.0, // 2%
                        'bonus_commission_rate' => 1.0, // 1% bonus for exceeding target
                        'deductions' => rand(0, 500000), // Random deductions
                        'adjustments' => rand(-200000, 300000), // Random adjustments
                        'deduction_reason' => rand(0, 1) ? 'Khấu trừ bảo hiểm' : null,
                        'adjustment_reason' => rand(0, 1) ? 'Thưởng đặc biệt' : null,
                        'payment_status' => $this->getPaymentStatus(),
                        'payment_method' => 'bank_transfer',
                        'is_approved' => true,
                        'approved_by' => $approvedBy,
                        'approved_at' => now(),
                        'calculated_by' => $approvedBy,
                        'sales_breakdown' => [
                            'xi_mang' => rand(10000000, 50000000),
                            'sat_thep' => rand(15000000, 60000000),
                            'gach' => rand(5000000, 30000000),
                            'cat_da' => rand(8000000, 40000000),
                        ],
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // Skip if duplicate entry error
                    if ($e->getCode() === '23000') {
                        continue;
                    }
                    throw $e;
                }

                // Calculate commission amounts
                $commission->calculateCommission();
                $commission->save();
            }
        }
    }

    /**
     * Seed employee payrolls for a store
     */
    private function seedEmployeePayrollsForStore(Store $store): void
    {
        $this->logSeedingProgress('seeding_employee_payrolls_for_store', [
            'store_id' => $store->id,
            'store_name' => $store->name
        ]);
        
        // Get a user from this store for approved_by
        $storeUser = $store->users()->first();
        $approvedBy = $storeUser ? $storeUser->id : null;

        $employees = Employee::where('store_id', $store->id)->get();
        $payrollMonths = $this->getRecordCount(6, 3); // Last 6 months for dev, 3 for testing

        foreach ($employees as $employee) {
            for ($i = 1; $i <= $payrollMonths; $i++) {
                $periodStart = now()->subMonths($i)->startOfMonth();
                $periodEnd = now()->subMonths($i)->endOfMonth();
                $payDate = $periodEnd->copy()->addDays(5); // Pay 5 days after month end

                // Check if payroll already exists for this period
                $existingPayroll = EmployeePayroll::where('store_id', $store->id)
                    ->where('employee_id', $employee->id)
                    ->where('period_start_date', $periodStart)
                    ->where('period_end_date', $periodEnd)
                    ->first();

                if ($existingPayroll) {
                    continue; // Skip if payroll already exists
                }

                // Get timesheet data for the period
                $timesheets = EmployeeTimesheet::where('employee_id', $employee->id)
                    ->whereBetween('work_date', [$periodStart, $periodEnd])
                    ->get();

                $regularHours = $timesheets->sum('regular_hours') ?: rand(160, 200); // Default if no timesheets
                $overtimeHours = $timesheets->sum('overtime_hours') ?: rand(0, 20);

                // Get commission for sales employees
                $commission = 0;
                if ($employee->department->code === 'SALES') {
                    $commissionRecord = EmployeeCommission::where('employee_id', $employee->id)
                        ->where('period_start_date', $periodStart)
                        ->first();
                    $commission = $commissionRecord ? $commissionRecord->net_commission : 0;
                }

                try {
                    $payroll = EmployeePayroll::create([
                        'store_id' => $store->id,
                        'employee_id' => $employee->id,
                        'payroll_period' => 'monthly',
                        'period_start_date' => $periodStart,
                        'period_end_date' => $periodEnd,
                        'pay_date' => $payDate,
                        'basic_salary' => $employee->basic_salary,
                        'hourly_rate' => $employee->basic_salary / 160, // Assuming 160 hours per month
                        'regular_hours' => $regularHours,
                        'overtime_hours' => $overtimeHours,
                        'overtime_rate' => ($employee->basic_salary / 160) * 1.5, // 1.5x overtime rate
                        'commission' => $commission,
                        'bonus' => rand(0, 2000000), // Random bonus up to 2M
                        'allowances' => rand(500000, 1500000), // Transport, meal allowances
                        'holiday_pay' => rand(0, 1000000),
                        'other_earnings' => rand(0, 500000),
                        'earnings_breakdown' => [
                            'transport_allowance' => 500000,
                            'meal_allowance' => 300000,
                            'phone_allowance' => 200000,
                        ],
                        'loan_deduction' => rand(0, 1) ? rand(500000, 2000000) : 0,
                        'advance_deduction' => rand(0, 1) ? rand(200000, 1000000) : 0,
                        'other_deductions' => rand(0, 300000),
                        'deductions_breakdown' => [
                            'uniform' => rand(0, 200000),
                            'parking' => rand(0, 100000),
                        ],
                        'payment_status' => $this->getPaymentStatus(),
                        'payment_method' => 'bank_transfer',
                        'bank_account' => $this->generateBankAccount(),
                        'is_approved' => true,
                        'approved_by' => $approvedBy,
                        'approved_at' => now(),
                        'calculated_by' => $approvedBy,
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // Skip if duplicate entry error
                    if ($e->getCode() === '23000') {
                        continue;
                    }
                    throw $e;
                }

                // Calculate taxes and final amounts
                $payroll->calculateTaxes();
                $payroll->calculatePayroll();
                $payroll->save();
            }
        }
    }

    /**
     * Helper methods
     */
    private function getEmployeesPerDepartment(string $departmentCode): int
    {
        $counts = [
            'SALES' => $this->getRecordCount(4, 2),
            'WAREHOUSE' => $this->getRecordCount(3, 2),
            'ACCOUNTING' => $this->getRecordCount(2, 1),
            'MANAGEMENT' => $this->getRecordCount(2, 1),
        ];

        return $counts[$departmentCode] ?? 1;
    }

    private function assignManagers(Store $store, array $employees): void
    {
        $departments = Department::where('store_id', $store->id)->get();
        
        foreach ($departments as $department) {
            $deptEmployees = array_filter($employees, function ($emp) use ($department) {
                return $emp->department_id === $department->id;
            });

            if (count($deptEmployees) > 1) {
                // Find manager-level employee
                $manager = collect($deptEmployees)->first(function ($emp) {
                    return in_array($emp->position->level, ['manager', 'director', 'executive']);
                });

                if ($manager) {
                    // Assign manager to department
                    $department->update(['manager_id' => $manager->user_id]);

                    // Assign manager to other employees in department
                    foreach ($deptEmployees as $employee) {
                        if ($employee->id !== $manager->id) {
                            $employee->update(['manager_id' => $manager->id]);
                        }
                    }
                }
            }
        }
    }

    private function generateIdNumber(): string
    {
        return rand(100000000, 999999999) . rand(100, 999);
    }

    private function generatePhoneNumber(): string
    {
        $prefixes = ['090', '091', '094', '083', '084', '085', '081', '082'];
        return $prefixes[array_rand($prefixes)] . rand(1000000, 9999999);
    }

    private function generateEmail(string $name): string
    {
        $nameParts = explode(' ', $name);
        $firstName = end($nameParts);
        $lastName = $nameParts[0];
        
        $email = strtolower($this->removeVietnameseAccents($firstName . '.' . $lastName));
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
        
        return $email . rand(1, 999) . '@' . $domains[array_rand($domains)];
    }

    private function removeVietnameseAccents(string $str): string
    {
        $accents = [
            'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
            'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
            'ì', 'í', 'ị', 'ỉ', 'ĩ',
            'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
            'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
            'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
            'đ'
        ];
        
        $replacements = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd'
        ];
        
        return str_replace($accents, $replacements, $str);
    }

    private function generateTaxId(): string
    {
        return rand(1000000000, 9999999999);
    }

    private function generateBankAccount(): string
    {
        return rand(100000000000, 999999999999);
    }

    private function getEmploymentType(string $level): string
    {
        $types = [
            'executive' => 'full_time',
            'director' => 'full_time',
            'manager' => 'full_time',
            'staff' => rand(0, 4) > 0 ? 'full_time' : 'part_time',
            'junior' => rand(0, 2) > 0 ? 'full_time' : 'part_time',
        ];

        return $types[$level] ?? 'full_time';
    }

    private function getSalaryGrade(string $level): string
    {
        $grades = [
            'executive' => 'A',
            'director' => 'B',
            'manager' => 'C',
            'staff' => 'D',
            'junior' => 'E',
        ];

        return $grades[$level] ?? 'D';
    }

    private function getWorkingHours(): array
    {
        return [
            'monday' => ['start' => '08:00', 'end' => '17:00'],
            'tuesday' => ['start' => '08:00', 'end' => '17:00'],
            'wednesday' => ['start' => '08:00', 'end' => '17:00'],
            'thursday' => ['start' => '08:00', 'end' => '17:00'],
            'friday' => ['start' => '08:00', 'end' => '17:00'],
            'saturday' => ['start' => '08:00', 'end' => '12:00'],
        ];
    }

    private function getEmployeeStatus(): string
    {
        $statuses = ['active', 'inactive', 'terminated'];
        $weights = [85, 10, 5]; // 85% active, 10% inactive, 5% terminated
        
        $rand = rand(1, 100);
        if ($rand <= $weights[0]) return $statuses[0];
        if ($rand <= $weights[0] + $weights[1]) return $statuses[1];
        return $statuses[2];
    }

    private function getSkillsForPosition(string $positionCode): array
    {
        $skills = [
            'SALES_MANAGER' => ['Quản lý đội nhóm', 'Kỹ năng bán hàng', 'Lập kế hoạch', 'Phân tích thị trường'],
            'SALES_EXEC' => ['Tư vấn khách hàng', 'Kỹ năng giao tiếp', 'Hiểu biết sản phẩm'],
            'SALES_SUPPORT' => ['Xử lý đơn hàng', 'Hỗ trợ khách hàng', 'Sử dụng máy tính'],
            'WAREHOUSE_MANAGER' => ['Quản lý kho', 'Kiểm soát tồn kho', 'Lập báo cáo'],
            'WAREHOUSE_STAFF' => ['Vận chuyển hàng hóa', 'Kiểm kê', 'An toàn lao động'],
            'FORKLIFT_OPERATOR' => ['Lái xe nâng', 'An toàn lao động', 'Bảo trì thiết bị'],
            'CHIEF_ACCOUNTANT' => ['Kế toán tài chính', 'Phân tích tài chính', 'Lập báo cáo'],
            'ACCOUNTANT' => ['Ghi sổ kế toán', 'Thuế', 'Excel'],
            'CASHIER' => ['Thu ngân', 'Xuất hóa đơn', 'Tính toán'],
            'GENERAL_MANAGER' => ['Lãnh đạo', 'Quản lý tổng thể', 'Ra quyết định', 'Chiến lược'],
            'DEPUTY_MANAGER' => ['Quản lý cấp trung', 'Điều phối', 'Giám sát'],
        ];

        return $skills[$positionCode] ?? ['Kỹ năng cơ bản'];
    }

    private function getCertificationsForPosition(string $positionCode): array
    {
        $certifications = [
            'CHIEF_ACCOUNTANT' => ['Chứng chỉ Kế toán trưởng', 'CPA'],
            'ACCOUNTANT' => ['Chứng chỉ Kế toán'],
            'FORKLIFT_OPERATOR' => ['Bằng lái xe nâng'],
            'GENERAL_MANAGER' => ['MBA', 'Chứng chỉ Quản lý'],
        ];

        return $certifications[$positionCode] ?? [];
    }

    private function getShiftType(string $departmentCode): string
    {
        $shifts = [
            'SALES' => ['morning', 'afternoon'],
            'WAREHOUSE' => ['morning', 'afternoon', 'evening'],
            'ACCOUNTING' => ['morning'],
            'MANAGEMENT' => ['morning'],
        ];

        $availableShifts = $shifts[$departmentCode] ?? ['morning'];
        return $availableShifts[array_rand($availableShifts)];
    }

    private function getWorkingHoursForShift(string $shiftType): array
    {
        $shifts = [
            'morning' => ['start' => '08:00:00', 'end' => '17:00:00', 'hours' => 8],
            'afternoon' => ['start' => '13:00:00', 'end' => '22:00:00', 'hours' => 8],
            'evening' => ['start' => '18:00:00', 'end' => '02:00:00', 'hours' => 8],
        ];

        return $shifts[$shiftType] ?? $shifts['morning'];
    }

    private function getAbsenceReason(): string
    {
        $reasons = [
            'Ốm đau',
            'Việc gia đình',
            'Nghỉ phép',
            'Đi công tác',
            'Khám sức khỏe',
        ];

        return $reasons[array_rand($reasons)];
    }

    private function getPaymentStatus(): string
    {
        $statuses = ['pending', 'approved', 'paid'];
        $weights = [20, 30, 50]; // 20% pending, 30% approved, 50% paid
        
        $rand = rand(1, 100);
        if ($rand <= $weights[0]) return $statuses[0];
        if ($rand <= $weights[0] + $weights[1]) return $statuses[1];
        return $statuses[2];
    }
}