# Requirements Document

## Introduction

Hệ thống BoxPos cần có một bộ seeders cơ bản để tạo dữ liệu mẫu cho việc phát triển và testing. Seeders phải tuân theo cấu trúc package của project và hỗ trợ multi-store architecture. Mỗi package sẽ có seeders riêng để tạo dữ liệu mẫu cho các bảng thuộc package đó, đảm bảo tính độc lập và dễ bảo trì.

## Requirements

### Requirement 1

**User Story:** Là một developer, tôi muốn có seeders cơ bản cho core system tables, để có thể khởi tạo dữ liệu cần thiết cho việc phát triển và testing.

#### Acceptance Criteria

1. WHEN chạy seeder THEN hệ thống SHALL tạo admin user mặc định với thông tin đăng nhập chuẩn
2. WHEN chạy seeder THEN hệ thống SHALL tạo ít nhất 2 stores mẫu với thông tin đầy đủ
3. WHEN chạy seeder THEN hệ thống SHALL tạo user-store relationships cho admin user
4. WHEN chạy seeder THEN hệ thống SHALL tạo cache và jobs tables cần thiết cho Laravel

### Requirement 2

**User Story:** Là một developer, tôi muốn có seeders cho material management module, để có thể test các chức năng quản lý vật liệu xây dựng.

#### Acceptance Criteria

1. WHEN chạy seeder THEN hệ thống SHALL tạo các material categories phổ biến (xi măng, sắt, gạch, cát, đá...)
2. WHEN chạy seeder THEN hệ thống SHALL tạo các material units chuẩn (kg, m3, cái, bao, tấn...)
3. WHEN chạy seeder THEN hệ thống SHALL tạo building materials mẫu với specifications đầy đủ
4. WHEN chạy seeder THEN hệ thống SHALL tạo material suppliers với contact information
5. WHEN chạy seeder THEN hệ thống SHALL tạo material inventory cho mỗi store
6. WHEN chạy seeder THEN hệ thống SHALL tạo material pricing history cho các materials

### Requirement 3

**User Story:** Là một developer, tôi muốn có seeders cho sales management module, để có thể test các chức năng bán hàng và quản lý đơn hàng.

#### Acceptance Criteria

1. WHEN chạy seeder THEN hệ thống SHALL tạo customers mẫu cho mỗi store
2. WHEN chạy seeder THEN hệ thống SHALL tạo product categories và products/services
3. WHEN chạy seeder THEN hệ thống SHALL tạo sales orders mẫu với items
4. WHEN chạy seeder THEN hệ thống SHALL tạo invoices tương ứng với sales orders
5. WHEN chạy seeder THEN hệ thống SHALL tạo payment methods chuẩn
6. WHEN chạy seeder THEN hệ thống SHALL tạo payments cho các invoices

### Requirement 4

**User Story:** Là một developer, tôi muốn có seeders cho employee management module, để có thể test các chức năng quản lý nhân viên.

#### Acceptance Criteria

1. WHEN chạy seeder THEN hệ thống SHALL tạo departments chuẩn (Bán hàng, Kho, Kế toán...)
2. WHEN chạy seeder THEN hệ thống SHALL tạo positions cho mỗi department
3. WHEN chạy seeder THEN hệ thống SHALL tạo employees mẫu cho mỗi store
4. WHEN chạy seeder THEN hệ thống SHALL tạo employee schedules và timesheets mẫu

### Requirement 5

**User Story:** Là một developer, tôi muốn có seeders cho financial management module, để có thể test các chức năng quản lý tài chính.

#### Acceptance Criteria

1. WHEN chạy seeder THEN hệ thống SHALL tạo cash categories chuẩn (Thu, Chi, Chuyển khoản...)
2. WHEN chạy seeder THEN hệ thống SHALL tạo cash accounts cho mỗi store (Tiền mặt, Ngân hàng...)
3. WHEN chạy seeder THEN hệ thống SHALL tạo cash transactions mẫu

### Requirement 6

**User Story:** Là một developer, tôi muốn có seeders cho marketing & loyalty module, để có thể test các chức năng khuyến mãi và khách hàng thân thiết.

#### Acceptance Criteria

1. WHEN chạy seeder THEN hệ thống SHALL tạo promotions mẫu cho mỗi store
2. WHEN chạy seeder THEN hệ thống SHALL tạo loyalty programs với membership tiers
3. WHEN chạy seeder THEN hệ thống SHALL tạo loyalty memberships cho customers
4. WHEN chạy seeder THEN hệ thống SHALL tạo loyalty transactions mẫu

### Requirement 7

**User Story:** Là một developer, tôi muốn có seeders cho reporting & notification modules, để có thể test các chức năng báo cáo và thông báo.

#### Acceptance Criteria

1. WHEN chạy seeder THEN hệ thống SHALL tạo report templates chuẩn
2. WHEN chạy seeder THEN hệ thống SHALL tạo notification templates chuẩn
3. WHEN chạy seeder THEN hệ thống SHALL tạo report dashboards mẫu

### Requirement 8

**User Story:** Là một developer, tôi muốn seeders tuân theo package structure và coding rules, để đảm bảo tính nhất quán và dễ bảo trì.

#### Acceptance Criteria

1. WHEN tạo seeder THEN mỗi package SHALL có seeder riêng trong thư mục src/Database/Seeders
2. WHEN tạo seeder THEN seeders SHALL sử dụng Loggable trait để log activities
3. WHEN tạo seeder THEN seeders SHALL sử dụng factories khi có thể
4. WHEN tạo seeder THEN seeders SHALL đảm bảo store_id isolation
5. WHEN chạy seeder THEN hệ thống SHALL có thể chạy từng package seeder độc lập
6. WHEN chạy seeder THEN hệ thống SHALL có DatabaseSeeder tổng hợp tất cả package seeders

### Requirement 9

**User Story:** Là một developer, tôi muốn seeders hỗ trợ multi-store architecture, để đảm bảo dữ liệu được tách biệt đúng cách giữa các stores.

#### Acceptance Criteria

1. WHEN chạy seeder THEN tất cả business data SHALL có store_id tương ứng
2. WHEN chạy seeder THEN dữ liệu SHALL được phân bổ đều giữa các stores
3. WHEN chạy seeder THEN relationships giữa các entities SHALL đảm bảo store isolation
4. WHEN chạy seeder THEN system tables (users, cache, jobs) SHALL không có store_id

### Requirement 10

**User Story:** Là một developer, tôi muốn seeders có thể chạy trong môi trường development và testing, để hỗ trợ việc phát triển và kiểm thử.

#### Acceptance Criteria

1. WHEN chạy seeder trong development THEN hệ thống SHALL tạo dữ liệu phong phú để demo
2. WHEN chạy seeder trong testing THEN hệ thống SHALL tạo dữ liệu tối thiểu cần thiết
3. WHEN chạy seeder THEN hệ thống SHALL có thể reset và chạy lại seeders
4. WHEN chạy seeder THEN hệ thống SHALL báo lỗi rõ ràng nếu có vấn đề
5. WHEN chạy seeder THEN hệ thống SHALL log quá trình seeding để debug