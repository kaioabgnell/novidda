<?php

namespace App\Http\Controllers;

use App\Models\Changelog;
use App\Models\Comment;
use App\Models\WidgetEvent;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        // Métricas escopadas automaticamente pelo tenant (global scope).
        $opens = WidgetEvent::where('type', 'open')->count();
        $views = WidgetEvent::where('type', 'view')->count();
        $reactions = WidgetEvent::where('type', 'reaction')->count();

        $accountId = $request->user()->account_id;
        $pendingComments = Comment::whereHas('changelog', fn ($q) => $q->where('account_id', $accountId))
            ->where('status', 'pending')->count();

        $published = Changelog::where('status', 'published')->count();

        $topReleases = Changelog::live()
            ->withCount(['reactions', 'comments'])
            ->orderByDesc('reactions_count')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'opens', 'views', 'reactions', 'pendingComments', 'published', 'topReleases'
        ));
    }
}
