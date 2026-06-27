@extends('layouts.app')
@section('title', 'Código de instalação')

@section('content')
<div style="max-width:720px;">
    <div class="card" style="margin-bottom:var(--gap);">
        <div style="display:flex;align-items:flex-start;gap:16px;margin-bottom:24px;">
            <div style="
                width:44px;height:44px;flex-shrink:0;
                background:var(--primary-pale);color:var(--primary);
                border-radius:var(--r-md);display:flex;align-items:center;justify-content:center;
                font-size:18px;">
                <i class="fa-solid fa-code"></i>
            </div>
            <div>
                <h3 style="font-size:18px;margin-bottom:4px;">Instale o widget</h3>
                <p style="margin:0;font-size:14px;color:var(--mute);">
                    Cole este trecho antes do <code>&lt;/body&gt;</code> do seu site.
                    O carregamento é assíncrono e não afeta a performance.
                </p>
            </div>
        </div>

        <div class="code-block" id="snippet">{{ $snippet }}</div>

        <div class="flex items-center gap-sm mt">
            <button class="btn btn-primary" onclick="copySnippet()">
                <i class="fa-solid fa-copy"></i> Copiar código
            </button>
            <span id="copied" style="display:none;font-size:14px;font-weight:600;color:var(--positive);">
                <i class="fa-solid fa-check"></i> Copiado!
            </span>
        </div>
    </div>

    <div class="card">
        <h4 style="font-size:16px;margin-bottom:16px;">Informações da conta</h4>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <div style="display:flex;justify-content:space-between;align-items:center;
                        padding:12px 16px;background:var(--canvas);border-radius:var(--r-md);">
                <span style="font-size:13px;font-weight:600;color:var(--mute);">Token da conta</span>
                <code style="font-size:13px;color:var(--ink);">{{ $token }}</code>
            </div>
        </div>

        <div style="margin-top:24px;padding:16px;background:var(--canvas-soft);
                    border-radius:var(--r-md);">
            <p style="margin:0;font-size:13px;color:var(--body-txt);line-height:1.6;">
                <i class="fa-solid fa-circle-info" style="color:var(--primary);margin-right:6px;"></i>
                O widget carrega menos de <strong>5 KB</strong> na abertura da página e só busca o conteúdo completo quando o usuário clica na campainha. Compatível com qualquer site ou framework.
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copySnippet() {
        var text = document.getElementById('snippet').innerText;
        navigator.clipboard.writeText(text).then(function () {
            var c = document.getElementById('copied');
            c.style.display = 'inline-flex';
            c.style.alignItems = 'center';
            c.style.gap = '6px';
            setTimeout(function(){ c.style.display = 'none'; }, 2500);
        });
    }
</script>
@endpush
