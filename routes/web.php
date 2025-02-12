<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScormController;
use App\Http\Controllers\ScormContentController;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect()->route('scorm.index');
});

// SCORM management routes
Route::get('test', [ScormController::class, 'index'])->name('scorm.index');
Route::get('scorm/create', [ScormController::class, 'create'])->name('scorm.create');
Route::post('store', [ScormController::class, 'store'])->name('scorm.upload');

// Other SCORM routes
Route::get('scorm/{scorm}', [ScormController::class, 'show'])->name('scorm.show');
Route::get('scorm/{scorm}/sco/{sco}/launch', [ScormController::class, 'launch'])->name('scorm.launch');
Route::post('scorm/{scorm}/sco/{sco}/track', [ScormController::class, 'track'])->name('scorm.track');
Route::get('scorm/{scorm}/attempt-data', [ScormController::class, 'getAttemptData'])->name('scorm.attempt-data');
Route::get('/scorm/attempt/{attempt}/progress', [ScormController::class, 'progress'])->name('scorm.progress');
Route::delete('scorm/{scorm}', [ScormController::class, 'destroy'])->name('scorm.destroy');

// SCORM content serving - this must be the last route to avoid conflicts
Route::get('scorm/content/{path}', [ScormContentController::class, 'serve'])
    ->where('path', '.*')
    ->name('scorm.content');
