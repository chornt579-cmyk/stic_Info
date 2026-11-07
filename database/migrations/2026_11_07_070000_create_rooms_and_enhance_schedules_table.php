<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create Rooms table for formal room entities
        if (!Schema::hasTable('rooms')) {
            Schema::create('rooms', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // e.g. 'Lab 1', 'Room 101'
                $table->string('name_kh')->nullable(); // e.g. 'បន្ទប់ពិសោធន៍ ១', 'បន្ទប់ ១០១'
                $table->string('building')->nullable(); // e.g. 'Building A', 'អគារ A'
                $table->string('floor')->nullable(); // e.g. '1', '2'
                $table->unsignedSmallInteger('capacity')->default(35);
                $table->enum('room_type', ['lecture', 'lab', 'workshop', 'hall'])->default('lecture');
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // 2. Enhance schedules table with room_id and high-performance conflict lookup indexes
        Schema::table('schedules', function (Blueprint $table) {
            // Drop legacy coarse unique indexes that prevented adjacent non-overlapping slots in the same shift
            // (e.g. 08:00-10:00 and 10:00-12:00 in morning shift)
            try {
                $table->dropUnique('schedule_teacher_unique');
            } catch (\Throwable $e) {
                // Ignore if index doesn't exist
            }

            try {
                $table->dropUnique('schedule_class_unique');
            } catch (\Throwable $e) {
                // Ignore if index doesn't exist
            }

            if (!Schema::hasColumn('schedules', 'room_id')) {
                $table->foreignId('room_id')->nullable()->after('shift')->constrained('rooms')->nullOnDelete();
            }

            // Conflict lookup composite indexes per governance manual section 3.5
            $table->index(['teacher_id', 'day_of_week'], 'schedules_teacher_day_idx');
            $table->index(['class_id', 'day_of_week'], 'schedules_class_day_idx');
            $table->index(['room_id', 'day_of_week'], 'schedules_room_day_idx');
        });

        // 3. PostgreSQL btree_gist and exclusion constraint for production DB
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist;');
            DB::statement('
                ALTER TABLE schedules ADD CONSTRAINT no_teacher_overlap EXCLUDE USING gist (
                    teacher_id WITH =,
                    day_of_week WITH =,
                    tsrange(start_time, end_time) WITH &&
                );
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE schedules DROP CONSTRAINT IF EXISTS no_teacher_overlap;');
        }

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('schedules_teacher_day_idx');
            $table->dropIndex('schedules_class_day_idx');
            $table->dropIndex('schedules_room_day_idx');

            if (Schema::hasColumn('schedules', 'room_id')) {
                $table->dropForeign(['room_id']);
                $table->dropColumn('room_id');
            }

            $table->unique(['teacher_id', 'day_of_week', 'shift'], 'schedule_teacher_unique');
            $table->unique(['class_id', 'day_of_week', 'shift'], 'schedule_class_unique');
        });

        Schema::dropIfExists('rooms');
    }
};
