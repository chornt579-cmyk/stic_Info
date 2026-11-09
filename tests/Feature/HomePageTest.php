<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads_successfully_with_institutional_branding(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('images/stic-logo.png');
        $response->assertSee(__('messages.institute_short'));
        $response->assertSee(__('messages.official_platform'));
        $response->assertSee('departments');
        $response->assertSee('announcements');
        $response->assertSee('about');
    }

    public function test_homepage_renders_departments_and_classes_count(): void
    {
        $dept = Department::create([
            'name' => 'Information Technology',
            'name_kh' => 'ដេប៉ាតឺម៉ង់ព័ត៌មានវិទ្យា',
            'code' => 'IT',
            'description' => 'Software engineering and networking',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('IT');
        $response->assertSee('ដេប៉ាតឺម៉ង់ព័ត៌មានវិទ្យា');
        $response->assertSee('0 ' . __('messages.classes_count_suffix'));
    }

    public function test_homepage_renders_public_announcements(): void
    {
        $admin = User::create([
            'name' => 'STIC Director',
            'email' => 'director@stic.edu.kh',
            'password' => bcrypt('AdminPassword123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $announcement = Announcement::create([
            'title' => 'Official Semester Commencement 2026',
            'content' => 'All polytechnic classes begin promptly next Monday.',
            'target_type' => 'public',
            'author_id' => $admin->id,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Official Semester Commencement 2026');
        $response->assertSee('STIC Director');
    }

    public function test_homepage_bilingual_switching_khmer_and_english(): void
    {
        // Khmer
        $responseKm = $this->get('/?lang=km');
        $responseKm->assertStatus(200);
        $responseKm->assertSee('វិទ្យាស្ថានពហុបច្ចេកទេសភូមិភាគតេជោសែន សៀមរាប');

        // English
        $responseEn = $this->get('/?lang=en');
        $responseEn->assertStatus(200);
        $responseEn->assertSee('Techo Sen Regional Polytechnic Institute of Siem Reap');
    }

    public function test_homepage_has_zero_decorative_emojis(): void
    {
        $response = $this->get('/');
        $content = $response->getContent();

        // Forbidden emojis on institutional homepage
        $forbiddenEmojis = ['🎓', '📁', '📢', '📚', '✍️', '🇰🇭', '🇬🇧', '🚪', '📊', '👨‍🏫'];

        foreach ($forbiddenEmojis as $emoji) {
            $this->assertStringNotContainsString(
                $emoji,
                $content,
                "Homepage contains forbidden decorative emoji: {$emoji}"
            );
        }
    }
}

