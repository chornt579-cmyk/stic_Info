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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->enum('target_type', [
                'public',
                'all_students',
                'class',
                'dept_students',
                'all_teachers',
                'dept_teachers',
            ])->default('public');
            $table->unsignedBigInteger('target_id')->nullable();
            $table->boolean('is_telegram_sent')->default(false);
            $table->timestamps();

            $table->index(['target_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
