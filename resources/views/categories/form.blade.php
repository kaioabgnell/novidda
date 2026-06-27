@extends('layouts.app')
@section('title', $category->exists ? 'Editar categoria' : 'Nova categoria')

@section('content')
<div class="card" style="max-width:480px;">
    <h3 style="font-size:18px;margin-bottom:24px;">
        {{ $category->exists ? 'Editar categoria' : 'Nova categoria' }}
    </h3>

    <form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}">
        @csrf
        @if ($category->exists) @method('PUT') @endif

        <div class="field">
            <label for="name">Nome</label>
            <input class="input" id="name" name="name"
                   value="{{ old('name', $category->name) }}"
                   required autofocus placeholder="Ex.: Novidade, Correção, API">
        </div>

        <div class="field">
            <label for="color">Cor de destaque</label>
            <div style="display:flex;gap:10px;align-items:center;">
                <input type="color" id="color_picker" name="_color_picker"
                       value="{{ old('color', $category->color ?: '#7B61FF') }}"
                       style="width:44px;height:44px;border:none;border-radius:var(--r-sm);cursor:pointer;padding:2px;"
                       oninput="document.getElementById('color').value=this.value">
                <input class="input" id="color" name="color"
                       value="{{ old('color', $category->color) }}"
                       placeholder="#7B61FF" style="flex:1;"
                       oninput="document.getElementById('color_picker').value=this.value">
            </div>
        </div>

        <div class="field">
            <label for="icon">Ícone Font Awesome (opcional)</label>
            <input class="input" id="icon" name="icon"
                   value="{{ old('icon', $category->icon) }}"
                   placeholder="fa-solid fa-star">
            <p style="font-size:12px;color:var(--mute);margin:6px 0 0;">
                Ex.: <code>fa-solid fa-star</code>, <code>fa-solid fa-wrench</code>, <code>fa-solid fa-rocket</code>
            </p>
        </div>

        <div class="flex gap-sm" style="margin-top:8px;">
            <button class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Salvar
            </button>
            <a href="{{ route('categories.index') }}" class="btn">Cancelar</a>
        </div>
    </form>
</div>
@endsection
