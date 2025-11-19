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
        if (!Schema::hasTable('product_variant')) {
            Schema::create('product_variant', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id')->unsigned();
                $table->foreign('product_id')->references('id')->on('products');
                $table->integer('size_id')->unsigned()->nullable();
                $table->foreign('size_id')->references('id')->on('sizes');
                $table->string('color')->nullable();
                $table->integer('quantity');
                $table->integer('price');
                $table->integer('created_by')->unsigned();
                $table->foreign('created_by')->references('id')->on('users');
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
        //
    }
};
