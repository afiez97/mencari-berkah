<?php

use App\Http\Controllers\BarisController;
use App\Http\Controllers\HafalanController;
use App\Http\Controllers\Api\LetterController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\LetterAudioController;
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
Route::get('/pelan', [HafalanController::class, 'plan'])->name('plan');
Route::get('/pelan.ics', [HafalanController::class, 'planCalendar'])->name('plan.ics');
Route::get('/baris', [BarisController::class, 'index'])->name('baris');
Route::get('/kredit', [HafalanController::class, 'credits'])->name('credits');
// Senarai fail audio statik untuk disimpan offline oleh service worker.
Route::get('/audio/senarai.json', fn () => response()->json(\App\Support\OpenAudio::staticFiles()))->name('audio.list');
Route::get('/baris/latih', [BarisController::class, 'practice'])->name('baris.latih');

Route::get('/tts', [TtsController::class, 'speak'])->name('tts');

Route::prefix('api')->group(function () {
    Route::get('/letters', [LetterController::class, 'index']);
    Route::get('/letters/{id}', [LetterController::class, 'show']);
    Route::post('/quiz/save', [LetterController::class, 'saveSession']);
    Route::post('/letters/{letter}/audio', [LetterAudioController::class, 'store'])->middleware('throttle:30,1');
    Route::delete('/letters/{letter}/audio', [LetterAudioController::class, 'destroy'])->middleware('throttle:30,1');

    Route::post('/sync', [SyncController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/sync/{code}', [SyncController::class, 'show'])->middleware('throttle:60,1');
    Route::put('/sync/{code}', [SyncController::class, 'update'])->middleware('throttle:60,1');
});
