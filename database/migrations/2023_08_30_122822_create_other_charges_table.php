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
        if (!Schema::hasTable('other_charges')) {
            Schema::create('other_charges', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->integer('amount');
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
        Schema::dropIfExists('other_charges');
    }
};
