<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class PruneAuditLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stic:prune-audit-logs
                            {--days= : Retention period in days (defaults to AUDIT_LOG_RETENTION_DAYS or 90)}
                            {--dry-run : Simulate the prune operation without deleting records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune audit logs older than the configured retention policy (minimum 90 days)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: env('AUDIT_LOG_RETENTION_DAYS', 90));

        // Governance Rule 6.2: Minimum 90 days retention policy
        if ($days < 90) {
            $this->warn('Governance Policy Notice: Audit logs must be retained for a minimum of 90 days.');
            $this->warn('Enforcing minimum 90 days retention.');
            $days = 90;
        }

        $cutoff = now()->subDays($days);
        $query = AuditLog::where('created_at', '<', $cutoff);
        $count = $query->count();

        $this->info("Audit log retention policy: {$days} days.");
        $this->info("Cutoff timestamp: {$cutoff->toDateTimeString()} (Phnom Penh)");

        if ($this->option('dry-run')) {
            $this->warn("[DRY RUN] Found {$count} audit log records older than {$days} days eligible for pruning.");
            return self::SUCCESS;
        }

        if ($count === 0) {
            $this->info('No audit logs older than the retention threshold found. Nothing to prune.');
            return self::SUCCESS;
        }

        // Record immutable audit event for the pruning action
        AuditLog::record('audit.pruned', null, [
            'pruned_count' => $count,
            'retention_days' => $days,
            'cutoff_date' => $cutoff->toIso8601String(),
        ]);

        $deleted = $query->delete();
        $this->info("Successfully pruned {$deleted} audit log records older than {$days} days.");

        return self::SUCCESS;
    }
}
