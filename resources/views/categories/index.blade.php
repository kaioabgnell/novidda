@extends('layouts.app')
@section('title', 'Categorias')

@section('content')
<div class="flex items-center justify-between mb">
    <p style="margin:0;font-size:14px;color:var(--mute);">Organize seus changelogs por categoria.</p>
    <a href="{{ route('categories.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Nova categoria
    </a>
</div>

@if ($categories->isEmpty())
    <div class="card">
        <div class="empty-state">
            <div class="icon"><i class="fa-solid fa-tags"></i></div>
            <h3>Nenhuma categoria</h3>
            <p>Crie categorias como "Novidade", "Correção", "API".</p>
            <a href="{{ route('categories.create') }}" class="btn btn-primary mt">
                <i class="fa-solid fa-plus"></i> Criar categoria
            </a>
        </div>
    </div>
@else
    <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr));">
        @foreach ($categories as $cat)
            <div class="card flex items-center justify-between" style="padding:20px;">
                <div class="flex items-center gap-sm">
                    <span style="
                        width:40px;height:40px;border-radius:var(--r-md);
                        display:inline-flex;align-items:center;justify-content:center;
                        background:{{ $cat->color ? $cat->color.'22' : 'var(--primary-pale)' }};
                        color:{{ $cat->color ?: 'var(--primary)' }};
                        font-size:15px;flex-shrink:0;">
                        <i class="{{ $cat->icon ?: 'fa-solid fa-tag' }}"></i>
                    </span>
                    <div>
                        <div style="font-size:14px;font-weight:700;color:var(--ink);">{{ $cat->name }}</div>
                        <div style="font-size:12px;color:var(--mute);margin-top:2px;">{{ $cat->changelogs_count }} changelog(s)</div>
                    </div>
                </div>
                <div class="flex gap-sm">
                    <a href="{{ route('categories.edit', $cat) }}" class="btn btn-sm btn-icon" title="Editar">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    <form method="POST" action="{{ route('categories.destroy', $cat) }}"
                          data-confirm="Remover esta categoria?">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-icon btn-danger" title="Remover">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
