<?php

use App\Http\Controllers\CharacterController;
use App\Http\Controllers\SceneController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('projects', ProjectController::class);
    Route::resource('scenes', SceneController::class);
    Route::resource('characters', CharacterController::class);
    
    // Rotas adicionais para funcionalidades específicas
    Route::post('scenes/reorder', [SceneController::class, 'reorder'])->name('scenes.reorder');
    Route::post('scenes/{scene}/characters', [SceneController::class, 'addCharacter'])->name('scenes.add-character');
    Route::delete('scenes/{scene}/characters/{character}', [SceneController::class, 'removeCharacter'])->name('scenes.remove-character');
    Route::post('/scenes/create-act', [SceneController::class, 'createAct'])->name('scenes.create-act');
    Route::get('/scenes/{project}/export', [SceneController::class, 'export'])->name('scenes.export');

    // Rotas para importação de Excel
    Route::get('/excel', [ExcelController::class, 'index'])->name('excel.index');
    Route::post('/excel/import', [ExcelController::class, 'import'])->name('excel.import');
    Route::get('/excel/{excelData}', [ExcelController::class, 'show'])->name('excel.show');
    Route::delete('/excel/{excelData}', [ExcelController::class, 'destroy'])->name('excel.destroy');
    Route::post('/excel/export', [ExcelController::class, 'export'])->name('excel.export');
});

require __DIR__.'/auth.php';
