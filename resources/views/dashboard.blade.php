@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

{{-- Métricas --}}
<div class="grid grid-metrics mb">
    <div class="metric-card">
        <div class="metric-icon"><i class="fa-solid fa-arrow-pointer"></i></div>
        <div class="value">{{ number_format($opens) }}</div>
        <div class="label">Aberturas do widget</div>
    </div>
    <div class="metric-card">
        <div class="metric-icon"><i class="fa-solid fa-eye"></i></div>
        <div class="value">{{ number_format($views) }}</div>
        <div class="label">Visualizações</div>
    </div>
    <div class="metric-card">
        <div class="metric-icon"><i class="fa-solid fa-heart"></i></div>
        <div class="value">{{ number_format($reactions) }}</div>
        <div class="label">Reações</div>
    </div>
    <div class="metric-card">
        <div class="metric-icon" style="background:var(--warning-pale);color:var(--warning);">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div class="value">{{ number_format($pendingComments) }}</div>
        <div class="label">Comentários pendentes</div>
    </div>
    <div class="metric-card">
        <div class="metric-icon" style="background:var(--positive-pale);color:var(--positive-deep);">
            <i class="fa-solid fa-bullhorn"></i>
        </div>
        <div class="value">{{ number_format($published) }}</div>
        <div class="label">Releases publicados</div>
    </div>
</div>

{{-- Top releases --}}
<div class="card">
    <div class="flex items-center justify-between" style="margin-bottom:20px;">
        <h3 style="margin:0;font-size:18px;">Top releases por engajamento</h3>
        <a href="{{ route('changelogs.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Novo
        </a>
    </div>

    @forelse ($topReleases as $release)
        <div class="flex items-center justify-between" style="padding:14px 0;border-bottom:1px solid var(--canvas);">
            <span style="font-size:14px;font-weight:600;color:var(--ink);">{{ $release->title }}</span>
            <span style="font-size:13px;color:var(--mute);display:flex;gap:16px;align-items:center;">
                <span><i class="fa-solid fa-heart" style="color:var(--primary);margin-right:4px;"></i>{{ $release->reactions_count }}</span>
                <span><i class="fa-solid fa-comment" style="color:var(--primary);margin-right:4px;"></i>{{ $release->comments_count }}</span>
            </span>
        </div>
    @empty
        <div class="empty-state" style="padding:48px 20px;">
            <div class="icon"><i class="fa-solid fa-seedling"></i></div>
            <h3>Sem dados de engajamento ainda</h3>
            <p>Publique um changelog e instale o widget para começar a ver métricas.</p>
            <a href="{{ route('changelogs.create') }}" class="btn btn-primary mt">
                <i class="fa-solid fa-plus"></i> Criar changelog
            </a>
        </div>
    @endforelse
</div>

@endsection
