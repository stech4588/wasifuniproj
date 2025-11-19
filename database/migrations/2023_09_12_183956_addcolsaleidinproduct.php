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
        if (!Schema::hasColumn('products', 'sale_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('sale_id')->unsigned()->nullable()->after('unit_id');
                $table->foreign('sale_id')->references('id')->on('sales');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('products', 'sale_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropConstrainedForeignId('sale_id');
            });
        }
    }
};
