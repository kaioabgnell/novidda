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
use Illuminate\Support\Facades\Storage;

// Fallback para servir arquivos de storage/app/public sem depender de symlink
// (necessário em hospedagens compartilhadas sem SSH, onde storage:link não pode ser executado).
// Só é acionada quando o Apache não encontra um arquivo físico em public/storage/{path}
// (ver RewriteCond !-f no .htaccess) — se o symlink existir e funcionar, esta rota nunca é chamada.
Route::get('storage/{path}', function (string $path) {
    if (! Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return Storage::disk('public')->response($path);
})->where('path', '.*')->name('storage.local');

Route::get('/fix-storage-link', function () {
    $target = storage_path('app/public');
    $link   = public_path('storage');   // usa public_path para evitar path hardcoded

    $info = [
        'target_path'    => $target,
        'target_exists'  => file_exists($target),
        'link_path'      => $link,
        'link_is_link'   => is_link($link),
        'link_is_dir'    => is_dir($link),
        'link_is_file'   => is_file($link),
        'symlink_func'   => function_exists('symlink') ? 'disponível' : 'DESABILITADA',
    ];

    // Remove qualquer symlink ou pasta vazia errada
    if (is_link($link)) {
        unlink($link);
        $info['removeu'] = 'symlink antigo removido';
    } elseif (is_dir($link)) {
        @rmdir($link);
        $info['removeu'] = 'diretório vazio removido (se estava vazio)';
    }

    if (!function_exists('symlink')) {
        $info['status'] = '❌ symlink() desabilitado neste servidor — use a rota /storage/{path} como fallback (já ativa)';
        return response()->json($info);
    }

    if (@symlink($target, $link)) {
        $info['status']  = '✅ Symlink criado com sucesso!';
        $info['testUrl'] = url('storage/uploads/');
    } else {
        $info['status'] = '❌ symlink() falhou — use a rota /storage/{path} como fallback (já ativa)';
    }

    return response()->json($info);
});

// ---- Convidado (não autenticado) ----
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::get('/', fn () => view('landing'))->name('home');

// ---- Painel (autenticado + tenant) ----
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('home', DashboardController::class)->name('dashboard');

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
