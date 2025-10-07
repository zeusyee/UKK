<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LeaderController;
use App\Http\Controllers\UserController;

// Redirect halaman utama ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// Register
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard umum (opsional, semua role bisa masuk setelah login)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// Admin routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/project/create', [\App\Http\Controllers\ProjectController::class, 'create'])->name('admin.project.create');
    Route::post('/admin/project', [\App\Http\Controllers\ProjectController::class, 'store'])->name('admin.project.store');
    Route::get('/admin/project', [\App\Http\Controllers\ProjectController::class, 'index'])->name('admin.project.index');
    Route::get('/admin/project-member', [AdminController::class, 'projectMember'])->name('admin.project-member');
    Route::post('/admin/add-member', [AdminController::class, 'addMember'])->name('admin.addMember');
    Route::delete('/admin/remove-member/{id}', [AdminController::class, 'removeMember'])->name('admin.removeMember');
});

// Leader routes
Route::middleware(['auth', 'role:leader'])->group(function () {
    Route::get('/leader/dashboard', [\App\Http\Controllers\LeaderController::class, 'dashboard'])->name('leader.dashboard');
    Route::get('/leader/task', [\App\Http\Controllers\TaskController::class, 'index'])->name('leader.task.index');
    Route::get('/leader/task/create', [\App\Http\Controllers\TaskController::class, 'create'])->name('leader.task.create');
    Route::post('/leader/task', [\App\Http\Controllers\TaskController::class, 'store'])->name('leader.task.store');
    Route::get('/leader/task/{id}/edit', [\App\Http\Controllers\TaskController::class, 'edit'])->name('leader.task.edit');
    Route::put('/leader/task/{id}', [\App\Http\Controllers\TaskController::class, 'update'])->name('leader.task.update');
    Route::delete('/leader/task/{id}', [\App\Http\Controllers\TaskController::class, 'destroy'])->name('leader.task.destroy');
    Route::get('/leader/task/{id}/review', [\App\Http\Controllers\TaskController::class, 'review'])->name('leader.task.review');
    Route::post('/leader/task/{id}/unblock', [\App\Http\Controllers\TaskController::class, 'unblock'])->name('leader.task.unblock');
});

// Comment routes
Route::middleware(['auth'])->group(function () {
    Route::get('/comment/{card_id}', [\App\Http\Controllers\CommentController::class, 'index'])->name('comment.index');
    Route::post('/comment/{card_id}', [\App\Http\Controllers\CommentController::class, 'store'])->name('comment.store');
});

// TimeLog routes
Route::middleware(['auth'])->group(function () {
    Route::get('/timelog/{card_id}', [\App\Http\Controllers\TimeLogController::class, 'index'])->name('timelog.index');
    Route::post('/timelog/{card_id}/start', [\App\Http\Controllers\TimeLogController::class, 'start'])->name('timelog.start');
    Route::post('/timelog/{card_id}/finish', [\App\Http\Controllers\TimeLogController::class, 'finish'])->name('timelog.finish');
});

// Report routes
Route::middleware(['auth'])->group(function () {
    Route::get('/report/project/{project_id}', [\App\Http\Controllers\ReportController::class, 'project'])->name('report.project');
    Route::get('/report/team/{project_id}', [\App\Http\Controllers\ReportController::class, 'team'])->name('report.team');
    Route::get('/report/task/{card_id}', [\App\Http\Controllers\ReportController::class, 'task'])->name('report.task');
});

// Notification routes
Route::middleware(['auth'])->group(function () {
    Route::get('/notification', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notification.index');
    Route::post('/notification/help-request', [\App\Http\Controllers\NotificationController::class, 'helpRequest'])->name('notification.helpRequest');
});

// User routes
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/user/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
});
