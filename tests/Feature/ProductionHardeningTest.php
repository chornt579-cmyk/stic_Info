<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present_on_all_responses(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNotNull($response->headers->get('Content-Security-Policy'));
    }

    public function test_create_admin_command_creates_active_administrator_account(): void
    {
        $this->artisan('stic:create-admin', [
            '--name' => 'Super Administrator',
            '--email' => 'super.admin@stic.edu.kh',
        ])
        ->expectsQuestion('Enter Secure Password (min 8 characters)', 'P@ssword1234')
        ->expectsQuestion('Confirm Secure Password', 'P@ssword1234')
        ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'super.admin@stic.edu.kh',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_prune_audit_logs_command_enforces_minimum_90_days_retention(): void
    {
        // User passes --days=30, but governance policy mandates minimum 90 days
        $this->artisan('stic:prune-audit-logs', ['--days' => 30, '--dry-run' => true])
            ->expectsOutputToContain('Enforcing minimum 90 days retention')
            ->assertSuccessful();
    }

    public function test_prune_audit_logs_command_prunes_only_records_older_than_threshold(): void
    {
        // 1. Create old log (100 days old)
        AuditLog::insert([
            'action' => 'old.action',
            'created_at' => now()->subDays(100),
        ]);

        // 2. Create recent log (10 days old)
        AuditLog::insert([
            'action' => 'recent.action',
            'created_at' => now()->subDays(10),
        ]);

        $this->assertEquals(2, AuditLog::count());

        // Dry-run should find 1 record but delete nothing
        $this->artisan('stic:prune-audit-logs', ['--days' => 90, '--dry-run' => true])
            ->expectsOutputToContain('Found 1 audit log records')
            ->assertSuccessful();

        $this->assertEquals(2, AuditLog::count());

        // Real execution
        $this->artisan('stic:prune-audit-logs', ['--days' => 90])
            ->expectsOutputToContain('Successfully pruned 1 audit log records')
            ->assertSuccessful();

        // Old log is deleted, recent log remains, plus the new audit.pruned log was recorded
        $this->assertDatabaseMissing('audit_logs', ['action' => 'old.action']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'recent.action']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'audit.pruned']);
    }

    public function test_api_readiness_endpoints_respond_cleanly(): void
    {
        Department::create([
            'name' => 'Electrical Engineering',
            'name_kh' => 'វិស្វកម្មអគ្គិសនី',
            'code' => 'EE',
        ]);

        $healthResponse = $this->getJson('/api/v1/health');
        $healthResponse->assertStatus(200);
        $healthResponse->assertJson(['status' => 'healthy', 'app' => 'STIC Info']);

        $deptResponse = $this->getJson('/api/v1/departments');
        $deptResponse->assertStatus(200);
        $deptResponse->assertJsonStructure(['departments' => [['id', 'name', 'code']]]);
    }
}
