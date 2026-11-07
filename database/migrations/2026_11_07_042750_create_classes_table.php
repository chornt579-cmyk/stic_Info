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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('name');
            $table->string('name_kh')->nullable();
            $table->unsignedTinyInteger('year')->default(1);
            $table->enum('shift', ['morning', 'afternoon', 'evening'])->default('morning');
            $table->string('room')->nullable();
            $table->string('floor')->nullable();
            $table->unsignedSmallInteger('capacity')->default(35);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
