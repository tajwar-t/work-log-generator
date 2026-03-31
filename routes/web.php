<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkLogController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('logs.index'));

    // Work Logs
    Route::resource('logs', WorkLogController::class);
    Route::get('/api/smart-fill', [WorkLogController::class, 'smartFill'])->name('logs.smart-fill');
    Route::get('/api/fetch-day',  [WorkLogController::class, 'fetchDay'])->name('logs.fetch-day');

    // Profile
    Route::get('/profile',           [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar',   [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // DM Chat
    Route::get('/api/chat/users',               [ChatController::class, 'users'])->name('chat.users');
    Route::get('/api/chat/conversation/{user}',  [ChatController::class, 'conversation'])->name('chat.conversation');
    Route::post('/api/chat/send',               [ChatController::class, 'send'])->name('chat.send');
    Route::get('/api/chat/poll/{user}',          [ChatController::class, 'poll'])->name('chat.poll');
    Route::get('/api/chat/unread',              [ChatController::class, 'unreadCount'])->name('chat.unread');

    // Group Chat
    Route::get('/api/group/messages',  [ChatController::class, 'groupMessages'])->name('group.messages');
    Route::post('/api/group/send',     [ChatController::class, 'groupSend'])->name('group.send');
    Route::get('/api/group/poll',      [ChatController::class, 'groupPoll'])->name('group.poll');

    // Danger
    Route::delete('/api/delete-all-logs', function () {
        \App\Models\WorkLog::where('user_id', \Illuminate\Support\Facades\Auth::id())->delete();
        return response()->json(['success' => true, 'message' => 'All logs deleted.']);
    });
});