<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('buyer')->after('password')->index();
            $table->string('phone', 30)->nullable()->after('email');
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('chat_enabled')->default(false);
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role','phone','address','is_active','chat_enabled']);
        });
    }
};
