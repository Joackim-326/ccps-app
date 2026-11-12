<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::put('/password', [AuthController::class, 'changePassword'])->name('password.change.update');

    // Challenges
    Route::middleware('role:receptionist')->group(function () {
        Route::get('/challenges/create', [ChallengeController::class, 'create'])->name('challenges.create');
        Route::post('/challenges', [ChallengeController::class, 'store'])->name('challenges.store');
    });
    Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges.index');
    Route::post('/challenges/{challenge}/reassign', [ChallengeController::class, 'reassign'])->middleware('role:receptionist,creator')->name('challenges.reassign');
    Route::post('/challenges/{challenge}/attend', [ChallengeController::class, 'attend'])->middleware('role:officer,creator')->name('challenges.attend');

    // Chat
    Route::get('/chat/unread', [ChatController::class, 'unread'])->name('chat.unread');
    Route::get('/chat/{user?}', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{user}/poll', [ChatController::class, 'poll'])->name('chat.poll');
    Route::post('/chat/{user}', [ChatController::class, 'store'])->middleware('throttle:60,1')->name('chat.store');
    Route::delete('/chat/{user}', [ChatController::class, 'clear'])->name('chat.clear');
    Route::put('/chat/{user}/note', [ChatController::class, 'note'])->name('chat.note');

    // Creator administration
    Route::middleware('role:creator')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::put('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/challenges', [Admin\ChallengeController::class, 'index'])->name('challenges.index');
        Route::delete('/challenges/{challenge}', [Admin\ChallengeController::class, 'destroy'])->name('challenges.destroy');
        Route::get('/logs', [Admin\LogController::class, 'index'])->name('logs.index');
    });
});
