<?php

namespace App\Http\Controllers;

use App\Jobs\SendTelegramNotificationJob;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\TimetableConflictService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Display the Admin Dashboard with System Stats & Management Tabs.
     */
    public function dashboard(Request $request)
    {
        // 1. High-Level System Statistics
        $stats = [
            'total_departments' => Department::count(),
            'total_classes' => SchoolClass::count(),
            'total_teachers' => User::where('role', 'teacher')->where('status', 'active')->count(),
            'pending_teachers' => User::where('role', 'teacher')->where('status', 'pending')->count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_rooms' => Room::count(),
            'total_schedules' => Schedule::count(),
            'total_announcements' => Announcement::count(),
            'total_logs' => AuditLog::count(),
        ];

        // 2. Pending Teacher Approvals (Always prioritized)
        $pendingTeachers = User::where('role', 'teacher')
            ->where('status', 'pending')
            ->with('department')
            ->latest()
            ->get();

        // 3. Departments with counts (Eager loaded)
        $departments = Department::withCount(['classes', 'users'])->orderBy('name')->get();

        // 4. Classes with department and student counts
        $classes = SchoolClass::with('department')
            ->withCount('students')
            ->orderBy('department_id')
            ->orderBy('year')
            ->get();

        // 5. Teachers Directory & All Active Teachers for Schedule Dropdown
        $allTeachers = User::where('role', 'teacher')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $teachers = User::where('role', 'teacher')
            ->with(['department', 'teachingClasses'])
            ->latest()
            ->paginate(15, ['*'], 'teachers_page');

        // 6. Rooms for Timetable Booking
        $rooms = Room::withCount('schedules')->orderBy('name')->get();

        // 7. Days of week standard (Monday to Sunday)
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        // 8. Students Directory with Search & Department Filter
        $studentQuery = User::where('role', 'student')->with(['department', 'schoolClass']);

        if ($request->filled('student_search')) {
            $search = $request->input('student_search');
            $studentQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('student_dept_id')) {
            $studentQuery->where('department_id', $request->input('student_dept_id'));
        }

        $students = $studentQuery->latest()->paginate(15, ['*'], 'students_page');

        // 9. Audit Logs Trail (Paginated)
        $auditLogs = AuditLog::with('user')
            ->latest('created_at')
            ->paginate(15, ['*'], 'logs_page');

        // 10. Schedules with eager loaded relationships and custom 7-day sort
        $scheduleQuery = Schedule::with(['schoolClass.department', 'teacher', 'roomRelation']);

        if ($request->filled('schedule_class_id')) {
            $scheduleQuery->where('class_id', $request->input('schedule_class_id'));
        }
        if ($request->filled('schedule_day')) {
            $scheduleQuery->where('day_of_week', $request->input('schedule_day'));
        }

        $schedules = $scheduleQuery->orderByRaw("CASE day_of_week
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
            ->paginate(20, ['*'], 'schedules_page');

        $allAnnouncements = Announcement::with('author')->latest()->paginate(15, ['*'], 'announcements_page');
        $announcements = Announcement::with('author')->latest()->take(15)->get();

        return view('admin.dashboard', compact(
            'stats',
            'pendingTeachers',
            'departments',
            'classes',
            'allTeachers',
            'teachers',
            'rooms',
            'days',
            'students',
            'auditLogs',
            'schedules',
            'announcements',
            'allAnnouncements'
        ));
    }

    // ==========================================
    // DEPARTMENTS CRUD
    // ==========================================

    public function storeDepartment(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:departments,code'],
            'description' => ['nullable', 'string'],
        ]);

        $dept = Department::create($validated);

        AuditLog::record('department.created', $dept, [
            'name' => $dept->name,
            'code' => $dept->code,
        ]);

        return back()->with('success', 'បានបង្កើតដេប៉ាតឺម៉ង់ថ្មីដោយជោគជ័យ!');
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', Rule::unique('departments', 'code')->ignore($department->id)],
            'description' => ['nullable', 'string'],
        ]);

        $department->update($validated);

        AuditLog::record('department.updated', $department, [
            'code' => $department->code,
        ]);

        return back()->with('success', 'បានកែប្រែដេប៉ាតឺម៉ង់ដោយជោគជ័យ!');
    }

    public function deleteDepartment(Department $department)
    {
        // Safety Cascading Check: Cannot delete department if classes or users exist
        if ($department->classes()->count() > 0 || $department->users()->count() > 0) {
            return back()->with('error', __('messages.cannot_delete_dept_has_classes'));
        }

        $code = $department->code;
        $department->delete();

        AuditLog::record('department.deleted', null, ['code' => $code]);

        return back()->with('success', 'បានលុបដេប៉ាតឺម៉ង់ដោយជោគជ័យ!');
    }

    // ==========================================
    // CLASSES CRUD
    // ==========================================

    public function storeClass(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1', 'max:5'],
            'shift' => ['required', 'in:morning,afternoon,evening'],
            'room' => ['nullable', 'string', 'max:50'],
            'floor' => ['nullable', 'string', 'max:20'],
            'capacity' => ['nullable', 'integer', 'min:1'],
        ]);

        $class = SchoolClass::create($validated);

        AuditLog::record('class.created', $class, [
            'name' => $class->name,
            'department_id' => $class->department_id,
        ]);

        return back()->with('success', 'បានបង្កើតថ្នាក់រៀនថ្មីដោយជោគជ័យ!');
    }

    public function updateClass(Request $request, SchoolClass $class)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1', 'max:5'],
            'shift' => ['required', 'in:morning,afternoon,evening'],
            'room' => ['nullable', 'string', 'max:50'],
            'floor' => ['nullable', 'string', 'max:20'],
            'capacity' => ['nullable', 'integer', 'min:1'],
        ]);

        $class->update($validated);

        AuditLog::record('class.updated', $class, [
            'name' => $class->name,
        ]);

        return back()->with('success', 'បានកែប្រែថ្នាក់រៀនដោយជោគជ័យ!');
    }

    public function deleteClass(SchoolClass $class)
    {
        // Safety Guard: Cannot delete class if students or schedules are linked
        if ($class->students()->count() > 0 || $class->schedules()->count() > 0) {
            return back()->with('error', __('messages.cannot_delete_class_has_students'));
        }

        $name = $class->name;
        $class->delete();

        AuditLog::record('class.deleted', null, ['name' => $name]);

        return back()->with('success', 'បានលុបថ្នាក់រៀនដោយជោគជ័យ!');
    }

    // ==========================================
    // TEACHER APPROVAL & STATUS MANAGEMENT
    // ==========================================

    public function approveTeacher(User $user)
    {
        return DB::transaction(function () use ($user) {
            /** @var User $lockedUser */
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            if (!$lockedUser->isTeacher()) {
                return back()->with('error', 'អ្នកប្រើប្រាស់នេះមិនមែនជាគ្រូបង្រៀនឡើយ!');
            }

            $lockedUser->approve();

            AuditLog::record('teacher.approved', $lockedUser, [
                'teacher_name' => $lockedUser->name,
                'teacher_email' => $lockedUser->email,
            ]);

            return back()->with('success', 'បានអនុម័តគ្រូបង្រៀនដោយជោគជ័យ!');
        });
    }

    public function rejectTeacher(User $user)
    {
        return DB::transaction(function () use ($user) {
            /** @var User $lockedUser */
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            if (!$lockedUser->isTeacher()) {
                return back()->with('error', 'អ្នកប្រើប្រាស់នេះមិនមែនជាគ្រូបង្រៀនឡើយ!');
            }

            $lockedUser->reject();

            AuditLog::record('teacher.rejected', $lockedUser, [
                'teacher_name' => $lockedUser->name,
                'teacher_email' => $lockedUser->email,
            ]);

            return back()->with('success', 'បានបដិសេធសំណើសុំរបស់គ្រូបង្រៀនដោយជោគជ័យ!');
        });
    }

    public function toggleUserStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => ['required', 'in:active,suspended'],
        ]);

        return DB::transaction(function () use ($request, $user) {
            /** @var User $lockedUser */
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            if ($lockedUser->id === Auth::id()) {
                return back()->with('error', 'អ្នកមិនអាចផ្អាកគណនីផ្ទាល់ខ្លួនរបស់អ្នកបានឡើយ!');
            }

            $oldStatus = $lockedUser->status;
            $lockedUser->status = $request->status;
            $lockedUser->save();

            AuditLog::record('user.status_changed', $lockedUser, [
                'old_status' => $oldStatus,
                'new_status' => $request->status,
            ]);

            return back()->with('success', 'បានកែប្រែស្ថានភាពអ្នកប្រើប្រាស់ដោយជោគជ័យ!');
        });
    }

    public function assignTeacherClasses(Request $request, User $user)
    {
        $request->validate([
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['exists:classes,id'],
            'subject' => ['nullable', 'string', 'max:255'],
        ]);

        $syncData = [];
        $subject = $request->input('subject', 'General');
        foreach ($request->input('class_ids', []) as $classId) {
            $syncData[$classId] = ['subject' => $subject];
        }

        $user->teachingClasses()->sync($syncData);

        AuditLog::record('teacher.assigned_classes', $user, [
            'class_count' => count($syncData),
        ]);

        return back()->with('success', 'បានចាត់តាំងថ្នាក់បង្រៀនជូនគ្រូដោយជោគជ័យ!');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'អ្នកមិនអាចលុបគណនីរបស់ខ្លួនឯងបានឡើយ!');
        }

        $name = $user->name;
        $role = $user->role;
        $user->delete(); // Soft delete

        AuditLog::record('user.deleted', null, [
            'name' => $name,
            'role' => $role,
        ]);

        return back()->with('success', 'បានលុបគណនីអ្នកប្រើប្រាស់ដោយជោគជ័យ!');
    }

    // ==========================================
    // SCHEDULES & TIMETABLE CONFLICT ENGINE
    // ==========================================

    public function storeSchedule(Request $request, TimetableConflictService $conflictService)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'teacher_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'shift' => 'required|in:morning,afternoon,evening',
            'room_id' => 'nullable|exists:rooms,id',
            'room' => 'nullable|string|max:100',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
        ]);

        if (!empty($validated['room_id']) && empty($validated['room'])) {
            $room = Room::find($validated['room_id']);
            $validated['room'] = $room?->name;
        }

        $schedule = $conflictService->createScheduleSafely($validated);

        AuditLog::record('schedule.created', $schedule, [
            'class_id' => $schedule->class_id,
            'teacher_id' => $schedule->teacher_id,
            'subject' => $schedule->subject,
            'day_of_week' => $schedule->day_of_week,
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'room' => $schedule->display_room,
        ]);

        return back()->with('success', __('messages.schedule_created_success'));
    }

    public function deleteSchedule(Schedule $schedule)
    {
        $details = [
            'class_id' => $schedule->class_id,
            'subject' => $schedule->subject,
            'teacher_id' => $schedule->teacher_id,
            'day_of_week' => $schedule->day_of_week,
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
        ];

        $schedule->delete();

        AuditLog::record('schedule.deleted', null, $details);

        return back()->with('success', __('messages.schedule_deleted_success'));
    }

    // ==========================================
    // ROOMS CRUD
    // ==========================================

    public function storeRoom(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:rooms,name',
            'name_kh' => 'nullable|string|max:100',
            'building' => 'nullable|string|max:50',
            'floor' => 'nullable|string|max:20',
            'capacity' => 'nullable|integer|min:1|max:500',
            'room_type' => 'required|in:lecture,lab,workshop,hall',
        ]);

        $room = Room::create($validated);

        AuditLog::record('room.created', $room, [
            'name' => $room->name,
            'building' => $room->building,
            'capacity' => $room->capacity,
        ]);

        return back()->with('success', __('messages.room_created_success'));
    }

    public function deleteRoom(Room $room)
    {
        if ($room->schedules()->count() > 0) {
            return back()->with('error', __('messages.cannot_delete_room_has_schedules'));
        }

        $name = $room->name;
        $room->delete();

        AuditLog::record('room.deleted', null, ['name' => $name]);

        return back()->with('success', __('messages.room_deleted_success'));
    }

    // ==========================================
    // ANNOUNCEMENTS
    // ==========================================

    public function storeAnnouncement(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_type' => 'required|in:public,all_students,class,dept_students,all_teachers,dept_teachers',
            'target_id' => 'nullable|integer',
            'send_telegram' => 'nullable|boolean',
        ]);

        $announcement = Announcement::create([
            'author_id' => Auth::id(),
            'title' => strip_tags($validated['title']),
            'content' => strip_tags($validated['content']),
            'target_type' => $validated['target_type'],
            'target_id' => $validated['target_id'] ?? null,
            'is_telegram_sent' => false,
        ]);

        AuditLog::record('announcement.created', $announcement, [
            'title' => $announcement->title,
            'target_type' => $announcement->target_type,
        ]);

        // Dispatch background queue job for Telegram notification if requested or public
        if ($request->boolean('send_telegram') || in_array($announcement->target_type, ['public', 'all_students'])) {
            SendTelegramNotificationJob::dispatch($announcement);
            AuditLog::record('telegram.broadcast_dispatched', $announcement, [
                'title' => $announcement->title,
            ]);
        }

        return back()->with('success', 'បានប្រកាសដំណឹងដោយជោគជ័យ!');
    }

    public function deleteAnnouncement(Announcement $announcement)
    {
        $announcement->delete();
        return back()->with('success', 'បានលុបដំណឹងដោយជោគជ័យ!');
    }
}
