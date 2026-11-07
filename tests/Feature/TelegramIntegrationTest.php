<?php

namespace Tests\Feature;

use App\Jobs\SendTelegramNotificationJob;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TelegramIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Department $department;
    protected Announcement $announcement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::forceCreate([
            'name' => 'Admin Bot',
            'email' => 'admin.bot@stic.edu.kh',
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

        $this->announcement = Announcement::create([
            'author_id' => $this->admin->id,
            'title' => 'Midterm Examination Schedule',
            'content' => 'The exams will start next Monday at 8:00 AM.',
            'target_type' => 'public',
            'is_telegram_sent' => false,
        ]);
    }

    public function test_telegram_service_sanitizes_pii_strictly(): void
    {
        $service = new TelegramService();

        $dirtyMessage = "Student email: sokha@stic.edu.kh, Phone: 012 345 678, Password: secretpassword123. Please check.";
        $cleanMessage = $service->sanitizePII($dirtyMessage);

        $this->assertStringNotContainsString('sokha@stic.edu.kh', $cleanMessage);
        $this->assertStringNotContainsString('012 345 678', $cleanMessage);
        $this->assertStringNotContainsString('secretpassword123', $cleanMessage);

        $this->assertStringContainsString('[EMAIL REDACTED]', $cleanMessage);
        $this->assertStringContainsString('[PHONE REDACTED]', $cleanMessage);
        $this->assertStringContainsString('[REDACTED]', $cleanMessage);
    }

    public function test_telegram_service_skips_gracefully_when_credentials_not_configured(): void
    {
        Config::set('services.telegram.bot_token', null);
        Config::set('services.telegram.channel_id', null);

        $service = new TelegramService();
        $result = $service->sendMessage('Test notice without tokens');

        $this->assertFalse($result['success']);
        $this->assertEquals('skipped', $result['status']);
    }

    public function test_telegram_service_sends_http_request_when_credentials_configured(): void
    {
        Config::set('services.telegram.bot_token', '123456:FAKE_TOKEN');
        Config::set('services.telegram.channel_id', '-100987654321');

        Http::fake([
            'https://api.telegram.org/bot123456:FAKE_TOKEN/sendMessage' => Http::response([
                'ok' => true,
                'result' => [
                    'message_id' => 9999,
                    'text' => 'Sent message',
                ],
            ], 200),
        ]);

        $service = new TelegramService();
        $result = $service->sendMessage('Broadcast Announcement');

        $this->assertTrue($result['success']);
        $this->assertEquals('sent', $result['status']);
        $this->assertEquals(9999, $result['message_id']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.telegram.org/bot123456:FAKE_TOKEN/sendMessage'
                && $request['chat_id'] === '-100987654321'
                && $request['parse_mode'] === 'HTML';
        });
    }

    public function test_queue_job_executes_and_marks_announcement_as_sent(): void
    {
        Config::set('services.telegram.bot_token', '123456:FAKE_TOKEN');
        Config::set('services.telegram.channel_id', '-100987654321');

        Http::fake([
            'https://api.telegram.org/bot123456:FAKE_TOKEN/sendMessage' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 777],
            ], 200),
        ]);

        $job = new SendTelegramNotificationJob($this->announcement);
        $job->handle(new TelegramService());

        $this->announcement->refresh();
        $this->assertTrue($this->announcement->is_telegram_sent);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'telegram.broadcast_sent',
            'model_id' => $this->announcement->id,
        ]);
    }

    public function test_queue_job_has_retry_and_backoff_policies(): void
    {
        $job = new SendTelegramNotificationJob($this->announcement);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([10, 30, 60], $job->backoff);
    }

    public function test_admin_announcement_creation_dispatches_telegram_queue_job(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->admin)->post('/admin/announcements', [
            'title' => 'Urgent School Closure',
            'content' => 'Campus closed due to heavy rain.',
            'target_type' => 'public',
            'send_telegram' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Queue::assertPushed(SendTelegramNotificationJob::class, function ($job) {
            return $job->announcement->title === 'Urgent School Closure';
        });

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'telegram.broadcast_dispatched',
        ]);
    }

    public function test_security_destination_cannot_be_overridden_from_user_input(): void
    {
        Config::set('services.telegram.bot_token', '123456:FAKE_TOKEN');
        Config::set('services.telegram.channel_id', '-100SERVER_CONFIGURED_CHANNEL');

        Http::fake([
            'https://api.telegram.org/bot123456:FAKE_TOKEN/sendMessage' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 101],
            ], 200),
        ]);

        $service = new TelegramService();
        // Attacker attempts to pass arbitrary chatId:
        $service->sendMessage('Test', '-100ATTACKER_GROUP_ID');

        // Verify request ONLY went to the server-configured channel:
        Http::assertSent(function ($request) {
            return $request['chat_id'] === '-100SERVER_CONFIGURED_CHANNEL';
        });
    }
}
