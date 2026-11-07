<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Determine today's day of week
        $days = [
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
        ];
        $todayKey = $days[date('w')];

        // Today's schedule
        $todaySchedules = [];
        $weeklySchedules = [];

        if ($user->class_id) {
            $todaySchedules = Schedule::with('teacher')
                ->where('class_id', $user->class_id)
                ->where('day_of_week', $todayKey)
                ->orderBy('shift')
                ->get();

            // Weekly schedule
            $weeklySchedules = Schedule::with('teacher')
                ->where('class_id', $user->class_id)
                ->orderBy('day_of_week')
                ->orderBy('shift')
                ->get()
                ->groupBy('day_of_week');
        }

        // Student Announcements:
        // Public + All Students + Their Class + Their Dept (and NOT teacher-only)
        $announcements = Announcement::with('author')
            ->where(function ($query) use ($user) {
                $query->where('target_type', 'public')
                    ->orWhere('target_type', 'all_students')
                    ->orWhere(function ($q) use ($user) {
                        $q->where('target_type', 'class')
                          ->where('target_id', $user->class_id);
                    })
                    ->orWhere(function ($q) use ($user) {
                        $q->where('target_type', 'dept_students')
                          ->where('target_id', $user->department_id);
                    });
            })
            ->latest()
            ->take(10)
            ->get();

        return view('student.dashboard', compact('user', 'todaySchedules', 'weeklySchedules', 'announcements', 'todayKey'));
    }
}
