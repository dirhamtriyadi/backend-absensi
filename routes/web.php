<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnnualHolidayController;
use App\Http\Controllers\WorkScheduleController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;

// Route::get("/", function () {
//     return view("welcome");
// });

Route::middleware(['guest'])->group(function () {
    Route::get('/', [AuthController::class, 'login'])->name('login');
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'postLogin'])->name('postLogin');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get("/dashboard", function () {
        return view("templates.main");
    })->name("dashboard");

    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::resource('annual-holidays', AnnualHolidayController::class);
    Route::resource('work-schedules', WorkScheduleController::class);
    Route::resource('attendances', AttendanceController::class);
    Route::get('attendance-reports', [AttendanceReportController::class, 'index'])->name('attendance-reports.index');
    Route::resource('leaves', LeaveController::class);
    Route::post('leaves/{leave}/response', [LeaveController::class, 'response'])->name('leaves.response');
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
});
