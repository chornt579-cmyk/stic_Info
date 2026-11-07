<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/pending-approval', [AuthController::class, 'pendingApproval'])->name('pending.approval');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Route to proper role dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Student Routes
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    });

    // Teacher Routes
    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
        Route::post('/announcements', [TeacherController::class, 'storeAnnouncement'])->name('announcements.store');
    });

    // Admin Routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Departments
        Route::post('/departments', [AdminController::class, 'storeDepartment'])->name('departments.store');
        Route::delete('/departments/{department}', [AdminController::class, 'deleteDepartment'])->name('departments.destroy');

        // Classes
        Route::post('/classes', [AdminController::class, 'storeClass'])->name('classes.store');
        Route::delete('/classes/{class}', [AdminController::class, 'deleteClass'])->name('classes.destroy');

        // Teachers Approval & Assignments
        Route::patch('/teachers/{user}/approve', [AdminController::class, 'approveTeacher'])->name('teachers.approve');
        Route::post('/teachers/{user}/classes', [AdminController::class, 'assignTeacherClasses'])->name('teachers.assign-classes');

        // Schedules
        Route::post('/schedules', [AdminController::class, 'storeSchedule'])->name('schedules.store');
        Route::delete('/schedules/{schedule}', [AdminController::class, 'deleteSchedule'])->name('schedules.destroy');

        // Announcements
        Route::post('/announcements', [AdminController::class, 'storeAnnouncement'])->name('announcements.store');
        Route::delete('/announcements/{announcement}', [AdminController::class, 'deleteAnnouncement'])->name('announcements.destroy');
    });
});
