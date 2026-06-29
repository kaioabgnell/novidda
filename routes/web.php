<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\CommentModerationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmbedController;
use App\Http\Controllers\RoadmapController;
use App\Http\Controllers\SegmentationController;
use App\Http\Controllers\WidgetSettingController;
use Illuminate\Support\Facades\Route;

// ---- Convidado (não autenticado) ----
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

// ---- Painel (autenticado + tenant) ----
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('changelogs', ChangelogController::class)->except('show');
    Route::post('changelogs/{changelog}/publish', [ChangelogController::class, 'publish'])->name('changelogs.publish');
    Route::post('changelogs/{changelog}/archive', [ChangelogController::class, 'archive'])->name('changelogs.archive');

    Route::resource('categories', CategoryController::class)->except('show');

    Route::resource('roadmap', RoadmapController::class)->except('show');

    // Moderação de comentários
    Route::get('comments', [CommentModerationController::class, 'index'])->name('comments.index');
    Route::patch('comments/{comment}/{decision}', [CommentModerationController::class, 'decide'])
        ->whereIn('decision', ['approve', 'reject'])->name('comments.decide');

    Route::get('embed', [EmbedController::class, 'show'])->name('embed.show');

    Route::get('widget-settings', [WidgetSettingController::class, 'edit'])->name('widget-settings.edit');
    Route::put('widget-settings', [WidgetSettingController::class, 'update'])->name('widget-settings.update');

    // ---- API de segmentação (retornam JSON) ----
    Route::get('api/admin/attributes/discovery', [SegmentationController::class, 'discovery'])
        ->name('segmentation.discovery');
    Route::post('api/admin/changelogs/{changelog}/estimate-reach', [SegmentationController::class, 'estimateReach'])
        ->name('segmentation.estimate-reach');
    Route::post('api/admin/changelogs/{changelog}/preview-audience', [SegmentationController::class, 'previewAudience'])
        ->name('segmentation.preview-audience');
});
