@extends('layouts.guest')
@section('title', 'Entrar')

@section('content')
<h2 class="auth-form-title">Boas-vindas de volta</h2>
<p class="auth-form-subtitle">Entre com sua conta Novidda.</p>

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="field">
        <label for="email">E-mail</label>
        <input class="input" id="email" type="email" name="email"
               value="{{ old('email') }}" required autofocus autocomplete="email">
    </div>

    <div class="field">
        <label for="password">Senha</label>
        <div style="position:relative;">
            <input class="input" id="password" type="password" name="password"
                   required autocomplete="current-password" style="padding-right:44px;">
            <button type="button" id="togglePassword"
                    aria-label="Mostrar senha" aria-pressed="false"
                    style="position:absolute;top:0;right:0;height:100%;width:44px;display:flex;
                           align-items:center;justify-content:center;background:none;border:none;
                           padding:0;cursor:pointer;color:var(--mute);">
                <i class="fa-solid fa-eye" id="togglePasswordIcon"></i>
            </button>
        </div>
    </div>

    <div class="field" style="display:flex;align-items:center;gap:8px;margin-bottom:28px;">
        <input type="checkbox" name="remember" id="remember" value="1"
               style="width:16px;height:16px;accent-color:var(--primary);cursor:pointer;">
        <label for="remember" style="margin:0;font-size:14px;font-weight:500;color:var(--mute);cursor:pointer;">
            Lembrar-me por 30 dias
        </label>
    </div>

    <button type="submit" class="btn btn-primary w-full" style="justify-content:center;padding:13px 24px;font-size:15px;">
        Entrar
    </button>
</form>

<p style="text-align:center;margin-top:28px;font-size:14px;color:var(--mute);">
    Não tem conta? <a href="{{ route('register') }}" style="font-weight:600;">Criar conta grátis</a>
</p>

<script>
    (function () {
        var btn   = document.getElementById('togglePassword');
        var input = document.getElementById('password');
        var icon  = document.getElementById('togglePasswordIcon');
        if (!btn || !input || !icon) return;

        btn.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !show);
            icon.classList.toggle('fa-eye-slash', show);
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            btn.setAttribute('aria-label', show ? 'Ocultar senha' : 'Mostrar senha');
            input.focus();
        });
    })();
</script>
@endsection
