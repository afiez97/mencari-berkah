<?php

use App\Http\Controllers\HafalanController;
use App\Http\Controllers\Api\LetterController;
use App\Http\Controllers\TtsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HafalanController::class, 'home'])->name('home');
Route::get('/belajar', [HafalanController::class, 'learn'])->name('learn');
Route::get('/belajar/{id}', [HafalanController::class, 'show'])->name('learn.show');
Route::get('/kuiz', [HafalanController::class, 'quiz'])->name('quiz');
Route::get('/kuiz/main', [HafalanController::class, 'quizPlay'])->name('quiz.play');
Route::get('/keputusan', [HafalanController::class, 'result'])->name('result');
Route::get('/statistik', [HafalanController::class, 'stats'])->name('stats');
Route::get('/hafalan', [HafalanController::class, 'hafalan'])->name('hafalan');
Route::get('/hafalan/sesi', [HafalanController::class, 'hafalanSesi'])->name('hafalan.sesi');

Route::get('/tts', [TtsController::class, 'speak'])->name('tts');

Route::prefix('api')->group(function () {
    Route::get('/letters', [LetterController::class, 'index']);
    Route::get('/letters/{id}', [LetterController::class, 'show']);
    Route::post('/quiz/save', [LetterController::class, 'saveSession']);
});
