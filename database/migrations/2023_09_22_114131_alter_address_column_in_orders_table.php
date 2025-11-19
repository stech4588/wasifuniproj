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
        if (Schema::hasColumn('orders', 'address_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('address_id');
            });
        }

        if (!Schema::hasColumn('orders', 'billing_address_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->integer('billing_address_id')->after('invoice_status');
            });
        }

        if (!Schema::hasColumn('orders', 'shipping_address_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->integer('shipping_address_id')->after('billing_address_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('orders', 'address_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->integer('address_id')->nullable()->after('invoice_status');
            });
        }

        if (Schema::hasColumn('orders', 'billing_address_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('billing_address_id');
            });
        }

        if (Schema::hasColumn('orders', 'shipping_address_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('shipping_address_id');
            });
        }
    }
};
