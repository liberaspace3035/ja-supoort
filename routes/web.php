<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('surveys.create');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/surveys/create', [SurveyController::class, 'create'])->name('surveys.create');
    Route::post('/surveys', [SurveyController::class, 'store'])->name('surveys.store');
    Route::get('/surveys/images/{date}/{filename}', [SurveyController::class, 'showImage'])
        ->where([
            'date' => '\d{4}-\d{2}-\d{2}',
            'filename' => '[a-f0-9\-]+\.jpg',
        ])
        ->name('surveys.images.show');
    Route::get('/surveys/export', [SurveyController::class, 'downloadExcel'])->name('surveys.export');
});

require __DIR__ . '/auth.php';
