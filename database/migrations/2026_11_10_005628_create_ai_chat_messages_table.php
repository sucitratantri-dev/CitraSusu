<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('ai_chat_messages')) {
            Schema::create('ai_chat_messages', function (Blueprint $table) {
                $table->id();
                $table->string('session_id', 100)->index();
                $table->string('sender', 20);
                $table->text('message');
                $table->timestamps();
                $table->index(['session_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
    }
};