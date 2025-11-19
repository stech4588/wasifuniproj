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
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->integer('unit_id')->unsigned();
                $table->foreign('unit_id')->references('id')->on('product_units');
                $table->integer('sale_id')->unsigned()->nullable();
                $table->foreign('sale_id')->references('id')->on('sales');
                $table->integer('serial_no');
                $table->text('description')->nullable();
                $table->integer('category_id')->unsigned();
                $table->foreign('category_id')->references('id')->on('categories');
                $table->integer('total_quantity');
                $table->boolean('is_active');
                $table->integer('created_by')->unsigned();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');;
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
