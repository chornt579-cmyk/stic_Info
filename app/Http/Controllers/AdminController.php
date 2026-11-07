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

class AdminController extends Controller
{
    public function dashboard()
    {
        $departments = Department::withCount('classes')->get();
        $classes = SchoolClass::with('department')->get();
        $teachers = User::where('role', 'teacher')->with(['department', 'teachingClasses'])->latest()->get();
        $students = User::where('role', 'student')->with(['department', 'schoolClass'])->latest()->take(50)->get();
        $schedules = Schedule::with(['schoolClass', 'teacher'])->orderBy('day_of_week')->orderBy('shift')->get();
        $announcements = Announcement::with('author')->latest()->take(20)->get();

        return view('admin.dashboard', compact(
            'departments',
            'classes',
            'teachers',
            'students',
            'schedules',
            'announcements'
        ));
    }

    // Departments CRUD
    public function storeDepartment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_kh' => 'nullable|string|max:255',
            'code' => 'required|string|max:10|unique:departments,code',
        ]);

        Department::create($request->only('name', 'name_kh', 'code', 'description'));
        return back()->with('success', 'បានបង្កើតដេប៉ាតឺម៉ង់ថ្មីដោយជោគជ័យ!');
    }

    public function deleteDepartment(Department $department)
    {
        $department->delete();
        return back()->with('success', 'បានលុបដេប៉ាតឺម៉ង់ដោយជោគជ័យ!');
    }

    // Classes CRUD
    public function storeClass(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'name_kh' => 'nullable|string|max:255',
            'year' => 'required|integer|min:1|max:5',
            'shift' => 'required|in:morning,afternoon,evening',
            'room' => 'nullable|string|max:50',
            'floor' => 'nullable|string|max:20',
        ]);

        SchoolClass::create($request->all());
        return back()->with('success', 'បានបង្កើតថ្នាក់រៀនថ្មីដោយជោគជ័យ!');
    }

    public function deleteClass(SchoolClass $class)
    {
        $class->delete();
        return back()->with('success', 'បានលុបថ្នាក់រៀនដោយជោគជ័យ!');
    }

    // Teacher Approval with Transaction & Row Locking
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

    // Teacher Rejection with Transaction & Row Locking
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

    // Toggle User Status (e.g. active, suspended) with Row Locking
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

    // Assign Classes to Teacher
    public function assignTeacherClasses(Request $request, User $user)
    {
        $request->validate([
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:classes,id',
        ]);

        $user->teachingClasses()->sync($request->class_ids ?? []);
        return back()->with('success', 'បានចាត់តាំងថ្នាក់បង្រៀនជូនគ្រូដោយជោគជ័យ!');
    }

    // Schedule Store with CONFLICT DETECTION
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

        // 1. Conflict Check: Teacher Double-Booking
        $teacherConflict = Schedule::where('teacher_id', $request->teacher_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('shift', $request->shift)
            ->first();

        if ($teacherConflict) {
            return back()->withInput()->with('error', '⚠️ បរាជ័យ៖ គ្រូបង្រៀនរូបនេះមានម៉ោងបង្រៀននៅថ្ងៃ និងវេននេះរួចហើយ!');
        }

        // 2. Conflict Check: Class Double-Booking
        $classConflict = Schedule::where('class_id', $request->class_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('shift', $request->shift)
            ->first();

        if ($classConflict) {
            return back()->withInput()->with('error', '⚠️ បរាជ័យ៖ ថ្នាក់រៀននេះមានម៉ោងរៀននៅថ្ងៃ និងវេននេះរួចហើយ!');
        }

        // 3. Conflict Check: Room Double-Booking
        if ($request->room) {
            $roomConflict = Schedule::where('room', $request->room)
                ->where('day_of_week', $request->day_of_week)
                ->where('shift', $request->shift)
                ->first();

            if ($roomConflict) {
                return back()->withInput()->with('error', '⚠️ បរាជ័យ៖ បន្ទប់រៀននេះ (' . $request->room . ') ត្រូវបានប្រើប្រាស់នៅថ្ងៃ និងវេននេះរួចហើយ!');
            }
        }

        Schedule::create($request->all());
        return back()->with('success', 'បានបញ្ចូលកាលវិភាគបង្រៀនដោយជោគជ័យ!');
    }

    public function deleteSchedule(Schedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'បានលុបកាលវិភាគដោយជោគជ័យ!');
    }

    // Announcements Store
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
