<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Add mobile to users if not exists
        if (!Schema::hasColumn('users', 'mobile')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('mobile', 20)->nullable()->after('email');
            });
        }

        // User messages from admin
        Schema::create('user_messages', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('body');
            $table->enum('channel', ['email', 'sms', 'both'])->default('email');
            $table->json('recipient_user_ids')->nullable(); // null = all users
            $table->boolean('sent_to_all')->default(false);
            $table->foreignId('sent_by')->constrained('admins')->cascadeOnDelete();
            $table->integer('total_sent')->default(0);
            $table->integer('total_failed')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_messages');
        if (Schema::hasColumn('users', 'mobile')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('mobile');
            });
        }
    }
};
