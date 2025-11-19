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
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('amount', 10, 2)->nullable();
                $table->decimal('percentage', 10, 2)->nullable();
                $table->enum('type', ['percentage', 'amount']);
                $table->date('start_date');
                $table->date('end_date');
                $table->integer('created_by')->unsigned();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('coupons');
    }
};
