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
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->increments('id');
                $table->enum('status', ['draft','confirmed','rejected','delivered']);
                $table->enum('invoice_status', ['paid', 'unpaid'])->nullable();
                $table->integer('address_id')->nullable();
                $table->integer('coupon_id')->nullable();
                $table->integer('other_charge_id')->nullable();
                $table->integer('sub_total');
                $table->integer('discount')->nullable();
                $table->integer('total_amount');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('orders');
    }
};
