@extends('layouts.guest')
@section('title', 'Criar conta')

@section('content')
<h2 class="auth-form-title">Crie sua conta</h2>
<p class="auth-form-subtitle">Comece a comunicar novidades em minutos.</p>

<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="field">
        <label for="company">Empresa / Produto</label>
        <input class="input" id="company" type="text" name="company"
               value="{{ old('company') }}" required autofocus placeholder="Ex.: Minha Startup">
    </div>

    <div class="field">
        <label for="name">Seu nome</label>
        <input class="input" id="name" type="text" name="name"
               value="{{ old('name') }}" required placeholder="João da Silva">
    </div>

    <div class="field">
        <label for="email">E-mail corporativo</label>
        <input class="input" id="email" type="email" name="email"
               value="{{ old('email') }}" required placeholder="joao@empresa.com">
    </div>

    <div class="field">
        <label for="password">Senha</label>
        <input class="input" id="password" type="password" name="password"
               required autocomplete="new-password" placeholder="Mín. 8 caracteres">
    </div>

    <div class="field" style="margin-bottom:28px;">
        <label for="password_confirmation">Confirmar senha</label>
        <input class="input" id="password_confirmation" type="password"
               name="password_confirmation" required autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-primary w-full" style="justify-content:center;padding:13px 24px;font-size:15px;">
        Criar conta
    </button>
</form>

<p style="text-align:center;margin-top:28px;font-size:14px;color:var(--mute);">
    Já tem conta? <a href="{{ route('login') }}" style="font-weight:600;">Entrar</a>
</p>
@endsection
