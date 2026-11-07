<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test public home page loads successfully.
     */
    public function test_homepage_loads_with_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * Test language switching between Khmer and English.
     */
    public function test_locale_switching(): void
    {
        // Default locale should be Khmer (km)
        $responseKm = $this->get('/?lang=km');
        $responseKm->assertStatus(200);
        $responseKm->assertSessionHas('locale', 'km');

        // Switch to English (en)
        $responseEn = $this->get('/?lang=en');
        $responseEn->assertStatus(200);
        $responseEn->assertSessionHas('locale', 'en');
    }

    /**
     * Test API readiness health check.
     */
    public function test_api_health_endpoint(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'healthy',
                'app' => 'STIC Info',
                'version' => '1.0.0',
            ]);
    }

    /**
     * Test secure admin creation artisan command.
     */
    public function test_admin_creation_artisan_command(): void
    {
        $this->artisan('stic:create-admin', [
            '--name' => 'Super Admin',
            '--email' => 'superadmin@stic.edu.kh',
        ])
        ->expectsQuestion('Enter Secure Password (min 8 characters)', 'SecurePass123!')
        ->expectsQuestion('Confirm Secure Password', 'SecurePass123!')
        ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'superadmin@stic.edu.kh',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }
}

