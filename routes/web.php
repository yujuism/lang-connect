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
use App\Http\Controllers\PdfController;
use App\Http\Controllers\DevTestController;
use App\Http\Controllers\TranscriptController;
use App\Http\Controllers\FlashcardController;
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

    // PDF (collaborative document reader)
    Route::post('/sessions/{session}/pdf/upload', [PdfController::class, 'upload'])->name('sessions.pdf.upload');
    Route::get('/sessions/{session}/pdf', [PdfController::class, 'show'])->name('sessions.pdf.show');
    Route::post('/sessions/{session}/pdf/highlights', [PdfController::class, 'saveHighlights'])->name('sessions.pdf.highlights');
    Route::post('/sessions/{session}/pdf/broadcast', [PdfController::class, 'broadcast'])->name('sessions.pdf.broadcast');
    Route::post('/sessions/{session}/pdf/drawings', [PdfController::class, 'saveDrawings'])->name('sessions.pdf.drawings');
    Route::post('/sessions/{session}/pdf/drawings/broadcast', [PdfController::class, 'broadcastDrawings'])->name('sessions.pdf.drawings.broadcast');
    Route::delete('/sessions/{session}/pdf', [PdfController::class, 'destroy'])->name('sessions.pdf.destroy');

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

    // Transcription (AI Analytics - Phase 7)
    Route::post('/api/transcription/upload-chunk', [TranscriptController::class, 'uploadChunk'])->name('transcription.upload-chunk');
    Route::get('/sessions/{session}/transcript', [TranscriptController::class, 'show'])->name('sessions.transcript');
    Route::post('/sessions/{session}/transcribe', [TranscriptController::class, 'transcribe'])->name('sessions.transcribe');

    // Flashcards (AI Analytics - Phase 7)
    Route::get('/flashcards', [FlashcardController::class, 'index'])->name('flashcards.index');
    Route::get('/flashcards/review', [FlashcardController::class, 'review'])->name('flashcards.review');
    Route::get('/flashcards/next-card', [FlashcardController::class, 'nextCard'])->name('flashcards.next-card');
    Route::post('/flashcards/{flashcard}/answer', [FlashcardController::class, 'answer'])->name('flashcards.answer');
    Route::get('/flashcards/session/{sessionId}', [FlashcardController::class, 'fromSession'])->name('flashcards.from-session');
    Route::delete('/flashcards/{flashcard}', [FlashcardController::class, 'destroy'])->name('flashcards.destroy');
});

// Dev testing routes (only in local environment)
Route::middleware('auth')->prefix('dev')->group(function () {
    Route::get('/test-transcribe', [DevTestController::class, 'testPage'])->name('dev.test-transcribe');
    Route::post('/test-transcribe', [DevTestController::class, 'testTranscribe']);
});

require __DIR__.'/auth.php';
