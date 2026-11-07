<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — STIC Info
|--------------------------------------------------------------------------
| Prepared for future Android / mobile application consumption.
| Uses token authentication (Sanctum) and standard JSON responses.
*/

Route::prefix('v1')->group(function () {
    // Health & System Info
    Route::get('/health', function () {
        return response()->json([
            'status' => 'healthy',
            'app' => config('app.name'),
            'version' => '1.0.0',
            'institution' => 'Techo Sen Regional Polytechnic Institute of Siem Reap (STIC)',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    // Public API Endpoints
    Route::get('/departments', function () {
        return response()->json([
            'departments' => \App\Models\Department::select('id', 'name', 'name_kh', 'code')->get(),
        ]);
    });

    Route::get('/announcements/public', function () {
        $announcements = \App\Models\Announcement::where('target_type', 'public')
            ->latest()
            ->take(20)
            ->get();

        return response()->json([
            'data' => $announcements,
        ]);
    });
});

