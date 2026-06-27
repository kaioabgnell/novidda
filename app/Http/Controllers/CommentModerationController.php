<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Support\Tenant;
use App\Support\WidgetCache;
use Illuminate\Http\Request;

class CommentModerationController extends Controller
{
    public function index()
    {
        $comments = Comment::with('changelog')
            ->whereHas('changelog') // garante escopo de tenant via global scope do changelog
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('comments.index', compact('comments'));
    }

    public function decide(Request $request, Comment $comment, string $decision)
    {
        // Garante que o comentário pertence a um changelog da conta atual.
        abort_unless($comment->changelog && $comment->changelog->account_id === Tenant::id(), 404);

        $comment->update(['status' => $decision === 'approve' ? 'approved' : 'rejected']);
        WidgetCache::bump(Tenant::id());

        return back()->with('status', $decision === 'approve' ? 'Comentário aprovado.' : 'Comentário rejeitado.');
    }
}
