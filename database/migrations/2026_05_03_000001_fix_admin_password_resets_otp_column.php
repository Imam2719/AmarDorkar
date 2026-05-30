<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * admin_password_resets.otp কলামে আগে string(6) ছিল,
     * কিন্তু Hash::make() দিয়ে hash করা value 60+ characters।
     * তাই column টা unlimited string এ পরিবর্তন করা হচ্ছে।
     */
    public function up(): void
    {
        Schema::table('admin_password_resets', function (Blueprint $table) {
            $table->string('otp')->change(); // 6 char limit সরিয়ে unlimited করা হলো
        });
    }

    public function down(): void
    {
        Schema::table('admin_password_resets', function (Blueprint $table) {
            $table->string('otp', 6)->change();
        });
    }
};
