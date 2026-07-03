@extends('layouts.app')
@section('title', 'Roadmap')

@section('content')
<div class="flex items-center justify-between mb">
    <p style="margin:0;font-size:14px;color:var(--mute);">Gerencie os itens de roadmap visíveis no widget.</p>
    <a href="{{ route('roadmap.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Novo item
    </a>
</div>

@if ($items->isEmpty())
    <div class="card">
        <div class="empty-state">
            <div class="icon"><i class="fa-solid fa-road"></i></div>
            <h3>Nenhum item de roadmap</h3>
            <p>Crie itens para comunicar o que está sendo planejado e desenvolvido.</p>
            <a href="{{ route('roadmap.create') }}" class="btn btn-primary mt">
                <i class="fa-solid fa-plus"></i> Criar item
            </a>
        </div>
    </div>
@else
    <div class="card" style="padding:0;overflow:hidden;">
        <div class="table-wrap">
            <table class="neu">
                <thead style="background:var(--card-bg);">
                    <tr>
                        <th style="padding:16px 16px 12px;">Título</th>
                        <th>Andamento</th>
                        <th>Engajamento</th>
                        <th>Publicação</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td style="font-weight:600;color:var(--ink);">{{ $item->title }}</td>
                        <td>
                            @if ($item->status === 'analyzing')
                                <span style="font-size:12px;font-weight:700;padding:3px 10px;border-radius:999px;background:#f9731622;color:#f97316;">
                                    Em análise
                                </span>
                            @elseif ($item->status === 'developing')
                                <span style="font-size:12px;font-weight:700;padding:3px 10px;border-radius:999px;background:#3b82f622;color:#3b82f6;">
                                    Em desenvolvimento
                                </span>
                            @else
                                <span style="font-size:12px;font-weight:700;padding:3px 10px;border-radius:999px;background:#8b5cf622;color:#8b5cf6;">
                                    Planejado
                                </span>
                            @endif
                        </td>
                        <td style="font-size:13px;color:var(--mute);white-space:nowrap;">
                            <i class="fa-solid fa-face-smile" style="color:var(--primary);margin-right:3px;"></i>{{ $item->feedbacks_count }}
                            &nbsp;
                            <i class="fa-solid fa-comment" style="color:var(--primary);margin-right:3px;"></i>{{ $item->comments_count }}
                            @if ($item->voting_enabled)
                                &nbsp;
                                <i class="fa-solid fa-thumbs-up" style="color:#10b981;margin-right:3px;"></i>{{ $item->votes_up_count }}
                                &nbsp;
                                <i class="fa-solid fa-thumbs-down" style="color:#ef4444;margin-right:3px;"></i>{{ $item->votes_down_count }}
                            @endif
                        </td>
                        <td style="font-size:13px;color:var(--mute);">
                            {{ $item->published_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td>
                            <div class="flex gap-sm" style="justify-content:flex-end;">
                                <a href="{{ route('roadmap.edit', $item) }}" class="btn btn-sm">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form method="POST" action="{{ route('roadmap.destroy', $item) }}"
                                      onsubmit="return confirm('Remover este item?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt">{{ $items->links() }}</div>
@endif
@endsection
