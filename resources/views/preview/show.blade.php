@extends('layouts.app')
@section('title', 'Preview')

@push('head')
<style>
    .preview-canvas {
        min-height: calc(100vh - 140px);
        border: 2px dashed var(--canvas);
        border-radius: var(--r-md);
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    body.dark .preview-canvas { border-color: rgba(123,97,255,.15); }
    .preview-hint { color: var(--mute); font-size: 13px; max-width: 320px; }
    .preview-hint i { display: block; font-size: 22px; margin-bottom: 10px; color: var(--primary); }
</style>
@endpush

@section('content')
<div class="preview-canvas">
    <div class="preview-hint">
        <i class="fa-solid fa-eye"></i>
        Esta página está em branco de propósito.<br>
        O widget no canto da tela é o mesmo que seus usuários veem, com as configurações atuais da sua conta.
    </div>
</div>
@endsection

@push('scripts')
<script>
  window.noviddaConfig = {
    token: @json($token),
    user: {
      id:    {{ auth()->id() }},
      email: @json(auth()->user()->email),
      name:  @json(auth()->user()->name),
      company: {
        id:   {{ $account->id }},
        name: @json($account->name)
      }
    }
  };
</script>
<script src="{{ url('widget.js') }}" async></script>
@endpush
