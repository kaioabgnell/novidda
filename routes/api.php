<?php

use App\Http\Controllers\Api\WidgetApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ---- API pública do widget (resolve conta pelo token) ----
Route::prefix('v1/widget/{token}')
    ->middleware(['widget.token', 'throttle:widget'])
    ->group(function () {
        Route::get('config', [WidgetApiController::class, 'config']);
        Route::get('unread-count', [WidgetApiController::class, 'unreadCount']);
        Route::match(['get', 'post'], 'feed', [WidgetApiController::class, 'feed']);
        Route::post('read', [WidgetApiController::class, 'read']);
        Route::post('reaction', [WidgetApiController::class, 'reaction']);
        Route::post('comment', [WidgetApiController::class, 'comment']);
        Route::post('changelog-feedback', [WidgetApiController::class, 'changelogFeedback']);
        Route::get('roadmap', [WidgetApiController::class, 'roadmapFeed']);
        Route::post('roadmap-feedback', [WidgetApiController::class, 'roadmapFeedback']);
        Route::post('roadmap-comment', [WidgetApiController::class, 'roadmapComment']);
        Route::post('roadmap-vote', [WidgetApiController::class, 'roadmapVote']);
        Route::get('contextual', [WidgetApiController::class, 'contextual']);
        Route::post('contextual/event', [WidgetApiController::class, 'contextualEvent'])
            ->middleware('throttle:60,1');
    });
