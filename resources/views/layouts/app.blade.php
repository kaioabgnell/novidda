<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Painel') · Novidda</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/neu.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ url('img/favicon.png') }}">
    <script>/* evita flash de tema */(function(){var t=localStorage.getItem('novidda-theme')||((window.matchMedia&&matchMedia('(prefers-color-scheme: dark)').matches)?'dark':'light');if(t==='dark'){document.documentElement.style.background='#0A0F1E';document.body&&document.body.classList.add('dark');}})();</script>
    @stack('head')
</head>
<body>
<div class="app-shell">

    {{-- Sidebar --}}
    <aside class="sidebar">
        <div class="brand">
            <img src="{{ asset('img/Novidda_Logo.png') }}" alt="Novidda" class="sidebar-logo">
        </div>

        @php $r = Route::currentRouteName(); @endphp
        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}"       class="nav-link {{ str_starts_with($r,'dashboard')?'active':'' }}">
                <i class="fa-solid fa-chart-line"></i><span>Dashboard</span>
            </a>
            <a href="{{ route('changelogs.index') }}" class="nav-link {{ str_starts_with($r,'changelogs')?'active':'' }}">
                <i class="fa-solid fa-bullhorn"></i><span>Changelogs</span>
            </a>
            <a href="{{ route('categories.index') }}" class="nav-link {{ str_starts_with($r,'categories')?'active':'' }}">
                <i class="fa-solid fa-tags"></i><span>Categorias</span>
            </a>
            <a href="{{ route('roadmap.index') }}"    class="nav-link {{ str_starts_with($r,'roadmap')?'active':'' }}">
                <i class="fa-solid fa-road"></i><span>Roadmap</span>
            </a>
            <a href="{{ route('comments.index') }}"   class="nav-link {{ str_starts_with($r,'comments')?'active':'' }}">
                <i class="fa-solid fa-comments"></i><span>Moderação</span>
            </a>
            <a href="{{ route('embed.show') }}"       class="nav-link {{ str_starts_with($r,'embed')?'active':'' }}">
                <i class="fa-solid fa-code"></i><span>Embed</span>
            </a>
            <a href="{{ route('widget-settings.edit') }}" class="nav-link {{ str_starts_with($r,'widget-settings')?'active':'' }}">
                <i class="fa-solid fa-sliders"></i><span>Widget</span>
            </a>
        </nav>
    </aside>

    {{-- Área principal --}}
    <div class="main">
        <header class="topbar">
            <div class="title">@yield('title', 'Painel')</div>
            <div class="spacer"></div>

            <button class="btn-ghost btn-icon theme-toggle" data-theme-toggle title="Alternar tema">
                <i data-theme-icon class="fa-solid fa-moon"></i>
            </button>

            <div class="topbar-user">
                <span class="user-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <span class="user-name" style="font-size:14px;font-weight:600;color:var(--ink);">{{ auth()->user()->name }}</span>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn-ghost btn-icon" title="Sair" style="color:var(--mute);">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        </header>

        <main class="content">
            @if (session('status'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i> {{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</div>

<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
