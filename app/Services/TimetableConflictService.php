<?php

namespace App\Services;

use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TimetableConflictService
{
    /**
     * Validate and create a schedule safely inside a database transaction with pessimistic row locking.
     *
     * @param array $data
     * @param int|null $ignoreScheduleId
     * @return Schedule
     * @throws ValidationException
     */
    public function createScheduleSafely(array $data, ?int $ignoreScheduleId = null): Schedule
    {
        return DB::transaction(function () use ($data, $ignoreScheduleId) {
            $this->validateTimeRange($data);

            // Pessimistic row locking on schedules for this day of week to prevent race conditions
            Schedule::where('day_of_week', $data['day_of_week'])
                ->lockForUpdate()
                ->get();

            $this->detectConflicts($data, $ignoreScheduleId);

            return Schedule::create($data);
        });
    }

    /**
     * Validate and update an existing schedule safely with conflict detection.
     *
     * @param Schedule $schedule
     * @param array $data
     * @return Schedule
     * @throws ValidationException
     */
    public function updateScheduleSafely(Schedule $schedule, array $data): Schedule
    {
        return DB::transaction(function () use ($schedule, $data) {
            $this->validateTimeRange($data);

            Schedule::where('day_of_week', $data['day_of_week'])
                ->lockForUpdate()
                ->get();

            $this->detectConflicts($data, $schedule->id);

            $schedule->update($data);
            return $schedule;
        });
    }

    /**
     * Validate start_time and end_time range sanity.
     */
    protected function validateTimeRange(array $data): void
    {
        $start = $data['start_time'] ?? null;
        $end = $data['end_time'] ?? null;

        if ($start && $end) {
            if ($start >= $end) {
                throw ValidationException::withMessages([
                    'end_time' => [__('messages.time_end_before_start') ?? 'ម៉ោងបញ្ចប់ត្រូវតែធំជាងម៉ោងចាប់ផ្ដើម! (End time must be after start time)'],
                ]);
            }
        }
    }

    /**
     * Detect conflicts across Teacher, Class, and Room dimensions.
     *
     * Mathematical Overlap Formula:
     * (existing_start < new_end) AND (existing_end > new_start)
     * Adjacent slots (e.g. 08:00-10:00 and 10:00-12:00) are explicitly permitted.
     *
     * @param array $data
     * @param int|null $ignoreScheduleId
     * @throws ValidationException
     */
    public function detectConflicts(array $data, ?int $ignoreScheduleId = null): void
    {
        $teacherId = $data['teacher_id'];
        $classId = $data['class_id'];
        $dayOfWeek = $data['day_of_week'];
        $shift = $data['shift'];
        $roomId = $data['room_id'] ?? null;
        $roomName = $data['room'] ?? null;
        $newStart = $data['start_time'] ?? null;
        $newEnd = $data['end_time'] ?? null;

        $timeOverlapQuery = function ($query) use ($newStart, $newEnd, $shift) {
            if ($newStart && $newEnd) {
                $query->where(function ($q) use ($newStart, $newEnd) {
                    $q->whereNotNull('start_time')
                      ->whereNotNull('end_time')
                      ->where('start_time', '<', $newEnd)
                      ->where('end_time', '>', $newStart);
                })->orWhere(function ($q) use ($shift) {
                    $q->where(function ($sq) {
                        $sq->whereNull('start_time')->orWhereNull('end_time');
                    })->where('shift', $shift);
                });
            } else {
                $query->where('shift', $shift);
            }
        };

        // 1. TEACHER CONFLICT CHECK
        $teacherConflict = Schedule::where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->when($ignoreScheduleId, fn($q) => $q->where('id', '!=', $ignoreScheduleId))
            ->where($timeOverlapQuery)
            ->with(['schoolClass', 'teacher'])
            ->first();

        if ($teacherConflict) {
            $slotInfo = $teacherConflict->start_time && $teacherConflict->end_time
                ? " ({$teacherConflict->start_time} - {$teacherConflict->end_time})"
                : " (វេន " . ucfirst($teacherConflict->shift) . ")";
            $className = $teacherConflict->schoolClass?->name ?? 'ថ្នាក់ផ្សេង';

            throw ValidationException::withMessages([
                'teacher_id' => [__('messages.schedule_conflict_teacher') . " [{$className}{$slotInfo}]"],
            ]);
        }

        // 2. CLASS CONFLICT CHECK
        $classConflict = Schedule::where('class_id', $classId)
            ->where('day_of_week', $dayOfWeek)
            ->when($ignoreScheduleId, fn($q) => $q->where('id', '!=', $ignoreScheduleId))
            ->where($timeOverlapQuery)
            ->with(['schoolClass', 'teacher'])
            ->first();

        if ($classConflict) {
            $slotInfo = $classConflict->start_time && $classConflict->end_time
                ? " ({$classConflict->start_time} - {$classConflict->end_time})"
                : " (វេន " . ucfirst($classConflict->shift) . ")";

            throw ValidationException::withMessages([
                'class_id' => [__('messages.schedule_conflict_class') . " [{$classConflict->subject}{$slotInfo}]"],
            ]);
        }

        // 3. ROOM CONFLICT CHECK
        if ($roomId || $roomName) {
            $roomConflict = Schedule::where('day_of_week', $dayOfWeek)
                ->when($ignoreScheduleId, fn($q) => $q->where('id', '!=', $ignoreScheduleId))
                ->where(function ($q) use ($roomId, $roomName) {
                    if ($roomId) {
                        $q->where('room_id', $roomId);
                    }
                    if ($roomName) {
                        $q->orWhere('room', $roomName);
                    }
                })
                ->where($timeOverlapQuery)
                ->with(['schoolClass', 'teacher'])
                ->first();

            if ($roomConflict) {
                $slotInfo = $roomConflict->start_time && $roomConflict->end_time
                    ? " ({$roomConflict->start_time} - {$roomConflict->end_time})"
                    : " (វេន " . ucfirst($roomConflict->shift) . ")";
                $conflictClass = $roomConflict->schoolClass?->name ?? 'ថ្នាក់ផ្សេង';

                throw ValidationException::withMessages([
                    'room' => [__('messages.schedule_conflict_room') . " [{$conflictClass}{$slotInfo}]"],
                ]);
            }
        }
    }
}
