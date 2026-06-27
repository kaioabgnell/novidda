@extends('layouts.app')
@section('title', $item->exists ? 'Editar item de roadmap' : 'Novo item de roadmap')

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
        .form-grid { display: grid; grid-template-columns: 2fr 1fr; gap: var(--gap); }
        @media (max-width: 880px) { .form-grid { grid-template-columns: 1fr; } }
        .section-label {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; color: var(--mute);
            margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--canvas);
        }
        body.dark .section-label { border-color: rgba(123,97,255,.1); }
        .checkbox-row { display: flex; align-items: center; gap: 8px; padding: 8px 0; }
        .checkbox-row input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--primary); cursor: pointer; flex-shrink: 0; }
        .checkbox-row label { font-size: 14px; font-weight: 500; color: var(--ink); cursor: pointer; margin: 0; }
        /* Tabs */
        .tabs-nav { display: flex; gap: 4px; border-bottom: 2px solid var(--canvas); margin-bottom: 20px; }
        body.dark .tabs-nav { border-color: rgba(123,97,255,.15); }
        .tab-btn { padding: 9px 18px; font-size: 13px; font-weight: 600; color: var(--mute); background: none; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; cursor: pointer; border-radius: var(--r-sm) var(--r-sm) 0 0; transition: color .15s, border-color .15s; }
        .tab-btn:hover { color: var(--ink); }
        .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .data-table th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--mute); padding: 8px 12px; border-bottom: 1px solid var(--canvas); }
        body.dark .data-table th { border-color: rgba(123,97,255,.1); }
        .data-table td { padding: 10px 12px; vertical-align: top; color: var(--ink); }
        .data-table tr + tr td { border-top: 1px solid var(--canvas); }
        body.dark .data-table tr + tr td { border-color: rgba(123,97,255,.07); }
        .data-table .muted { color: var(--mute); font-size: 12px; }
        .badge { display: inline-block; padding: 2px 8px; font-size: 11px; font-weight: 600; border-radius: 99px; }
        .badge-pending  { background: rgba(255,193,7,.15);  color: #b8860b; }
        .badge-approved { background: rgba(40,167,69,.12);  color: #1e7e34; }
        .badge-rejected { background: rgba(220,53,69,.12);  color: #c0392b; }
        body.dark .badge-pending  { background: rgba(255,193,7,.2);  color: #ffc107; }
        body.dark .badge-approved { background: rgba(40,167,69,.2);  color: #28a745; }
        body.dark .badge-rejected { background: rgba(220,53,69,.2);  color: #dc3545; }
        .score-icon { font-size: 18px; }
    </style>
@endpush

@section('content')
<form id="roadmap-form" method="POST"
      action="{{ $item->exists ? route('roadmap.update', $item) : route('roadmap.store') }}">
    @csrf
    @if ($item->exists) @method('PUT') @endif

    <div class="form-grid">

        {{-- Coluna principal --}}
        <div style="display:flex;flex-direction:column;gap:var(--gap);">
            <div class="card">
                <div class="section-label">Conteúdo</div>

                <div class="field">
                    <label for="title">Título</label>
                    <input class="input" id="title" name="title"
                           value="{{ old('title', $item->title) }}" required
                           placeholder="Ex.: Integração com Slack">
                </div>

                <div class="field" style="margin-bottom:0;">
                    <label>Descrição</label>
                    <div id="editor">{!! old('description', $item->description) !!}</div>
                    <input type="hidden" name="description" id="description">
                </div>
            </div>
        </div>

        {{-- Coluna lateral --}}
        <div style="display:flex;flex-direction:column;gap:var(--gap);">
            <div class="card">
                <div class="section-label">Publicação</div>

                <div class="field">
                    <label for="status">Andamento</label>
                    <select class="select" id="status" name="status">
                        @foreach (['analyzing' => 'Em análise', 'developing' => 'Em desenvolvimento'] as $k => $lbl)
                            <option value="{{ $k }}" @selected(old('status', $item->status) === $k)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="published_at">Data de publicação</label>
                    <input class="input" type="datetime-local" id="published_at" name="published_at"
                           value="{{ old('published_at', $item->published_at?->format('Y-m-d\TH:i')) }}">
                    <p style="font-size:12px;color:var(--mute);margin:6px 0 0;">Deixe em branco para publicar imediatamente.</p>
                </div>

                <div class="checkbox-row" style="margin-bottom:0;">
                    <input type="checkbox" name="feedback_enabled" value="1"
                           id="feedback_enabled"
                           @checked(old('feedback_enabled', $item->feedback_enabled ?? true))>
                    <label for="feedback_enabled">Habilitar feedback (😢 😐 😊)</label>
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-sm mt">
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Salvar
        </button>
        <a href="{{ route('roadmap.index') }}" class="btn">Cancelar</a>
    </div>
</form>

@if ($item->exists)
<div class="card" style="margin-top:var(--gap);">
    <div class="tabs-nav">
        <button class="tab-btn active" data-tab="tab-comments">
            Comentários
            @if ($comments->count()) <span style="margin-left:4px;opacity:.6;">({{ $comments->count() }})</span> @endif
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
    document.getElementById('description').value = quill.root.innerHTML;
    quill.on('text-change', function () {
        document.getElementById('description').value = quill.root.innerHTML;
    });
    document.getElementById('roadmap-form').addEventListener('submit', function () {
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
