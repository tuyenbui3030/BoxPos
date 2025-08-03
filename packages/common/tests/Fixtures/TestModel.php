<?php

namespace Packages\Common\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TestModel extends Model
{
    protected $table = 'test_models';
    
    protected $fillable = [
        'name',
        'description',
        'store_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Create the test table for this model
     */
    public static function createTable(): void
    {
        if (!Schema::hasTable('test_models')) {
            Schema::create('test_models', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Drop the test table
     */
    public static function dropTable(): void
    {
        Schema::dropIfExists('test_models');
    }
}