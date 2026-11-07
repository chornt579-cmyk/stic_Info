<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\TimetableConflictService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TimetableConflictTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $teacher1;
    protected User $teacher2;
    protected Department $department;
    protected SchoolClass $class1;
    protected SchoolClass $class2;
    protected Room $room1;
    protected Room $room2;
    protected TimetableConflictService $conflictService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conflictService = app(TimetableConflictService::class);

        $this->admin = User::forceCreate([
            'name' => 'Admin Timetable',
            'email' => 'admin.timetable@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $this->department = Department::create([
            'name' => 'Information Technology',
            'name_kh' => 'បច្ចេកវិទ្យាព័ត៌មាន',
            'code' => 'IT',
        ]);

        $this->class1 = SchoolClass::create([
            'department_id' => $this->department->id,
            'name' => 'IT Year 1 A',
            'name_kh' => 'IT ឆ្នាំទី១ ក',
            'year' => 1,
            'shift' => 'morning',
            'room' => '101',
        ]);

        $this->class2 = SchoolClass::create([
            'department_id' => $this->department->id,
            'name' => 'IT Year 2 B',
            'name_kh' => 'IT ឆ្នាំទី២ ខ',
            'year' => 2,
            'shift' => 'morning',
            'room' => '102',
        ]);

        $this->teacher1 = User::forceCreate([
            'name' => 'Teacher Dara',
            'email' => 'teacher.dara@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'active',
            'department_id' => $this->department->id,
        ]);

        $this->teacher2 = User::forceCreate([
            'name' => 'Teacher Vanna',
            'email' => 'teacher.vanna@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'active',
            'department_id' => $this->department->id,
        ]);

        $this->room1 = Room::create([
            'name' => 'Lab 1',
            'name_kh' => 'បន្ទប់ពិសោធន៍ ១',
            'building' => 'Building A',
            'floor' => '1',
            'capacity' => 30,
            'room_type' => 'lab',
        ]);

        $this->room2 = Room::create([
            'name' => 'Lab 2',
            'name_kh' => 'បន្ទប់ពិសោធន៍ ២',
            'building' => 'Building A',
            'floor' => '2',
            'capacity' => 35,
            'room_type' => 'lab',
        ]);
    }

    public function test_adjacent_time_slots_are_strictly_permitted(): void
    {
        // Existing slot: 08:00 - 10:00
        Schedule::create([
            'class_id' => $this->class1->id,
            'teacher_id' => $this->teacher1->id,
            'room_id' => $this->room1->id,
            'subject' => 'Web Programming',
            'day_of_week' => 'monday',
            'shift' => 'morning',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        // Adjacent before: 06:00 - 08:00 (Allowed)
        $scheduleBefore = $this->conflictService->createScheduleSafely([
            'class_id' => $this->class1->id,
            'teacher_id' => $this->teacher1->id,
            'room_id' => $this->room1->id,
            'subject' => 'Morning Prep',
            'day_of_week' => 'monday',
            'shift' => 'morning',
            'start_time' => '06:00',
            'end_time' => '08:00',
        ]);

        $this->assertNotNull($scheduleBefore->id);

        // Adjacent after: 10:00 - 12:00 (Allowed)
        $scheduleAfter = $this->conflictService->createScheduleSafely([
            'class_id' => $this->class1->id,
            'teacher_id' => $this->teacher1->id,
            'room_id' => $this->room1->id,
            'subject' => 'Advanced CSS',
            'day_of_week' => 'monday',
            'shift' => 'morning',
            'start_time' => '10:00',
            'end_time' => '12:00',
        ]);

        $this->assertNotNull($scheduleAfter->id);
    }

    public function test_teacher_overlap_conflict_is_detected_and_rejected(): void
    {
        // Teacher 1 teaches Class 1 on Monday 08:00 - 10:00 in Room 1
        Schedule::create([
            'class_id' => $this->class1->id,
            'teacher_id' => $this->teacher1->id,
            'room_id' => $this->room1->id,
            'subject' => 'Database I',
            'day_of_week' => 'monday',
            'shift' => 'morning',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        // Attempt: Same Teacher 1 scheduled for Class 2 on Monday 09:00 - 11:00 in Room 2 (Overlaps 09:00-10:00)
        $this->expectException(ValidationException::class);

        $this->conflictService->createScheduleSafely([
            'class_id' => $this->class2->id,
            'teacher_id' => $this->teacher1->id,
            'room_id' => $this->room2->id,
            'subject' => 'Database II',
            'day_of_week' => 'monday',
            'shift' => 'morning',
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);
    }

    public function test_class_overlap_conflict_is_detected_and_rejected(): void
    {
        // Class 1 has Teacher 1 on Tuesday 13:00 - 15:00
        Schedule::create([
            'class_id' => $this->class1->id,
            'teacher_id' => $this->teacher1->id,
            'room_id' => $this->room1->id,
            'subject' => 'Networking',
            'day_of_week' => 'tuesday',
            'shift' => 'afternoon',
            'start_time' => '13:00',
            'end_time' => '15:00',
        ]);

        // Attempt: Class 1 has Teacher 2 on Tuesday 14:00 - 16:00 (Overlaps 14:00-15:00)
        $this->expectException(ValidationException::class);

        $this->conflictService->createScheduleSafely([
            'class_id' => $this->class1->id,
            'teacher_id' => $this->teacher2->id,
            'room_id' => $this->room2->id,
            'subject' => 'Cybersecurity',
            'day_of_week' => 'tuesday',
            'shift' => 'afternoon',
            'start_time' => '14:00',
            'end_time' => '16:00',
        ]);
    }

    public function test_room_overlap_conflict_is_detected_and_rejected(): void
    {
        // Room 1 booked for Class 1 on Wednesday 08:00 - 10:00
        Schedule::create([
            'class_id' => $this->class1->id,
            'teacher_id' => $this->teacher1->id,
            'room_id' => $this->room1->id,
            'subject' => 'Algorithms',
            'day_of_week' => 'wednesday',
            'shift' => 'morning',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        // Attempt: Class 2 with Teacher 2 tries to book Room 1 on Wednesday 08:30 - 09:30 (Contained inside)
        $this->expectException(ValidationException::class);

        $this->conflictService->createScheduleSafely([
            'class_id' => $this->class2->id,
            'teacher_id' => $this->teacher2->id,
            'room_id' => $this->room1->id,
            'subject' => 'Data Structures',
            'day_of_week' => 'wednesday',
            'shift' => 'morning',
            'start_time' => '08:30',
            'end_time' => '09:30',
        ]);
    }

    public function test_invalid_time_range_end_before_start_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        $this->conflictService->createScheduleSafely([
            'class_id' => $this->class1->id,
            'teacher_id' => $this->teacher1->id,
            'room_id' => $this->room1->id,
            'subject' => 'Invalid Time',
            'day_of_week' => 'thursday',
            'shift' => 'morning',
            'start_time' => '11:00',
            'end_time' => '09:00', // End before start
        ]);
    }

    public function test_full_week_including_sunday_is_supported(): void
    {
        // Sunday Weekend Class
        $response = $this->actingAs($this->admin)->post('/admin/schedules', [
            'class_id' => $this->class1->id,
            'teacher_id' => $this->teacher1->id,
            'room_id' => $this->room1->id,
            'subject' => 'Sunday Tech Workshop',
            'day_of_week' => 'sunday',
            'shift' => 'morning',
            'start_time' => '08:00',
            'end_time' => '11:00',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('schedules', [
            'subject' => 'Sunday Tech Workshop',
            'day_of_week' => 'sunday',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'schedule.created',
        ]);
    }

    public function test_admin_can_create_and_delete_rooms_with_protection(): void
    {
        // 1. Create Room
        $response = $this->actingAs($this->admin)->post('/admin/rooms', [
            'name' => 'Robotics Lab 3',
            'name_kh' => 'បន្ទប់ពិសោធន៍ រ៉ូបូត ៣',
            'building' => 'Building C',
            'floor' => '2',
            'capacity' => 25,
            'room_type' => 'lab',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $room = Room::where('name', 'Robotics Lab 3')->first();
        $this->assertNotNull($room);

        // 2. Schedule uses this room
        Schedule::create([
            'class_id' => $this->class1->id,
            'teacher_id' => $this->teacher1->id,
            'room_id' => $room->id,
            'subject' => 'Robotics 101',
            'day_of_week' => 'friday',
            'shift' => 'morning',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        // 3. Attempt to delete room while active schedules exist -> MUST FAIL
        $deleteResponse = $this->actingAs($this->admin)->delete("/admin/rooms/{$room->id}");
        $deleteResponse->assertRedirect();
        $deleteResponse->assertSessionHas('error');

        $this->assertDatabaseHas('rooms', ['id' => $room->id, 'deleted_at' => null]);

        // 4. Delete the schedule first
        Schedule::where('room_id', $room->id)->delete();

        // 5. Delete room now succeeds
        $deleteSuccessResponse = $this->actingAs($this->admin)->delete("/admin/rooms/{$room->id}");
        $deleteSuccessResponse->assertRedirect();
        $deleteSuccessResponse->assertSessionHas('success');

        $this->assertSoftDeleted('rooms', ['id' => $room->id]);
    }
}
