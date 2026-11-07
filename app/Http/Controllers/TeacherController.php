<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Teaching schedules
        $schedules = Schedule::with('schoolClass.department')
            ->where('teacher_id', $user->id)
            ->orderBy('day_of_week')
            ->orderBy('shift')
            ->get();

        // Teaching classes
        $classes = $user->teachingClasses;

        // Sent announcements
        $sentAnnouncements = Announcement::where('author_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        // Announcements relevant to teacher
        $announcements = Announcement::with('author')
            ->where(function ($query) use ($user) {
                $query->where('target_type', 'public')
                    ->orWhere('target_type', 'all_teachers')
                    ->orWhere(function ($q) use ($user) {
                        $q->where('target_type', 'dept_teachers')
                          ->where('target_id', $user->department_id);
                    });
            })
            ->latest()
            ->take(10)
            ->get();

        return view('teacher.dashboard', compact('user', 'schedules', 'classes', 'sentAnnouncements', 'announcements'));
    }

    public function storeAnnouncement(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'class_id' => ['required', 'exists:classes,id'],
        ]);

        // Verify teacher actually teaches this class
        if (!$user->teachingClasses->contains('id', $request->class_id)) {
            return back()->with('error', 'អ្នកអាចផ្ញើដំណឹងបានតែទៅកាន់ថ្នាក់ដែលអ្នកបង្រៀនប៉ុណ្ណោះ!');
        }

        Announcement::create([
            'author_id' => $user->id,
            'title' => $request->title,
            'content' => $request->content,
            'target_type' => 'class',
            'target_id' => $request->class_id,
        ]);

        return back()->with('success', 'បានប្រកាសដំណឹងទៅកាន់ថ្នាក់ដោយជោគជ័យ!');
    }
}
