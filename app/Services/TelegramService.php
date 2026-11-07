<?php

namespace App\Services;

use App\Models\Announcement;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected ?string $botToken;
    protected ?string $defaultChannelId;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->defaultChannelId = config('services.telegram.channel_id');
    }

    /**
     * Check if Telegram credentials are configured in .env / config.
     */
    public function isConfigured(): bool
    {
        return !empty($this->botToken) && !empty($this->defaultChannelId);
    }

    /**
     * Send a sanitized text message to configured Telegram Channel/Group.
     * Destination is strictly loaded from config (Governance Manual Section 4.7).
     *
     * @param string $message
     * @param string|null $chatId
     * @return array
     */
    public function sendMessage(string $message, ?string $chatId = null): array
    {
        if (!$this->isConfigured()) {
            Log::info('TelegramService: Bot credentials not configured in environment. Skipping broadcast.');
            return [
                'success' => false,
                'status' => 'skipped',
                'reason' => 'Telegram credentials not configured in environment.',
            ];
        }

        // PII Prohibition Guard: Redact emails, phone numbers, and credentials
        $sanitizedMessage = $this->sanitizePII($message);

        // Security Guard: Never allow user input to override destination chat_id
        $destination = $this->defaultChannelId;

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $destination,
                'text' => $sanitizedMessage,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            if ($response->successful()) {
                $body = $response->json();
                return [
                    'success' => true,
                    'status' => 'sent',
                    'message_id' => $body['result']['message_id'] ?? null,
                ];
            }

            Log::error('TelegramService API error: ' . $response->body());
            return [
                'success' => false,
                'status' => 'failed',
                'error_code' => $response->status(),
                'reason' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('TelegramService Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 'exception',
                'reason' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send an Announcement to Telegram.
     */
    public function sendAnnouncement(Announcement $announcement): array
    {
        $formattedText = $this->formatAnnouncement($announcement);
        return $this->sendMessage($formattedText);
    }

    /**
     * Format Announcement cleanly for Telegram HTML parse mode.
     */
    public function formatAnnouncement(Announcement $announcement): string
    {
        $audience = match($announcement->target_type) {
            'class' => 'ថ្នាក់រៀនជាក់លាក់ (Class)',
            'dept_students' => 'ដេប៉ាតឺម៉ង់ (Students)',
            'dept_teachers' => 'ដេប៉ាតឺម៉ង់ (Teachers)',
            'all_teachers' => 'គ្រូបង្រៀនទាំងអស់ (All Teachers)',
            'all_students' => 'សិស្ស/និស្សិតទាំងអស់ (All Students)',
            default => 'សាធារណៈ (Public)',
        };

        $title = htmlspecialchars($announcement->title, ENT_QUOTES, 'UTF-8');
        $content = htmlspecialchars($announcement->content, ENT_QUOTES, 'UTF-8');
        $author = htmlspecialchars($announcement->author?->name ?? 'STIC Admin', ENT_QUOTES, 'UTF-8');

        return "📢 <b>សេចក្ដីជូនដំណឹង STIC Info</b>\n" .
               "━━━━━━━━━━━━━━━━\n" .
               "<b>ចំណងជើង៖</b> {$title}\n" .
               "<b>ទស្សនិកជន៖</b> {$audience}\n" .
               "<b>អ្នកផ្សាយ៖</b> {$author}\n\n" .
               "{$content}\n" .
               "━━━━━━━━━━━━━━━━\n" .
               "🏛 <i>វិទ្យាស្ថានពហុបច្ចេកទេសភូមិភាគតេជោសែន សៀមរាប</i>";
    }

    /**
     * Format Schedule Update Alert for Telegram.
     */
    public function formatScheduleAlert(string $title, string $details): string
    {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeDetails = htmlspecialchars($details, ENT_QUOTES, 'UTF-8');

        return "🗓️ <b>ដំណឹងប្ដូរកាលវិភាគសិក្សា (STIC Timetable)</b>\n" .
               "━━━━━━━━━━━━━━━━\n" .
               "<b>ចំណងជើង៖</b> {$safeTitle}\n\n" .
               "{$safeDetails}\n" .
               "━━━━━━━━━━━━━━━━\n" .
               "🏛 <i>STIC Siem Reap</i>";
    }

    /**
     * PII Prohibition Sanitizer:
     * Never send student/teacher phone numbers, emails, passwords, or personal credentials into Telegram group chats.
     */
    public function sanitizePII(string $text): string
    {
        // 1. Redact Emails
        $text = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[EMAIL REDACTED]', $text);

        // 2. Redact Phone Numbers (Cambodia standard +855 or 0xx xxx xxx)
        $text = preg_replace('/(\+?855[\s.-]?|0)[1-9][0-9]{1}[\s.-]?[0-9]{3}[\s.-]?[0-9]{3,4}/', '[PHONE REDACTED]', $text);

        // 3. Redact Password / Token leaks
        $text = preg_replace('/(?i)(password|token|secret|credential)\s*[:=]\s*\S+/', '$1: [REDACTED]', $text);

        return $text;
    }
}
