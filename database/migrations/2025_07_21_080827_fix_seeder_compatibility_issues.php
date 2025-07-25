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
        // Fix report_templates table to match seeder expectations
        if (Schema::hasTable('report_templates')) {
            Schema::table('report_templates', function (Blueprint $table) {
                // Add missing columns that seeders expect
                if (!Schema::hasColumn('report_templates', 'output_format')) {
                    $table->enum('output_format', ['pdf', 'excel', 'csv'])->default('pdf')->after('calculations');
                }
                if (!Schema::hasColumn('report_templates', 'query')) {
                    $table->text('query')->nullable()->after('output_format');
                }
                if (!Schema::hasColumn('report_templates', 'parameters')) {
                    $table->json('parameters')->nullable()->after('query');
                }
            });
        }

        // Fix report_instances table to match seeder expectations
        if (Schema::hasTable('report_instances')) {
            Schema::table('report_instances', function (Blueprint $table) {
                // Add missing columns that seeders expect
                if (!Schema::hasColumn('report_instances', 'output_format')) {
                    $table->enum('output_format', ['pdf', 'excel', 'csv'])->default('pdf');
                }
            });
        }

        // Fix cash_accounts table to add missing columns
        if (Schema::hasTable('cash_accounts')) {
            Schema::table('cash_accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('cash_accounts', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('is_active');
                }
            });
        }

        // Fix products table to add missing columns that seeders expect
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'unit')) {
                    $table->string('unit', 50)->default('pcs')->after('weight');
                }
                if (!Schema::hasColumn('products', 'selling_price')) {
                    $table->decimal('selling_price', 15, 2)->default(0)->after('cost_price');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns
        if (Schema::hasTable('report_templates')) {
            Schema::table('report_templates', function (Blueprint $table) {
                $table->dropColumn(['output_format', 'query', 'parameters']);
            });
        }

        if (Schema::hasTable('report_instances')) {
            Schema::table('report_instances', function (Blueprint $table) {
                $table->dropColumn(['output_format']);
            });
        }

        if (Schema::hasTable('cash_accounts')) {
            Schema::table('cash_accounts', function (Blueprint $table) {
                $table->dropColumn(['is_default']);
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn(['unit', 'selling_price']);
            });
        }
    }
};
