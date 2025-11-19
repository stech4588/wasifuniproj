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
        if (!Schema::hasTable('order_item_details')) {
            Schema::create('order_item_details', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('order_id')->unsigned();
                $table->integer('product_id');
                $table->integer('product_variant_id');
                $table->string('product_name');
                $table->integer('price');
                $table->integer('quantity');
                $table->integer('created_by')->unsigned();
                $table->foreign('created_by')->references('id')->on('users');
                $table->foreign('order_id')->references('id')->on('orders');
                $table->foreign('product_variant_id')->references('id')->on('product_variant');
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
        Schema::dropIfExists('order_item_details');
    }
};
