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
    Route::get('/leader/dashboard', [LeaderController::class, 'dashboard'])->name('leader.dashboard');
});

// User routes
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/user/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
});
