@extends('layouts.app')
@section('title', 'Moderação de comentários')

@section('content')
@if ($comments->isEmpty())
    <div class="card">
        <div class="empty-state">
            <div class="icon"><i class="fa-solid fa-comments"></i></div>
            <h3>Nenhum comentário pendente</h3>
            <p>Comentários enviados pelos usuários aparecem aqui para aprovação.</p>
        </div>
    </div>
@else
    <div style="display:flex;flex-direction:column;gap:12px;">
        @foreach ($comments as $comment)
            <div class="card" style="padding:20px;">
                <div class="flex items-center justify-between" style="margin-bottom:12px;">
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <span style="
                            width:36px;height:36px;
                            display:inline-flex;align-items:center;justify-content:center;
                            background:var(--canvas-soft);
                            border-radius:var(--r-pill);
                            font-size:13px;font-weight:700;color:var(--primary);
                            flex-shrink:0;">
                            {{ mb_strtoupper(mb_substr($comment->author_name ?: 'A', 0, 1)) }}
                        </span>
                        <div>
                            <div style="font-size:14px;font-weight:700;color:var(--ink);">
                                {{ $comment->author_name ?: 'Anônimo' }}
                            </div>
                            <div style="font-size:12px;color:var(--mute);">
                                em <strong style="color:var(--body-txt);">"{{ $comment->changelog->title }}"</strong>
                                · {{ $comment->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-sm">
                        <form method="POST" action="{{ route('comments.decide', [$comment, 'approve']) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm" style="color:var(--positive);background:var(--positive-pale);">
                                <i class="fa-solid fa-check"></i> Aprovar
                            </button>
                        </form>
                        <form method="POST" action="{{ route('comments.decide', [$comment, 'reject']) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm btn-danger" style="background:var(--negative-pale);">
                                <i class="fa-solid fa-xmark"></i> Rejeitar
                            </button>
                        </form>
                    </div>
                </div>

                <p style="margin:0;font-size:14px;color:var(--ink);line-height:1.6;
                           background:var(--canvas);padding:12px 16px;border-radius:var(--r-md);">
                    {{ $comment->body }}
                </p>
            </div>
        @endforeach
    </div>

    <div class="mt">{{ $comments->links() }}</div>
@endif
@endsection
