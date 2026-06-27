@extends('layouts.app')
@section('title', 'Configurações do Widget')

@section('content')
@php $theme = $settings->theme ?? []; @endphp
<div style="max-width:640px;">
    <div class="card">
        <form method="POST" action="{{ route('widget-settings.update') }}">
            @csrf @method('PUT')

            <div style="margin-bottom:28px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:4px;">Aparência</h3>
                <p style="font-size:13px;color:var(--mute);margin:0;">Personalize como o widget aparece no seu site.</p>
            </div>

            {{-- Ícone do botão flutuante --}}
            <div class="field">
                <label for="button_icon">Ícone do botão flutuante <span style="font-size:11px;color:var(--mute);font-weight:400;">(classe FontAwesome, ex: <code>fa-solid fa-star</code>)</span></label>
                <div style="display:flex;gap:10px;align-items:center;">
                    <span id="icon_preview" style="width:40px;height:40px;border-radius:var(--r-sm);background:{{ $theme['accent'] ?? '#6c5ce7' }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;flex-shrink:0;">
                        @if(!empty($theme['button_icon']))
                            <i class="{{ $theme['button_icon'] }}"></i>
                        @else
                            <i class="fa-solid fa-bell"></i>
                        @endif
                    </span>
                    <input class="input" id="button_icon" name="button_icon"
                           value="{{ old('button_icon', $theme['button_icon'] ?? '') }}"
                           placeholder="fa-solid fa-bell"
                           oninput="updateIconPreview(this.value)"
                           style="flex:1;">
                </div>
                <p style="font-size:12px;color:var(--mute);margin:6px 0 0;">Deixe em branco para usar o sino padrão. Veja os ícones disponíveis em <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com/icons</a>.</p>
            </div>

            {{-- Texto do painel --}}
            <div class="field">
                <label for="button_text">Título do painel de novidades</label>
                <input class="input" id="button_text" name="button_text"
                       value="{{ old('button_text', $settings->button_text) }}"
                       required placeholder="Novidades">
                <p style="font-size:12px;color:var(--mute);margin:6px 0 0;">Texto exibido no cabeçalho do painel. Aceita HTML — ex: <code>&lt;i class=&quot;fa-solid fa-star&quot;&gt;&lt;/i&gt; Novidade</code></p>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="field">
                    <label for="open_mode">Modo de abertura</label>
                    <select class="select" id="open_mode" name="open_mode">
                        <option value="side"     @selected(old('open_mode', $settings->open_mode)==='side')>Painel lateral</option>
                        <option value="dropdown" @selected(old('open_mode', $settings->open_mode)==='dropdown')>Dropdown</option>
                    </select>
                </div>
                <div class="field">
                    <label for="position">Posição</label>
                    <select class="select" id="position" name="position">
                        <option value="right" @selected(old('position', $settings->position)==='right')>Direita</option>
                        <option value="left"  @selected(old('position', $settings->position)==='left')>Esquerda</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="accent">Cor de destaque</label>
                <div style="display:flex;gap:10px;align-items:center;">
                    <input type="color" id="accent_picker"
                           value="{{ old('accent', $theme['accent'] ?? '#7B61FF') }}"
                           style="width:44px;height:44px;border:none;border-radius:var(--r-sm);cursor:pointer;padding:2px;"
                           oninput="document.getElementById('accent').value=this.value;document.getElementById('icon_preview').style.background=this.value;">
                    <input class="input" id="accent" name="accent"
                           value="{{ old('accent', $theme['accent'] ?? '#7B61FF') }}"
                           placeholder="#7B61FF" style="flex:1;"
                           oninput="document.getElementById('accent_picker').value=this.value">
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:var(--canvas);border-radius:var(--r-md);margin-bottom:16px;">
                <input type="checkbox" name="dark" value="1" id="dark_mode"
                       @checked(old('dark', $theme['dark'] ?? false))
                       style="width:16px;height:16px;accent-color:var(--primary);cursor:pointer;">
                <label for="dark_mode" style="font-size:14px;font-weight:500;color:var(--ink);cursor:pointer;margin:0;">
                    Widget em modo escuro por padrão
                </label>
            </div>

            {{-- FEEDBACK -- REMOVIDO, reservado para uso futuro
            <hr class="divider">

            <div style="margin-bottom:20px;margin-top:24px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:4px;">Feedback de satisfação</h3>
                <p style="font-size:13px;color:var(--mute);margin:0;">Quando ativo, exibe 3 emojis (triste, neutro, feliz) no rodapé do widget para o leitor avaliar a experiência.</p>
            </div>

            <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:var(--canvas);border-radius:var(--r-md);margin-bottom:28px;">
                <input type="checkbox" name="feedback_enabled" value="1" id="feedback_enabled"
                       @checked(old('feedback_enabled', $settings->feedback_enabled ?? false))
                       style="width:16px;height:16px;accent-color:var(--primary);cursor:pointer;">
                <label for="feedback_enabled" style="font-size:14px;font-weight:500;color:var(--ink);cursor:pointer;margin:0;">
                    Habilitar feedback de satisfação no widget
                </label>
            </div>
            --}}

            <hr class="divider">

            <div style="margin-bottom:20px;margin-top:24px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:4px;">Roadmap</h3>
                <p style="font-size:13px;color:var(--mute);margin:0;">Exibe uma aba de Roadmap na barra inferior do widget.</p>
            </div>

            <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:var(--canvas);border-radius:var(--r-md);margin-bottom:28px;">
                <input type="checkbox" name="roadmap_enabled" value="1" id="roadmap_enabled"
                       @checked(old('roadmap_enabled', $settings->roadmap_enabled ?? true))
                       style="width:16px;height:16px;accent-color:var(--primary);cursor:pointer;">
                <label for="roadmap_enabled" style="font-size:14px;font-weight:500;color:var(--ink);cursor:pointer;margin:0;">
                    Mostrar aba Roadmap no widget
                </label>
            </div>

            <hr class="divider">

            <div style="margin-bottom:20px;margin-top:24px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:4px;">Quantidade de changelogs</h3>
                <p style="font-size:13px;color:var(--mute);margin:0;">Número de changelogs exibidos no widget. Máximo de 20 itens.</p>
            </div>

            <div class="field" style="margin-bottom:28px;">
                <label for="feed_limit">Changelogs visíveis no widget</label>
                <div style="display:flex;align-items:center;gap:14px;">
                    <input type="range" id="feed_limit_range" min="1" max="20"
                           value="{{ old('feed_limit', $settings->feed_limit ?? 5) }}"
                           style="flex:1;accent-color:var(--primary);cursor:pointer;"
                           oninput="document.getElementById('feed_limit').value=this.value;document.getElementById('feed_limit_preview').textContent=this.value;">
                    <input type="number" class="input" id="feed_limit" name="feed_limit" min="1" max="20"
                           value="{{ old('feed_limit', $settings->feed_limit ?? 5) }}"
                           style="width:72px;text-align:center;"
                           oninput="var v=Math.min(20,Math.max(1,this.value||1));this.value=v;document.getElementById('feed_limit_range').value=v;document.getElementById('feed_limit_preview').textContent=v;">
                    <span id="feed_limit_preview"
                          style="font-size:22px;font-weight:800;color:var(--primary);min-width:28px;text-align:center;">{{ old('feed_limit', $settings->feed_limit ?? 5) }}</span>
                </div>
            </div>

            <hr class="divider">

            {{-- webhook_url oculto — mantido para não quebrar a validação --}}
            <input type="hidden" name="webhook_url" value="{{ old('webhook_url', $settings->webhook_url) }}">

            <div style="margin-bottom:20px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:4px;">CSS personalizado</h3>
                <p style="font-size:13px;color:var(--mute);margin:0;">Aplicado dentro do Shadow DOM do widget.</p>
            </div>

            <div class="field" style="margin-bottom:28px;">
                <label for="custom_css">CSS</label>
                <textarea class="textarea" id="custom_css" name="custom_css"
                          style="font-family:ui-monospace,'Fira Code',monospace;font-size:13px;min-height:120px;"
                          placeholder="/* Exemplo: */ .item { border-left-color: red; }">{{ old('custom_css', $settings->custom_css) }}</textarea>
            </div>

            <button class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Salvar configurações
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function updateIconPreview(cls) {
    var p = document.getElementById('icon_preview');
    p.innerHTML = cls ? '<i class="' + cls + '"></i>' : '<i class="fa-solid fa-bell"></i>';
}
</script>
@endpush
@endsection
