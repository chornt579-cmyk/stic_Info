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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['student', 'teacher', 'admin'])->default('student')->after('email');
            $table->string('phone')->nullable()->after('role');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete()->after('phone');
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete()->after('department_id');
            $table->enum('status', ['active', 'pending', 'suspended'])->default('active')->after('class_id');
            $table->timestamp('approved_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['class_id']);
            $table->dropColumn(['role', 'phone', 'department_id', 'class_id', 'status', 'approved_at']);
        });
    }
};
