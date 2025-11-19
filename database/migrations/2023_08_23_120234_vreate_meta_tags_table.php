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
        if (!Schema::hasTable('metatags')) {
            Schema::create('metatags', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('page_id');
                $table->string('name');
                $table->string('content');
                $table->foreign('page_id')->references('id')->on('pages');
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
        Schema::dropIfExists('metatags');
    }
};
