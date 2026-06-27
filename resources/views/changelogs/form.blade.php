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
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--mute);
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--canvas);
        }
        body.dark .section-label { border-color: rgba(123,97,255,.1); }
        .checkbox-row {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 0;
        }
        .checkbox-row input[type=checkbox] {
            width: 16px; height: 16px;
            accent-color: var(--primary);
            cursor: pointer; flex-shrink: 0;
        }
        .checkbox-row label {
            font-size: 14px; font-weight: 500;
            color: var(--ink); cursor: pointer; margin: 0;
        }
        /* Tabs */
        .tabs-nav {
            display: flex; gap: 4px;
            border-bottom: 2px solid var(--canvas);
            margin-bottom: 20px;
        }
        body.dark .tabs-nav { border-color: rgba(123,97,255,.15); }
        .tab-btn {
            padding: 9px 18px;
            font-size: 13px; font-weight: 600;
            color: var(--mute);
            background: none; border: none;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            cursor: pointer; border-radius: var(--r-sm) var(--r-sm) 0 0;
            transition: color .15s, border-color .15s;
        }
        .tab-btn:hover { color: var(--ink); }
        .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
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
    </style>
@endpush

@section('content')
<form id="changelog-form" method="POST"
      action="{{ $changelog->exists ? route('changelogs.update', $changelog) : route('changelogs.store') }}"
      enctype="multipart/form-data">
    @csrf
    @if ($changelog->exists) @method('PUT') @endif
    @php $ws = $changelog->widgetSettings; @endphp

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

            <div class="card">
                <div class="section-label">Widget</div>

                <div class="checkbox-row">
                    <input type="checkbox" name="show_reactions" value="1"
                           id="show_reactions"
                           @checked(old('show_reactions', $ws->show_reactions ?? true))>
                    <label for="show_reactions">Mostrar reações</label>
                </div>
                <div class="checkbox-row">
                    <input type="checkbox" name="show_comments" value="1"
                           id="show_comments"
                           @checked(old('show_comments', $ws->show_comments ?? true))>
                    <label for="show_comments">Mostrar comentários</label>
                </div>
                <div class="checkbox-row">
                    <input type="checkbox" name="allow_comments" value="1"
                           id="allow_comments"
                           @checked(old('allow_comments', $ws->allow_comments ?? true))>
                    <label for="allow_comments">Permitir novos comentários</label>
                </div>
                <div class="checkbox-row" style="margin-bottom:16px;">
                    <input type="checkbox" name="feedback_enabled" value="1"
                           id="feedback_enabled"
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
                    <input type="checkbox" name="cta_new_tab" value="1"
                           id="cta_new_tab"
                           @checked(old('cta_new_tab', $ws->cta_new_tab ?? true))>
                    <label for="cta_new_tab">Abrir em nova aba</label>
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-sm mt">
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Salvar
        </button>
        <a href="{{ route('changelogs.index') }}" class="btn">Cancelar</a>
    </div>
</form>

@if ($changelog->exists)
<div class="card" style="margin-top:var(--gap);">
    <div class="tabs-nav">
        <button class="tab-btn active" data-tab="tab-comments">
            Comentários
            @if ($comments->count()) <span style="margin-left:4px;opacity:.6;">({{ $comments->count() }})</span> @endif
        </button>
        <button class="tab-btn" data-tab="tab-reactions">
            Reações
            @if ($reactions->sum('total')) <span style="margin-left:4px;opacity:.6;">({{ $reactions->sum('total') }})</span> @endif
        </button>
        <button class="tab-btn" data-tab="tab-feedbacks">
            Feedbacks
            @if ($feedbacks->count()) <span style="margin-left:4px;opacity:.6;">({{ $feedbacks->count() }})</span> @endif
        </button>
    </div>

    {{-- Tab: Comentários --}}
    <div id="tab-comments" class="tab-pane active">
        @if ($comments->isEmpty())
            <p style="color:var(--mute);font-size:13px;margin:0;">Nenhum comentário ainda.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Autor</th>
                        <th>Mensagem</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
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

    {{-- Tab: Reações --}}
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

    {{-- Tab: Feedbacks --}}
    <div id="tab-feedbacks" class="tab-pane">
        @if ($feedbacks->isEmpty())
            <p style="color:var(--mute);font-size:13px;margin:0;">Nenhum feedback ainda.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Avaliação</th>
                        <th>Comentário</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($feedbacks as $f)
                        <tr>
                            <td style="white-space:nowrap;">
                                <span class="score-icon">
                                    {{ match($f->score) { 'sad' => '😢', 'neutral' => '😐', 'happy' => '😊' } }}
                                </span>
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
    // Pre-popula no carregamento (modo edição: usuário pode não tocar na descrição)
    document.getElementById('description').value = quill.root.innerHTML;

    quill.on('text-change', function () {
        document.getElementById('description').value = quill.root.innerHTML;
    });
    document.getElementById('changelog-form').addEventListener('submit', function () {
        document.getElementById('description').value = quill.root.innerHTML;
    });

    document.querySelectorAll('.tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
            document.querySelectorAll('.tab-pane').forEach(function (p) { p.classList.remove('active'); });
            btn.classList.add('active');
            document.getElementById(btn.dataset.tab).classList.add('active');
        });
    });
</script>
@endpush
