<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'store_id')) {
            Schema::table('customers', function (Blueprint $table) {
                // 1. Thêm cột store_id cho phép NULL trước
                $table->foreignId('store_id')->nullable()->after('id');

                // 2. Index tạm (tránh quên)
                $table->index(['store_id']);
                $table->index(['store_id', 'customer_code']);
                $table->index(['store_id', 'email']);
            });

            // 3. Gán giá trị store_id cho các bản ghi cũ nếu cần
            // Ví dụ gán tất cả về store_id = 1
            DB::table('customers')->update(['store_id' => 1]);

            // 4. Đảm bảo store_id = 1 tồn tại trong bảng stores
            // Nếu chưa có thì thêm thủ công hoặc seed trước

            // 5. Sửa cột store_id thành NOT NULL và thêm ràng buộc FK
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('store_id')->nullable(false)->change();
                $table->foreign('store_id')
                    ->references('id')
                    ->on('stores')
                    ->onDelete('cascade');
            });
        }

        // Bạn có thể copy logic tương tự cho các bảng khác
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'store_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropForeign(['store_id']);
                $table->dropIndex(['store_id', 'email']);
                $table->dropIndex(['store_id', 'customer_code']);
                $table->dropIndex(['store_id']);
                $table->dropColumn('store_id');
            });
        }
    }
};
