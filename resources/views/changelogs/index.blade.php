@extends('layouts.app')
@section('title', 'Changelogs')

@section('content')
@php $hasFilters = $filters['search'] !== '' || $filters['type'] !== '' || $filters['status'] !== '' || $filters['from'] !== '' || $filters['to'] !== ''; @endphp

<div class="flex items-center justify-between mb">
    <p style="margin:0;font-size:14px;color:var(--mute);">Gerencie seus anúncios e releases.</p>
    <a href="{{ route('changelogs.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Novo changelog
    </a>
</div>

@if ($changelogs->isEmpty() && ! $hasFilters)
    <div class="card">
        <div class="empty-state">
            <div class="icon"><i class="fa-solid fa-bullhorn"></i></div>
            <h3>Nenhum changelog ainda</h3>
            <p>Crie seu primeiro anúncio para começar a comunicar novidades.</p>
            <a href="{{ route('changelogs.create') }}" class="btn btn-primary mt">
                <i class="fa-solid fa-plus"></i> Criar changelog
            </a>
        </div>
    </div>
@else
    {{-- Filtros --}}
    <div class="card mb">
        <form method="GET" action="{{ route('changelogs.index') }}" class="filter-bar">
            <div class="filter-field filter-search">
                <label for="search">Pesquisar por título</label>
                <input type="text" class="input" id="search" name="search"
                       value="{{ $filters['search'] }}" placeholder="Digite o título…">
            </div>

            <div class="filter-field">
                <label for="type">Tipo</label>
                <select class="select" id="type" name="type">
                    <option value="">Todos</option>
                    @foreach (['feature'=>'Feature','hotfix'=>'Hotfix','improvement'=>'Melhoria','announcement'=>'Anúncio'] as $k => $lbl)
                        <option value="{{ $k }}" @selected($filters['type'] === $k)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label for="status">Status</label>
                <select class="select" id="status" name="status">
                    <option value="">Todos</option>
                    @foreach (['draft'=>'Rascunho','published'=>'Publicado','archived'=>'Arquivado'] as $k => $lbl)
                        <option value="{{ $k }}" @selected($filters['status'] === $k)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label for="from">Publicação de</label>
                <input type="date" class="input" id="from" name="from" value="{{ $filters['from'] }}">
            </div>

            <div class="filter-field">
                <label for="to">Publicação até</label>
                <input type="date" class="input" id="to" name="to" value="{{ $filters['to'] }}">
            </div>

            <div class="filter-field">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass"></i> Filtrar
                </button>
            </div>

            @if ($hasFilters)
                <div class="filter-field">
                    <a href="{{ route('changelogs.index') }}" class="btn" title="Limpar filtros">
                        <i class="fa-solid fa-xmark"></i> Limpar
                    </a>
                </div>
            @endif
        </form>
    </div>

    @if ($changelogs->isEmpty())
        <div class="card">
            <div class="empty-state">
                <div class="icon"><i class="fa-solid fa-filter-circle-xmark"></i></div>
                <h3>Nenhum resultado</h3>
                <p>Nenhum changelog corresponde aos filtros selecionados.</p>
                <a href="{{ route('changelogs.index') }}" class="btn mt">
                    <i class="fa-solid fa-xmark"></i> Limpar filtros
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
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Engajamento</th>
                            <th>Banner</th>
                            <th>Publicação</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($changelogs as $c)
                        <tr>
                            <td style="font-weight:600;color:var(--ink);">{{ $c->title }}</td>
                            <td>
                                <span style="font-size:12px;font-weight:600;padding:3px 10px;border-radius:999px;background:var(--canvas-soft);color:var(--body-txt);">
                                    {{ ['feature'=>'Feature','hotfix'=>'Hotfix','improvement'=>'Melhoria','announcement'=>'Anúncio'][$c->type] }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $c->status }}">
                                    {{ ['draft'=>'Rascunho','published'=>'Publicado','archived'=>'Arquivado'][$c->status] }}
                                </span>
                            </td>
                            <td style="font-size:13px;color:var(--mute);">
                                <i class="fa-solid fa-heart" style="color:var(--primary);margin-right:3px;"></i>{{ $c->reactions_count }}
                                &nbsp;
                                <i class="fa-solid fa-comment" style="color:var(--primary);margin-right:3px;"></i>{{ $c->comments_count }}
                            </td>
                            <td>
                                @if ($c->contextualBanner && $c->contextualBanner->enabled && $c->contextualBanner->isActive())
                                    <span title="Banner contextual ativo" style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:3px 8px;border-radius:99px;background:rgba(123,97,255,.12);color:var(--primary);">
                                        <i class="fa-solid fa-rectangle-ad"></i> Ativo
                                    </span>
                                @elseif ($c->contextualBanner)
                                    <span style="font-size:11px;color:var(--mute);">—</span>
                                @else
                                    <span style="font-size:11px;color:var(--mute);">—</span>
                                @endif
                            </td>
                            <td style="font-size:13px;color:var(--mute);">
                                {{ $c->published_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td>
                                <div class="flex gap-sm" style="justify-content:flex-end;">
                                    <a href="{{ route('changelogs.edit', $c) }}" class="btn btn-sm btn-icon" title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    @if ($c->status !== 'published')
                                        <form method="POST" action="{{ route('changelogs.publish', $c) }}">@csrf
                                            <button class="btn btn-sm btn-icon" title="Publicar" style="color:var(--positive);">
                                                <i class="fa-solid fa-paper-plane"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if ($c->status !== 'archived')
                                        <form method="POST" action="{{ route('changelogs.archive', $c) }}">@csrf
                                            <button class="btn btn-sm btn-icon" title="Arquivar" style="color:var(--mute);">
                                                <i class="fa-solid fa-box-archive"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('changelogs.destroy', $c) }}"
                                          data-confirm="Remover este changelog?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-icon btn-danger" title="Remover">
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

        <div class="mt">{{ $changelogs->links() }}</div>
    @endif
@endif
@endsection
