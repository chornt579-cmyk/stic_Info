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

class StudentTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $classmate;
    protected User $teacher;
    protected Department $department;
    protected SchoolClass $schoolClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create([
            'name' => 'Civil Engineering',
            'name_kh' => 'វិស្វកម្មសំណង់ស៊ីវិល',
            'code' => 'CE',
        ]);

        $this->schoolClass = SchoolClass::create([
            'department_id' => $this->department->id,
            'name' => 'CE Year 1 A',
            'name_kh' => 'CE ឆ្នាំទី១ ក',
            'year' => 1,
            'shift' => 'morning',
            'room' => '102',
        ]);

        $this->teacher = User::forceCreate([
            'name' => 'Teacher Meng',
            'email' => 'teacher.meng@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'active',
            'department_id' => $this->department->id,
        ]);

        $this->student = User::forceCreate([
            'name' => 'Student Bunthoeun',
            'email' => 'bunthoeun@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'status' => 'active',
            'department_id' => $this->department->id,
            'class_id' => $this->schoolClass->id,
            'phone' => '012 111 222',
        ]);

        $this->classmate = User::forceCreate([
            'name' => 'Classmate Bopha',
            'email' => 'bopha.secret@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'status' => 'active',
            'department_id' => $this->department->id,
            'class_id' => $this->schoolClass->id,
            'phone' => '098 777 888',
        ]);
    }

    public function test_student_dashboard_rendered_for_active_student(): void
    {
        $response = $this->actingAs($this->student)->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('schedules');
        $response->assertViewHas('todaySchedules');
        $response->assertViewHas('announcements');
        $response->assertViewHas('classmates');
        $response->assertSee('Student Bunthoeun');
        $response->assertSee('CE ឆ្នាំទី១ ក');
    }

    public function test_suspended_student_is_logged_out_and_redirected(): void
    {
        $suspendedStudent = User::forceCreate([
            'name' => 'Suspended Student',
            'email' => 'suspended.student@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'status' => 'suspended',
            'department_id' => $this->department->id,
        ]);

        $response = $this->actingAs($suspendedStudent)->get('/student/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_teacher_cannot_access_student_dashboard(): void
    {
        $response = $this->actingAs($this->teacher)->get('/student/dashboard');

        // EnsureUserRole aborts with 403 Forbidden and audits the unauthorized attempt
        $response->assertStatus(403);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->teacher->id,
            'action' => 'auth.unauthorized_attempt',
        ]);
    }

    public function test_student_can_view_weekly_schedules_including_weekend(): void
    {
        // Monday schedule
        Schedule::create([
            'class_id' => $this->schoolClass->id,
            'teacher_id' => $this->teacher->id,
            'subject' => 'Surveying Engineering',
            'day_of_week' => 'monday',
            'shift' => 'morning',
            'room' => 'Field 1',
            'start_time' => '07:30',
            'end_time' => '10:30',
        ]);

        // Sunday weekend schedule
        Schedule::create([
            'class_id' => $this->schoolClass->id,
            'teacher_id' => $this->teacher->id,
            'subject' => 'CAD Drafting Workshop',
            'day_of_week' => 'sunday',
            'shift' => 'morning',
            'room' => 'CAD Lab',
            'start_time' => '08:00',
            'end_time' => '12:00',
        ]);

        $response = $this->actingAs($this->student)->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Surveying Engineering');
        $response->assertSee('CAD Drafting Workshop');
        $response->assertSee('Field 1');
        $response->assertSee('CAD Lab');
        $response->assertSee('Teacher Meng');
    }

    public function test_student_without_class_renders_gracefully(): void
    {
        $unassignedStudent = User::forceCreate([
            'name' => 'Unassigned Student',
            'email' => 'unassigned@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'status' => 'active',
            'department_id' => $this->department->id,
            'class_id' => null,
        ]);

        $response = $this->actingAs($unassignedStudent)->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertSee(__('messages.no_class_assigned_notice'));
    }

    public function test_student_receives_relevant_announcements_and_filters_teacher_only_notices(): void
    {
        // Public announcement
        $publicAnn = Announcement::create([
            'author_id' => $this->teacher->id,
            'title' => 'Campus Water Maintenance',
            'content' => 'Water outage from 12pm to 1pm.',
            'target_type' => 'public',
        ]);

        // Class announcement
        $classAnn = Announcement::create([
            'author_id' => $this->teacher->id,
            'title' => 'Midterm Exam Schedule',
            'content' => 'Exam is next Monday.',
            'target_type' => 'class',
            'target_id' => $this->schoolClass->id,
        ]);

        // Teacher only announcement (Student must NOT see this)
        $teacherAnn = Announcement::create([
            'author_id' => $this->teacher->id,
            'title' => 'Faculty Salary Meeting',
            'content' => 'Strictly confidential for faculty members.',
            'target_type' => 'all_teachers',
        ]);

        $response = $this->actingAs($this->student)->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Campus Water Maintenance');
        $response->assertSee('Midterm Exam Schedule');
        $response->assertDontSee('Faculty Salary Meeting');
    }

    public function test_student_classmates_list_never_leaks_emails_or_phone_numbers(): void
    {
        $response = $this->actingAs($this->student)->get('/student/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Classmate Bopha');

        // Strict data privacy verification:
        $response->assertDontSee('bopha.secret@stic.edu.kh');
        $response->assertDontSee('098 777 888');
    }

    public function test_student_can_update_phone_number_with_audit_trail(): void
    {
        $response = $this->actingAs($this->student)->put('/student/profile', [
            'phone' => '012 888 777',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->student->id,
            'phone' => '012 888 777',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->student->id,
            'action' => 'student_update_profile',
        ]);
    }

    public function test_student_phone_update_rejects_invalid_phone_format(): void
    {
        $response = $this->actingAs($this->student)->put('/student/profile', [
            'phone' => 'invalid-phone-string',
        ]);

        $response->assertSessionHasErrors(['phone']);
    }
}
