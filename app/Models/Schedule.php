<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'teacher_id',
        'room_id',
        'subject',
        'day_of_week',
        'shift',
        'room',
        'start_time',
        'end_time',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function roomRelation(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * Get display room name from either formal Room entity or legacy string.
     */
    public function getDisplayRoomAttribute(): string
    {
        if ($this->roomRelation) {
            return $this->roomRelation->display_name;
        }

        return $this->room ?? 'N/A';
    }
}
