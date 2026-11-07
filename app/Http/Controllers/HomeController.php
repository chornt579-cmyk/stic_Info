<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Department;
use App\Models\Schedule;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Get public announcements
        $announcements = Announcement::with('author')
            ->where('target_type', 'public')
            ->latest()
            ->take(6)
            ->get();

        // Get departments with classes count
        $departments = Department::withCount('classes')->get();

        return view('home', compact('announcements', 'departments'));
    }
}
