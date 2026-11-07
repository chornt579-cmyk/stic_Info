<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Department $department;
    protected SchoolClass $schoolClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::forceCreate([
            'name' => 'Admin User',
            'email' => 'admin@stic.edu.kh',
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

        $this->schoolClass = SchoolClass::create([
            'department_id' => $this->department->id,
            'name' => 'IT Year 1',
            'name_kh' => 'IT ឆ្នាំ១',
            'year' => 1,
            'shift' => 'morning',
            'room' => '101',
        ]);
    }

    public function test_admin_dashboard_can_be_rendered_for_admin_with_statistics(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
        $response->assertViewHas('departments');
        $response->assertViewHas('classes');
        $response->assertViewHas('pendingTeachers');
        $response->assertViewHas('auditLogs');
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $student = User::forceCreate([
            'name' => 'Regular Student',
            'email' => 'student@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'status' => 'active',
        ]);

        $response = $this->actingAs($student)->get('/admin/dashboard');
        $response->assertStatus(403);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.unauthorized_attempt',
        ]);
    }

    public function test_admin_can_create_department_with_audit_log(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/departments', [
            'name' => 'Civil Engineering',
            'name_kh' => 'វិស្វកម្មសំណង់',
            'code' => 'CE',
            'description' => 'Civil Engineering Department',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('departments', [
            'code' => 'CE',
            'name' => 'Civil Engineering',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'department.created',
        ]);
    }

    public function test_admin_can_update_department(): void
    {
        $response = $this->actingAs($this->admin)->put("/admin/departments/{$this->department->id}", [
            'name' => 'IT & Software Engineering',
            'name_kh' => 'ព័ត៌មានវិទ្យា',
            'code' => 'IT',
            'description' => 'Updated Description',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('departments', [
            'id' => $this->department->id,
            'name' => 'IT & Software Engineering',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'department.updated',
            'model_id' => $this->department->id,
        ]);
    }

    public function test_admin_cannot_delete_department_with_active_classes(): void
    {
        // $this->department already has $this->schoolClass
        $response = $this->actingAs($this->admin)->delete("/admin/departments/{$this->department->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('departments', ['id' => $this->department->id]);
    }

    public function test_admin_can_delete_empty_department(): void
    {
        $emptyDept = Department::create([
            'name' => 'Empty Department',
            'code' => 'EMPTY',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/departments/{$emptyDept->id}");

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('departments', ['id' => $emptyDept->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'department.deleted',
        ]);
    }

    public function test_admin_can_create_class(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/classes', [
            'department_id' => $this->department->id,
            'name' => 'IT Year 2 Morning',
            'name_kh' => 'IT ឆ្នាំ២ ព្រឹក',
            'year' => 2,
            'shift' => 'morning',
            'room' => '202',
            'capacity' => 40,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('classes', [
            'name' => 'IT Year 2 Morning',
            'year' => 2,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'class.created',
        ]);
    }

    public function test_admin_cannot_delete_class_with_enrolled_students(): void
    {
        // Enroll a student in $this->schoolClass
        User::forceCreate([
            'name' => 'Enrolled Student',
            'email' => 'enrolled@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'status' => 'active',
            'department_id' => $this->department->id,
            'class_id' => $this->schoolClass->id,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/classes/{$this->schoolClass->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('classes', ['id' => $this->schoolClass->id]);
    }

    public function test_admin_can_delete_empty_class(): void
    {
        $emptyClass = SchoolClass::create([
            'department_id' => $this->department->id,
            'name' => 'Empty Class',
            'year' => 3,
            'shift' => 'afternoon',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/classes/{$emptyClass->id}");

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('classes', ['id' => $emptyClass->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'class.deleted',
        ]);
    }

    public function test_admin_can_toggle_teacher_status_active_and_suspended(): void
    {
        $teacher = User::forceCreate([
            'name' => 'Toggle Teacher',
            'email' => 'toggle.teacher@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'active',
        ]);

        // Suspend teacher
        $suspendResponse = $this->actingAs($this->admin)->patch("/admin/users/{$teacher->id}/status", [
            'status' => 'suspended',
        ]);

        $suspendResponse->assertSessionHas('success');
        $this->assertEquals('suspended', $teacher->fresh()->status);

        // Reactivate teacher
        $activateResponse = $this->actingAs($this->admin)->patch("/admin/users/{$teacher->id}/status", [
            'status' => 'active',
        ]);

        $activateResponse->assertSessionHas('success');
        $this->assertEquals('active', $teacher->fresh()->status);
    }

    public function test_admin_cannot_suspend_themselves(): void
    {
        $response = $this->actingAs($this->admin)->patch("/admin/users/{$this->admin->id}/status", [
            'status' => 'suspended',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('active', $this->admin->fresh()->status);
    }

    public function test_admin_can_assign_classes_to_teacher(): void
    {
        $teacher = User::forceCreate([
            'name' => 'Assigned Teacher',
            'email' => 'assigned.teacher@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/teachers/{$teacher->id}/classes", [
            'class_ids' => [$this->schoolClass->id],
            'subject' => 'Web Programming',
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue($teacher->fresh()->teachingClasses->contains('id', $this->schoolClass->id));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'teacher.assigned_classes',
            'model_id' => $teacher->id,
        ]);
    }

    public function test_admin_can_search_students_and_filter_by_department(): void
    {
        User::forceCreate([
            'name' => 'Chan Dara',
            'email' => 'dara@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'status' => 'active',
            'department_id' => $this->department->id,
            'class_id' => $this->schoolClass->id,
        ]);

        // Search by name
        $response = $this->actingAs($this->admin)->get('/admin/dashboard?student_search=Dara');

        $response->assertStatus(200);
        $response->assertSee('Chan Dara');

        // Filter by department
        $responseDept = $this->actingAs($this->admin)->get("/admin/dashboard?student_dept_id={$this->department->id}");
        $responseDept->assertStatus(200);
        $responseDept->assertSee('Chan Dara');
    }

    public function test_admin_can_soft_delete_student(): void
    {
        $student = User::forceCreate([
            'name' => 'Student To Delete',
            'email' => 'delete.me@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/users/{$student->id}");

        $response->assertSessionHas('success');
        $this->assertSoftDeleted('users', ['id' => $student->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.deleted',
        ]);
    }
}
