<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * Disable updated_at since audit logs are permanent and immutable.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'ip_address',
        'user_agent',
        'details',
        'created_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'details' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Relationship: Actor user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Static helper to log an immutable audit event cleanly with sanitization.
     */
    public static function record(
        string $action,
        ?Model $model = null,
        array $details = [],
        ?int $userId = null
    ): self {
        // Sanitization Guard: NEVER log sensitive keys
        $forbiddenKeys = ['password', 'password_confirmation', 'token', 'secret', 'remember_token', 'api_token'];
        foreach ($forbiddenKeys as $key) {
            unset($details[$key]);
        }

        return static::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->getKey() : null,
            'ip_address' => Request::ip(),
            'user_agent' => substr(Request::userAgent() ?? '', 0, 500),
            'details' => $details,
            'created_at' => now(),
        ]);
    }
}

