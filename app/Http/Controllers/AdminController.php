<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\User;
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

        // 5. Teachers Directory
        $teachers = User::where('role', 'teacher')
            ->with(['department', 'teachingClasses'])
            ->latest()
            ->paginate(15, ['*'], 'teachers_page');

        // 6. Students Directory with Search & Department Filter
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

        // 7. Audit Logs Trail (Paginated)
        $auditLogs = AuditLog::with('user')
            ->latest('created_at')
            ->paginate(15, ['*'], 'logs_page');

        // 8. Schedules and Announcements for management
        $schedules = Schedule::with(['schoolClass.department', 'teacher'])->orderBy('day_of_week')->orderBy('shift')->get();
        $announcements = Announcement::with('author')->latest()->take(15)->get();

        return view('admin.dashboard', compact(
            'stats',
            'pendingTeachers',
            'departments',
            'classes',
            'teachers',
            'students',
            'auditLogs',
            'schedules',
            'announcements'
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
    // SCHEDULES & ANNOUNCEMENTS (Existing Core)
    // ==========================================

    public function storeSchedule(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'teacher_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'shift' => 'required|in:morning,afternoon,evening',
            'room' => 'nullable|string|max:50',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
        ]);

        Schedule::create($request->all());
        return back()->with('success', 'បានបញ្ចូលកាលវិភាគបង្រៀនដោយជោគជ័យ!');
    }

    public function deleteSchedule(Schedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'បានលុបកាលវិភាគដោយជោគជ័យ!');
    }

    public function storeAnnouncement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_type' => 'required|in:public,all_students,class,dept_students,all_teachers,dept_teachers',
            'target_id' => 'nullable|integer',
        ]);

        Announcement::create([
            'author_id' => Auth::id(),
            'title' => $request->title,
            'content' => $request->content,
            'target_type' => $request->target_type,
            'target_id' => $request->target_id,
        ]);

        return back()->with('success', 'បានប្រកាសដំណឹងដោយជោគជ័យ!');
    }

    public function deleteAnnouncement(Announcement $announcement)
    {
        $announcement->delete();
        return back()->with('success', 'បានលុបដំណឹងដោយជោគជ័យ!');
    }
}
