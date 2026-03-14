<?php

use App\Http\Controllers\CharacterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SceneController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware(['auth', 'throttle:100,1'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('projects', ProjectController::class)->except(['show']);
    Route::resource('scenes', SceneController::class);
    Route::resource('episodes', EpisodeController::class);
    Route::resource('characters', CharacterController::class);
    Route::delete('characters/{character}/scenes/{scene}/dialogue', [CharacterController::class, 'removeDialogue'])->name('characters.remove-dialogue');
    Route::post('characters/{character}/scenes/{scene}/dialogue/restore', [CharacterController::class, 'restoreDialogue'])->name('characters.restore-dialogue');

    // Rotas adicionais para funcionalidades específicas
    Route::post('scenes/{scene}/characters', [SceneController::class, 'addCharacter'])->name('scenes.add-character');
    Route::delete('scenes/{scene}/characters/{character}', [SceneController::class, 'removeCharacter'])->name('scenes.remove-character');
    Route::post('/scenes/create-act', [SceneController::class, 'createAct'])->name('scenes.create-act');
    Route::post('/scenes/update-act-title', [SceneController::class, 'updateActTitle'])->name('scenes.update-act-title');
    Route::post('/scenes/reorder', [SceneController::class, 'reorder'])->name('scenes.reorder');
    Route::get('/scenes/{project}/export', [SceneController::class, 'export'])->name('scenes.export');

    Route::get('/projects/{project}/files', [ProjectController::class, 'filesIndex'])->name('projects.files.index');
    Route::post('/projects/{project}/files', [ProjectController::class, 'filesStore'])
        ->middleware('throttle:20,1')
        ->name('projects.files.store');
    Route::get('/projects/{project}/files/{storedName}', [ProjectController::class, 'filesDownload'])->name('projects.files.download');
    Route::delete('/projects/{project}/files/{storedName}', [ProjectController::class, 'filesDestroy'])->name('projects.files.destroy');
    Route::post('/projects/{project}/documents', [ProjectController::class, 'documentsStore'])->name('projects.documents.store');
    Route::put('/projects/{project}/documents/{storedName}', [ProjectController::class, 'documentsUpdate'])->name('projects.documents.update');

    Route::get('/projects/{project}/share', [ProjectController::class, 'share'])->name('projects.share');
    Route::post('/projects/{project}/invites', [ProjectController::class, 'invitesStore'])->name('projects.invites.store');
    Route::delete('/projects/{project}/invites/{invitation}', [ProjectController::class, 'invitesDestroy'])->name('projects.invites.destroy');
    Route::patch('/projects/{project}/members/{user}', [ProjectController::class, 'membersUpdate'])->name('projects.members.update');
    Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'membersDestroy'])->name('projects.members.destroy');
    Route::get('/invites/{token}', [ProjectController::class, 'acceptInvite'])->name('invites.accept');
    Route::post('/invites/{invitation}/accept', [ProjectController::class, 'acceptInviteById'])->name('invites.accept.by-id');
    Route::post('/invites/{invitation}/reject', [ProjectController::class, 'rejectInviteById'])->name('invites.reject.by-id');

    // Rotas para importação de Excel com rate limiting específico
    Route::get('/excel', [ExcelController::class, 'index'])->name('excel.index');
    Route::post('/excel/import', [ExcelController::class, 'import'])
        ->middleware('throttle:10,1') // 10 uploads por minuto
        ->name('excel.import');
    Route::get('/excel/{excelData}', [ExcelController::class, 'show'])->name('excel.show');
    Route::delete('/excel/{excelData}', [ExcelController::class, 'destroy'])->name('excel.destroy');
    Route::post('/excel/export', [ExcelController::class, 'export'])
        ->middleware('throttle:20,1') // 20 exports por minuto
        ->name('excel.export');
});

require __DIR__.'/auth.php';
