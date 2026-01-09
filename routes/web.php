<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LearningRequestController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\CanvasController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/members', [HomeController::class, 'members'])->name('members');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/languages', [ProfileController::class, 'updateLanguages'])->name('profile.update-languages');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Learning Requests
    Route::get('/learning-requests', [LearningRequestController::class, 'index'])->name('learning-requests.index');
    Route::get('/learning-requests/create', [LearningRequestController::class, 'create'])->name('learning-requests.create');
    Route::post('/learning-requests', [LearningRequestController::class, 'store'])->name('learning-requests.store');
    Route::get('/learning-requests/{learningRequest}', [LearningRequestController::class, 'show'])->name('learning-requests.show');
    Route::post('/learning-requests/{learningRequest}/cancel', [LearningRequestController::class, 'cancel'])->name('learning-requests.cancel');
    Route::get('/browse-requests', [LearningRequestController::class, 'browse'])->name('learning-requests.browse');
    Route::post('/learning-requests/{learningRequest}/accept', [LearningRequestController::class, 'accept'])->name('learning-requests.accept');

    // Sessions
    Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::post('/learning-requests/{learningRequest}/start-session', [SessionController::class, 'startFromRequest'])->name('sessions.start');
    Route::get('/sessions/{session}', [SessionController::class, 'show'])->name('sessions.show');
    Route::post('/sessions/{session}/save-notes', [SessionController::class, 'saveNotes'])->name('sessions.save-notes');
    Route::post('/sessions/{session}/complete', [SessionController::class, 'complete'])->name('sessions.complete');
    Route::get('/sessions/{session}/review', [SessionController::class, 'review'])->name('sessions.review');

    // Reviews
    Route::post('/sessions/{session}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Canvas (collaborative whiteboard)
    Route::post('/sessions/{session}/canvas', [CanvasController::class, 'save'])->name('sessions.canvas.save');
    Route::get('/sessions/{session}/canvas', [CanvasController::class, 'load'])->name('sessions.canvas.load');
    Route::post('/sessions/{session}/canvas/broadcast', [CanvasController::class, 'broadcast'])->name('sessions.canvas.broadcast');

    // Achievements
    Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.index');

    // Leaderboard
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');

    // Levels
    Route::get('/levels', [LevelController::class, 'index'])->name('levels.index');

    // Messages
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user}/send', [MessageController::class, 'send'])->name('messages.send');
    Route::post('/messages/{user}/mark-read', [MessageController::class, 'markRead'])->name('messages.mark-read');
    Route::get('/messages/{user}/fetch', [MessageController::class, 'fetch'])->name('messages.fetch');

    // Calls (Voice/Video)
    Route::get('/call/{user}/window', [CallController::class, 'window'])->name('call.window');
    Route::post('/call/{user}/initiate', [CallController::class, 'initiate'])->name('call.initiate');
    Route::post('/call/{call}/accept', [CallController::class, 'accept'])->name('call.accept');
    Route::post('/call/{call}/reject', [CallController::class, 'reject'])->name('call.reject');
    Route::post('/call/{call}/end', [CallController::class, 'end'])->name('call.end');
    Route::post('/call/{call}/signal', [CallController::class, 'signal'])->name('call.signal');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/fetch', [NotificationController::class, 'fetch'])->name('notifications.fetch');
});

require __DIR__.'/auth.php';
