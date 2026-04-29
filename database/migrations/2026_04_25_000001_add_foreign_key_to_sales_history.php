<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_history', function (Blueprint $table) {
            // Add the missing foreign key constraint on order_id.
            // Using nullOnDelete so sales records are kept as audit trail
            // even if the order is deleted (orphan prevention).
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales_history', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });
    }
};
