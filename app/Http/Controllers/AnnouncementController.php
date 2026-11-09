<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    /**
     * Get the latest published announcement for the active user (or guest).
     * Used for Browser & Phone notifications and real-time alerts.
     */
    public function latest(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Announcement::with('author')->latest('created_at');

        if (!$user) {
            // Guest visitors see public announcements
            $query->where('target_type', 'public');
        } elseif ($user->isStudent()) {
            // Students see: public, all_students, their department, or their specific class
            $query->where(function ($q) use ($user) {
                $q->where('target_type', 'public')
                  ->orWhere('target_type', 'all_students')
                  ->orWhere(function ($sub) use ($user) {
                      $sub->where('target_type', 'dept_students')
                          ->where('target_id', $user->department_id);
                  })
                  ->orWhere(function ($sub) use ($user) {
                      $sub->where('target_type', 'class')
                          ->where('target_id', $user->class_id);
                  });
            });
        } elseif ($user->isTeacher()) {
            // Teachers see: public, all_teachers, their department, or announcements authored by them
            $query->where(function ($q) use ($user) {
                $q->where('target_type', 'public')
                  ->orWhere('target_type', 'all_teachers')
                  ->orWhere(function ($sub) use ($user) {
                      $sub->where('target_type', 'dept_teachers')
                          ->where('target_id', $user->department_id);
                  })
                  ->orWhere('author_id', $user->id);
            });
        }
        // Admins see all announcements

        $latest = $query->first();

        if (!$latest) {
            return response()->json([
                'has_announcement' => false,
            ]);
        }

        // Determine destination link
        $url = route('home') . '#announcements';
        if ($user) {
            if ($user->isAdmin()) {
                $url = route('admin.dashboard') . '#announcements';
            } elseif ($user->isTeacher()) {
                $url = route('teacher.dashboard') . '#tab-announcements';
            } elseif ($user->isStudent()) {
                $url = route('student.dashboard') . '#tab-announcements';
            }
        }

        return response()->json([
            'has_announcement' => true,
            'announcement' => [
                'id' => $latest->id,
                'title' => $latest->title,
                'snippet' => Str::limit(strip_tags($latest->content), 120),
                'target_type' => $latest->target_type,
                'author_name' => $latest->author ? $latest->author->name : 'គណៈគ្រប់គ្រង STIC',
                'created_at_human' => $latest->created_at->diffForHumans(),
                'created_at_formatted' => $latest->created_at->timezone('Asia/Phnom_Penh')->format('d/m/Y H:i'),
                'url' => $url,
            ],
        ]);
    }
}

