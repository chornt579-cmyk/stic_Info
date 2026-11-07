<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'name_kh',
        'building',
        'floor',
        'capacity',
        'room_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    /**
     * Relationship: Schedules booked in this room.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'room_id');
    }

    /**
     * Display title helper (Khmer + English).
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->name_kh && $this->name_kh !== $this->name) {
            return "{$this->name_kh} ({$this->name})";
        }
        return $this->name;
    }
}
