<?php

use App\Http\Controllers\Quiz\AdminQuizController;
use App\Http\Controllers\Quiz\DisplayQuizController;
use App\Http\Controllers\Quiz\PlayerQuizController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('quizzes/admin', [AdminQuizController::class, 'index'])->name('quizzes.admin.index');
    Route::get('quizzes/admin/create', [AdminQuizController::class, 'create'])->name('quizzes.admin.create');
    Route::post('quizzes/admin', [AdminQuizController::class, 'store'])->name('quizzes.admin.store');
    Route::get('quizzes/admin/{quiz}', [AdminQuizController::class, 'edit'])->name('quizzes.admin.edit');
    Route::put('quizzes/admin/{quiz}', [AdminQuizController::class, 'update'])->name('quizzes.admin.update');
    Route::delete('quizzes/admin/{quiz}', [AdminQuizController::class, 'destroy'])->name('quizzes.admin.destroy');
    Route::post('quizzes/admin/{quiz}/sessions', [AdminQuizController::class, 'startSession'])->name('quizzes.admin.sessions.start');
    Route::get('quizzes/admin/sessions/{session}', [AdminQuizController::class, 'showSession'])->name('quizzes.admin.sessions.show');
    Route::post('quizzes/admin/sessions/{session}/advance', [AdminQuizController::class, 'advance'])->name('quizzes.admin.sessions.advance');
});

Route::get('play', [PlayerQuizController::class, 'joinPage'])->name('quizzes.player.join-page');
Route::post('play/join', [PlayerQuizController::class, 'join'])->name('quizzes.player.join');
Route::get('play/{session}', [PlayerQuizController::class, 'play'])->name('quizzes.player.play');
Route::post('play/{session}/answer', [PlayerQuizController::class, 'submitAnswer'])->name('quizzes.player.answer');

Route::get('display/{session}', [DisplayQuizController::class, 'show'])->name('quizzes.display.show');
