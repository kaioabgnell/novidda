@extends('layouts.app')
@section('title', $changelog->exists ? 'Editar changelog' : 'Novo changelog')

@push('head')
    <link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}">
    <style>
        .ql-toolbar.ql-snow { border-color: var(--primary-pale); border-radius: var(--r-md) var(--r-md) 0 0; background: var(--card-bg); }
        .ql-container.ql-snow { border-color: var(--primary-pale); border-radius: 0 0 var(--r-md) var(--r-md); background: var(--card-bg); min-height: 200px; }
        .ql-container.ql-snow:focus-within { border-color: var(--primary); }
        body.dark .ql-toolbar.ql-snow,
        body.dark .ql-container.ql-snow { border-color: rgba(123,97,255,.2); background: var(--card-bg); }
        body.dark .ql-snow .ql-stroke { stroke: var(--mute); }
        body.dark .ql-snow .ql-fill  { fill:   var(--mute); }
        body.dark .ql-snow .ql-picker { color: var(--ink); }
        body.dark .ql-editor { color: var(--ink); }
        body.dark .ql-editor.ql-blank::before { color: var(--mute); }
        .media-thumb { width: 88px; height: 60px; object-fit: cover; border-radius: var(--r-sm); }
        .form-grid { display: grid; grid-template-columns: 2fr 1fr; gap: var(--gap); }
        @media (max-width: 880px) { .form-grid { grid-template-columns: 1fr; } }
        .section-label {
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .06em;
            color: var(--mute); margin-bottom: 14px;
            padding-bottom: 8px; border-bottom: 1px solid var(--canvas);
        }
        body.dark .section-label { border-color: rgba(123,97,255,.1); }
        .checkbox-row {
            display: flex; align-items: center; gap: 8px; padding: 8px 0;
        }
        .checkbox-row input[type=checkbox] {
            width: 16px; height: 16px; accent-color: var(--primary); cursor: pointer; flex-shrink: 0;
        }
        .checkbox-row label {
            font-size: 14px; font-weight: 500; color: var(--ink); cursor: pointer; margin: 0;
        }
        .rule-row { display: flex; align-items: center; gap: 8px; }
        .rule-row .input, .rule-row .select { height: 36px; font-size: 13px; }

        /* ── Tab nav ── */
        .tabs-nav {
            display: flex; gap: 4px;
            border-bottom: 2px solid var(--canvas);
            margin-bottom: 20px;
        }
        body.dark .tabs-nav { border-color: rgba(123,97,255,.15); }
        .tab-btn {
            padding: 9px 18px; font-size: 13px; font-weight: 600;
            color: var(--mute); background: none; border: none;
            border-bottom: 2px solid transparent; margin-bottom: -2px;
            cursor: pointer; border-radius: var(--r-sm) var(--r-sm) 0 0;
            transition: color .15s, border-color .15s; display: flex; align-items: center; gap: 6px;
        }
        .tab-btn:hover { color: var(--ink); }
        .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
        .tab-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--primary); flex-shrink: 0;
        }
        .tab-count {
            font-size: 11px; font-weight: 600; color: var(--primary);
        }

        /* ── Tabela de analytics ── */
        .data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .data-table th {
            text-align: left; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .05em;
            color: var(--mute); padding: 8px 12px;
            border-bottom: 1px solid var(--canvas);
        }
        body.dark .data-table th { border-color: rgba(123,97,255,.1); }
        .data-table td { padding: 10px 12px; vertical-align: top; color: var(--ink); }
        .data-table tr + tr td { border-top: 1px solid var(--canvas); }
        body.dark .data-table tr + tr td { border-color: rgba(123,97,255,.07); }
        .data-table .muted { color: var(--mute); font-size: 12px; }
        .badge {
            display: inline-block; padding: 2px 8px;
            font-size: 11px; font-weight: 600; border-radius: 99px;
        }
        .badge-pending  { background: rgba(255,193,7,.15);  color: #b8860b; }
        .badge-approved { background: rgba(40,167,69,.12);  color: #1e7e34; }
        .badge-rejected { background: rgba(220,53,69,.12);  color: #c0392b; }
        body.dark .badge-pending  { background: rgba(255,193,7,.2);  color: #ffc107; }
        body.dark .badge-approved { background: rgba(40,167,69,.2);  color: #28a745; }
        body.dark .badge-rejected { background: rgba(220,53,69,.2);  color: #dc3545; }
        .reaction-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; }
        .reaction-row + .reaction-row { border-top: 1px solid var(--canvas); }
        body.dark .reaction-row + .reaction-row { border-color: rgba(123,97,255,.07); }
        .reaction-emoji { font-size: 22px; }
        .reaction-count { font-size: 18px; font-weight: 700; color: var(--ink); }
        .score-icon { font-size: 18px; }

        /* ── Segmentação ── */
        .seg-toggle-card {
            background: var(--canvas); border-radius: var(--r-md);
            padding: 16px; margin-bottom: 20px; border: 2px solid transparent;
            cursor: pointer; transition: border-color .15s;
        }
        .seg-toggle-card.selected {
            border-color: var(--primary); background: rgba(123,97,255,.06);
        }
        body.dark .seg-toggle-card.selected { background: rgba(123,97,255,.1); }
        .seg-toggle-card .seg-radio {
            width: 16px; height: 16px; flex-shrink: 0;
            accent-color: var(--primary); cursor: pointer;
        }
        .seg-rule-row {
            display: grid;
            grid-template-columns: 200px 180px 1fr 36px;
            gap: 8px; align-items: center;
        }
        @media (max-width: 680px) {
            .seg-rule-row { grid-template-columns: 1fr 1fr; }
            .seg-rule-remove { grid-column: 2; justify-self: end; }
        }
        .reach-bar-wrap {
            background: var(--canvas); border-radius: 99px; height: 8px; overflow: hidden; flex: 1;
        }
        .reach-bar-fill {
            height: 100%; border-radius: 99px;
            background: var(--primary); transition: width .3s ease;
        }
        .seg-attr-datalist option { font-size: 13px; }
        .seg-empty-state {
            text-align: center; padding: 32px 20px;
            color: var(--mute); font-size: 13px;
        }
        /* Accordion FAQ */
        .seg-faq summary {
            cursor: pointer; font-size: 13px; font-weight: 600;
            color: var(--mute); list-style: none; display: flex;
            align-items: center; gap: 6px; padding: 10px 0;
        }
        .seg-faq summary::before { content: '▸'; font-size: 11px; }
        .seg-faq[open] summary::before { content: '▾'; }
        .seg-faq p { font-size: 13px; color: var(--mute); margin: 0 0 10px; line-height: 1.6; }
    </style>
@endpush

@section('content')
<form id="changelog-form" method="POST"
      action="{{ $changelog->exists ? route('changelogs.update', $changelog) : route('changelogs.store') }}"
      enctype="multipart/form-data">
    @csrf
    @if ($changelog->exists) @method('PUT') @endif
    @php
        $ws = $changelog->widgetSettings;
        $bn = $banner ?? null;
        $bnEnabled = old('banner.enabled', $bn?->enabled ?? false);
        $segEnabled = old('segment_enabled', $changelog->segment_enabled ?? false);
        $existingSegRules = old('segment_rules', $changelog->exists ? $changelog->segmentRules->map(fn($r) => [
            'attribute' => $r->attribute,
            'operator'  => $r->operator,
            'value'     => is_array($r->value) ? implode(', ', $r->value) : ($r->value ?? ''),
        ])->toArray() : []);
    @endphp

    {{-- ── Tabs de navegação do formulário ── --}}
    <div class="tabs-nav" id="form-tabs-nav">
        <button type="button" class="tab-btn active" data-tab="tab-form-content" data-tab-group="form">
            Conteúdo
        </button>
        <button type="button" class="tab-btn" data-tab="tab-form-widget" data-tab-group="form" id="btn-tab-widget">
            Widget
            @if ($bn && $bn->enabled && $bn->isActive())
                <span class="tab-dot" title="Banner contextual ativo"></span>
            @endif
        </button>
        <button type="button" class="tab-btn" data-tab="tab-form-segment" data-tab-group="form" id="btn-tab-segment">
            Segmentação
            @if ($segEnabled && count($existingSegRules))
                <span class="tab-dot" title="Regras ativas"></span>
                <span class="tab-count">({{ count($existingSegRules) }})</span>
            @endif
        </button>
    </div>

    {{-- ══════════════════════════════════════════
         ABA: CONTEÚDO
    ══════════════════════════════════════════ --}}
    <div id="tab-form-content" class="tab-pane active">
        <div class="form-grid">

            {{-- Coluna principal --}}
            <div style="display:flex;flex-direction:column;gap:var(--gap);">
                <div class="card">
                    <div class="section-label">Conteúdo</div>

                    <div class="field">
                        <label for="title">Título</label>
                        <input class="input" id="title" name="title"
                               value="{{ old('title', $changelog->title) }}" required
                               placeholder="Ex.: Novo painel de métricas">
                    </div>

                    <div class="field" style="margin-bottom:0;">
                        <label>Descrição</label>
                        <div id="editor">{!! old('description', $changelog->description) !!}</div>
                        <input type="hidden" name="description" id="description">
                    </div>
                </div>

                <div class="card">
                    <div class="section-label">Mídia</div>

                    <div class="field">
                        <label>Imagens</label>
                        <input class="input" type="file" name="images[]" accept="image/*" multiple
                               style="padding:10px 14px;">
                        @if ($changelog->exists && $changelog->media->where('type','image')->count())
                            <div class="flex gap-sm mt" style="flex-wrap:wrap;">
                                @foreach ($changelog->media->where('type','image') as $m)
                                    <label style="background:var(--canvas-soft);padding:10px;border-radius:var(--r-md);text-align:center;cursor:pointer;">
                                        <img src="{{ $m->display_url }}" class="media-thumb"><br>
                                        <span style="display:flex;align-items:center;gap:5px;margin-top:6px;font-size:12px;color:var(--mute);">
                                            <input type="checkbox" name="remove_media[]" value="{{ $m->id }}"
                                                   style="accent-color:var(--negative);"> remover
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="field" style="margin-bottom:0;">
                        <label for="youtube_urls">Vídeos do YouTube (uma URL por linha)</label>
                        <textarea class="textarea" id="youtube_urls" name="youtube_urls"
                                  placeholder="https://youtube.com/watch?v=...">{{ old('youtube_urls') }}</textarea>
                        @if ($changelog->exists && $changelog->media->where('type','youtube')->count())
                            <div style="display:flex;flex-direction:column;gap:6px;margin-top:8px;">
                                @foreach ($changelog->media->where('type','youtube') as $m)
                                    <label style="display:flex;align-items:center;gap:8px;background:var(--canvas-soft);padding:8px 12px;border-radius:var(--r-md);cursor:pointer;">
                                        <i class="fa-brands fa-youtube" style="color:#ef4444;font-size:15px;flex-shrink:0;"></i>
                                        <span style="font-size:13px;color:var(--mute);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $m->url }}</span>
                                        <span style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--mute);flex-shrink:0;">
                                            <input type="checkbox" name="remove_media[]" value="{{ $m->id }}"
                                                   style="accent-color:var(--negative);width:14px;height:14px;"> remover
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Coluna lateral --}}
            <div style="display:flex;flex-direction:column;gap:var(--gap);">
                <div class="card">
                    <div class="section-label">Publicação</div>

                    <div class="field">
                        <label for="type">Tipo</label>
                        <select class="select" id="type" name="type">
                            @foreach (['feature'=>'Feature','hotfix'=>'Hotfix','improvement'=>'Melhoria','announcement'=>'Anúncio'] as $k=>$lbl)
                                <option value="{{ $k }}" @selected(old('type',$changelog->type)===$k)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="status">Status</label>
                        <select class="select" id="status" name="status">
                            @foreach (['draft'=>'Rascunho','published'=>'Publicado','archived'=>'Arquivado'] as $k=>$lbl)
                                <option value="{{ $k }}" @selected(old('status',$changelog->status)===$k)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="published_at">Agendar publicação</label>
                        <input class="input" type="datetime-local" id="published_at" name="published_at"
                               value="{{ old('published_at', $changelog->published_at?->format('Y-m-d\TH:i')) }}">
                        <p style="font-size:12px;color:var(--mute);margin:6px 0 0;">Data futura + Publicado = agendamento automático.</p>
                    </div>

                    <div class="field" style="margin-bottom:0;">
                        <label for="reaction_emoji">Emoji da reação</label>
                        <input class="input" id="reaction_emoji" name="reaction_emoji"
                               value="{{ old('reaction_emoji', $changelog->reaction_emoji ?: '❤️') }}"
                               maxlength="16">
                    </div>
                </div>

                <div class="card">
                    <div class="section-label">Categorias</div>
                    @forelse ($categories as $cat)
                        <div class="checkbox-row">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                   id="cat_{{ $cat->id }}"
                                   @checked(in_array($cat->id, old('categories', $selected)))>
                            <label for="cat_{{ $cat->id }}">{{ $cat->name }}</label>
                        </div>
                    @empty
                        <p style="font-size:13px;color:var(--mute);margin:0;">
                            Nenhuma categoria. <a href="{{ route('categories.create') }}">Criar</a>.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         ABA: WIDGET
    ══════════════════════════════════════════ --}}
    <div id="tab-form-widget" class="tab-pane">

        <div class="card">
            <div class="section-label">Exibição no widget</div>

            <div class="checkbox-row">
                <input type="checkbox" name="show_reactions" value="1" id="show_reactions"
                       @checked(old('show_reactions', $ws->show_reactions ?? true))>
                <label for="show_reactions">Mostrar reações</label>
            </div>
            <div class="checkbox-row">
                <input type="checkbox" name="show_comments" value="1" id="show_comments"
                       @checked(old('show_comments', $ws->show_comments ?? true))>
                <label for="show_comments">Mostrar comentários</label>
            </div>
            <div class="checkbox-row">
                <input type="checkbox" name="allow_comments" value="1" id="allow_comments"
                       @checked(old('allow_comments', $ws->allow_comments ?? true))>
                <label for="allow_comments">Permitir novos comentários</label>
            </div>
            <div class="checkbox-row" style="margin-bottom:16px;">
                <input type="checkbox" name="feedback_enabled" value="1" id="feedback_enabled"
                       @checked(old('feedback_enabled', $ws->feedback_enabled ?? false))>
                <label for="feedback_enabled">Habilitar feedback (😢 😐 😊)</label>
            </div>

            <hr class="divider">

            <div class="field">
                <label for="cta_text">Botão de ação — texto</label>
                <input class="input" id="cta_text" name="cta_text"
                       value="{{ old('cta_text', $ws->cta_text ?? '') }}"
                       placeholder="Ver mais">
            </div>
            <div class="field">
                <label for="cta_url">URL do botão</label>
                <input class="input" id="cta_url" name="cta_url"
                       value="{{ old('cta_url', $ws->cta_url ?? '') }}"
                       placeholder="https://...">
            </div>
            <div class="field" style="margin-bottom:12px;">
                <label for="cta_color">Cor do botão</label>
                <input class="input" id="cta_color" name="cta_color"
                       value="{{ old('cta_color', $ws->cta_color ?? '') }}"
                       placeholder="#7B61FF">
            </div>
            <div class="checkbox-row">
                <input type="checkbox" name="cta_new_tab" value="1" id="cta_new_tab"
                       @checked(old('cta_new_tab', $ws->cta_new_tab ?? true))>
                <label for="cta_new_tab">Abrir em nova aba</label>
            </div>
        </div>

        {{-- ── Banner Contextual ── --}}
        <div class="card mt" id="nv-banner-card">
            <div class="banner-toggle-hd" id="nv-banner-hd" onclick="nvToggleBanner()" style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;user-select:none;padding:2px 0;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:36px;height:36px;border-radius:var(--r-sm);background:var(--primary-pale);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                        <i class="fa-solid fa-rectangle-ad"></i>
                    </div>
                    <div>
                        <div style="font-size:15px;font-weight:700;color:var(--ink);display:flex;align-items:center;gap:8px;">
                            Banner Contextual
                            @if ($bn && $bn->enabled && $bn->isActive())
                                <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px;background:rgba(123,97,255,.12);color:var(--primary);">ATIVO</span>
                            @endif
                        </div>
                        <div style="font-size:12px;color:var(--mute);margin-top:2px;">Aparece em URLs específicas do sistema do cliente</div>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-down" id="nv-banner-chevron" style="color:var(--mute);transition:transform .2s;{{ $bnEnabled ? 'transform:rotate(180deg)' : '' }}"></i>
            </div>

            <div id="nv-banner-body" style="{{ $bnEnabled ? '' : 'display:none;' }}border-top:1px solid var(--canvas);padding-top:22px;margin-top:16px;">
                <div class="checkbox-row" style="margin-bottom:20px;">
                    <input type="checkbox" name="banner[enabled]" value="1" id="banner_enabled"
                           onchange="nvBannerEnableChange(this)"
                           @checked($bnEnabled)
                           style="width:16px;height:16px;accent-color:var(--primary);cursor:pointer;">
                    <label for="banner_enabled" style="font-size:14px;font-weight:600;color:var(--ink);cursor:pointer;margin:0;">
                        Ativar banner contextual para este changelog
                    </label>
                </div>

                <div id="nv-banner-fields" style="{{ $bnEnabled ? '' : 'opacity:.45;pointer-events:none;' }}">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px;">
                        <div class="field" style="margin-bottom:0;">
                            <label for="banner_style">Estilo</label>
                            <select class="select" id="banner_style" name="banner[style]" onchange="nvBannerStyleChange(this)">
                                <option value="toast"      @selected(old('banner.style', $bn?->style) === 'toast')>Toast (canto)</option>
                                <option value="top_bar"    @selected(old('banner.style', $bn?->style) === 'top_bar')>Barra superior</option>
                                <option value="bottom_bar" @selected(old('banner.style', $bn?->style) === 'bottom_bar')>Barra inferior</option>
                            </select>
                        </div>
                        <div class="field" style="margin-bottom:0;" id="banner_position_wrap">
                            <label for="banner_position">Posição do toast</label>
                            <select class="select" id="banner_position" name="banner[position]">
                                <option value="bottom_right" @selected(old('banner.position', $bn?->position) === 'bottom_right')>Inferior direito</option>
                                <option value="bottom_left"  @selected(old('banner.position', $bn?->position) === 'bottom_left')>Inferior esquerdo</option>
                                <option value="top_right"    @selected(old('banner.position', $bn?->position) === 'top_right')>Superior direito</option>
                                <option value="top_left"     @selected(old('banner.position', $bn?->position) === 'top_left')>Superior esquerdo</option>
                            </select>
                        </div>
                        <div class="field" style="margin-bottom:0;">
                            <label for="banner_frequency">Frequência</label>
                            <select class="select" id="banner_frequency" name="banner[frequency]" onchange="nvBannerFreqChange(this)">
                                <option value="once_per_user"  @selected(old('banner.frequency', $bn?->frequency) === 'once_per_user')>Uma vez por usuário</option>
                                <option value="until_clicked"  @selected(old('banner.frequency', $bn?->frequency) === 'until_clicked')>Até clicar no CTA</option>
                                <option value="times_capped"   @selected(old('banner.frequency', $bn?->frequency) === 'times_capped')>Máximo de N vezes</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                        <div class="field" style="margin-bottom:0;" id="banner_cap_wrap">
                            <label for="banner_frequency_cap">Máximo de exibições</label>
                            <input class="input" type="number" id="banner_frequency_cap" name="banner[frequency_cap]"
                                   value="{{ old('banner.frequency_cap', $bn?->frequency_cap) }}"
                                   min="1" max="50" placeholder="Ex.: 3"
                                   style="{{ old('banner.frequency', $bn?->frequency) !== 'times_capped' ? 'opacity:.4;' : '' }}">
                        </div>
                        <div class="field" style="margin-bottom:0;">
                            <label for="banner_auto_dismiss">Auto-fechar após (segundos)</label>
                            <input class="input" type="number" id="banner_auto_dismiss" name="banner[auto_dismiss_seconds]"
                                   value="{{ old('banner.auto_dismiss_seconds', $bn?->auto_dismiss_seconds) }}"
                                   min="1" max="300" placeholder="Sem auto-fechar">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                        <div class="field" style="margin-bottom:0;">
                            <label for="banner_expires_at">Expirar em</label>
                            <input class="input" type="date" id="banner_expires_at" name="banner[expires_at]"
                                   value="{{ old('banner.expires_at', $bn?->expires_at?->format('Y-m-d')) }}">
                            <p style="font-size:11px;color:var(--mute);margin:4px 0 0;">Deixe em branco para nunca expirar.</p>
                        </div>
                        <div class="field" style="margin-bottom:0;">
                            <label for="banner_custom_copy">Título do banner <span style="font-weight:400;color:var(--mute);">(opcional)</span></label>
                            <div style="display:flex;gap:8px;">
                                <input class="input" id="banner_custom_copy" name="banner[custom_copy]"
                                       value="{{ old('banner.custom_copy', $bn?->custom_copy) }}"
                                       placeholder="Deixe em branco para usar o título do changelog"
                                       maxlength="500" style="flex:1;min-width:0;">
                                <select class="select" name="banner[title_align]" id="banner_title_align"
                                        title="Alinhamento do título" style="width:112px;flex-shrink:0;">
                                    <option value="left"   @selected(old('banner.title_align', $bn?->title_align ?: 'left') === 'left')>Esquerda</option>
                                    <option value="center" @selected(old('banner.title_align', $bn?->title_align) === 'center')>Centro</option>
                                    <option value="right"  @selected(old('banner.title_align', $bn?->title_align) === 'right')>Direita</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field" style="margin-bottom:20px;">
                        <div style="display:flex;align-items:flex-end;gap:8px;">
                            <div style="flex:1;min-width:0;">
                                <label for="banner_description">Descrição <span style="font-weight:400;color:var(--mute);">(opcional)</span></label>
                                <textarea class="textarea" id="banner_description" name="banner[description]"
                                          rows="2" maxlength="1000"
                                          placeholder="Texto adicional exibido abaixo do título">{{ old('banner.description', $bn?->description) }}</textarea>
                            </div>
                            <select class="select" name="banner[description_align]" id="banner_description_align"
                                    title="Alinhamento da descrição" style="width:112px;flex-shrink:0;">
                                <option value="left"   @selected(old('banner.description_align', $bn?->description_align ?: 'left') === 'left')>Esquerda</option>
                                <option value="center" @selected(old('banner.description_align', $bn?->description_align) === 'center')>Centro</option>
                                <option value="right"  @selected(old('banner.description_align', $bn?->description_align) === 'right')>Direita</option>
                            </select>
                        </div>
                    </div>

                    <div class="checkbox-row" style="margin-bottom:12px;">
                        <input type="checkbox" name="banner[countdown_enabled]" value="1" id="banner_countdown_enabled"
                               onchange="nvBannerCountdownChange(this)"
                               @checked(old('banner.countdown_enabled', $bn?->countdown_enabled ?? false))
                               style="width:16px;height:16px;accent-color:var(--primary);cursor:pointer;">
                        <label for="banner_countdown_enabled" style="font-size:14px;font-weight:600;color:var(--ink);cursor:pointer;margin:0;">
                            Exibir contador regressivo
                        </label>
                    </div>
                    <div class="field" id="banner_countdown_wrap" style="margin-bottom:20px;{{ old('banner.countdown_enabled', $bn?->countdown_enabled ?? false) ? '' : 'display:none;' }}">
                        <label for="banner_countdown_target_at">Data/hora final do contador</label>
                        <input class="input" type="datetime-local" id="banner_countdown_target_at" name="banner[countdown_target_at]"
                               value="{{ old('banner.countdown_target_at', $bn?->countdown_target_at?->format('Y-m-d\TH:i')) }}">
                        <p style="font-size:11px;color:var(--mute);margin:4px 0 0;">O banner exibirá dias, horas, minutos e segundos restantes até esta data.</p>
                    </div>

                    <div style="border-top:1px solid var(--canvas);padding-top:20px;margin-bottom:20px;">
                        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--mute);margin-bottom:14px;">Cores</div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div class="field" style="margin-bottom:0;">
                                <label for="banner_bg_color">Cor de fundo</label>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <input type="color" id="banner_bg_color_picker"
                                           value="{{ old('banner.bg_color', $bn?->bg_color ?: '#7B61FF') }}"
                                           oninput="document.getElementById('banner_bg_color').value=this.value"
                                           style="width:38px;height:36px;padding:2px;border:1px solid var(--primary-pale);border-radius:var(--r-sm);cursor:pointer;flex-shrink:0;background:var(--card-bg);">
                                    <input class="input" id="banner_bg_color" name="banner[bg_color]"
                                           value="{{ old('banner.bg_color', $bn?->bg_color) }}"
                                           placeholder="#7B61FF" maxlength="20"
                                           oninput="syncColorPicker('banner_bg_color','banner_bg_color_picker')">
                                </div>
                            </div>
                            <div class="field" style="margin-bottom:0;">
                                <label for="banner_text_color">Cor do texto</label>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <input type="color" id="banner_text_color_picker"
                                           value="{{ old('banner.text_color', $bn?->text_color ?: '#ffffff') }}"
                                           oninput="document.getElementById('banner_text_color').value=this.value"
                                           style="width:38px;height:36px;padding:2px;border:1px solid var(--primary-pale);border-radius:var(--r-sm);cursor:pointer;flex-shrink:0;background:var(--card-bg);">
                                    <input class="input" id="banner_text_color" name="banner[text_color]"
                                           value="{{ old('banner.text_color', $bn?->text_color) }}"
                                           placeholder="#ffffff" maxlength="20"
                                           oninput="syncColorPicker('banner_text_color','banner_text_color_picker')">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px;">
                        <div class="field" style="margin-bottom:0;">
                            <label for="banner_cta_text">Texto do botão (CTA)</label>
                            <input class="input" id="banner_cta_text" name="banner[cta_text]"
                                   value="{{ old('banner.cta_text', $bn?->cta_text) }}"
                                   placeholder="Ex.: Ver novidade" maxlength="80">
                        </div>
                        <div class="field" style="margin-bottom:0;">
                            <label for="banner_cta_url">URL do CTA</label>
                            <input class="input" id="banner_cta_url" name="banner[cta_url]"
                                   value="{{ old('banner.cta_url', $bn?->cta_url) }}"
                                   placeholder="https://...">
                        </div>
                        <div class="field" style="margin-bottom:0;">
                            <label for="banner_cta_color">Cor do botão</label>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <input type="color" id="banner_cta_color_picker"
                                       value="{{ old('banner.cta_color', $bn?->cta_color ?: '#5b45d4') }}"
                                       oninput="document.getElementById('banner_cta_color').value=this.value"
                                       style="width:38px;height:36px;padding:2px;border:1px solid var(--primary-pale);border-radius:var(--r-sm);cursor:pointer;flex-shrink:0;background:var(--card-bg);">
                                <input class="input" id="banner_cta_color" name="banner[cta_color]"
                                       value="{{ old('banner.cta_color', $bn?->cta_color) }}"
                                       placeholder="#5b45d4" maxlength="20"
                                       oninput="syncColorPicker('banner_cta_color','banner_cta_color_picker')">
                            </div>
                        </div>
                    </div>

                    <div class="checkbox-row" style="margin-bottom:24px;">
                        <input type="checkbox" name="banner[cta_new_tab]" value="1" id="banner_cta_new_tab"
                               @checked(old('banner.cta_new_tab', $bn?->cta_new_tab ?? false))
                               style="width:16px;height:16px;accent-color:var(--primary);cursor:pointer;">
                        <label for="banner_cta_new_tab" style="font-size:14px;font-weight:500;color:var(--ink);cursor:pointer;margin:0;">
                            Abrir CTA em nova aba
                        </label>
                    </div>

                    <div style="border-top:1px solid var(--canvas);padding-top:20px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                            <div>
                                <div style="font-size:13px;font-weight:700;color:var(--ink);">Regras de URL</div>
                                <div style="font-size:12px;color:var(--mute);margin-top:2px;">Define em quais páginas o banner aparece (OR lógico entre inclusões).</div>
                            </div>
                            <button type="button" class="btn btn-sm" onclick="nvAddRule('include')" style="flex-shrink:0;">
                                <i class="fa-solid fa-plus"></i> Adicionar regra
                            </button>
                        </div>

                        <div id="nv-rules-list" style="display:flex;flex-direction:column;gap:8px;">
                            @php
                                $existingRules = old('banner_rules', $bn ? $bn->rules->map(fn($r) => ['type' => $r->type, 'mode' => $r->match_mode, 'pattern' => $r->pattern])->toArray() : []);
                            @endphp
                            @forelse ($existingRules as $i => $rule)
                                <div class="rule-row" data-index="{{ $i }}">
                                    <select class="select rule-type" name="banner_rules[{{ $i }}][type]" style="width:110px;flex-shrink:0;">
                                        <option value="include" @selected(($rule['type'] ?? 'include') === 'include')>Incluir</option>
                                        <option value="exclude" @selected(($rule['type'] ?? '') === 'exclude')>Excluir</option>
                                    </select>
                                    <select class="select rule-mode" name="banner_rules[{{ $i }}][mode]" style="width:130px;flex-shrink:0;">
                                        <option value="contains"    @selected(($rule['mode'] ?? 'contains') === 'contains')>Contém</option>
                                        <option value="starts_with" @selected(($rule['mode'] ?? '') === 'starts_with')>Começa com</option>
                                        <option value="exact"       @selected(($rule['mode'] ?? '') === 'exact')>Exato</option>
                                        <option value="regex"       @selected(($rule['mode'] ?? '') === 'regex')>Regex</option>
                                    </select>
                                    <input class="input rule-pattern" name="banner_rules[{{ $i }}][pattern]"
                                           value="{{ $rule['pattern'] ?? '' }}"
                                           placeholder="Ex.: /relatorios" style="flex:1;min-width:0;">
                                    <button type="button" class="btn btn-sm btn-danger btn-icon" onclick="this.closest('.rule-row').remove();nvReindexRules();" title="Remover">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            @empty
                            @endforelse
                        </div>

                        <div id="nv-rules-empty" style="{{ count($existingRules) ? 'display:none;' : '' }}padding:14px;text-align:center;font-size:13px;color:var(--mute);background:var(--canvas);border-radius:var(--r-md);margin-top:8px;">
                            Nenhuma regra. Adicione ao menos uma URL de inclusão.
                        </div>
                    </div>

                    <div style="border-top:1px solid var(--canvas);padding-top:20px;margin-top:20px;display:flex;align-items:center;justify-content:flex-end;">
                        <button type="button" class="btn" onclick="nvPreviewBanner()">
                            <i class="fa-solid fa-eye"></i> Pré-visualizar banner
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         ABA: SEGMENTAÇÃO
    ══════════════════════════════════════════ --}}
    <div id="tab-form-segment" class="tab-pane">
        <div class="card">
            <div style="font-size:15px;font-weight:700;color:var(--ink);margin-bottom:4px;">Quem vai ver este changelog</div>
            <div style="font-size:13px;color:var(--mute);margin-bottom:20px;">Defina quem entre seus usuários pode ver esta release.</div>

            {{-- Toggle todos / segmentado --}}
            <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px;">
                <label class="seg-toggle-card {{ !$segEnabled ? 'selected' : '' }}" id="seg-card-all" onclick="nvSegToggle(false)">
                    <div style="display:flex;align-items:flex-start;gap:12px;">
                        <input type="radio" name="_seg_mode" value="all" class="seg-radio mt"
                               {{ !$segEnabled ? 'checked' : '' }} style="margin-top:2px;">
                        <div>
                            <div style="font-size:14px;font-weight:600;color:var(--ink);">Todos os usuários</div>
                            <div style="font-size:12px;color:var(--mute);margin-top:2px;">Default. Não aplica filtro. Usuários anônimos também veem.</div>
                        </div>
                    </div>
                </label>

                <label class="seg-toggle-card {{ $segEnabled ? 'selected' : '' }}" id="seg-card-rules" onclick="nvSegToggle(true)">
                    <div style="display:flex;align-items:flex-start;gap:12px;">
                        <input type="radio" name="_seg_mode" value="rules" class="seg-radio"
                               {{ $segEnabled ? 'checked' : '' }} style="margin-top:2px;">
                        <div>
                            <div style="font-size:14px;font-weight:600;color:var(--ink);">Apenas usuários que correspondem a regras</div>
                            <div style="font-size:12px;color:var(--mute);margin-top:2px;">Filtra por atributos do usuário. Usuários anônimos não veem.</div>
                        </div>
                    </div>
                </label>
            </div>

            {{-- Campo oculto real --}}
            <input type="hidden" name="segment_enabled" id="seg_enabled_input" value="{{ $segEnabled ? '1' : '0' }}">

            {{-- Painel de regras --}}
            <div id="seg-rules-panel" style="{{ $segEnabled ? '' : 'display:none;' }}">
                <hr class="divider">

                <div style="display:flex;align-items:center;justify-content:space-between;margin:20px 0 12px;">
                    <div>
                        <div style="font-size:13px;font-weight:700;color:var(--ink);">Regras
                            <span style="font-weight:400;color:var(--mute);font-size:12px;">(todas precisam ser verdadeiras)</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm" onclick="nvSegAddRule()" style="flex-shrink:0;">
                        <i class="fa-solid fa-plus"></i> Adicionar regra
                    </button>
                </div>

                <datalist id="seg-attr-datalist"></datalist>

                <div id="seg-rules-list" style="display:flex;flex-direction:column;gap:10px;"></div>

                <div id="seg-rules-empty" style="padding:14px;text-align:center;font-size:13px;color:var(--mute);background:var(--canvas);border-radius:var(--r-md);margin-top:4px;">
                    Nenhuma regra. Clique em "Adicionar regra" para começar.
                </div>

                {{-- Alcance estimado --}}
                @if ($changelog->exists)
                <hr class="divider" style="margin-top:24px;">

                <div style="margin-top:20px;">
                    <div style="font-size:13px;font-weight:700;color:var(--ink);margin-bottom:12px;">Alcance estimado</div>

                    <div id="seg-reach-widget" style="background:var(--canvas);border-radius:var(--r-md);padding:16px;">
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                            <span id="seg-reach-label" style="font-size:14px;font-weight:600;color:var(--ink);">—</span>
                            <span id="seg-reach-pct" style="font-size:12px;color:var(--mute);"></span>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="reach-bar-wrap">
                                <div class="reach-bar-fill" id="seg-reach-bar" style="width:0%"></div>
                            </div>
                            <button type="button" class="btn btn-sm" onclick="nvSegPreview()" style="flex-shrink:0;">
                                <i class="fa-solid fa-eye"></i> Pré-visualizar
                            </button>
                        </div>
                        <div id="seg-reach-loading" style="font-size:12px;color:var(--mute);margin-top:6px;display:none;">
                            <i class="fa-solid fa-spinner fa-spin"></i> Calculando…
                        </div>
                    </div>
                </div>
                @endif

                <hr class="divider" style="margin-top:24px;">

                <details class="seg-faq" style="margin-top:16px;">
                    <summary>O que acontece com usuários anônimos?</summary>
                    <p style="margin-top:8px;">Usuários anônimos (que não enviam dados via <code>window.noviddaConfig.user</code>) <strong>não veem</strong> changelogs com regras de segmentação ativas. Para que esses usuários vejam o changelog, selecione "Todos os usuários".</p>
                </details>
            </div>
        </div>
    </div>

    {{-- Botões de ação (sempre visíveis) --}}
    <div class="flex gap-sm mt">
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Salvar
        </button>
        <a href="{{ route('changelogs.index') }}" class="btn">Cancelar</a>
    </div>
</form>

{{-- Modal preview de banner --}}
<div id="nv-preview-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:var(--card-bg);border-radius:var(--r-md);padding:24px;max-width:520px;width:90%;position:relative;">
        <button type="button" onclick="document.getElementById('nv-preview-overlay').style.display='none';"
                style="position:absolute;top:12px;right:14px;background:none;border:none;font-size:20px;cursor:pointer;color:var(--mute);">&times;</button>
        <h4 style="margin-bottom:16px;font-size:15px;">Pré-visualização do banner</h4>
        <div id="nv-preview-frame" style="border:1px solid var(--primary-pale);border-radius:var(--r-md);overflow:hidden;min-height:120px;position:relative;background:var(--canvas);"></div>
        <p style="font-size:11px;color:var(--mute);margin:10px 0 0;">A aparência final pode variar de acordo com o tema do widget.</p>
    </div>
</div>

{{-- Modal de pré-visualização de audiência --}}
<div id="seg-preview-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:var(--card-bg);border-radius:var(--r-md);padding:24px;max-width:700px;width:95%;position:relative;max-height:85vh;overflow-y:auto;">
        <button type="button" onclick="document.getElementById('seg-preview-overlay').style.display='none';"
                style="position:absolute;top:12px;right:14px;background:none;border:none;font-size:20px;cursor:pointer;color:var(--mute);">&times;</button>
        <h4 style="margin-bottom:4px;font-size:15px;">Quem vê esta release</h4>
        <p id="seg-preview-count" style="font-size:13px;color:var(--mute);margin-bottom:16px;">—</p>
        <div id="seg-preview-body"></div>
        <div id="seg-preview-pagination" style="display:flex;align-items:center;gap:6px;margin-top:16px;font-size:13px;"></div>
    </div>
</div>

{{-- Analytics (comentários, reações, feedbacks) — apenas no modo edição --}}
@if ($changelog->exists)
<div class="card" style="margin-top:var(--gap);">
    <div class="tabs-nav">
        <button class="tab-btn active" data-tab="tab-comments" data-tab-group="analytics">
            Comentários
            @if ($comments->count()) <span style="margin-left:4px;opacity:.6;">({{ $comments->count() }})</span> @endif
        </button>
        <button class="tab-btn" data-tab="tab-reactions" data-tab-group="analytics">
            Reações
            @if ($reactions->sum('total')) <span style="margin-left:4px;opacity:.6;">({{ $reactions->sum('total') }})</span> @endif
        </button>
        <button class="tab-btn" data-tab="tab-feedbacks" data-tab-group="analytics">
            Feedbacks
            @if ($feedbacks->count()) <span style="margin-left:4px;opacity:.6;">({{ $feedbacks->count() }})</span> @endif
        </button>
    </div>

    <div id="tab-comments" class="tab-pane active">
        @if ($comments->isEmpty())
            <p style="color:var(--mute);font-size:13px;margin:0;">Nenhum comentário ainda.</p>
        @else
            <table class="data-table">
                <thead><tr><th>Autor</th><th>Mensagem</th><th>Status</th><th>Data</th></tr></thead>
                <tbody>
                    @foreach ($comments as $c)
                        <tr>
                            <td style="white-space:nowrap;">{{ $c->author_name ?: 'Anônimo' }}</td>
                            <td>{{ $c->body }}</td>
                            <td style="white-space:nowrap;">
                                <span class="badge badge-{{ $c->status }}">
                                    {{ match($c->status) { 'pending' => 'Pendente', 'approved' => 'Aprovado', 'rejected' => 'Rejeitado' } }}
                                </span>
                            </td>
                            <td class="muted" style="white-space:nowrap;">{{ $c->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div id="tab-reactions" class="tab-pane">
        @if ($reactions->isEmpty())
            <p style="color:var(--mute);font-size:13px;margin:0;">Nenhuma reação ainda.</p>
        @else
            @foreach ($reactions as $r)
                <div class="reaction-row">
                    <span class="reaction-emoji">{{ $r->emoji }}</span>
                    <span class="reaction-count">{{ $r->total }}</span>
                    <span class="muted">{{ $r->total === 1 ? 'reação' : 'reações' }}</span>
                </div>
            @endforeach
        @endif
    </div>

    <div id="tab-feedbacks" class="tab-pane">
        @if ($feedbacks->isEmpty())
            <p style="color:var(--mute);font-size:13px;margin:0;">Nenhum feedback ainda.</p>
        @else
            <table class="data-table">
                <thead><tr><th>Avaliação</th><th>Comentário</th><th>Data</th></tr></thead>
                <tbody>
                    @foreach ($feedbacks as $f)
                        <tr>
                            <td style="white-space:nowrap;">
                                <span class="score-icon">{{ match($f->score) { 'sad' => '😢', 'neutral' => '😐', 'happy' => '😊' } }}</span>
                                <span style="font-size:12px;color:var(--mute);margin-left:4px;">
                                    {{ match($f->score) { 'sad' => 'Insatisfeito', 'neutral' => 'Neutro', 'happy' => 'Satisfeito' } }}
                                </span>
                            </td>
                            <td>{{ $f->comment ?: '—' }}</td>
                            <td class="muted" style="white-space:nowrap;">{{ $f->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="{{ asset('vendor/quill/quill.js') }}"></script>
<script>
// ── Quill ──
var quill = new Quill('#editor', {
    theme: 'snow',
    modules: { toolbar: [
        [{ header: [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['blockquote', 'code-block', 'link'],
        ['clean']
    ] }
});
document.getElementById('description').value = quill.root.innerHTML;
quill.on('text-change', function () {
    document.getElementById('description').value = quill.root.innerHTML;
});
document.getElementById('changelog-form').addEventListener('submit', function () {
    document.getElementById('description').value = quill.root.innerHTML;
});

// ── Tabs (scoped por grupo) ──
document.querySelectorAll('.tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var group = btn.dataset.tabGroup || 'default';
        document.querySelectorAll('.tab-btn[data-tab-group="' + group + '"]').forEach(function (b) {
            b.classList.remove('active');
        });
        document.querySelectorAll('.tab-pane').forEach(function (p) {
            if (p.id && document.querySelector('.tab-btn[data-tab="' + p.id + '"][data-tab-group="' + group + '"]')) {
                p.classList.remove('active');
            }
        });
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});

// ── Banner Contextual ──
function nvToggleBanner() {
    var body = document.getElementById('nv-banner-body');
    var chev = document.getElementById('nv-banner-chevron');
    var open = body.style.display !== 'none';
    body.style.display = open ? 'none' : '';
    chev.style.transform = open ? '' : 'rotate(180deg)';
}
function nvBannerEnableChange(cb) {
    var fields = document.getElementById('nv-banner-fields');
    fields.style.opacity = cb.checked ? '' : '.45';
    fields.style.pointerEvents = cb.checked ? '' : 'none';
}
function nvBannerStyleChange(sel) {
    var posWrap = document.getElementById('banner_position_wrap');
    posWrap.style.display = sel.value === 'toast' ? '' : 'none';
}
function nvBannerCountdownChange(cb) {
    document.getElementById('banner_countdown_wrap').style.display = cb.checked ? '' : 'none';
}
function nvBannerFreqChange(sel) {
    var capInput = document.getElementById('banner_frequency_cap');
    capInput.style.opacity = sel.value === 'times_capped' ? '' : '.4';
    capInput.disabled = sel.value !== 'times_capped';
}
var nvRuleIdx = {{ count($banner?->rules ?? []) }};
function nvAddRule(type) {
    var list = document.getElementById('nv-rules-list');
    var empty = document.getElementById('nv-rules-empty');
    var i = nvRuleIdx++;
    var div = document.createElement('div');
    div.className = 'rule-row';
    div.dataset.index = i;
    div.innerHTML =
        '<select class="select rule-type" name="banner_rules[' + i + '][type]" style="width:110px;flex-shrink:0;">' +
            '<option value="include"' + (type === 'include' ? ' selected' : '') + '>Incluir</option>' +
            '<option value="exclude"' + (type === 'exclude' ? ' selected' : '') + '>Excluir</option>' +
        '</select>' +
        '<select class="select rule-mode" name="banner_rules[' + i + '][mode]" style="width:130px;flex-shrink:0;">' +
            '<option value="contains">Contém</option>' +
            '<option value="starts_with">Começa com</option>' +
            '<option value="exact">Exato</option>' +
            '<option value="regex">Regex</option>' +
        '</select>' +
        '<input class="input rule-pattern" name="banner_rules[' + i + '][pattern]" placeholder="Ex.: /relatorios" style="flex:1;min-width:0;">' +
        '<button type="button" class="btn btn-sm btn-danger btn-icon" onclick="this.closest(\'.rule-row\').remove();nvReindexRules();" title="Remover">' +
            '<i class="fa-solid fa-trash"></i>' +
        '</button>';
    list.appendChild(div);
    empty.style.display = 'none';
    div.querySelector('.rule-pattern').focus();
}
function nvReindexRules() {
    var rows = document.querySelectorAll('#nv-rules-list .rule-row');
    var empty = document.getElementById('nv-rules-empty');
    rows.forEach(function (row, i) {
        row.querySelectorAll('[name]').forEach(function (el) {
            el.name = el.name.replace(/banner_rules\[\d+\]/, 'banner_rules[' + i + ']');
        });
    });
    empty.style.display = rows.length === 0 ? '' : 'none';
}
function syncColorPicker(inputId, pickerId) {
    var val = document.getElementById(inputId).value;
    if (/^#[0-9a-fA-F]{3,8}$/.test(val)) {
        document.getElementById(pickerId).value = val;
    }
}
function nvPreviewBanner() {
    var style       = document.getElementById('banner_style').value;
    var pos         = document.getElementById('banner_position').value;
    var copyEl      = document.getElementById('banner_custom_copy');
    var titleEl     = document.getElementById('title');
    var copy        = copyEl.value || (titleEl ? titleEl.value : '') || 'Texto do banner';
    var description = document.getElementById('banner_description').value;
    var titleAlign  = document.getElementById('banner_title_align').value || 'left';
    var descAlign   = document.getElementById('banner_description_align').value || 'left';
    var ctaText     = document.getElementById('banner_cta_text').value;
    var bgColor     = document.getElementById('banner_bg_color').value;
    var textColor   = document.getElementById('banner_text_color').value;
    var ctaColor    = document.getElementById('banner_cta_color').value;
    var countdownOn = document.getElementById('banner_countdown_enabled').checked;
    var countdownAt = document.getElementById('banner_countdown_target_at').value;
    var defaultAccent = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#7B61FF';
    var bg   = /^#[0-9a-fA-F]{3,8}$/.test(bgColor)   ? bgColor   : defaultAccent;
    var fg   = /^#[0-9a-fA-F]{3,8}$/.test(textColor) ? textColor : '#ffffff';
    var cBtn = /^#[0-9a-fA-F]{3,8}$/.test(ctaColor)  ? ctaColor  : darken(bg);
    var posMap = { bottom_right:'bottom:16px;right:16px', bottom_left:'bottom:16px;left:16px', top_right:'top:16px;right:16px', top_left:'top:16px;left:16px' };
    var isBar = style === 'top_bar' || style === 'bottom_bar';
    var bannerStyle = '';
    if (style === 'toast') bannerStyle = 'position:absolute;max-width:300px;background:' + bg + ';color:' + fg + ';border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,.18);padding:14px 16px;' + (posMap[pos] || posMap.bottom_right);
    else if (style === 'top_bar') bannerStyle = 'position:absolute;top:0;left:0;right:0;background:' + bg + ';color:' + fg + ';padding:10px 16px;display:flex;align-items:center;justify-content:space-between;';
    else bannerStyle = 'position:absolute;bottom:0;left:0;right:0;background:' + bg + ';color:' + fg + ';padding:10px 16px;display:flex;align-items:center;justify-content:space-between;';
    var descHtml = description ? '<div style="font-size:' + (isBar ? '11px' : '12px') + ';opacity:.85;margin-top:4px;text-align:' + descAlign + ';">' + escHtml(description) + '</div>' : '';
    var ctaHtml  = (!isBar && ctaText) ? '<a href="#" style="display:inline-block;margin-top:10px;padding:6px 14px;background:' + cBtn + ';color:#fff;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;">' + escHtml(ctaText) + '</a>' : '';
    var countdownHtml = '';
    if (countdownOn && countdownAt) {
        var diff = new Date(countdownAt).getTime() - Date.now();
        if (diff > 0) {
            var totalSec = Math.floor(diff / 1000);
            var d = Math.floor(totalSec / 86400), h = Math.floor((totalSec % 86400) / 3600), m = Math.floor((totalSec % 3600) / 60), s = totalSec % 60;
            var pad2 = function (n) { return (n < 10 ? '0' : '') + n; };
            var box = function (num, lbl) { return '<div style="border:1px solid currentColor;opacity:.9;border-radius:6px;padding:3px 6px;min-width:28px;text-align:center;flex-shrink:0;"><div style="font-size:13px;font-weight:700;line-height:1.2;">' + num + '</div><div style="font-size:8px;text-transform:uppercase;opacity:.75;margin-top:2px;">' + lbl + '</div></div>'; };
            countdownHtml = '<div style="display:flex;gap:6px;flex-shrink:0;">' + box(pad2(d),'dias') + box(pad2(h),'hs') + box(pad2(m),'min') + box(pad2(s),'seg') + '</div>';
        }
    }
    // O alinhamento do título move o bloco (título+descrição+contador) inteiro;
    // o botão (bar) fica sempre fixo no extremo direito, fora do bloco alinhável.
    var groupJustify = titleAlign === 'center' ? 'center' : (titleAlign === 'right' ? 'flex-end' : 'flex-start');
    var html = '<div style="' + bannerStyle + '">';
    if (isBar) {
        html += '<div style="display:flex;align-items:center;flex:1;min-width:0;justify-content:' + groupJustify + ';">' +
                    '<div style="display:flex;align-items:center;gap:12px;min-width:0;">' +
                        '<div style="min-width:0;">' +
                            '<span style="font-size:13px;font-weight:600;text-align:' + titleAlign + ';display:block;">' + escHtml(copy) + '</span>' +
                            descHtml +
                        '</div>' +
                        countdownHtml +
                    '</div>' +
                '</div>' +
                '<div style="display:flex;align-items:center;gap:12px;flex-shrink:0;margin-left:12px;">';
        if (ctaText) html += '<a href="#" style="padding:4px 12px;background:' + cBtn + ';color:#fff;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;flex-shrink:0;">' + escHtml(ctaText) + '</a>';
        html += '<span style="cursor:pointer;font-size:18px;opacity:.7;flex-shrink:0;">&times;</span></div>';
    } else {
        html += '<div style="display:flex;align-items:center;gap:12px;padding-right:20px;justify-content:' + groupJustify + ';">' +
                    '<div style="display:flex;align-items:center;gap:12px;min-width:0;">' +
                        '<div style="min-width:0;">' +
                            '<div style="font-size:13px;font-weight:600;text-align:' + titleAlign + ';">' + escHtml(copy) + '</div>' +
                            descHtml +
                        '</div>' +
                        countdownHtml +
                    '</div>' +
                '</div>' + ctaHtml;
        html += '<div style="position:absolute;top:8px;right:10px;cursor:pointer;font-size:16px;opacity:.6;">&times;</div>';
    }
    html += '</div>';
    document.getElementById('nv-preview-frame').innerHTML = html;
    document.getElementById('nv-preview-overlay').style.display = 'flex';
}
function escHtml(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function darken(hex) {
    var r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
    if (isNaN(r)) return '#5b45d4';
    r = Math.max(0, r-30); g = Math.max(0, g-30); b = Math.max(0, b-30);
    return '#' + [r,g,b].map(function(x){return x.toString(16).padStart(2,'0');}).join('');
}

// ── Segmentação ──
var nvSegRuleIdx = 0;
var nvSegAttrs = { canonical: [], discovered: [] };
var nvSegReachTimer = null;
var nvSegPreviewPage = 1;

// Operadores por tipo
var nvOperatorLabels = {
    equals: 'igual a', not_equals: 'diferente de',
    contains: 'contém', starts_with: 'começa com', ends_with: 'termina com',
    greater_than: 'maior que', less_than: 'menor que',
    before: 'anterior a', after: 'posterior a',
    in: 'está em', not_in: 'não está em',
    exists: 'existe', not_exists: 'não existe'
};
var nvOperatorsByType = {
    string:  ['equals','not_equals','contains','starts_with','ends_with','in','not_in','exists','not_exists'],
    number:  ['equals','not_equals','greater_than','less_than','in','not_in','exists','not_exists'],
    boolean: ['equals','exists','not_exists'],
    date:    ['equals','before','after','exists','not_exists'],
    array:   ['in','not_in','exists','not_exists'],
    unknown: Object.keys(nvOperatorLabels)
};

function nvSegToggle(segmented) {
    document.getElementById('seg_enabled_input').value = segmented ? '1' : '0';
    document.getElementById('seg-card-all').classList.toggle('selected', !segmented);
    document.getElementById('seg-card-rules').classList.toggle('selected', segmented);
    document.querySelector('#seg-card-all input').checked = !segmented;
    document.querySelector('#seg-card-rules input').checked = segmented;
    document.getElementById('seg-rules-panel').style.display = segmented ? '' : 'none';
    if (segmented && nvSegAttrs.canonical.length === 0) {
        nvSegLoadAttrs();
    }
}

function nvSegLoadAttrs() {
    fetch('/api/admin/attributes/discovery', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        nvSegAttrs.canonical   = data.canonical || [];
        nvSegAttrs.discovered  = data.discovered || [];
        nvSegAttrs.total30d    = data.total_users_30d || 0;
        nvSegUpdateDatalist();
    })
    .catch(function() {});
}

function nvSegUpdateDatalist() {
    var dl = document.getElementById('seg-attr-datalist');
    dl.innerHTML = '';
    nvSegAttrs.canonical.forEach(function(a) {
        var opt = document.createElement('option');
        opt.value = a.path;
        opt.label = 'Novidda — ' + a.path;
        dl.appendChild(opt);
    });
    nvSegAttrs.discovered.forEach(function(a) {
        var opt = document.createElement('option');
        opt.value = a.path;
        opt.label = a.path + ' (' + a.coverage_pct + '%)';
        dl.appendChild(opt);
    });
}

function nvSegGetAttrType(attrPath) {
    var found = nvSegAttrs.canonical.find(function(a) { return a.path === attrPath; });
    if (!found) found = nvSegAttrs.discovered.find(function(a) { return a.path === attrPath; });
    return found ? found.type : 'unknown';
}

function nvSegAddRule(existingAttr, existingOp, existingVal) {
    var list  = document.getElementById('seg-rules-list');
    var empty = document.getElementById('seg-rules-empty');
    var i = nvSegRuleIdx++;
    var type = existingAttr ? nvSegGetAttrType(existingAttr) : 'unknown';
    var ops = nvOperatorsByType[type] || nvOperatorsByType.unknown;

    var opsHtml = ops.map(function(op) {
        return '<option value="' + op + '"' + (op === existingOp ? ' selected' : '') + '>' + nvOperatorLabels[op] + '</option>';
    }).join('');

    var noVal = existingOp === 'exists' || existingOp === 'not_exists';

    var div = document.createElement('div');
    div.className = 'seg-rule-row';
    div.dataset.ruleIdx = i;
    div.innerHTML =
        '<input class="input seg-attr-input" list="seg-attr-datalist"' +
            ' name="segment_rules[' + i + '][attribute]"' +
            ' placeholder="Atributo (ex: plan)" style="min-width:0;"' +
            ' value="' + escHtml(existingAttr || '') + '"' +
            ' oninput="nvSegAttrChange(this)">' +
        '<select class="select seg-op-select" name="segment_rules[' + i + '][operator]"' +
            ' onchange="nvSegOpChange(this)" style="min-width:0;">' +
            opsHtml +
        '</select>' +
        '<input class="input seg-val-input" name="segment_rules[' + i + '][value]"' +
            ' placeholder="Valor" style="min-width:0;"' +
            ' value="' + escHtml(existingVal || '') + '"' +
            (noVal ? ' style="display:none;min-width:0;"' : '') + '>' +
        '<button type="button" class="btn btn-sm btn-danger btn-icon seg-rule-remove"' +
            ' onclick="nvSegRemoveRule(this)" title="Remover">' +
            '<i class="fa-solid fa-trash"></i>' +
        '</button>';

    if (noVal) {
        div.querySelector('.seg-val-input').style.display = 'none';
    }

    list.appendChild(div);
    empty.style.display = 'none';
    if (!existingAttr) div.querySelector('.seg-attr-input').focus();

    nvSegReindexRules();
    nvSegScheduleReach();
}

function nvSegRemoveRule(btn) {
    btn.closest('.seg-rule-row').remove();
    var list = document.getElementById('seg-rules-list');
    var empty = document.getElementById('seg-rules-empty');
    empty.style.display = list.children.length === 0 ? '' : 'none';
    nvSegReindexRules();
    nvSegScheduleReach();
}

function nvSegReindexRules() {
    var rows = document.querySelectorAll('#seg-rules-list .seg-rule-row');
    rows.forEach(function(row, i) {
        row.querySelectorAll('[name]').forEach(function(el) {
            el.name = el.name.replace(/segment_rules\[\d+\]/, 'segment_rules[' + i + ']');
        });
    });
}

function nvSegAttrChange(input) {
    var row  = input.closest('.seg-rule-row');
    var sel  = row.querySelector('.seg-op-select');
    var type = nvSegGetAttrType(input.value);
    var ops  = nvOperatorsByType[type] || nvOperatorsByType.unknown;
    var cur  = sel.value;
    sel.innerHTML = ops.map(function(op) {
        return '<option value="' + op + '"' + (op === cur ? ' selected' : '') + '>' + nvOperatorLabels[op] + '</option>';
    }).join('');
    nvSegOpChange(sel);
    nvSegScheduleReach();
}

function nvSegOpChange(sel) {
    var row  = sel.closest('.seg-rule-row');
    var val  = row.querySelector('.seg-val-input');
    var noVal = sel.value === 'exists' || sel.value === 'not_exists';
    val.style.display = noVal ? 'none' : '';
    if (noVal) val.value = '';
    nvSegScheduleReach();
}

function nvSegScheduleReach() {
    clearTimeout(nvSegReachTimer);
    nvSegReachTimer = setTimeout(nvSegRefreshReach, 300);
}

function nvSegCollectRules() {
    var rules = [];
    document.querySelectorAll('#seg-rules-list .seg-rule-row').forEach(function(row) {
        var attr = row.querySelector('.seg-attr-input').value.trim();
        var op   = row.querySelector('.seg-op-select').value;
        var val  = row.querySelector('.seg-val-input').value.trim();
        if (attr && op) {
            rules.push({ attribute: attr, operator: op, value: val || null });
        }
    });
    return rules;
}

function nvSegRefreshReach() {
    var reachWidget = document.getElementById('seg-reach-widget');
    if (!reachWidget) return;
    var rules = nvSegCollectRules();
    var loading = document.getElementById('seg-reach-loading');
    loading.style.display = '';

    fetch('{{ $changelog->exists ? route('segmentation.estimate-reach', $changelog) : '#' }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
        },
        body: JSON.stringify({ rules: rules })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        loading.style.display = 'none';
        document.getElementById('seg-reach-label').textContent =
            data.matched + ' de ' + data.total + ' usuários ativos';
        document.getElementById('seg-reach-pct').textContent = '(' + data.percentage + '%)';
        document.getElementById('seg-reach-bar').style.width = data.percentage + '%';
    })
    .catch(function() { loading.style.display = 'none'; });
}

function nvSegPreview() {
    nvSegPreviewPage = 1;
    nvSegLoadPreview(1);
}

function nvSegLoadPreview(page) {
    var overlay = document.getElementById('seg-preview-overlay');
    overlay.style.display = 'flex';
    document.getElementById('seg-preview-body').innerHTML = '<p style="color:var(--mute);font-size:13px;">Carregando…</p>';

    var rules = nvSegCollectRules();
    fetch('{{ $changelog->exists ? route('segmentation.preview-audience', $changelog) : '#' }}?page=' + page, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
        },
        body: JSON.stringify({ rules: rules })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('seg-preview-count').textContent =
            data.total + ' usuário(s) atendem aos critérios';

        if (data.items.length === 0) {
            document.getElementById('seg-preview-body').innerHTML =
                '<p style="color:var(--mute);font-size:13px;">Nenhum usuário encontrado.</p>';
        } else {
            var rows = data.items.map(function(u) {
                var snap = u.snapshot || {};
                return '<tr><td style="font-family:monospace;font-size:12px;white-space:nowrap;">' + escHtml(u.reader_id) + '</td>' +
                    '<td style="font-size:12px;">' + escHtml(snap.plan || '—') + '</td>' +
                    '<td style="font-size:12px;">' + escHtml(snap.role || '—') + '</td>' +
                    '<td style="font-size:12px;color:var(--mute);white-space:nowrap;">' + escHtml(u.last_seen || '—') + '</td></tr>';
            }).join('');
            document.getElementById('seg-preview-body').innerHTML =
                '<table class="data-table"><thead><tr><th>Reader ID</th><th>Plan</th><th>Role</th><th>Último acesso</th></tr></thead><tbody>' + rows + '</tbody></table>';
        }

        // Paginação
        var pag = document.getElementById('seg-preview-pagination');
        pag.innerHTML = '';
        if (data.last_page > 1) {
            if (data.current_page > 1) {
                var prev = document.createElement('button');
                prev.type = 'button'; prev.className = 'btn btn-sm';
                prev.textContent = '‹ Anterior';
                prev.onclick = function() { nvSegLoadPreview(data.current_page - 1); };
                pag.appendChild(prev);
            }
            var info = document.createElement('span');
            info.style.cssText = 'font-size:13px;color:var(--mute);';
            info.textContent = data.current_page + ' / ' + data.last_page;
            pag.appendChild(info);
            if (data.current_page < data.last_page) {
                var next = document.createElement('button');
                next.type = 'button'; next.className = 'btn btn-sm';
                next.textContent = 'Próxima ›';
                next.onclick = function() { nvSegLoadPreview(data.current_page + 1); };
                pag.appendChild(next);
            }
        }
    })
    .catch(function() {
        document.getElementById('seg-preview-body').innerHTML =
            '<p style="color:var(--mute);font-size:13px;">Erro ao carregar dados.</p>';
    });
}

// Inicialização
(function () {
    // Banner
    var styleEl = document.getElementById('banner_style');
    if (styleEl) nvBannerStyleChange(styleEl);
    var freqEl = document.getElementById('banner_frequency');
    if (freqEl) nvBannerFreqChange(freqEl);
    ['banner_bg_color','banner_text_color','banner_cta_color'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el && el.value) syncColorPicker(id, id + '_picker');
    });

    // Segmentação — carrega atributos e popula regras existentes
    var segEnabled = document.getElementById('seg_enabled_input').value === '1';
    if (segEnabled) {
        nvSegLoadAttrs();
    }

    @foreach ($existingSegRules as $rule)
    nvSegAddRule(
        {{ json_encode($rule['attribute']) }},
        {{ json_encode($rule['operator']) }},
        {{ json_encode($rule['value']) }}
    );
    @endforeach

    @if ($changelog->exists && $segEnabled)
    // Calcula alcance inicial
    setTimeout(nvSegRefreshReach, 400);
    @endif
})();
</script>
@endpush
