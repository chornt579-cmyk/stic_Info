<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * Retry policy per Governance Manual Section 4.7.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     * Exponential-like backoff (10s, 30s, 60s) to handle Telegram rate limits.
     */
    public array $backoff = [10, 30, 60];

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    public ?Announcement $announcement;
    public ?string $customMessage;

    /**
     * Create a new job instance.
     */
    public function __construct(?Announcement $announcement = null, ?string $customMessage = null)
    {
        $this->announcement = $announcement;
        $this->customMessage = $customMessage;
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegramService): void
    {
        if ($this->announcement) {
            $result = $telegramService->sendAnnouncement($this->announcement);

            if ($result['success']) {
                $this->announcement->update(['is_telegram_sent' => true]);

                AuditLog::record(
                    'telegram.broadcast_sent',
                    $this->announcement,
                    [
                        'message_id' => $result['message_id'] ?? null,
                        'target_type' => $this->announcement->target_type,
                    ],
                    $this->announcement->author_id
                );
            } elseif ($result['status'] === 'skipped') {
                AuditLog::record(
                    'telegram.broadcast_skipped',
                    $this->announcement,
                    ['reason' => $result['reason'] ?? 'Not configured'],
                    $this->announcement->author_id
                );
            } else {
                // Trigger queue retry/backoff
                throw new \RuntimeException('Telegram broadcast failed: ' . ($result['reason'] ?? 'Unknown error'));
            }
        } elseif ($this->customMessage) {
            $result = $telegramService->sendMessage($this->customMessage);

            if (!$result['success'] && $result['status'] !== 'skipped') {
                throw new \RuntimeException('Telegram custom message failed: ' . ($result['reason'] ?? 'Unknown error'));
            }
        }
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('SendTelegramNotificationJob permanently failed after retries: ' . ($exception?->getMessage() ?? ''));

        AuditLog::record(
            'telegram.job_failed',
            $this->announcement,
            [
                'error' => substr($exception?->getMessage() ?? 'Unknown error', 0, 500),
                'attempts' => $this->attempts(),
            ],
            $this->announcement?->author_id
        );
    }
}
