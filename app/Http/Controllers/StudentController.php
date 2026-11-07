<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /**
     * Display the Student Portal dashboard.
     */
    public function dashboard()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->loadMissing(['department', 'schoolClass.department']);

        // Standardized 7 days of the week (Monday to Sunday)
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        // Today's day name in official timezone Asia/Phnom_Penh
        $today = strtolower(now()->timezone(config('app.timezone', 'Asia/Phnom_Penh'))->format('l'));

        $schedules = collect();
        $todaySchedules = collect();
        $schedulesByDay = collect();

        if ($user->class_id) {
            // Eager load teacher with department to prevent N+1 queries
            $schedules = Schedule::with(['teacher.department'])
                ->where('class_id', $user->class_id)
                ->orderByRaw("CASE day_of_week
                    WHEN 'monday' THEN 1
                    WHEN 'tuesday' THEN 2
                    WHEN 'wednesday' THEN 3
                    WHEN 'thursday' THEN 4
                    WHEN 'friday' THEN 5
                    WHEN 'saturday' THEN 6
                    WHEN 'sunday' THEN 7
                    ELSE 8 END")
                ->orderBy('start_time')
                ->orderBy('shift')
                ->get();

            $todaySchedules = $schedules->where('day_of_week', $today);
            $schedulesByDay = $schedules->groupBy('day_of_week');
        }

        // Relevant announcements for this student: Public + All Students + Dept Students + Class Students
        $announcements = Announcement::with('author')
            ->where(function ($query) use ($user) {
                $query->where('target_type', 'public')
                    ->orWhere('target_type', 'all_students')
                    ->orWhere(function ($q) use ($user) {
                        $q->where('target_type', 'dept_students')
                          ->where('target_id', $user->department_id);
                    });

                if ($user->class_id) {
                    $query->orWhere(function ($q) use ($user) {
                        $q->where('target_type', 'class')
                          ->where('target_id', $user->class_id);
                    });
                }
            })
            ->latest()
            ->paginate(15);

        // Classmates with strict data privacy: ONLY select id, name, created_at.
        // Rule: NEVER expose classmates' phone numbers or emails!
        $classmates = $user->class_id
            ? User::where('class_id', $user->class_id)
                ->where('role', 'student')
                ->where('status', 'active')
                ->where('id', '!=', $user->id)
                ->select(['id', 'name', 'created_at'])
                ->orderBy('name')
                ->get()
            : collect();

        return view('student.dashboard', compact(
            'user',
            'days',
            'today',
            'schedules',
            'todaySchedules',
            'schedulesByDay',
            'announcements',
            'classmates'
        ));
    }

    /**
     * Update student contact phone number.
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'regex:/^[0-9+\s-]{8,20}$/'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $oldPhone = $user->phone;
        $user->phone = $validated['phone'];
        $user->save();

        AuditLog::record(
            'student_update_profile',
            $user,
            [
                'old_phone' => $oldPhone,
                'new_phone' => $user->phone,
            ],
            $user->id
        );

        return back()->with('success', __('messages.profile_updated_success'));
    }
}
