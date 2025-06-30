# SPEC CHI TIẾT HỆ THỐNG QUẢN LÝ BÁN HÀNG TENANTPOS
## Tài liệu phân tích màn hình và chức năng để phát triển phần mềm

## 1. TỔNG QUAN HỆ THỐNG

### 1.1 Kiến trúc tổng thể
- **Backend Framework**: Laravel (PHP Framework)
- **Frontend**: Laravel Livewire (Full-stack framework)
- **UI Library**: Bootstrap + Alpine.js (via Livewire)
- **Multi-tenant**: Mỗi retailer có subdomain riêng
- **Database**: MySQL/PostgreSQL
- **Responsive**: Hỗ trợ desktop và mobile

### 1.2 Cấu trúc layout chung
Tất cả màn hình đều có cấu trúc layout chung:
- **Header**: Logo, navigation, user menu
- **Main Navigation**: Menu chính với dropdown
- **Content Area**: Nội dung động của từng màn hình
- **Footer**: Support widgets, chat, notifications

## 2. PHÂN TÍCH CHI TIẾT CÁC COMPONENT CHUNG

### 2.1 HEADER COMPONENT
**Vị trí**: Cố định ở đầu trang, hiển thị trên tất cả màn hình

**Cấu trúc bố cục**:
```
[Logo] [Mobile Menu] ---- [Widgets] [Support] [Language] [Branch] [Notifications] [Settings] [User]
```

**Chi tiết các thành phần**:

#### 2.1.1 Logo Area
- **Logo tùy chỉnh**: Hiển thị logo của doanh nghiệp (nếu có)
- **Logo mặc định**: Logo TenantPOS (nếu không có logo tùy chỉnh)
- **Company name**: Tên công ty hiển thị trong title
- **Mobile menu button**: Hamburger menu cho mobile

#### 2.1.2 Widget Bar (chỉ desktop)
- **Omni-channel menu**: Tích hợp đa kênh bán hàng
- **TenantShip integration**: Tích hợp vận chuyển
- **TenantPOS Connect**: Kết nối ứng dụng

#### 2.1.3 Support Tools Dropdown
- **Theme selector**: 8 màu chủ đề (color-df, color1-8)
- **Support links**: Liên kết hỗ trợ khách hàng
- **Download tools**: TeamViewer, UltraViewer, AnyDesk
- **Fingerprint app**: Ứng dụng chấm công vân tay
- **Feedback system**: Góp ý và đánh giá

#### 2.1.4 Language Selector
- **Dropdown**: Chọn ngôn ngữ (Tiếng Việt, English)
- **Livewire Component**: Component UI với Alpine.js
- **Auto-save**: Lưu preference của user

#### 2.1.5 Branch Selector
- **Hiển thị**: Chỉ khi có nhiều hơn 1 chi nhánh
- **Dropdown**: Danh sách chi nhánh active
- **Filter**: Có thể search chi nhánh
- **Auto-switch**: Chuyển context khi chọn chi nhánh khác

#### 2.1.6 Notification Center
- **Icon**: Bell icon với badge số lượng
- **Dropdown**: Danh sách thông báo
- **Real-time**: Cập nhật real-time

#### 2.1.7 Settings Menu
- **Version mới**: Popover với template động
- **Version cũ**: Dropdown menu cố định
- **Permissions**: Hiển thị menu theo quyền hạn

#### 2.1.8 User Profile Menu
- **Avatar**: Icon user
- **Dropdown**: Account, KMA, Logout
- **Hover load**: Load dynamic content khi hover

### 2.2 MAIN NAVIGATION BAR
**Vị trí**: Bên dưới header, navigation chính của hệ thống

**Cấu trúc bố cục**:
```
[Menu Items with Dropdowns] ---- [Quick Actions: Cashier | Kitchen | Sale]
```

**Chi tiết các thành phần**:

#### 2.2.1 Dynamic Menu System
- **Menu structure**: Được load từ server theo quyền hạn
- **Mega menu**: Menu lớn với nhiều cột cho module phức tạp
- **Dropdown menu**: Menu thả xuống cho module đơn giản
- **Active state**: Highlight menu đang được chọn
- **Permission-based**: Chỉ hiển thị menu user có quyền

#### 2.2.2 Menu Types
- **Regular dropdown**: Menu thông thường với submenu
- **Mega menu**: Menu lớn với groups và subgroups
- **Special menus**: Menu đặc biệt (Bán online, Báo cáo)

#### 2.2.3 Quick Action Buttons
- **Cashier (Thu ngân)**: Link đến màn hình bán hàng F&B
- **Kitchen (Bếp)**: Link đến màn hình bếp (chỉ F&B)
- **Sale (Bán hàng)**: Link đến màn hình bán hàng chính

#### 2.2.4 Responsive Behavior
- **Desktop**: Horizontal menu với hover effects
- **Mobile**: Collapsible menu với touch events
- **Overlay**: Overlay background khi mở menu mobile

## 3. PHÂN TÍCH CHI TIẾT CÁC MÀN HÌNH

### 3.1 MÀN HÌNH TỔNG QUAN (DASHBOARD)
**Livewire Component**: `Dashboard\Overview`
**Route**: `/dashboard`

#### 3.1.1 Initial Setup Modal
**Mục đích**: Thiết lập thông tin ban đầu cho doanh nghiệp mới

**Bố cục**:
```
[Left Panel: Icon + Image] | [Right Panel: Setup Form]
```

**Left Panel**:
- **Icon**: Money check edit icon
- **Title**: "Thiết lập dữ liệu ban đầu"
- **Image**: Sample data illustration

**Right Panel - Setup Form**:

**Header**:
- **Welcome message**: "Chào mừng [Username] đến với TenantPOS"
- **Description**: Mô tả về TenantPOS

**Form Fields**:

1. **Industry Selection (Ngành nghề)**:
   - **Component**: Livewire Select Component
   - **Data source**: `industryList`
   - **Fields**: `Name`, `Id`
   - **Behavior**: Ẩn khi `currentIndustryId == '15'`

2. **Working Time (Thời gian hoạt động)**:
   - **Component**: Radio buttons group
   - **Options**:
     - `0`: Sắp mở cửa
     - `1`: Dưới 1 năm
     - `2`: Từ 1-5 năm
     - `3`: Trên 5 năm
   - **Model**: `workingTime`

3. **Business Model (Mô hình kinh doanh)**:
   - **Component**: Checkbox group (multiple selection)
   - **Options**:
     - `businessStore`: Bán tại cửa hàng
     - `businessFanpage`: Fanpage Facebook
     - `businessFBProfile`: Profile Facebook
     - `businessEcommerce`: Thương mại điện tử
     - `businessTiktokShop`: TikTok Shop
     - `businessWebsite`: Website
   - **Layout**: 2 columns

4. **Daily Sales Range (Số đơn hàng/ngày)**:
   - **Component**: Radio buttons
   - **Data source**: `numOrderOptions`
   - **Model**: `infoShipping.NumOrderType`

5. **Sample Data Option**:
   - **Component**: Checkbox
   - **Label**: "Sử dụng dữ liệu mẫu TenantPOS để trải nghiệm tính năng"
   - **Model**: `useSampleData`
   - **Event**: `filterbyProductType()` on change

**Footer**:
- **Submit button**: "Hoàn thành" với icon check
- **Action**: `confirmIntitialData()`

#### 3.1.2 Main Dashboard (sau khi setup)
**Nội dung**: Màn hình dashboard chính sẽ được render bởi Livewire component

### 3.2 MODULE BÁO CÁO (REPORTS)

#### 3.2.1 Báo cáo Bán hàng
**Livewire Component**: `Reports\SalesReport`
**Route**: `/reports/sales`

**Mục đích**: Phân tích doanh thu và hiệu quả bán hàng

**Cấu trúc màn hình**:
```
[Filter Panel]
[Summary Cards]
[Charts & Graphs]
[Detailed Data Grid]
[Export Controls]
```

**Filter Panel**:
- **Date Range Picker**: Chọn khoảng thời gian
- **Branch Filter**: Lọc theo chi nhánh
- **Employee Filter**: Lọc theo nhân viên bán hàng
- **Product Category Filter**: Lọc theo danh mục sản phẩm
- **Customer Group Filter**: Lọc theo nhóm khách hàng

**Summary Cards**:
- **Total Revenue**: Tổng doanh thu
- **Total Orders**: Tổng số đơn hàng
- **Average Order Value**: Giá trị đơn hàng trung bình
- **Total Profit**: Tổng lợi nhuận
- **Growth Rate**: Tỷ lệ tăng trưởng so với kỳ trước

**Charts Section**:
- **Revenue Trend**: Biểu đồ xu hướng doanh thu theo thời gian
- **Top Products**: Top sản phẩm bán chạy
- **Sales by Channel**: Doanh thu theo kênh bán hàng
- **Sales by Time**: Phân tích theo giờ/ngày/tháng

**Data Grid**:
- **Columns**: Ngày, Doanh thu, Số đơn, Lợi nhuận, Tăng trưởng
- **Sorting**: Sắp xếp theo các cột
- **Pagination**: Phân trang
- **Row Details**: Chi tiết khi click vào row

#### 3.2.2 Báo cáo Cuối ngày
**Livewire Component**: `Reports\EndOfDayReport`
**Route**: `/reports/end-of-day`

**Mục đích**: Tổng kết hoạt động kinh doanh cuối ngày

**Sections**:
- **Cash Summary**: Tổng kết tiền mặt
- **Sales Summary**: Tổng kết bán hàng
- **Payment Methods**: Phân tích theo phương thức thanh toán
- **Staff Performance**: Hiệu suất nhân viên
- **Inventory Changes**: Thay đổi tồn kho

#### 3.2.3 Báo cáo Hàng hóa
**Livewire Component**: `Reports\ProductReport`
**Route**: `/reports/products`

**Mục đích**: Phân tích hiệu quả sản phẩm và tồn kho

**Sections**:
- **Product Performance**: Hiệu suất sản phẩm
- **Inventory Status**: Tình trạng tồn kho
- **ABC Analysis**: Phân tích ABC
- **Slow Moving Items**: Hàng tồn kho chậm
- **Stock Alerts**: Cảnh báo tồn kho

#### 3.2.4 Báo cáo Khách hàng
**Livewire Component**: `Reports\CustomerReport`
**Route**: `/reports/customers`

**Sections**:
- **Customer Acquisition**: Khách hàng mới
- **Customer Retention**: Khách hàng quay lại
- **Customer Lifetime Value**: Giá trị khách hàng
- **Customer Segmentation**: Phân khúc khách hàng
- **Loyalty Analysis**: Phân tích lòng trung thành

#### 3.2.5 Báo cáo Nhân viên
**Livewire Component**: `Reports\EmployeeReport`
**Route**: `/reports/employees`

**Sections**:
- **Sales Performance**: Hiệu suất bán hàng
- **Commission Report**: Báo cáo hoa hồng
- **Attendance Report**: Báo cáo chấm công
- **Productivity Analysis**: Phân tích năng suất

#### 3.2.6 Báo cáo Tài chính
**Livewire Component**: `Reports\FinancialReport`
**Route**: `/reports/financial`

**Sections**:
- **Profit & Loss**: Báo cáo lãi lỗ
- **Cash Flow**: Dòng tiền
- **Revenue Analysis**: Phân tích doanh thu
- **Cost Analysis**: Phân tích chi phí
- **Financial Ratios**: Các chỉ số tài chính

#### 3.2.7 Báo cáo Kênh bán hàng
**Livewire Component**: `Reports\SalesChannelReport`
**Route**: `/reports/sales-channels`

**Mục đích**: Phân tích hiệu quả các kênh bán hàng và đa kênh

**Sections**:
- **Channel Performance**: Hiệu suất từng kênh bán hàng
  - **In-store Sales**: Bán hàng tại cửa hàng
  - **Online Sales**: Bán hàng online
  - **Facebook Sales**: Bán hàng qua Facebook
  - **E-commerce Integration**: Tích hợp thương mại điện tử
  - **Mobile App Sales**: Bán hàng qua ứng dụng mobile

- **Channel Comparison**: So sánh các kênh
  - **Revenue by Channel**: Doanh thu theo kênh
  - **Order Volume**: Số lượng đơn hàng
  - **Average Order Value**: Giá trị đơn hàng trung bình
  - **Customer Acquisition**: Thu hút khách hàng
  - **Conversion Rates**: Tỷ lệ chuyển đổi

- **Multi-channel Analytics**: Phân tích đa kênh
  - **Cross-channel Customers**: Khách hàng đa kênh
  - **Channel Attribution**: Phân bổ theo kênh
  - **Customer Journey**: Hành trình khách hàng
  - **Channel Synergy**: Hiệu ứng tương tác kênh

- **Integration Metrics**: Chỉ số tích hợp
  - **API Performance**: Hiệu suất API
  - **Sync Status**: Trạng thái đồng bộ
  - **Error Rates**: Tỷ lệ lỗi
  - **Data Consistency**: Tính nhất quán dữ liệu

#### 3.2.8 Báo cáo Nhà cung cấp
**Livewire Component**: `Reports\SupplierReport`
**Route**: `/reports/suppliers`

**Mục đích**: Phân tích hiệu quả và mối quan hệ với nhà cung cấp

**Sections**:
- **Supplier Performance**: Hiệu suất nhà cung cấp
  - **Purchase Volume**: Khối lượng mua hàng
  - **Purchase Value**: Giá trị mua hàng
  - **Order Frequency**: Tần suất đặt hàng
  - **Lead Time**: Thời gian giao hàng
  - **Quality Metrics**: Chỉ số chất lượng

- **Cost Analysis**: Phân tích chi phí
  - **Cost Trends**: Xu hướng giá cả
  - **Price Comparison**: So sánh giá
  - **Discount Analysis**: Phân tích chiết khấu
  - **Payment Terms**: Điều kiện thanh toán
  - **Total Cost of Ownership**: Tổng chi phí sở hữu

- **Supplier Reliability**: Độ tin cậy nhà cung cấp
  - **On-time Delivery**: Giao hàng đúng hạn
  - **Order Accuracy**: Độ chính xác đơn hàng
  - **Return Rates**: Tỷ lệ trả hàng
  - **Defect Rates**: Tỷ lệ lỗi
  - **Service Quality**: Chất lượng dịch vụ

- **Relationship Management**: Quản lý mối quan hệ
  - **Contract Status**: Trạng thái hợp đồng
  - **Payment History**: Lịch sử thanh toán
  - **Communication Log**: Nhật ký liên lạc
  - **Risk Assessment**: Đánh giá rủi ro

#### 3.2.9 Báo cáo Đặt hàng
**Livewire Component**: `Reports\OrderReport`
**Route**: `/reports/orders`

**Mục đích**: Phân tích quy trình và hiệu quả đặt hàng

**Sections**:
- **Order Analytics**: Phân tích đơn hàng
  - **Order Volume Trends**: Xu hướng số lượng đơn
  - **Order Value Analysis**: Phân tích giá trị đơn
  - **Order Status Distribution**: Phân bổ trạng thái đơn
  - **Seasonal Patterns**: Mẫu theo mùa
  - **Peak Time Analysis**: Phân tích giờ cao điểm

- **Order Processing**: Xử lý đơn hàng
  - **Processing Time**: Thời gian xử lý
  - **Fulfillment Rate**: Tỷ lệ hoàn thành
  - **Cancellation Rate**: Tỷ lệ hủy đơn
  - **Return Rate**: Tỷ lệ trả hàng
  - **Customer Satisfaction**: Hài lòng khách hàng

- **Product Performance**: Hiệu suất sản phẩm
  - **Best Sellers**: Sản phẩm bán chạy
  - **Slow Movers**: Sản phẩm bán chậm
  - **Cross-sell Analysis**: Phân tích bán chéo
  - **Bundle Performance**: Hiệu suất combo
  - **Category Analysis**: Phân tích danh mục

- **Customer Behavior**: Hành vi khách hàng
  - **Order Frequency**: Tần suất đặt hàng
  - **Basket Analysis**: Phân tích giỏ hàng
  - **Customer Segments**: Phân khúc khách hàng
  - **Loyalty Impact**: Tác động lòng trung thành
  - **Repeat Purchase**: Mua hàng lặp lại

### 3.3 MODULE HÀNG HÓA (INVENTORY)

#### 3.3.1 Danh sách Hàng hóa
**Livewire Component**: `Products\ProductList`
**Route**: `/products`

**Mục đích**: Quản lý catalog sản phẩm và thông tin hàng hóa

**Cấu trúc màn hình**:
```
[Toolbar: Search + Filters + Actions]
[Product Grid/List View]
[Pagination]
```

**Toolbar Section**:
- **Search Box**: Tìm kiếm theo tên, mã sản phẩm, barcode
- **Category Filter**: Lọc theo danh mục sản phẩm
- **Status Filter**: Lọc theo trạng thái (Active/Inactive)
- **Supplier Filter**: Lọc theo nhà cung cấp
- **Stock Status**: Lọc theo tình trạng tồn kho
- **Add Product Button**: Thêm sản phẩm mới
- **Import/Export**: Nhập/xuất dữ liệu
- **Bulk Actions**: Hành động hàng loạt

**Product Grid**:
- **Columns**:
  - **Image**: Hình ảnh sản phẩm (thumbnail)
  - **Code**: Mã sản phẩm
  - **Name**: Tên sản phẩm
  - **Category**: Danh mục
  - **Unit**: Đơn vị tính
  - **Cost Price**: Giá vốn
  - **Sale Price**: Giá bán
  - **Stock**: Tồn kho
  - **Status**: Trạng thái
  - **Actions**: Sửa, Xóa, Xem chi tiết

**Grid Features**:
- **Sorting**: Sắp xếp theo các cột
- **Filtering**: Lọc inline
- **Row Selection**: Chọn nhiều rows
- **Context Menu**: Menu chuột phải
- **Inline Editing**: Sửa trực tiếp trên grid

**Product Detail Modal/Page**:
- **Basic Info**: Tên, mã, mô tả, danh mục
- **Pricing**: Giá vốn, giá bán, bảng giá
- **Inventory**: Tồn kho, min/max stock
- **Images**: Gallery hình ảnh
- **Barcode**: Mã vạch
- **Attributes**: Thuộc tính mở rộng
- **Variants**: Biến thể sản phẩm (size, color, etc.)

#### 3.3.2 Thiết lập Giá
**Livewire Component**: `Products\PricingSetup`
**Route**: `/products/pricing`

**Mục đích**: Quản lý bảng giá và chính sách giá

**Sections**:

**Price List Management**:
- **Default Price List**: Bảng giá mặc định
- **Customer Group Pricing**: Giá theo nhóm khách hàng
- **Branch Specific Pricing**: Giá theo chi nhánh
- **Promotional Pricing**: Giá khuyến mãi

**Bulk Price Update**:
- **Category-based**: Cập nhật theo danh mục
- **Percentage Adjustment**: Điều chỉnh theo %
- **Fixed Amount**: Điều chỉnh số tiền cố định
- **Price Rules**: Quy tắc tính giá tự động

**Price History**:
- **Change Log**: Lịch sử thay đổi giá
- **Effective Dates**: Ngày hiệu lực
- **User Tracking**: Người thay đổi

#### 3.3.3 Trả hàng Nhập
**Livewire Component**: `Products\PurchaseReturns`
**Route**: `/products/purchase-returns`

**Mục đích**: Xử lý trả hàng cho nhà cung cấp

**Workflow**:
1. **Select Purchase Order**: Chọn đơn nhập hàng gốc
2. **Select Items**: Chọn sản phẩm cần trả
3. **Return Reason**: Lý do trả hàng
4. **Quantity & Condition**: Số lượng và tình trạng
5. **Financial Impact**: Tác động tài chính
6. **Approval**: Phê duyệt (nếu cần)

**Return Form**:
- **Return Information**: Thông tin phiếu trả
- **Supplier Details**: Thông tin nhà cung cấp
- **Items Grid**: Danh sách hàng trả
- **Financial Summary**: Tổng kết tài chính
- **Documents**: Chứng từ đính kèm

### 3.4 MODULE KHO HÀNG (WAREHOUSE)

#### 3.4.1 Kiểm kho
**Livewire Component**: `Warehouse\StockTake`
**Route**: `/warehouse/stock-take`

**Mục đích**: Kiểm tra và điều chỉnh tồn kho thực tế

**Workflow**:
1. **Create Stock Take**: Tạo phiếu kiểm kho
2. **Select Products**: Chọn sản phẩm cần kiểm
3. **Physical Count**: Đếm thực tế
4. **Compare & Adjust**: So sánh và điều chỉnh
5. **Approve**: Phê duyệt kết quả

**Stock Take Form**:
- **Header**: Thông tin phiếu kiểm
- **Product Selection**: Chọn sản phẩm
- **Count Interface**: Giao diện đếm hàng
- **Variance Report**: Báo cáo chênh lệch
- **Adjustment Actions**: Hành động điều chỉnh

**Features**:
- **Barcode Scanning**: Quét mã vạch
- **Mobile Support**: Hỗ trợ mobile
- **Batch Processing**: Xử lý hàng loạt
- **Real-time Updates**: Cập nhật real-time

#### 3.4.2 Xuất hủy
**Livewire Component**: `Warehouse\Disposal`
**Route**: `/warehouse/disposal`

**Mục đích**: Xử lý hàng hỏng, hết hạn, không bán được

**Disposal Form**:
- **Disposal Information**: Thông tin phiếu xuất hủy
- **Items Selection**: Chọn hàng cần hủy
- **Disposal Reason**: Lý do hủy hàng
- **Cost Impact**: Tác động chi phí
- **Approval Workflow**: Quy trình phê duyệt

**Disposal Reasons**:
- **Expired**: Hết hạn sử dụng
- **Damaged**: Hàng hỏng
- **Obsolete**: Hàng lỗi thời
- **Quality Issues**: Vấn đề chất lượng
- **Other**: Lý do khác

### 3.5 MODULE KHÁCH HÀNG (CUSTOMERS)

#### 3.5.1 Quản lý Khách hàng
**Livewire Component**: `Customers\CustomerManagement`
**Route**: `/customers`

**Mục đích**: Quản lý thông tin và mối quan hệ khách hàng

**Cấu trúc màn hình**:
```
[Search & Filter Bar]
[Customer Grid/Cards View]
[Customer Detail Panel/Modal]
```

**Search & Filter Bar**:
- **Search Box**: Tìm theo tên, SĐT, email
- **Customer Group Filter**: Lọc theo nhóm khách hàng
- **Status Filter**: Trạng thái (Active/Inactive)
- **Location Filter**: Lọc theo địa điểm
- **Registration Date**: Lọc theo ngày đăng ký
- **Add Customer Button**: Thêm khách hàng mới

**Customer Grid**:
- **Columns**:
  - **Avatar**: Ảnh đại diện
  - **Name**: Tên khách hàng
  - **Phone**: Số điện thoại
  - **Email**: Email
  - **Group**: Nhóm khách hàng
  - **Total Spent**: Tổng chi tiêu
  - **Last Order**: Đơn hàng cuối
  - **Loyalty Points**: Điểm tích lũy
  - **Actions**: Sửa, Xem chi tiết, Xóa

**Customer Detail Modal**:

**Basic Information Tab**:
- **Personal Info**: Tên, SĐT, email, ngày sinh
- **Address**: Địa chỉ chi tiết
- **Customer Group**: Phân nhóm khách hàng
- **Notes**: Ghi chú về khách hàng

**Purchase History Tab**:
- **Order List**: Danh sách đơn hàng
- **Purchase Summary**: Tổng kết mua hàng
- **Favorite Products**: Sản phẩm yêu thích
- **Purchase Patterns**: Mẫu mua hàng

**Loyalty Program Tab**:
- **Points Balance**: Số điểm hiện tại
- **Points History**: Lịch sử tích điểm
- **Rewards**: Phần thưởng đã đổi
- **Tier Status**: Cấp độ thành viên

**Communication Tab**:
- **Contact History**: Lịch sử liên hệ
- **Marketing Preferences**: Sở thích marketing
- **Feedback**: Phản hồi từ khách hàng

### 3.6 MODULE NHÂN VIÊN (EMPLOYEES)

#### 3.6.1 Danh sách Nhân viên
**Livewire Component**: `Employees\EmployeeList`
**Route**: `/employees`

**Employee Grid**:
- **Columns**:
  - **Avatar**: Ảnh nhân viên
  - **Employee Code**: Mã nhân viên
  - **Full Name**: Họ tên
  - **Position**: Chức vụ
  - **Department**: Phòng ban
  - **Phone**: Số điện thoại
  - **Email**: Email
  - **Status**: Trạng thái
  - **Actions**: Sửa, Xem chi tiết

#### 3.6.2 Bảng Chấm công
**Livewire Component**: `Employees\Timesheet`
**Route**: `/employees/timesheet`

**Timesheet Interface**:
- **Calendar View**: Lịch chấm công
- **Employee Filter**: Lọc theo nhân viên
- **Date Range**: Chọn khoảng thời gian
- **Attendance Status**: Trạng thái chấm công
- **Overtime Tracking**: Theo dõi tăng ca

**Daily Timesheet**:
- **Check In/Out**: Giờ vào/ra
- **Break Time**: Thời gian nghỉ
- **Total Hours**: Tổng giờ làm
- **Overtime**: Giờ tăng ca
- **Notes**: Ghi chú

#### 3.6.3 Bảng Hoa hồng
**Livewire Component**: `Employees\Commission`
**Route**: `/employees/commission`

**Commission Calculation**:
- **Sales Performance**: Hiệu suất bán hàng
- **Commission Rate**: Tỷ lệ hoa hồng
- **Target Achievement**: Đạt chỉ tiêu
- **Bonus**: Thưởng thêm
- **Total Commission**: Tổng hoa hồng

#### 3.6.4 Bảng Lương
**Livewire Component**: `Employees\Payroll`
**Route**: `/employees/payroll`

**Payroll Components**:
- **Basic Salary**: Lương cơ bản
- **Overtime Pay**: Lương tăng ca
- **Commission**: Hoa hồng
- **Allowances**: Phụ cấp
- **Deductions**: Các khoản trừ
- **Net Pay**: Lương thực nhận

#### 3.6.5 Lịch Làm việc
**Livewire Component**: `Employees\Schedule`
**Route**: `/employees/schedule`

**Schedule Management**:
- **Calendar View**: Lịch làm việc
- **Shift Assignment**: Phân ca làm việc
- **Employee Availability**: Khả năng làm việc
- **Schedule Conflicts**: Xung đột lịch
- **Shift Swapping**: Đổi ca làm việc

#### 3.6.6 Thiết lập Nhân viên
**File**: `TenantPOS - Nhân viên - Thiết lập nhân viên.html`
**Route**: `/#/employees/setup/`

**Mục đích**: Cấu hình và thiết lập thông tin nhân viên

**Main Setup Interface**:
- **Employee Configuration**: Cấu hình nhân viên tổng thể
- **Department Setup**: Thiết lập phòng ban
- **Position Management**: Quản lý chức vụ
- **Permission Templates**: Mẫu phân quyền
- **Workflow Configuration**: Cấu hình quy trình

**Setup Categories**:
- **Basic Information**: Thông tin cơ bản
- **Access Control**: Kiểm soát truy cập
- **Performance Settings**: Cài đặt hiệu suất
- **Integration Setup**: Thiết lập tích hợp

#### 3.6.7 Thiết lập Nhân viên - Bước 1
**File**: `TenantPOS - Nhân viên - Thiết lập nhân viên - 1.html`
**Route**: `/#/employees/setup/step1/`

**Mục đích**: Bước 1 - Thông tin cơ bản nhân viên

**Wizard Step 1 Content**:
- **Personal Information**: Thông tin cá nhân
  - **Full Name**: Họ và tên
  - **Employee Code**: Mã nhân viên
  - **ID Number**: Số CMND/CCCD
  - **Date of Birth**: Ngày sinh
  - **Gender**: Giới tính
  - **Phone Number**: Số điện thoại
  - **Email Address**: Địa chỉ email
  - **Address**: Địa chỉ thường trú

- **Employment Details**: Chi tiết tuyển dụng
  - **Hire Date**: Ngày vào làm
  - **Employment Type**: Loại hình lao động
  - **Probation Period**: Thời gian thử việc
  - **Contract Type**: Loại hợp đồng
  - **Work Location**: Địa điểm làm việc

**Navigation**:
- **Next Button**: Chuyển sang bước 2
- **Save Draft**: Lưu nháp
- **Cancel**: Hủy bỏ

#### 3.6.8 Thiết lập Nhân viên - Bước 2
**File**: `TenantPOS - Nhân viên - Thiết lập nhân viên - 2.html`
**Route**: `/#/employees/setup/step2/`

**Mục đích**: Bước 2 - Chức vụ và phòng ban

**Wizard Step 2 Content**:
- **Organizational Structure**: Cơ cấu tổ chức
  - **Department**: Phòng ban
  - **Position**: Chức vụ
  - **Job Title**: Tên công việc
  - **Reporting Manager**: Quản lý trực tiếp
  - **Team Assignment**: Phân công nhóm

- **Work Schedule**: Lịch làm việc
  - **Working Hours**: Giờ làm việc
  - **Shift Pattern**: Mẫu ca làm việc
  - **Break Time**: Thời gian nghỉ
  - **Overtime Policy**: Chính sách tăng ca
  - **Holiday Schedule**: Lịch nghỉ lễ

**Navigation**:
- **Previous Button**: Quay lại bước 1
- **Next Button**: Chuyển sang bước 3
- **Save Draft**: Lưu nháp

#### 3.6.9 Thiết lập Nhân viên - Bước 3
**File**: `TenantPOS - Nhân viên - Thiết lập nhân viên - 3.html`
**Route**: `/#/employees/setup/step3/`

**Mục đích**: Bước 3 - Phân quyền và truy cập

**Wizard Step 3 Content**:
- **System Access**: Truy cập hệ thống
  - **User Account**: Tài khoản người dùng
  - **Username**: Tên đăng nhập
  - **Password Policy**: Chính sách mật khẩu
  - **Two-Factor Auth**: Xác thực 2 yếu tố
  - **Session Settings**: Cài đặt phiên làm việc

- **Permission Management**: Quản lý quyền hạn
  - **Role Assignment**: Phân quyền vai trò
  - **Module Access**: Truy cập module
  - **Feature Permissions**: Quyền tính năng
  - **Data Access Level**: Mức truy cập dữ liệu
  - **Branch Permissions**: Quyền chi nhánh

- **Security Settings**: Cài đặt bảo mật
  - **IP Restrictions**: Hạn chế IP
  - **Time Restrictions**: Hạn chế thời gian
  - **Device Limitations**: Giới hạn thiết bị
  - **Audit Logging**: Ghi log kiểm toán

**Navigation**:
- **Previous Button**: Quay lại bước 2
- **Next Button**: Chuyển sang bước 4
- **Save Draft**: Lưu nháp

#### 3.6.10 Thiết lập Nhân viên - Bước 4
**File**: `TenantPOS - Nhân viên - Thiết lập nhân viên - 4.html`
**Route**: `/#/employees/setup/step4/`

**Mục đích**: Bước 4 - Lương và phúc lợi

**Wizard Step 4 Content**:
- **Salary Information**: Thông tin lương
  - **Basic Salary**: Lương cơ bản
  - **Salary Grade**: Bậc lương
  - **Pay Frequency**: Tần suất trả lương
  - **Currency**: Đơn vị tiền tệ
  - **Effective Date**: Ngày hiệu lực

- **Compensation Structure**: Cơ cấu thù lao
  - **Commission Rate**: Tỷ lệ hoa hồng
  - **Bonus Eligibility**: Điều kiện thưởng
  - **Overtime Rate**: Mức lương tăng ca
  - **Holiday Pay**: Lương ngày lễ
  - **Performance Incentives**: Khuyến khích hiệu suất

- **Benefits Package**: Gói phúc lợi
  - **Health Insurance**: Bảo hiểm y tế
  - **Social Insurance**: Bảo hiểm xã hội
  - **Vacation Days**: Ngày nghỉ phép
  - **Sick Leave**: Nghỉ ốm
  - **Training Budget**: Ngân sách đào tạo

- **Tax Information**: Thông tin thuế
  - **Tax ID**: Mã số thuế
  - **Tax Exemptions**: Miễn giảm thuế
  - **Dependent Info**: Thông tin người phụ thuộc

**Final Actions**:
- **Previous Button**: Quay lại bước 3
- **Complete Setup**: Hoàn thành thiết lập
- **Save & Add Another**: Lưu và thêm nhân viên khác
- **Preview**: Xem trước thông tin

### 3.7 MODULE NHẬP HÀNG (PURCHASING)

#### 3.7.1 Nhà cung cấp
**File**: `TenantPOS - Nhập hàng - Nhà cung cấp.html`
**Route**: `/#/purchasing/suppliers/`

**Supplier Management**:
- **Supplier List**: Danh sách nhà cung cấp
- **Contact Information**: Thông tin liên hệ
- **Payment Terms**: Điều kiện thanh toán
- **Performance Metrics**: Chỉ số hiệu suất
- **Purchase History**: Lịch sử nhập hàng

#### 3.7.2 Nhập hàng
**File**: `TenantPOS - Nhập hàng - Nhập hàng.html`
**Route**: `/#/purchasing/orders/`

**Purchase Order Process**:
1. **Create PO**: Tạo đơn đặt hàng
2. **Supplier Selection**: Chọn nhà cung cấp
3. **Product Selection**: Chọn sản phẩm
4. **Pricing & Terms**: Giá cả và điều kiện
5. **Approval**: Phê duyệt
6. **Goods Receipt**: Nhận hàng
7. **Invoice Matching**: Đối chiếu hóa đơn

### 3.8 MODULE ĐƠN HÀNG (ORDERS)

#### 3.8.1 Hóa đơn
**File**: `TenantPOS - Đơn hàng - Hóa đơn.html`
**Route**: `/#/orders/invoices/`

**Invoice Management**:
- **Invoice List**: Danh sách hóa đơn
- **Invoice Creation**: Tạo hóa đơn mới
- **Invoice Templates**: Mẫu hóa đơn
- **Payment Tracking**: Theo dõi thanh toán
- **E-invoice Integration**: Tích hợp hóa đơn điện tử

**Invoice Form**:
- **Customer Selection**: Chọn khách hàng
- **Product Selection**: Chọn sản phẩm
- **Pricing & Discounts**: Giá và giảm giá
- **Tax Calculation**: Tính thuế
- **Payment Methods**: Phương thức thanh toán
- **Notes & Terms**: Ghi chú và điều khoản

#### 3.8.2 Trả hàng
**File**: `TenantPOS - Đơn hàng - Trả hàng.html`
**Route**: `/#/orders/returns/`

**Return Process**:
1. **Original Invoice**: Hóa đơn gốc
2. **Return Items**: Sản phẩm trả
3. **Return Reason**: Lý do trả hàng
4. **Condition Assessment**: Đánh giá tình trạng
5. **Refund Processing**: Xử lý hoàn tiền
6. **Inventory Update**: Cập nhật tồn kho

#### 3.8.3 Vận đơn
**File**: `TenantPOS - Đơn hàng - Vận đơn.html`
**Route**: `/#/orders/shipments/`

**Shipping Management**:
- **Shipment Creation**: Tạo vận đơn
- **Carrier Selection**: Chọn đơn vị vận chuyển
- **Tracking Integration**: Tích hợp tracking
- **Delivery Status**: Trạng thái giao hàng
- **Shipping Cost**: Chi phí vận chuyển

#### 3.8.4 Đặt hàng
**File**: `TenantPOS - Đơn hàng - Đặt hàng.html`
**Route**: `/#/orders/orders/`

**Order Management**:
- **Order Creation**: Tạo đơn hàng
- **Order Processing**: Xử lý đơn hàng
- **Order Fulfillment**: Thực hiện đơn hàng
- **Order Tracking**: Theo dõi đơn hàng
- **Order History**: Lịch sử đơn hàng

#### 3.8.5 Đối tác Giao hàng
**File**: `TenantPOS - Đơn hàng - Đối tác giao hàng.html`
**Route**: `/#/orders/delivery-partners/`

**Delivery Partner Management**:
- **Partner List**: Danh sách đối tác
- **Integration Setup**: Thiết lập tích hợp
- **Rate Management**: Quản lý giá cước
- **Performance Tracking**: Theo dõi hiệu suất
- **API Configuration**: Cấu hình API

### 3.9 MODULE SỔ QUỸ (CASH MANAGEMENT)

#### 3.9.1 Sổ quỹ
**File**: `TenantPOS - Sổ quỹ.html`
**Route**: `/#/cash/transactions/`

**Cash Management Interface**:
```
[Cash Accounts Summary]
[Transaction Entry Form]
[Transaction History Grid]
[Reports & Analytics]
```

**Cash Accounts Summary**:
- **Account Cards**: Thẻ tài khoản tiền
- **Current Balance**: Số dư hiện tại
- **Today's Transactions**: Giao dịch hôm nay
- **Account Status**: Trạng thái tài khoản

**Transaction Entry Form**:
- **Transaction Type**: Loại giao dịch (Thu/Chi)
- **Amount**: Số tiền
- **Category**: Danh mục
- **Description**: Mô tả
- **Reference**: Chứng từ tham chiếu
- **Account**: Tài khoản tiền

**Transaction History**:
- **Date Range Filter**: Lọc theo thời gian
- **Transaction Grid**: Bảng giao dịch
- **Balance Tracking**: Theo dõi số dư
- **Reconciliation**: Đối chiếu

**Cash Flow Reports**:
- **Daily Cash Flow**: Dòng tiền hàng ngày
- **Cash Position**: Vị thế tiền mặt
- **Category Analysis**: Phân tích theo danh mục
- **Trend Analysis**: Phân tích xu hướng

## 4. COMMON UI COMPONENTS

### 4.1 Data Grid Component
**Features**:
- **Sorting**: Sắp xếp đa cột
- **Filtering**: Lọc inline và advanced
- **Pagination**: Phân trang với page size options
- **Selection**: Single/multiple row selection
- **Export**: Excel, PDF export
- **Column Management**: Ẩn/hiện cột, resize
- **Responsive**: Adaptive trên mobile

### 4.2 Form Components
**Input Types**:
- **Text Input**: Với validation
- **Number Input**: Với format và range
- **Date Picker**: Kendo DatePicker
- **Dropdown**: Kendo DropDownList
- **Multi-select**: Kendo MultiSelect
- **File Upload**: Với preview
- **Rich Text Editor**: CKEditor integration

### 4.3 Modal/Popup Components
**Types**:
- **Standard Modal**: Thông tin, form
- **Confirmation Dialog**: Xác nhận hành động
- **Alert/Notification**: Thông báo
- **Loading Overlay**: Hiển thị loading
- **Image Gallery**: Xem hình ảnh

### 4.4 Navigation Components
**Types**:
- **Breadcrumb**: Đường dẫn navigation
- **Tabs**: Tab navigation
- **Wizard**: Multi-step process
- **Tree View**: Hierarchical data
- **Menu**: Context menu, dropdown menu

## 5. RESPONSIVE DESIGN

### 5.1 Breakpoints
- **Desktop**: >= 1024px
- **Tablet**: 768px - 1023px
- **Mobile**: < 768px

### 5.2 Mobile Adaptations
- **Collapsible Menu**: Hamburger menu
- **Touch Optimized**: Larger touch targets
- **Swipe Gestures**: Navigation gestures
- **Simplified UI**: Reduced complexity
- **Offline Support**: Local storage

## 6. PERFORMANCE CONSIDERATIONS

### 6.1 Loading Strategies
- **Lazy Loading**: Load content on demand
- **Progressive Loading**: Load critical content first
- **Caching**: Browser and CDN caching
- **Compression**: Gzip, minification
- **Image Optimization**: WebP, responsive images

### 6.2 Data Management
- **Pagination**: Server-side pagination
- **Virtual Scrolling**: Large datasets
- **Debounced Search**: Reduce API calls
- **Optimistic Updates**: Immediate UI feedback
- **Background Sync**: Offline data sync

## 7. SECURITY FEATURES

### 7.1 Authentication
- **Session Management**: Secure sessions
- **Multi-factor Auth**: Optional 2FA
- **Password Policies**: Strong passwords
- **Account Lockout**: Brute force protection

### 7.2 Authorization
- **Role-based Access**: Permission system
- **Feature Flags**: Hide/show features
- **Data Isolation**: Multi-tenant security
- **Audit Logging**: Track user actions

## 8. INTEGRATION POINTS

### 8.1 Third-party Services
- **Payment Gateways**: Multiple providers
- **Shipping APIs**: Delivery partners
- **Accounting Systems**: ERP integration
- **E-commerce Platforms**: Online stores
- **Social Media**: Facebook, TikTok
