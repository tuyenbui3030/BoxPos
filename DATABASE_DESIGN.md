# BOXPOS DATABASE DESIGN SPECIFICATION
## Thiết kế Database khoa học với Data Cutoff và Multi-tenant Architecture

## 1. KIẾN TRÚC TỔNG THỂ

### 1.1 Multi-tenant Strategy
- **Single Database, Multi-tenant**: Sử dụng `store_id` để phân tách dữ liệu
- **Data Isolation**: Mọi bảng business đều có `store_id` foreign key
- **Performance**: Indexes được tối ưu cho multi-tenant queries
- **Security**: Row-level security thông qua Laravel policies

### 1.2 Data Cutoff Strategy
- **Archive Tables**: Mỗi bảng chính có bảng archive tương ứng
- **Cutoff Periods**: Configurable cutoff periods (monthly/quarterly/yearly)
- **Performance**: Partitioning theo thời gian cho large tables
- **Access**: Historical data vẫn accessible qua unified views

## 2. CORE ENTITIES DESIGN

### 2.1 Store Management (Multi-tenant Core)
```sql
-- Stores (Tenant Management)
stores: id, name, subdomain, domain, settings, status, created_at, updated_at

-- User-Store Relationships
user_stores: id, user_id, store_id, role, permissions, is_active, created_at

-- Store Settings
store_settings: id, store_id, key, value, type, created_at, updated_at
```

### 2.2 Product Management
```sql
-- Product Categories (Hierarchical)
product_categories: id, store_id, parent_id, name, code, description, sort_order, is_active, created_at, updated_at

-- Products (Core Product Info)
products: id, store_id, category_id, name, code, barcode, description, unit, cost_price, sale_price,
         min_stock, max_stock, is_active, created_at, updated_at, created_by, updated_by

-- Product Variants (Size, Color, etc.)
product_variants: id, product_id, name, code, barcode, cost_price, sale_price, stock_quantity,
                 attributes, is_active, created_at, updated_at

-- Product Images
product_images: id, product_id, image_path, alt_text, sort_order, is_primary, created_at

-- Price Lists (Multi-pricing)
price_lists: id, store_id, name, type, customer_group_id, branch_id, is_active, created_at, updated_at

-- Product Prices
product_prices: id, product_id, price_list_id, price, effective_from, effective_to, created_at
```

### 2.3 Customer Management
```sql
-- Customer Groups
customer_groups: id, store_id, name, discount_rate, loyalty_multiplier, is_active, created_at, updated_at

-- Customers
customers: id, store_id, group_id, code, name, phone, email, address, date_of_birth, gender,
          total_spent, total_orders, loyalty_points, last_order_date, is_active, created_at, updated_by

-- Customer Addresses
customer_addresses: id, customer_id, type, address_line_1, address_line_2, city, district, ward,
                   postal_code, is_default, created_at, updated_at
```

## 3. INVENTORY & WAREHOUSE DESIGN

### 3.1 Stock Management
```sql
-- Stock (Current Stock Levels)
stocks: id, store_id, product_id, variant_id, branch_id, quantity, reserved_quantity,
       cost_price, last_updated, updated_by

-- Stock Movements (All Stock Changes)
stock_movements: id, store_id, product_id, variant_id, branch_id, movement_type, reference_type,
                reference_id, quantity_before, quantity_change, quantity_after, cost_price,
                reason, notes, created_at, created_by

-- Stock Takes (Physical Inventory)
stock_takes: id, store_id, branch_id, reference_number, status, scheduled_date, started_at,
            completed_at, notes, created_by, approved_by, created_at, updated_at

-- Stock Take Items
stock_take_items: id, stock_take_id, product_id, variant_id, system_quantity, counted_quantity,
                 variance, cost_price, notes, created_at, updated_at

-- Disposals (Waste/Damage)
disposals: id, store_id, branch_id, reference_number, disposal_date, reason, total_cost,
          status, notes, created_by, approved_by, created_at, updated_at

-- Disposal Items
disposal_items: id, disposal_id, product_id, variant_id, quantity, cost_price, reason, notes
```

## 4. ORDER & SALES DESIGN

### 4.1 Order Management
```sql
-- Orders (Sales Orders)
orders: id, store_id, branch_id, customer_id, order_number, order_date, status, channel,
       subtotal, discount_amount, tax_amount, total_amount, payment_status,
       notes, created_by, updated_at

-- Order Items
order_items: id, order_id, product_id, variant_id, quantity, unit_price, discount_amount,
            total_price, cost_price, notes, created_at

-- Invoices
invoices: id, store_id, order_id, invoice_number, invoice_date, due_date, status,
         subtotal, discount_amount, tax_amount, total_amount, paid_amount,
         payment_status, notes, created_by, updated_at

-- Invoice Items
invoice_items: id, invoice_id, product_id, variant_id, quantity, unit_price, discount_amount,
              total_price, tax_rate, tax_amount, created_at

-- Returns
returns: id, store_id, order_id, invoice_id, return_number, return_date, reason, status,
        subtotal, refund_amount, notes, created_by, approved_by, created_at, updated_at

-- Return Items
return_items: id, return_id, product_id, variant_id, quantity, unit_price, refund_amount,
             condition, reason, notes, created_at
```

## 5. EMPLOYEE & HR DESIGN

### 5.1 Employee Management
```sql
-- Departments
departments: id, store_id, name, description, manager_id, is_active, created_at, updated_at

-- Positions
positions: id, store_id, department_id, name, description, base_salary, commission_rate,
          is_active, created_at, updated_at

-- Employees
employees: id, store_id, department_id, position_id, employee_code, first_name, last_name,
          phone, email, address, date_of_birth, hire_date, employment_type, status,
          base_salary, commission_rate, created_at, updated_at

-- Timesheets
timesheets: id, store_id, employee_id, date, check_in, check_out, break_minutes,
           total_hours, overtime_hours, status, notes, created_at, approved_by

-- Payroll
payroll: id, store_id, employee_id, period_start, period_end, base_salary, overtime_pay,
        commission, allowances, deductions, gross_pay, tax, net_pay, status,
        processed_at, created_by

-- Employee Schedules
employee_schedules: id, store_id, employee_id, date, shift_start, shift_end, break_minutes,
                   status, notes, created_at, updated_at
```

## 6. FINANCIAL & CASH MANAGEMENT DESIGN

### 6.1 Cash Management
```sql
-- Cash Accounts
cash_accounts: id, store_id, branch_id, name, type, currency, opening_balance,
              current_balance, is_active, created_at, updated_at

-- Cash Transactions
cash_transactions: id, store_id, account_id, transaction_date, type, category,
                  amount, balance_after, reference_type, reference_id,
                  description, created_by, created_at

-- Payment Methods
payment_methods: id, store_id, name, type, account_id, is_active, created_at, updated_at

-- Suppliers
suppliers: id, store_id, name, code, contact_person, phone, email, address,
          payment_terms, tax_number, is_active, created_at, updated_at

-- Purchase Orders
purchase_orders: id, store_id, supplier_id, po_number, order_date, expected_date, status,
                subtotal, tax_amount, total_amount, notes, created_by, updated_at

-- Purchase Order Items
purchase_order_items: id, purchase_order_id, product_id, variant_id, quantity, unit_price,
                     total_price, received_quantity, created_at
```

## 7. DATA CUTOFF & ARCHIVING DESIGN

### 7.1 Archive Strategy
```sql
-- Archive Configuration
archive_configs: id, table_name, cutoff_period, retention_months, last_cutoff_date,
                next_cutoff_date, is_active, created_at, updated_at

-- Archive Tables (Example for orders)
orders_archive: [same structure as orders] + archived_at, archive_batch_id

-- Archive Batches
archive_batches: id, table_name, cutoff_date, records_count, status, started_at,
                completed_at, notes, created_by

-- Data Retention Policies
data_retention_policies: id, store_id, table_name, retention_period, archive_after,
                        delete_after, is_active, created_at, updated_at
```

### 7.2 Performance Optimization Tables
```sql
-- Daily Sales Summary (Pre-aggregated for performance)
daily_sales_summary: id, store_id, branch_id, date, total_orders, total_revenue,
                    total_profit, avg_order_value, top_product_id, created_at, updated_at

-- Product Performance Cache
product_performance_cache: id, store_id, product_id, period_start, period_end,
                          quantity_sold, revenue, profit, rank, last_updated

-- Customer Analytics Cache
customer_analytics_cache: id, store_id, customer_id, period_start, period_end,
                         total_orders, total_spent, avg_order_value, last_order_date, last_updated
```

## 8. LOGGING & AUDIT DESIGN (Mandatory per CODING_RULES)

### 8.1 Activity Logging
```sql
-- Activity Logs (Mandatory for all business operations)
activity_logs: id, store_id, user_id, action, model_type, model_id, old_values,
              new_values, ip_address, user_agent, created_at

-- Error Logs
error_logs: id, store_id, user_id, error_type, message, stack_trace, context,
           ip_address, created_at

-- Performance Logs
performance_logs: id, store_id, operation, execution_time, query_count, memory_usage,
                 context, created_at

-- User Sessions
user_sessions: id, user_id, store_id, session_id, ip_address, user_agent, login_at,
              last_activity, logout_at, is_active
```

## 9. REPORTING SUPPORT DESIGN

### 9.1 Analytics Tables
```sql
-- Sales Analytics
sales_analytics: id, store_id, branch_id, date, hour, channel, total_orders, total_revenue,
                total_profit, avg_order_value, created_at

-- Inventory Analytics
inventory_analytics: id, store_id, product_id, date, opening_stock, closing_stock,
                    stock_in, stock_out, stock_value, created_at

-- Customer Behavior Analytics
customer_behavior: id, store_id, customer_id, date, visits, orders, revenue,
                  avg_order_value, last_visit, created_at
```

## 10. INDEXES & CONSTRAINTS STRATEGY

### 10.1 Critical Indexes
```sql
-- Multi-tenant Performance Indexes
CREATE INDEX idx_products_store_active ON products(store_id, is_active, created_at);
CREATE INDEX idx_orders_store_date ON orders(store_id, order_date, status);
CREATE INDEX idx_customers_store_active ON customers(store_id, is_active, last_order_date);
CREATE INDEX idx_stock_movements_store_date ON stock_movements(store_id, created_at, movement_type);

-- Archive Performance Indexes
CREATE INDEX idx_orders_archive_date ON orders_archive(store_id, archived_at);
CREATE INDEX idx_activity_logs_store_date ON activity_logs(store_id, created_at);

-- Reporting Indexes
CREATE INDEX idx_daily_sales_store_date ON daily_sales_summary(store_id, date);
CREATE INDEX idx_product_performance_store_period ON product_performance_cache(store_id, period_start, period_end);
```

### 10.2 Data Integrity Constraints
```sql
-- Foreign Key Constraints
ALTER TABLE products ADD CONSTRAINT fk_products_store FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE;
ALTER TABLE orders ADD CONSTRAINT fk_orders_store FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE;
ALTER TABLE customers ADD CONSTRAINT fk_customers_store FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE;

-- Business Rule Constraints
ALTER TABLE products ADD CONSTRAINT chk_products_prices CHECK (cost_price >= 0 AND sale_price >= 0);
ALTER TABLE orders ADD CONSTRAINT chk_orders_amounts CHECK (subtotal >= 0 AND total_amount >= 0);
ALTER TABLE stocks ADD CONSTRAINT chk_stocks_quantity CHECK (quantity >= 0);

-- Unique Constraints
ALTER TABLE products ADD CONSTRAINT uk_products_store_code UNIQUE (store_id, code);
ALTER TABLE customers ADD CONSTRAINT uk_customers_store_code UNIQUE (store_id, code);
ALTER TABLE employees ADD CONSTRAINT uk_employees_store_code UNIQUE (store_id, employee_code);
```

## 11. PACKAGE ORGANIZATION STRATEGY

### 11.1 Recommended Package Structure
```
packages/
├── product/          # Product Management
├── inventory/        # Inventory & Warehouse
├── customer/         # Customer Management (already exists)
├── order/           # Order & Sales Management
├── employee/        # Employee & HR Management
├── finance/         # Financial & Cash Management
├── report/          # Reporting & Analytics
├── archive/         # Data Cutoff & Archiving
└── audit/           # Logging & Audit (extends existing log package)
```

### 11.2 Cross-Package Dependencies
- **Core Dependencies**: store, user, log (existing packages)
- **Business Dependencies**: product → inventory, order → product, order → customer
- **Reporting Dependencies**: report → all business packages
- **Archive Dependencies**: archive → all business packages

## 12. IMPLEMENTATION PRIORITY

### 12.1 Phase 1 - Core Business (High Priority)
1. **Product Management**: Categories, Products, Variants, Pricing
2. **Customer Management**: Enhance existing customer package
3. **Basic Inventory**: Stock levels, Stock movements

### 12.2 Phase 2 - Operations (Medium Priority)
1. **Order Management**: Orders, Invoices, Returns
2. **Employee Management**: Basic HR functionality
3. **Cash Management**: Basic financial tracking

### 12.3 Phase 3 - Advanced Features (Lower Priority)
1. **Advanced Inventory**: Stock takes, Disposals
2. **Advanced HR**: Payroll, Schedules, Commission
3. **Reporting & Analytics**: Pre-aggregated reports
4. **Data Cutoff**: Archive system implementation

## 13. MIGRATION STRATEGY

### 13.1 Migration Naming Convention
```
YYYY_MM_DD_HHMMSS_create_[package]_[table_name]_table.php
YYYY_MM_DD_HHMMSS_add_[field]_to_[table_name]_table.php
YYYY_MM_DD_HHMMSS_create_[table_name]_archive_table.php
```

### 13.2 Data Migration Considerations
- **Existing Data**: Preserve existing customers, users, stores data
- **Backward Compatibility**: Ensure existing functionality continues to work
- **Performance**: Use chunked migrations for large datasets
- **Rollback**: All migrations must be reversible