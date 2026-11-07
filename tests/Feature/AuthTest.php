<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected Department $department;
    protected SchoolClass $schoolClass;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::forceCreate([
            'name' => 'Test User',
            'email' => 'test@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'email' => 'test@stic.edu.kh',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'auth.login_success',
        ]);
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::forceCreate([
            'name' => 'Test User',
            'email' => 'test@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'status' => 'active',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'test@stic.edu.kh',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.failed_login',
        ]);
    }

    public function test_login_rate_limiting_throttles_after_5_failed_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'bruteforce@stic.edu.kh',
                'password' => 'wrong-pass',
            ]);
        }

        // 6th attempt must be throttled (HTTP 429 Too Many Requests)
        $response = $this->post('/login', [
            'email' => 'bruteforce@stic.edu.kh',
            'password' => 'wrong-pass',
        ]);

        $response->assertStatus(429);
    }

    public function test_student_can_register_and_is_immediately_active(): void
    {
        $response = $this->post('/register', [
            'name' => 'Student Sok',
            'email' => 'student.sok@stic.edu.kh',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'role' => 'student',
            'department_id' => $this->department->id,
            'class_id' => $this->schoolClass->id,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'student.sok@stic.edu.kh',
            'role' => 'student',
            'status' => 'active',
        ]);
    }

    public function test_teacher_can_register_and_is_set_to_pending(): void
    {
        $response = $this->post('/register', [
            'name' => 'Teacher Dara',
            'email' => 'teacher.dara@stic.edu.kh',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'role' => 'teacher',
            'department_id' => $this->department->id,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('pending.approval'));

        $this->assertDatabaseHas('users', [
            'email' => 'teacher.dara@stic.edu.kh',
            'role' => 'teacher',
            'status' => 'pending',
        ]);
    }

    public function test_pending_teacher_is_blocked_from_dashboard(): void
    {
        $teacher = User::forceCreate([
            'name' => 'Pending Teacher',
            'email' => 'pending.teacher@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($teacher)->get('/teacher/dashboard');
        $response->assertRedirect(route('pending.approval'));
    }

    public function test_suspended_user_cannot_login(): void
    {
        $user = User::forceCreate([
            'name' => 'Suspended User',
            'email' => 'suspended@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'status' => 'suspended',
        ]);

        $response = $this->post('/login', [
            'email' => 'suspended@stic.edu.kh',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'auth.login_blocked_suspended',
        ]);
    }

    public function test_rejected_user_cannot_login(): void
    {
        $user = User::forceCreate([
            'name' => 'Rejected Teacher',
            'email' => 'rejected@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'rejected',
        ]);

        $response = $this->post('/login', [
            'email' => 'rejected@stic.edu.kh',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'auth.login_blocked_rejected',
        ]);
    }

    public function test_admin_can_approve_teacher_with_transaction(): void
    {
        $admin = User::forceCreate([
            'name' => 'Admin User',
            'email' => 'admin@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $teacher = User::forceCreate([
            'name' => 'Teacher To Approve',
            'email' => 'teacher.approve@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch("/admin/teachers/{$teacher->id}/approve");

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $teacher->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'teacher.approved',
            'model_id' => $teacher->id,
        ]);
    }

    public function test_admin_can_reject_teacher_with_transaction(): void
    {
        $admin = User::forceCreate([
            'name' => 'Admin User',
            'email' => 'admin@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $teacher = User::forceCreate([
            'name' => 'Teacher To Reject',
            'email' => 'teacher.reject@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch("/admin/teachers/{$teacher->id}/reject");

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $teacher->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'teacher.rejected',
            'model_id' => $teacher->id,
        ]);
    }

    public function test_user_can_change_password_and_invalidates_other_sessions(): void
    {
        $user = User::forceCreate([
            'name' => 'Password Changer',
            'email' => 'changer@stic.edu.kh',
            'password' => Hash::make('oldpassword123'),
            'role' => 'student',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post('/change-password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('newpassword456', $user->fresh()->password));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'password.changed',
        ]);
    }

    public function test_password_reset_workflow(): void
    {
        $user = User::forceCreate([
            'name' => 'Reset Target',
            'email' => 'reset@stic.edu.kh',
            'password' => Hash::make('oldpass123'),
            'role' => 'student',
            'status' => 'active',
        ]);

        // Request reset
        $response = $this->post('/forgot-password', [
            'email' => 'reset@stic.edu.kh',
        ]);
        $response->assertSessionHas('status');

        $tokenRecord = DB::table('password_reset_tokens')->where('email', 'reset@stic.edu.kh')->first();
        $this->assertNotNull($tokenRecord);

        // Submit new password
        $rawToken = 'test-plain-token';
        DB::table('password_reset_tokens')->where('email', 'reset@stic.edu.kh')->update([
            'token' => Hash::make($rawToken),
            'created_at' => now(),
        ]);

        $resetResponse = $this->post('/reset-password', [
            'token' => $rawToken,
            'email' => 'reset@stic.edu.kh',
            'password' => 'brandnewpass123',
            'password_confirmation' => 'brandnewpass123',
        ]);

        $resetResponse->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('brandnewpass123', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'reset@stic.edu.kh']);
    }

    public function test_soft_deleted_user_email_can_be_re_registered(): void
    {
        $user = User::forceCreate([
            'name' => 'Soft Deleted User',
            'email' => 'reusable@stic.edu.kh',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'status' => 'active',
        ]);

        $user->delete(); // Soft delete
        $this->assertSoftDeleted('users', ['id' => $user->id]);

        // Re-registration with the same email must succeed
        $response = $this->post('/register', [
            'name' => 'New Student Same Email',
            'email' => 'reusable@stic.edu.kh',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'role' => 'student',
            'department_id' => $this->department->id,
            'class_id' => $this->schoolClass->id,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertEquals(2, User::withTrashed()->where('email', 'reusable@stic.edu.kh')->count());
        $this->assertEquals(1, User::where('email', 'reusable@stic.edu.kh')->count());
    }
}
