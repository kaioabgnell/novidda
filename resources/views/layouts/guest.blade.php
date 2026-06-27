<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Entrar') · Novidda</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/neu.css') }}">
    <script>/* evita flash */(function(){var t=localStorage.getItem('novidda-theme')||((window.matchMedia&&matchMedia('(prefers-color-scheme: dark)').matches)?'dark':'light');if(t==='dark')document.body&&document.body.classList.add('dark');document.documentElement.setAttribute('data-theme',t);})();</script>
</head>
<body>
<div class="auth-split">

    {{-- Lado esquerdo — hero de boas-vindas --}}
    <div class="auth-hero">
        <div class="auth-hero-content">
            <img src="{{ asset('img/Novidda_Logo.png') }}" alt="Novidda" class="auth-hero-logo">

            <h1 class="auth-hero-title">Comunique novidades<br>com impacto.</h1>

            <p class="auth-hero-body">
                Release notes e changelogs bonitos, diretamente no seu produto.
                Widget embeddable, leve e pronto em minutos.
            </p>

            <div class="auth-feature-list">
                <div class="auth-feature">
                    <span class="auth-feature-icon"><i class="fa-solid fa-bolt"></i></span>
                    <span>Widget assíncrono de menos de 5 KB</span>
                </div>
                <div class="auth-feature">
                    <span class="auth-feature-icon"><i class="fa-solid fa-shield-halved"></i></span>
                    <span>Multi-tenant com isolamento total de dados</span>
                </div>
                <div class="auth-feature">
                    <span class="auth-feature-icon"><i class="fa-solid fa-calendar-check"></i></span>
                    <span>Agendamento de publicação sem workers</span>
                </div>
                <div class="auth-feature">
                    <span class="auth-feature-icon"><i class="fa-solid fa-code"></i></span>
                    <span>Uma linha de código para instalar</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Lado direito — formulário --}}
    <div class="auth-form-panel">
        <div class="auth-form-inner">
            @if ($errors->any())
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    {{ $errors->first() }}
                </div>
            @endif
            @if (session('status'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

</div>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
