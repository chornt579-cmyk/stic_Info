<?php

namespace App\Http\Controllers;

use App\Jobs\SendTelegramNotificationJob;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    /**
     * Display the comprehensive Teacher Dashboard.
     */
    public function dashboard()
    {
        /** @var User $user */
        $user = Auth::user();

        // 7 days in standardized order
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        // Teaching schedules with eager-loaded class and department (N+1 safe)
        $schedules = Schedule::with(['schoolClass.department'])
            ->where('teacher_id', $user->id)
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

        // Current day in Asia/Phnom_Penh timezone
        $today = strtolower(now()->timezone(config('app.timezone', 'Asia/Phnom_Penh'))->format('l'));
        $todaySchedules = $schedules->where('day_of_week', $today);
        $schedulesByDay = $schedules->groupBy('day_of_week');

        // Assigned teaching classes with department and enrolled student count
        $classes = $user->teachingClasses()
            ->with(['department'])
            ->withCount('students')
            ->get();

        $totalStudentsTaught = $classes->sum('students_count');

        // Sent announcements authored by this teacher
        $sentAnnouncements = Announcement::with('author')
            ->where('author_id', $user->id)
            ->latest()
            ->take(20)
            ->get();

        // Internal announcements for teachers
        $internalAnnouncements = Announcement::with('author')
            ->where(function ($query) use ($user) {
                $query->where('target_type', 'public')
                    ->orWhere('target_type', 'all_teachers')
                    ->orWhere(function ($q) use ($user) {
                        $q->where('target_type', 'dept_teachers')
                          ->where('target_id', $user->department_id);
                    });
            })
            ->where('author_id', '!=', $user->id)
            ->latest()
            ->take(15)
            ->get();

        return view('teacher.dashboard', compact(
            'user',
            'days',
            'today',
            'schedules',
            'todaySchedules',
            'schedulesByDay',
            'classes',
            'totalStudentsTaught',
            'sentAnnouncements',
            'internalAnnouncements'
        ));
    }

    /**
     * Post an announcement strictly to a class taught by this teacher.
     */
    public function storeAnnouncement(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
        ]);

        // Authorization check: Verify teacher actually teaches this class
        if (!$user->teachingClasses()->where('classes.id', $validated['class_id'])->exists()) {
            return back()->with('error', __('messages.only_teaching_classes_allowed'));
        }

        $announcement = Announcement::create([
            'author_id' => $user->id,
            'title' => strip_tags($validated['title']),
            'content' => strip_tags($validated['content']),
            'target_type' => 'class',
            'target_id' => $validated['class_id'],
        ]);

        AuditLog::record(
            'teacher_create_announcement',
            $announcement,
            [
                'title' => $announcement->title,
                'target_class_id' => $announcement->target_id,
            ],
            $user->id
        );

        if ($request->boolean('send_telegram')) {
            SendTelegramNotificationJob::dispatch($announcement);
        }

        return back()->with('success', __('messages.announcement_sent_success'));
    }

    /**
     * Delete an announcement authored by this teacher.
     */
    public function destroyAnnouncement(Announcement $announcement)
    {
        if ($announcement->author_id !== Auth::id()) {
            abort(403, __('messages.access_denied'));
        }

        $details = [
            'title' => $announcement->title,
            'target_type' => $announcement->target_type,
            'target_id' => $announcement->target_id,
        ];

        $announcement->delete();

        AuditLog::record(
            'teacher_delete_announcement',
            $announcement,
            $details,
            Auth::id()
        );

        return back()->with('success', __('messages.announcement_deleted_success'));
    }

    /**
     * Update teacher profile contact info.
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
            'teacher_update_profile',
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
