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
        if (!Schema::hasTable('sales')) {
            Schema::create('sales', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('sale_price');
                $table->string('name');
                $table->string('product_id');
                $table->date('start_date');
                $table->date('end_date');
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
        Schema::dropIfExists('sales');
    }
};
