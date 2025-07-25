<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Employees\Models\Department;
use Packages\Employees\Models\Position;
use Packages\Employees\Models\Employee;
use Packages\Employees\Models\EmployeeTimesheet;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::all();

        foreach ($stores as $store) {
            // Create departments
            $departments = $this->createDepartments($store);
            
            // Create positions
            $positions = $this->createPositions($store, $departments);
            
            // Create employees
            $employees = $this->createEmployees($store, $departments, $positions);
            
            // Create timesheets
            $this->createTimesheets($store, $employees);
        }
    }

    private function createDepartments($store)
    {
        $departments = [];
        
        $departmentData = [
            [
                'code' => 'SALES',
                'name' => 'Phòng Bán hàng',
                'description' => 'Phụ trách bán hàng và chăm sóc khách hàng',
                'location' => 'Tầng 1',
                'budget' => 50000000,
                'max_employees' => 10,
            ],
            [
                'code' => 'WAREHOUSE',
                'name' => 'Phòng Kho',
                'description' => 'Quản lý kho hàng và logistics',
                'location' => 'Khu vực kho',
                'budget' => 30000000,
                'max_employees' => 8,
            ],
            [
                'code' => 'ADMIN',
                'name' => 'Phòng Hành chính',
                'description' => 'Quản lý hành chính và nhân sự',
                'location' => 'Tầng 2',
                'budget' => 40000000,
                'max_employees' => 5,
            ],
            [
                'code' => 'FINANCE',
                'name' => 'Phòng Tài chính',
                'description' => 'Quản lý tài chính và kế toán',
                'location' => 'Tầng 2',
                'budget' => 35000000,
                'max_employees' => 4,
            ],
        ];

        foreach ($departmentData as $index => $data) {
            $department = Department::create(array_merge($data, [
                'store_id' => $store->id,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]));
            $departments[$data['code']] = $department;
        }

        return $departments;
    }

    private function createPositions($store, $departments)
    {
        $positions = [];
        
        $positionData = [
            [
                'department' => 'SALES',
                'code' => 'SALES_MANAGER',
                'title' => 'Trưởng phòng Bán hàng',
                'level' => 'manager',
                'min_salary' => 15000000,
                'max_salary' => 25000000,
                'responsibilities' => 'Quản lý đội ngũ bán hàng, lập kế hoạch kinh doanh',
                'requirements' => 'Kinh nghiệm 3+ năm trong lĩnh vực bán hàng',
            ],
            [
                'department' => 'SALES',
                'code' => 'SALES_STAFF',
                'title' => 'Nhân viên Bán hàng',
                'level' => 'entry',
                'min_salary' => 8000000,
                'max_salary' => 15000000,
                'responsibilities' => 'Tư vấn và bán hàng cho khách hàng',
                'requirements' => 'Giao tiếp tốt, am hiểu sản phẩm',
            ],
            [
                'department' => 'WAREHOUSE',
                'code' => 'WAREHOUSE_MANAGER',
                'title' => 'Trưởng kho',
                'level' => 'manager',
                'min_salary' => 12000000,
                'max_salary' => 20000000,
                'responsibilities' => 'Quản lý kho hàng, kiểm soát tồn kho',
                'requirements' => 'Kinh nghiệm quản lý kho 2+ năm',
            ],
            [
                'department' => 'WAREHOUSE',
                'code' => 'WAREHOUSE_STAFF',
                'title' => 'Nhân viên Kho',
                'level' => 'entry',
                'min_salary' => 7000000,
                'max_salary' => 12000000,
                'responsibilities' => 'Nhập xuất kho, kiểm đếm hàng hóa',
                'requirements' => 'Cẩn thận, có trách nhiệm',
            ],
            [
                'department' => 'ADMIN',
                'code' => 'HR_MANAGER',
                'title' => 'Trưởng phòng Nhân sự',
                'level' => 'manager',
                'min_salary' => 15000000,
                'max_salary' => 25000000,
                'responsibilities' => 'Quản lý nhân sự, tuyển dụng, đào tạo',
                'requirements' => 'Bằng cử nhân, kinh nghiệm HR 3+ năm',
            ],
            [
                'department' => 'FINANCE',
                'code' => 'ACCOUNTANT',
                'title' => 'Kế toán',
                'level' => 'junior',
                'min_salary' => 10000000,
                'max_salary' => 18000000,
                'responsibilities' => 'Ghi sổ kế toán, lập báo cáo tài chính',
                'requirements' => 'Bằng cử nhân Kế toán, chứng chỉ hành nghề',
            ],
        ];

        foreach ($positionData as $index => $data) {
            $position = Position::create([
                'store_id' => $store->id,
                'department_id' => $departments[$data['department']]->id,
                'code' => $data['code'],
                'title' => $data['title'],
                'level' => $data['level'],
                'min_salary' => $data['min_salary'],
                'max_salary' => $data['max_salary'],
                'responsibilities' => $data['responsibilities'],
                'requirements' => $data['requirements'],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
            $positions[$data['code']] = $position;
        }

        return $positions;
    }

    private function createEmployees($store, $departments, $positions)
    {
        $employees = [];
        $users = User::whereHas('stores', function($query) use ($store) {
            $query->where('store_id', $store->id);
        })->get();
        
        $employeeData = [
            [
                'position' => 'SALES_MANAGER',
                'full_name' => 'Nguyễn Văn Quản',
                'first_name' => 'Quản',
                'last_name' => 'Nguyễn Văn',
                'id_number' => '123456789',
                'date_of_birth' => '1985-03-15',
                'gender' => 'male',
                'phone' => '0901111111',
                'email' => 'quan.nv@company.com',
                'basic_salary' => 20000000,
                'employment_type' => 'full_time',
                'contract_type' => 'permanent',
            ],
            [
                'position' => 'SALES_STAFF',
                'full_name' => 'Trần Thị Lan',
                'first_name' => 'Lan',
                'last_name' => 'Trần Thị',
                'id_number' => '987654321',
                'date_of_birth' => '1992-07-20',
                'gender' => 'female',
                'phone' => '0902222222',
                'email' => 'lan.tt@company.com',
                'basic_salary' => 12000000,
                'employment_type' => 'full_time',
                'contract_type' => 'permanent',
            ],
            [
                'position' => 'WAREHOUSE_MANAGER',
                'full_name' => 'Lê Văn Kho',
                'first_name' => 'Kho',
                'last_name' => 'Lê Văn',
                'id_number' => '456789123',
                'date_of_birth' => '1988-11-10',
                'gender' => 'male',
                'phone' => '0903333333',
                'email' => 'kho.lv@company.com',
                'basic_salary' => 16000000,
                'employment_type' => 'full_time',
                'contract_type' => 'permanent',
            ],
            [
                'position' => 'WAREHOUSE_STAFF',
                'full_name' => 'Phạm Thị Hoa',
                'first_name' => 'Hoa',
                'last_name' => 'Phạm Thị',
                'id_number' => '789123456',
                'date_of_birth' => '1995-05-25',
                'gender' => 'female',
                'phone' => '0904444444',
                'email' => 'hoa.pt@company.com',
                'basic_salary' => 9000000,
                'employment_type' => 'full_time',
                'contract_type' => 'permanent',
            ],
            [
                'position' => 'ACCOUNTANT',
                'full_name' => 'Hoàng Văn Toán',
                'first_name' => 'Toán',
                'last_name' => 'Hoàng Văn',
                'id_number' => '321654987',
                'date_of_birth' => '1990-09-12',
                'gender' => 'male',
                'phone' => '0905555555',
                'email' => 'toan.hv@company.com',
                'basic_salary' => 14000000,
                'employment_type' => 'full_time',
                'contract_type' => 'permanent',
            ],
        ];

        foreach ($employeeData as $index => $data) {
            $position = $positions[$data['position']];
            $user = $users->skip($index)->first();
            
            $employee = Employee::create([
                'store_id' => $store->id,
                'user_id' => $user ? $user->id : null,
                'department_id' => $position->department_id,
                'position_id' => $position->id,
                'employee_code' => Employee::generateEmployeeCode($store->id),
                'full_name' => $data['full_name'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'id_number' => $data['id_number'],
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'address' => 'TP.HCM',
                'hire_date' => now()->subMonths(rand(1, 24)),
                'employment_type' => $data['employment_type'],
                'contract_type' => $data['contract_type'],
                'contract_start_date' => now()->subMonths(rand(1, 24)),
                'basic_salary' => $data['basic_salary'],
                'currency' => 'VND',
                'pay_frequency' => 'monthly',
                'working_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '17:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '17:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '17:00'],
                    'thursday' => ['start' => '08:00', 'end' => '17:00'],
                    'friday' => ['start' => '08:00', 'end' => '17:00'],
                    'saturday' => ['start' => '08:00', 'end' => '12:00'],
                ],
                'weekly_hours' => 44,
                'vacation_days' => 12,
                'sick_leave_days' => 30,
                'status' => 'active',
                'health_insurance' => true,
                'social_insurance' => true,
            ]);
            $employees[] = $employee;
        }

        return $employees;
    }

    private function createTimesheets($store, $employees)
    {
        foreach ($employees as $employee) {
            // Create timesheets for the last 30 days
            for ($i = 30; $i >= 1; $i--) {
                $workDate = now()->subDays($i);
                
                // Skip weekends for some employees
                if ($workDate->isWeekend() && rand(0, 2) == 0) {
                    continue;
                }
                
                $checkInTime = $workDate->copy()->setTime(8, rand(0, 30), 0);
                $checkOutTime = $workDate->copy()->setTime(17, rand(0, 30), 0);
                
                if (rand(0, 10) > 8) { // 20% chance of overtime
                    $checkOutTime->addHours(rand(1, 3));
                }
                
                EmployeeTimesheet::create([
                    'store_id' => $store->id,
                    'employee_id' => $employee->id,
                    'work_date' => $workDate->toDateString(),
                    'check_in_time' => $checkInTime,
                    'check_out_time' => $checkOutTime,
                    'break_start_time' => $workDate->copy()->setTime(12, 0, 0),
                    'break_end_time' => $workDate->copy()->setTime(13, 0, 0),
                    'regular_hours' => 8,
                    'overtime_hours' => max(0, $checkOutTime->diffInHours($checkInTime) - 9), // 8 work + 1 break
                    'break_hours' => 1,
                    'total_hours' => $checkOutTime->diffInHours($checkInTime) - 1,
                    'status' => 'present',
                    'is_approved' => true,
                    'approved_by' => 1,
                    'approved_at' => $workDate->addDay(),
                ]);
            }
        }
    }
}
