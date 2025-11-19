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
        if (!Schema::hasTable('addresses')) {
            Schema::create('addresses', function (Blueprint $table) {
                $table->increments('id');
                $table->enum('type', ['billing', 'shipping']);
                $table->integer('country_id')->unsigned();
                $table->foreign('country_id')->references('id')->on('countries');
                $table->integer('city_id')->unsigned();
                $table->foreign('city_id')->references('id')->on('cities');
                $table->string('street_1')->nullable();
                $table->string('street_2')->nullable();
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
        Schema::dropIfExists('addresses');
    }
};
