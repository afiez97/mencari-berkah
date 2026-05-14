<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_token')->unique();
            $table->string('mode');
            $table->integer('total_questions');
            $table->integer('correct')->default(0);
            $table->integer('wrong')->default(0);
            $table->integer('duration_seconds')->default(0);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_sessions');
    }
};
