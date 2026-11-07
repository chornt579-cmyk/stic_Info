<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected User $otherTeacher;
    protected User $student;
    protected Department $department;
    protected SchoolClass $assignedClass;
    protected SchoolClass $unassignedClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create([
            'name' => 'Computer Science',
            'name_kh' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ',
            'code' => 'CS',
        ]);

        $this->teacher = User::forceCreate([
            'name' => 'Teacher Sok',
            'email' => 'teacher.sok@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'active',
            'approved_at' => now(),
            'department_id' => $this->department->id,
            'phone' => '012 345 678',
        ]);

        $this->otherTeacher = User::forceCreate([
            'name' => 'Teacher Chan',
            'email' => 'teacher.chan@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'active',
            'approved_at' => now(),
            'department_id' => $this->department->id,
            'phone' => '098 765 432',
        ]);

        $this->assignedClass = SchoolClass::create([
            'department_id' => $this->department->id,
            'name' => 'CS Year 2 A',
            'name_kh' => 'CS ឆ្នាំទី២ ក',
            'year' => 2,
            'shift' => 'morning',
            'room' => '201',
        ]);

        $this->unassignedClass = SchoolClass::create([
            'department_id' => $this->department->id,
            'name' => 'CS Year 3 B',
            'name_kh' => 'CS ឆ្នាំទី៣ ខ',
            'year' => 3,
            'shift' => 'afternoon',
            'room' => '301',
        ]);

        $this->student = User::forceCreate([
            'name' => 'Student Dara',
            'email' => 'student.dara@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'status' => 'active',
            'department_id' => $this->department->id,
            'class_id' => $this->assignedClass->id,
        ]);

        // Assign class to teacher
        $this->teacher->teachingClasses()->attach($this->assignedClass->id, [
            'subject' => 'Web Development',
        ]);
    }

    public function test_teacher_dashboard_can_be_rendered_for_active_teacher(): void
    {
        $response = $this->actingAs($this->teacher)->get('/teacher/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('schedules');
        $response->assertViewHas('todaySchedules');
        $response->assertViewHas('classes');
        $response->assertViewHas('sentAnnouncements');
        $response->assertViewHas('internalAnnouncements');
        $response->assertSee('Teacher Sok');
        $response->assertSee('CS ឆ្នាំទី២ ក');
    }

    public function test_pending_teacher_cannot_access_dashboard(): void
    {
        $pendingTeacher = User::forceCreate([
            'name' => 'Pending Teacher',
            'email' => 'pending@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'pending',
            'department_id' => $this->department->id,
        ]);

        $response = $this->actingAs($pendingTeacher)->get('/teacher/dashboard');

        $response->assertRedirect('/pending-approval');
    }

    public function test_suspended_teacher_is_blocked_from_teacher_dashboard(): void
    {
        $suspendedTeacher = User::forceCreate([
            'name' => 'Suspended Teacher',
            'email' => 'suspended@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'suspended',
            'department_id' => $this->department->id,
        ]);

        $response = $this->actingAs($suspendedTeacher)->get('/teacher/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_student_cannot_access_teacher_dashboard(): void
    {
        $response = $this->actingAs($this->student)->get('/teacher/dashboard');

        // Role middleware aborts with 403 Forbidden and audits the attempt
        $response->assertStatus(403);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->student->id,
            'action' => 'auth.unauthorized_attempt',
        ]);
    }

    public function test_teacher_can_post_announcement_to_assigned_class(): void
    {
        $response = $this->actingAs($this->teacher)->post('/teacher/announcements', [
            'class_id' => $this->assignedClass->id,
            'title' => 'Project Submission Deadline',
            'content' => 'Please submit your source code before Friday 5:00 PM.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('announcements', [
            'author_id' => $this->teacher->id,
            'title' => 'Project Submission Deadline',
            'target_type' => 'class',
            'target_id' => $this->assignedClass->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->teacher->id,
            'action' => 'teacher_create_announcement',
        ]);
    }

    public function test_teacher_cannot_post_announcement_to_unassigned_class(): void
    {
        $response = $this->actingAs($this->teacher)->post('/teacher/announcements', [
            'class_id' => $this->unassignedClass->id,
            'title' => 'Unauthorized Post',
            'content' => 'This should fail because teacher does not teach this class.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('announcements', [
            'title' => 'Unauthorized Post',
        ]);
    }

    public function test_teacher_can_delete_own_announcement(): void
    {
        $announcement = Announcement::create([
            'author_id' => $this->teacher->id,
            'title' => 'Temporary Notice',
            'content' => 'To be deleted shortly.',
            'target_type' => 'class',
            'target_id' => $this->assignedClass->id,
        ]);

        $response = $this->actingAs($this->teacher)->delete("/teacher/announcements/{$announcement->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('announcements', [
            'id' => $announcement->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->teacher->id,
            'action' => 'teacher_delete_announcement',
        ]);
    }

    public function test_teacher_cannot_delete_another_teachers_announcement(): void
    {
        $otherAnnouncement = Announcement::create([
            'author_id' => $this->otherTeacher->id,
            'title' => 'Teacher Chan Notice',
            'content' => 'Teacher Sok cannot delete this.',
            'target_type' => 'class',
            'target_id' => $this->assignedClass->id,
        ]);

        $response = $this->actingAs($this->teacher)->delete("/teacher/announcements/{$otherAnnouncement->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('announcements', [
            'id' => $otherAnnouncement->id,
        ]);
    }

    public function test_teacher_can_update_phone_number_with_audit_trail(): void
    {
        $response = $this->actingAs($this->teacher)->put('/teacher/profile', [
            'phone' => '015 888 999',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->teacher->id,
            'phone' => '015 888 999',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->teacher->id,
            'action' => 'teacher_update_profile',
        ]);
    }

    public function test_teacher_phone_validation_rejects_invalid_format(): void
    {
        $response = $this->actingAs($this->teacher)->put('/teacher/profile', [
            'phone' => 'abc-not-a-phone',
        ]);

        $response->assertSessionHasErrors(['phone']);
    }

    public function test_teacher_weekly_and_today_schedules_render_properly(): void
    {
        // Create full week schedule slots
        Schedule::create([
            'class_id' => $this->assignedClass->id,
            'teacher_id' => $this->teacher->id,
            'subject' => 'Web App Architecture',
            'day_of_week' => 'monday',
            'shift' => 'morning',
            'room' => 'Lab 1',
            'start_time' => '08:00',
            'end_time' => '11:00',
        ]);

        Schedule::create([
            'class_id' => $this->assignedClass->id,
            'teacher_id' => $this->teacher->id,
            'subject' => 'Weekend Tech Workshop',
            'day_of_week' => 'saturday',
            'shift' => 'morning',
            'room' => 'Workshop 2',
            'start_time' => '08:00',
            'end_time' => '12:00',
        ]);

        $response = $this->actingAs($this->teacher)->get('/teacher/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Web App Architecture');
        $response->assertSee('Weekend Tech Workshop');
        $response->assertSee('Lab 1');
        $response->assertSee('Workshop 2');
    }
}
