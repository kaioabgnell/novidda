@extends('layouts.app')
@section('title', 'Instalação & Documentação')

@push('head')
<style>
    /* ── Layout da página ── */
    .doc-wrap { max-width: 860px; }

    /* ── Hero ── */
    .doc-hero {
        display: flex; align-items: flex-start; gap: 16px; margin-bottom: 28px;
    }
    .doc-hero-icon {
        width: 48px; height: 48px; flex-shrink: 0;
        background: var(--primary-pale); color: var(--primary);
        border-radius: var(--r-md); display: flex; align-items: center; justify-content: center;
        font-size: 20px;
    }

    /* ── Tabs ── */
    .tabs-nav {
        display: flex; gap: 4px;
        border-bottom: 2px solid var(--canvas);
        margin-bottom: 28px;
    }
    body.dark .tabs-nav { border-color: rgba(123,97,255,.15); }
    .tab-btn {
        padding: 9px 18px; font-size: 13px; font-weight: 600;
        color: var(--mute); background: none; border: none;
        border-bottom: 2px solid transparent; margin-bottom: -2px;
        cursor: pointer; border-radius: var(--r-md) var(--r-md) 0 0;
        transition: color .15s, border-color .15s;
    }
    .tab-btn:hover { color: var(--ink); }
    .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
    .tab-pane { display: none; }
    .tab-pane.active { display: block; }

    /* ── Blocos de código com cabeçalho ── */
    .code-card {
        border: 1px solid var(--primary-pale);
        border-radius: var(--r-md); overflow: hidden; margin-bottom: 20px;
    }
    body.dark .code-card { border-color: rgba(123,97,255,.2); }
    .code-card-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 16px;
        background: var(--primary-pale); border-bottom: 1px solid var(--primary-pale);
    }
    body.dark .code-card-header { background: rgba(123,97,255,.12); border-color: rgba(123,97,255,.2); }
    .code-card-title {
        font-size: 12px; font-weight: 700; letter-spacing: .05em;
        text-transform: uppercase; color: var(--primary);
        display: flex; align-items: center; gap: 8px;
    }
    .code-card pre {
        margin: 0; padding: 20px;
        font-family: ui-monospace, 'Fira Code', 'Cascadia Code', monospace;
        font-size: 12.5px; line-height: 1.75;
        color: var(--ink);
        background: var(--canvas-soft);
        overflow-x: auto; white-space: pre;
    }
    body.dark .code-card pre { background: var(--canvas-soft); }

    /* Syntax highlighting manual */
    .hl-comment { color: #8B8BA0; font-style: italic; }
    .hl-tag     { color: #7B61FF; }
    .hl-attr    { color: #00B4D8; }
    .hl-str     { color: #43aa8b; }
    .hl-kw      { color: #f4845f; }
    .hl-fn      { color: #9d8fff; }
    .hl-num     { color: #f4845f; }
    .hl-var     { color: var(--ink); }
    body.dark .hl-str { color: #6ee7b7; }
    body.dark .hl-tag { color: #a78bfa; }

    /* ── Tabela de atributos ── */
    .attr-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 20px; }
    .attr-table thead th {
        text-align: left; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .05em;
        color: var(--mute); padding: 8px 12px;
        border-bottom: 2px solid var(--canvas);
    }
    body.dark .attr-table thead th { border-color: rgba(123,97,255,.15); }
    .attr-table tbody td { padding: 10px 12px; vertical-align: top; color: var(--ink); }
    .attr-table tbody tr + tr td { border-top: 1px solid var(--canvas); }
    body.dark .attr-table tbody tr + tr td { border-color: rgba(123,97,255,.07); }
    .attr-table code {
        font-family: ui-monospace, monospace; font-size: 12px;
        background: var(--primary-pale); color: var(--primary);
        border-radius: 4px; padding: 2px 6px;
    }
    body.dark .attr-table code { background: rgba(123,97,255,.15); }
    .type-badge {
        display: inline-block; padding: 2px 8px; border-radius: 99px;
        font-size: 11px; font-weight: 700;
    }
    .type-string  { background: rgba(0,180,216,.12); color: #0096c7; }
    .type-number  { background: rgba(244,132,95,.12); color: #d4633a; }
    .type-boolean { background: rgba(67,170,139,.12); color: #2d9b6f; }
    .type-date    { background: rgba(123,97,255,.12); color: #7B61FF; }
    .type-object  { background: rgba(139,139,160,.12); color: #6b6b80; }
    .type-any     { background: rgba(139,139,160,.1);  color: #8B8BA0; }
    body.dark .type-string  { background: rgba(0,180,216,.18); color: #48cae4; }
    body.dark .type-number  { background: rgba(244,132,95,.18); color: #f4845f; }
    body.dark .type-boolean { background: rgba(67,170,139,.18); color: #6ee7b7; }
    body.dark .type-date    { background: rgba(123,97,255,.18); color: #a78bfa; }

    /* ── Info boxes ── */
    .info-box {
        display: flex; gap: 12px; align-items: flex-start;
        padding: 14px 16px; border-radius: var(--r-md); margin-bottom: 20px;
        font-size: 13px; line-height: 1.6;
    }
    .info-box-icon { flex-shrink: 0; margin-top: 1px; font-size: 15px; }
    .info-tip  { background: rgba(123,97,255,.08); }
    .info-warn { background: rgba(244,132,95,.09); }
    .info-ok   { background: rgba(67,170,139,.09); }
    body.dark .info-tip  { background: rgba(123,97,255,.14); }
    body.dark .info-warn { background: rgba(244,132,95,.14); }
    body.dark .info-ok   { background: rgba(67,170,139,.14); }
    .info-tip  .info-box-icon { color: var(--primary); }
    .info-warn .info-box-icon { color: #f4845f; }
    .info-ok   .info-box-icon { color: #43aa8b; }

    /* ── Token card ── */
    .token-row {
        display: flex; align-items: center; justify-content: space-between;
        gap: 16px; padding: 14px 18px;
        background: var(--canvas); border-radius: var(--r-md);
    }
    .token-row code {
        font-family: ui-monospace, monospace; font-size: 13px;
        color: var(--ink); word-break: break-all;
    }

    /* ── Seção label ── */
    .section-label {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: var(--mute);
        margin: 0 0 14px; padding-bottom: 8px;
        border-bottom: 1px solid var(--canvas);
    }
    body.dark .section-label { border-color: rgba(123,97,255,.1); }

    /* ── Breadcrumb steps ── */
    .steps { counter-reset: step; display: flex; flex-direction: column; gap: 20px; }
    .step  { display: flex; gap: 14px; }
    .step-num {
        width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
        background: var(--primary); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700;
    }
    .step-body { flex: 1; min-width: 0; }
    .step-title { font-size: 14px; font-weight: 700; color: var(--ink); margin-bottom: 6px; }
    .step-desc  { font-size: 13px; color: var(--mute); line-height: 1.6; }

    /* ── Copy button ── */
    .copy-btn {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 600; cursor: pointer;
        color: var(--primary); background: none; border: none; padding: 4px 8px;
        border-radius: 6px; transition: background .15s;
    }
    .copy-btn:hover { background: var(--primary-pale); }
    body.dark .copy-btn:hover { background: rgba(123,97,255,.15); }
    .copy-ok { color: #43aa8b !important; }
</style>
@endpush

@section('content')
<div class="doc-wrap">

    {{-- Hero --}}
    <div class="doc-hero">
        <div class="doc-hero-icon"><i class="fa-solid fa-code"></i></div>
        <div>
            <h3 style="font-size:20px;margin-bottom:4px;">Instalação & Documentação</h3>
            <p style="margin:0;font-size:14px;color:var(--mute);line-height:1.6;">
                Tudo que você precisa para instalar o widget, identificar usuários e ativar a segmentação de audiência.
            </p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tabs-nav">
        <button class="tab-btn active" data-tab="tab-install" data-tab-group="doc">
            <i class="fa-solid fa-download" style="font-size:11px;"></i> Instalação
        </button>
        <button class="tab-btn" data-tab="tab-attrs" data-tab-group="doc">
            <i class="fa-solid fa-user-tag" style="font-size:11px;"></i> Atributos
        </button>
        <button class="tab-btn" data-tab="tab-php" data-tab-group="doc">
            <i class="fa-brands fa-php" style="font-size:13px;"></i> PHP / Laravel
        </button>
    </div>

    {{-- ══════════════════════════════════════════
         ABA: INSTALAÇÃO
    ══════════════════════════════════════════ --}}
    <div id="tab-install" class="tab-pane active">

        {{-- Aviso obrigatório para SaaS com login --}}
        <div class="info-box info-warn" style="margin-bottom:var(--gap);align-items:flex-start;">
            <span class="info-box-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <div>
                <strong>Usa um SaaS/sistema com login de usuário? A identificação é obrigatória.</strong><br>
                Para que cada usuário veja as novidades de forma individual — e o contador de não-lidos
                seja <strong>por pessoa</strong>, e não por navegador — você <strong>precisa</strong> informar o usuário
                logado via <code>window.noviddaConfig</code>. Sem isso, usuários diferentes no mesmo navegador
                compartilham o mesmo estado de leitura (um zera o contador do outro, e usuários novos aparecem
                com tudo já lido).
                <div class="code-card" style="margin-top:12px;">
                    <div class="code-card-header">
                        <div class="code-card-title"><i class="fa-solid fa-key"></i> Estrutura obrigatória</div>
                        <button class="copy-btn" onclick="nvCopy(this, 'snippet-required')">
                            <i class="fa-solid fa-copy"></i> Copiar
                        </button>
                    </div>
<pre id="snippet-required"><span class="hl-kw">window</span>.noviddaConfig = {
  <span class="hl-attr">token</span>: <span class="hl-str">'{{ $token }}'</span>,
  <span class="hl-attr">user</span>: {
    <span class="hl-attr">id</span>:    <span class="hl-tag">&lt;?=</span> <span class="hl-var">$usuarioLogado</span>-><span class="hl-attr">id</span> <span class="hl-tag">?&gt;</span>,     <span class="hl-comment">// id real do usuário logado (obrigatório)</span>
    <span class="hl-attr">email</span>: <span class="hl-str">'<span class="hl-tag">&lt;?=</span> <span class="hl-var">$usuarioLogado</span>-><span class="hl-attr">email</span> <span class="hl-tag">?&gt;</span>'</span>,
    <span class="hl-attr">name</span>:  <span class="hl-str">'<span class="hl-tag">&lt;?=</span> <span class="hl-var">$usuarioLogado</span>-><span class="hl-attr">name</span> <span class="hl-tag">?&gt;</span>'</span>
  }
};</pre>
                </div>
                <span style="display:block;margin-top:10px;font-size:12.5px;">
                    <i class="fa-solid fa-circle-info" style="margin-right:4px;"></i>
                    O <code>user.id</code> precisa ser <strong>dinâmico</strong> (o id de quem está logado naquela sessão) —
                    nunca um valor fixo. Renderize-o no servidor a cada página. O <code>email</code> serve de
                    alternativa quando não houver id interno.
                </span>
            </div>
        </div>

        {{-- Passo 1 -- Instalação mínima --}}
        <div class="card" style="margin-bottom:var(--gap);">
            <div class="section-label">Início rápido</div>

            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <div class="step-body">
                        <div class="step-title">Cole o snippet antes de <code>&lt;/body&gt;</code></div>
                        <div class="step-desc">
                            O script carrega de forma assíncrona — não bloqueia o renderizamento da página.
                            Compatível com qualquer framework ou CMS.
                        </div>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div class="step-body">
                        <div class="step-title">Identifique o usuário logado <span style="color:#f4845f;">(obrigatório para SaaS com login)</span></div>
                        <div class="step-desc">
                            Passe os dados do usuário via <code>window.noviddaConfig.user</code>. É o que garante o
                            contador de não-lidos <strong>por usuário</strong> (e não por navegador), além de habilitar
                            segmentação de audiência, rastreabilidade e personalização.
                        </div>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <div class="step-body">
                        <div class="step-title">Pronto</div>
                        <div class="step-desc">
                            O widget aparece automaticamente no seu site. Nenhuma configuração adicional necessária.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Snippet mínimo --}}
        <div class="card" style="margin-bottom:var(--gap);">
            <div class="section-label">Modo anônimo — instalação mínima</div>

            <div class="info-box info-warn">
                <span class="info-box-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                <span>Use este snippet <strong>apenas em sites sem login</strong> (landing pages, sites públicos).
                Em um SaaS/sistema com login de usuário, <strong>não use este modo</strong>: o contador de não-lidos
                ficaria por navegador (compartilhado entre logins). Nesse caso, use a estrutura com
                <code>window.noviddaConfig.user</code> abaixo. Changelogs com <strong>regras de segmentação</strong>
                também não são exibidos para visitantes anônimos.</span>
            </div>

            <div class="code-card">
                <div class="code-card-header">
                    <div class="code-card-title">
                        <i class="fa-brands fa-html5"></i> HTML
                    </div>
                    <button class="copy-btn" onclick="nvCopy(this, 'snippet-min')">
                        <i class="fa-solid fa-copy"></i> Copiar
                    </button>
                </div>
                <pre id="snippet-min"><span class="hl-comment">&lt;!-- Cole antes do &lt;/body&gt; --&gt;</span>
<span class="hl-tag">&lt;script</span> <span class="hl-attr">src</span>=<span class="hl-str">"{{ url('widget.js') }}"</span>
        <span class="hl-attr">data-token</span>=<span class="hl-str">"{{ $token }}"</span>
        <span class="hl-attr">async</span><span class="hl-tag">&gt;&lt;/script&gt;</span></pre>
            </div>
        </div>

        {{-- Snippet completo com usuário --}}
        <div class="card" style="margin-bottom:var(--gap);">
            <div class="section-label">Com identificação de usuário — obrigatório para SaaS com login</div>

            <div class="info-box info-ok">
                <span class="info-box-icon"><i class="fa-solid fa-circle-check"></i></span>
                <span>Defina <code>window.noviddaConfig</code> <strong>antes</strong> do script do widget.
                O <code>user.id</code> é <strong>obrigatório</strong> em sistemas com login (é a chave da leitura por usuário);
                os demais campos são opcionais — passe apenas os disponíveis no seu sistema.</span>
            </div>

            <div class="code-card">
                <div class="code-card-header">
                    <div class="code-card-title">
                        <i class="fa-brands fa-js"></i> JavaScript + HTML
                    </div>
                    <button class="copy-btn" onclick="nvCopy(this, 'snippet-full')">
                        <i class="fa-solid fa-copy"></i> Copiar
                    </button>
                </div>
                <pre id="snippet-full"><span class="hl-comment">&lt;!-- 1. Configuração (antes do widget) --&gt;</span>
<span class="hl-tag">&lt;script&gt;</span>
  <span class="hl-kw">window</span>.noviddaConfig = {
    <span class="hl-attr">token</span>: <span class="hl-str">'{{ $token }}'</span>,  <span class="hl-comment">// obrigatório</span>

    <span class="hl-attr">user</span>: {             <span class="hl-comment">// opcional — omita se o visitante for anônimo</span>

      <span class="hl-comment">// ── Atributos canônicos ──</span>
      <span class="hl-attr">id</span>:         <span class="hl-str">'user_123'</span>,
      <span class="hl-attr">email</span>:      <span class="hl-str">'ana@empresa.com'</span>,
      <span class="hl-attr">name</span>:       <span class="hl-str">'Ana Silva'</span>,
      <span class="hl-attr">plan</span>:       <span class="hl-str">'pro'</span>,
      <span class="hl-attr">role</span>:       <span class="hl-str">'admin'</span>,
      <span class="hl-attr">created_at</span>: <span class="hl-str">'2024-03-15'</span>,

      <span class="hl-attr">company</span>: {
        <span class="hl-attr">id</span>:   <span class="hl-str">'company_789'</span>,
        <span class="hl-attr">name</span>: <span class="hl-str">'Operand'</span>,
        <span class="hl-attr">plan</span>: <span class="hl-str">'enterprise'</span>
      },

      <span class="hl-comment">// ── Atributos personalizados (qualquer chave) ──</span>
      <span class="hl-attr">attributes</span>: {
        <span class="hl-attr">industry</span>:            <span class="hl-str">'agência'</span>,
        <span class="hl-attr">employees</span>:           <span class="hl-num">87</span>,
        <span class="hl-attr">beta_program</span>:        <span class="hl-kw">true</span>,
        <span class="hl-attr">onboarding_completed</span>:<span class="hl-kw">true</span>,
        <span class="hl-attr">mrr</span>:                 <span class="hl-num">1500</span>,
        <span class="hl-attr">tags</span>:                [<span class="hl-str">'vip'</span>, <span class="hl-str">'early_adopter'</span>],
        <span class="hl-attr">last_purchase</span>:       <span class="hl-str">'2026-06-12'</span>
      }
    }
  };
<span class="hl-tag">&lt;/script&gt;</span>

<span class="hl-comment">&lt;!-- 2. Script do widget --&gt;</span>
<span class="hl-tag">&lt;script</span> <span class="hl-attr">src</span>=<span class="hl-str">"{{ url('widget.js') }}"</span> <span class="hl-attr">async</span><span class="hl-tag">&gt;&lt;/script&gt;</span></pre>
            </div>
        </div>

        {{-- O que a identificação desbloqueia --}}
        <div class="card">
            <div class="section-label">O que a identificação desbloqueia</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;">
                @foreach ([
                    ['fa-filter','Segmentação de audiência','Mostre changelogs apenas para usuários específicos (plano, papel, atributos).','var(--primary)','rgba(123,97,255,.08)'],
                    ['fa-bookmark','Histórico por usuário','Rastreia quais changelogs cada usuário já leu, mantendo o badge correto entre sessões.','#43aa8b','rgba(67,170,139,.08)'],
                    ['fa-comment','Comentários nominais','O nome do usuário aparece nos comentários em vez de "Anônimo".','#00B4D8','rgba(0,180,216,.08)'],
                    ['fa-chart-pie','Analytics segmentado','Métricas de visualização calculadas sobre a audiência elegível, não sobre o total.','#f4845f','rgba(244,132,95,.08)'],
                ] as [$icon,$title,$desc,$color,$bg])
                <div style="background:{{ $bg }};border-radius:var(--r-md);padding:16px;">
                    <div style="font-size:18px;color:{{ $color }};margin-bottom:8px;"><i class="fa-solid fa-{{ $icon }}"></i></div>
                    <div style="font-size:13px;font-weight:700;color:var(--ink);margin-bottom:4px;">{{ $title }}</div>
                    <div style="font-size:12px;color:var(--mute);line-height:1.5;">{{ $desc }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         ABA: ATRIBUTOS
    ══════════════════════════════════════════ --}}
    <div id="tab-attrs" class="tab-pane">

        {{-- Canônicos --}}
        <div class="card" style="margin-bottom:var(--gap);">
            <div class="section-label">Atributos canônicos — campos fixos do Novidda</div>

            <div class="info-box info-tip">
                <span class="info-box-icon"><i class="fa-solid fa-circle-info"></i></span>
                <span>Esses campos existem no nível raiz do objeto <code>user</code>.
                Todos são opcionais — passe apenas os que existem no seu sistema.
                O Novidda os trata de forma especial (leitura, comentários, segmentação).</span>
            </div>

            <div style="overflow-x:auto;">
                <table class="attr-table">
                    <thead>
                        <tr>
                            <th>Campo</th>
                            <th>Tipo</th>
                            <th>Formato</th>
                            <th>Para que serve</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>id</code></td>
                            <td><span class="type-badge type-string">string | number</span></td>
                            <td style="color:var(--mute);font-size:12px;">qualquer</td>
                            <td>Identifica o usuário entre sessões. Necessário para histórico de leitura, reações e comentários persistentes.</td>
                        </tr>
                        <tr>
                            <td><code>email</code></td>
                            <td><span class="type-badge type-string">string</span></td>
                            <td style="color:var(--mute);font-size:12px;">RFC 5321</td>
                            <td>Aparece na moderação de comentários. Permite contato futuro fora do widget.</td>
                        </tr>
                        <tr>
                            <td><code>name</code></td>
                            <td><span class="type-badge type-string">string</span></td>
                            <td style="color:var(--mute);font-size:12px;">qualquer</td>
                            <td>Substitui "Anônimo" nos comentários públicos.</td>
                        </tr>
                        <tr>
                            <td><code>plan</code></td>
                            <td><span class="type-badge type-string">string</span></td>
                            <td style="color:var(--mute);font-size:12px;"><code>free</code>, <code>pro</code>, <code>enterprise</code>…</td>
                            <td>Atributo de segmentação mais comum em SaaS. Padronizar a chave evita inconsistência entre integrações.</td>
                        </tr>
                        <tr>
                            <td><code>role</code></td>
                            <td><span class="type-badge type-string">string</span></td>
                            <td style="color:var(--mute);font-size:12px;"><code>admin</code>, <code>member</code>, <code>viewer</code>…</td>
                            <td>Segundo atributo mais usado para segmentação. Permite direcionar releases de admin para administradores.</td>
                        </tr>
                        <tr>
                            <td><code>created_at</code></td>
                            <td><span class="type-badge type-date">date</span></td>
                            <td style="color:var(--mute);font-size:12px;">ISO 8601 — <code>2024-03-15</code></td>
                            <td>Data de cadastro do usuário. Permite regras temporais ("usuários cadastrados há mais de 6 meses").</td>
                        </tr>
                        <tr>
                            <td><code>company.id</code></td>
                            <td><span class="type-badge type-string">string</span></td>
                            <td style="color:var(--mute);font-size:12px;">qualquer</td>
                            <td>ID da empresa (multi-tenant B2B). Use dot-notation nas regras de segmentação.</td>
                        </tr>
                        <tr>
                            <td><code>company.name</code></td>
                            <td><span class="type-badge type-string">string</span></td>
                            <td style="color:var(--mute);font-size:12px;">qualquer</td>
                            <td>Nome da empresa. Aparece em contextos de moderação.</td>
                        </tr>
                        <tr>
                            <td><code>company.plan</code></td>
                            <td><span class="type-badge type-string">string</span></td>
                            <td style="color:var(--mute);font-size:12px;">qualquer</td>
                            <td>Plano da empresa (vs. plano do usuário individual). Separa os dois contextos de billing.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Atributos personalizados --}}
        <div class="card" style="margin-bottom:var(--gap);">
            <div class="section-label">Atributos personalizados — objeto <code>attributes</code></div>

            <div class="info-box info-tip">
                <span class="info-box-icon"><i class="fa-solid fa-circle-info"></i></span>
                <span>Tudo dentro de <code>attributes</code> é do seu domínio — o Novidda não interpreta o significado,
                apenas armazena e compara. Use para segmentar por qualquer dado relevante do seu produto.</span>
            </div>

            <div style="overflow-x:auto;">
                <table class="attr-table">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Exemplo</th>
                            <th>Operadores disponíveis</th>
                            <th>Uso típico</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="type-badge type-string">string</span></td>
                            <td><code>industry: 'agência'</code></td>
                            <td style="font-size:12px;color:var(--mute);">igual a, diferente de, contém, começa com, termina com, está em, não está em, existe, não existe</td>
                            <td style="font-size:12px;color:var(--mute);">Segmentar por vertical, região, perfil</td>
                        </tr>
                        <tr>
                            <td><span class="type-badge type-number">number</span></td>
                            <td><code>employees: 87</code></td>
                            <td style="font-size:12px;color:var(--mute);">igual a, diferente de, maior que, menor que, está em, não está em, existe, não existe</td>
                            <td style="font-size:12px;color:var(--mute);">Tamanho da empresa, MRR, contagem de uso</td>
                        </tr>
                        <tr>
                            <td><span class="type-badge type-boolean">boolean</span></td>
                            <td><code>beta_program: true</code></td>
                            <td style="font-size:12px;color:var(--mute);">igual a, existe, não existe</td>
                            <td style="font-size:12px;color:var(--mute);">Feature flags, opt-ins, conclusão de etapas</td>
                        </tr>
                        <tr>
                            <td><span class="type-badge type-date">date</span></td>
                            <td><code>last_purchase: '2026-06-12'</code></td>
                            <td style="font-size:12px;color:var(--mute);">igual a, anterior a, posterior a, existe, não existe</td>
                            <td style="font-size:12px;color:var(--mute);">Atividade recente, janelas de reengajamento</td>
                        </tr>
                        <tr>
                            <td><span class="type-badge type-any">array</span></td>
                            <td><code>tags: ['vip', 'early_adopter']</code></td>
                            <td style="font-size:12px;color:var(--mute);">está em, não está em, existe, não existe</td>
                            <td style="font-size:12px;color:var(--mute);">Multi-labels, coleções de permissões</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="info-box info-warn" style="margin-bottom:0;">
                <span class="info-box-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                <div>
                    <strong>Limites de segurança</strong><br>
                    O objeto <code>user</code> total não pode exceder <strong>8 KB</strong>.
                    O objeto <code>attributes</code> deve ser plano (1 nível de profundidade — sem objetos aninhados dentro de attributes).
                    Dados que violam esses limites são silenciosamente ignorados.
                </div>
            </div>
        </div>

        {{-- Dot notation --}}
        <div class="card">
            <div class="section-label">Dot-notation nas regras de segmentação</div>
            <p style="font-size:13px;color:var(--mute);margin:0 0 16px;">
                Nas regras de segmentação do painel, referencie atributos usando ponto para navegar em objetos aninhados:
            </p>
            <div style="overflow-x:auto;">
                <table class="attr-table" style="margin-bottom:0;">
                    <thead><tr><th>No widget (JavaScript)</th><th>Na regra de segmentação</th></tr></thead>
                    <tbody>
                        <tr>
                            <td><code>user.plan</code></td>
                            <td><code>plan</code></td>
                        </tr>
                        <tr>
                            <td><code>user.company.plan</code></td>
                            <td><code>company.plan</code></td>
                        </tr>
                        <tr>
                            <td><code>user.attributes.industry</code></td>
                            <td><code>attributes.industry</code></td>
                        </tr>
                        <tr>
                            <td><code>user.attributes.beta_program</code></td>
                            <td><code>attributes.beta_program</code></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         ABA: PHP / LARAVEL
    ══════════════════════════════════════════ --}}
    <div id="tab-php" class="tab-pane">

        <div class="card" style="margin-bottom:var(--gap);">
            <div class="section-label">Onde instalar no seu projeto Laravel</div>

            <div class="steps" style="margin-bottom:0;">
                <div class="step">
                    <div class="step-num">1</div>
                    <div class="step-body">
                        <div class="step-title">Layout principal (<code>resources/views/layouts/app.blade.php</code>)</div>
                        <div class="step-desc">
                            A melhor localização: todos os sub-templates herdam automaticamente o widget.
                            Coloque o bloco antes do fechamento de <code>&lt;/body&gt;</code>.
                        </div>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div class="step-body">
                        <div class="step-title">Alternativa: template com autenticação</div>
                        <div class="step-desc">
                            Se você tem layouts separados para guest e autenticado, instale apenas no layout
                            de usuário autenticado — assim o <code>auth()->user()</code> está sempre disponível.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Exemplo mínimo --}}
        <div class="card" style="margin-bottom:var(--gap);">
            <div class="section-label">Exemplo com usuário autenticado — Laravel / Blade</div>

            <div class="info-box info-tip">
                <span class="info-box-icon"><i class="fa-solid fa-circle-info"></i></span>
                <span>Use <code>@@json()</code> para serializar arrays PHP diretamente como JavaScript.
                O <code>auth()->check()</code> garante que o bloco de usuário só é enviado quando há sessão ativa.</span>
            </div>

            <div class="code-card">
                <div class="code-card-header">
                    <div class="code-card-title">
                        <i class="fa-brands fa-laravel"></i> Blade — resources/views/layouts/app.blade.php
                    </div>
                    <button class="copy-btn" onclick="nvCopy(this, 'snippet-laravel')">
                        <i class="fa-solid fa-copy"></i> Copiar
                    </button>
                </div>
<pre id="snippet-laravel"><span class="hl-comment">&lt;!-- Cole antes do &lt;/body&gt; no seu layout principal --&gt;</span>
<span class="hl-tag">&lt;script&gt;</span>
  <span class="hl-kw">window</span>.noviddaConfig = {
    <span class="hl-attr">token</span>: <span class="hl-str">'{{ $token }}'</span>,

    @<span class="hl-kw">auth</span>
    <span class="hl-attr">user</span>: @<span class="hl-fn">json</span>([
      <span class="hl-comment">// Atributos canônicos</span>
      <span class="hl-str">'id'</span>         => <span class="hl-fn">auth</span>()-><span class="hl-fn">id</span>(),
      <span class="hl-str">'email'</span>      => <span class="hl-fn">auth</span>()-><span class="hl-fn">user</span>()-><span class="hl-attr">email</span>,
      <span class="hl-str">'name'</span>       => <span class="hl-fn">auth</span>()-><span class="hl-fn">user</span>()-><span class="hl-attr">name</span>,
      <span class="hl-str">'plan'</span>       => <span class="hl-fn">auth</span>()-><span class="hl-fn">user</span>()-><span class="hl-attr">plan</span>,       <span class="hl-comment">// string: 'free', 'pro', 'enterprise'</span>
      <span class="hl-str">'role'</span>       => <span class="hl-fn">auth</span>()-><span class="hl-fn">user</span>()-><span class="hl-attr">role</span>,       <span class="hl-comment">// string: 'admin', 'member', 'viewer'</span>
      <span class="hl-str">'created_at'</span> => <span class="hl-fn">auth</span>()-><span class="hl-fn">user</span>()-><span class="hl-attr">created_at</span>-><span class="hl-fn">toIso8601String</span>(),

      <span class="hl-comment">// Objeto company (B2B)</span>
      <span class="hl-str">'company'</span> => [
        <span class="hl-str">'id'</span>   => <span class="hl-fn">auth</span>()-><span class="hl-fn">user</span>()-><span class="hl-attr">company_id</span>,
        <span class="hl-str">'name'</span> => <span class="hl-fn">auth</span>()-><span class="hl-fn">user</span>()-><span class="hl-fn">company</span>?-><span class="hl-attr">name</span>,
        <span class="hl-str">'plan'</span> => <span class="hl-fn">auth</span>()-><span class="hl-fn">user</span>()-><span class="hl-fn">company</span>?-><span class="hl-attr">plan</span>,
      ],

      <span class="hl-comment">// Atributos personalizados do seu domínio</span>
      <span class="hl-str">'attributes'</span> => [
        <span class="hl-str">'industry'</span>            => <span class="hl-fn">auth</span>()-><span class="hl-fn">user</span>()-><span class="hl-fn">company</span>?-><span class="hl-attr">industry</span>,
        <span class="hl-str">'employees'</span>           => <span class="hl-fn">auth</span>()-><span class="hl-fn">user</span>()-><span class="hl-fn">company</span>?-><span class="hl-attr">employees_count</span>,
        <span class="hl-str">'beta_program'</span>        => <span class="hl-fn">auth</span>()-><span class="hl-fn">user</span>()-><span class="hl-attr">in_beta</span>,          <span class="hl-comment">// bool</span>
        <span class="hl-str">'onboarding_completed'</span>=> <span class="hl-fn">auth</span>()-><span class="hl-fn">user</span>()-><span class="hl-attr">onboarding_step</span> === <span class="hl-str">'done'</span>,
        <span class="hl-str">'mrr'</span>                 => <span class="hl-fn">auth</span>()-><span class="hl-fn">user</span>()-><span class="hl-fn">subscription</span>?-><span class="hl-fn">mrr</span>(),  <span class="hl-comment">// number</span>
        <span class="hl-str">'tags'</span>                => <span class="hl-fn">auth</span>()-><span class="hl-fn">user</span>()-><span class="hl-attr">tags</span>-><span class="hl-fn">pluck</span>(<span class="hl-str">'slug'</span>)-><span class="hl-fn">all</span>(), <span class="hl-comment">// array</span>
      ],
    ]),
    @<span class="hl-kw">endauth</span>
  };
<span class="hl-tag">&lt;/script&gt;</span>
<span class="hl-tag">&lt;script</span> <span class="hl-attr">src</span>=<span class="hl-str">"{{ url('widget.js') }}"</span> <span class="hl-attr">async</span><span class="hl-tag">&gt;&lt;/script&gt;</span></pre>
            </div>
        </div>

        {{-- PHP puro (sem framework) --}}
        <div class="card" style="margin-bottom:var(--gap);">
            <div class="section-label">PHP puro — sem framework</div>

            <div class="code-card">
                <div class="code-card-header">
                    <div class="code-card-title">
                        <i class="fa-brands fa-php"></i> PHP
                    </div>
                    <button class="copy-btn" onclick="nvCopy(this, 'snippet-php')">
                        <i class="fa-solid fa-copy"></i> Copiar
                    </button>
                </div>
<pre id="snippet-php"><span class="hl-comment">&lt;!-- Cole antes do &lt;/body&gt; --&gt;</span>
<span class="hl-tag">&lt;?php</span> <span class="hl-kw">if</span> (<span class="hl-fn">isset</span>(<span class="hl-var">$_SESSION</span>[<span class="hl-str">'user'</span>])): <span class="hl-tag">?&gt;</span>
<span class="hl-tag">&lt;script&gt;</span>
  <span class="hl-kw">window</span>.noviddaConfig = {
    <span class="hl-attr">token</span>: <span class="hl-str">'{{ $token }}'</span>,
    <span class="hl-attr">user</span>: <span class="hl-tag">&lt;?=</span> <span class="hl-fn">json_encode</span>([
      <span class="hl-str">'id'</span>    => <span class="hl-var">$_SESSION</span>[<span class="hl-str">'user'</span>][<span class="hl-str">'id'</span>],
      <span class="hl-str">'email'</span> => <span class="hl-var">$_SESSION</span>[<span class="hl-str">'user'</span>][<span class="hl-str">'email'</span>],
      <span class="hl-str">'name'</span>  => <span class="hl-var">$_SESSION</span>[<span class="hl-str">'user'</span>][<span class="hl-str">'name'</span>],
      <span class="hl-str">'plan'</span>  => <span class="hl-var">$_SESSION</span>[<span class="hl-str">'user'</span>][<span class="hl-str">'plan'</span>] ?? <span class="hl-str">'free'</span>,
      <span class="hl-str">'role'</span>  => <span class="hl-var">$_SESSION</span>[<span class="hl-str">'user'</span>][<span class="hl-str">'role'</span>] ?? <span class="hl-str">'member'</span>,
      <span class="hl-str">'attributes'</span> => [
        <span class="hl-str">'industry'</span> => <span class="hl-var">$_SESSION</span>[<span class="hl-str">'user'</span>][<span class="hl-str">'industry'</span>] ?? <span class="hl-kw">null</span>,
      ],
    ], <span class="hl-kw">JSON_UNESCAPED_UNICODE</span>) <span class="hl-tag">?&gt;</span>,
  };
<span class="hl-tag">&lt;/script&gt;</span>
<span class="hl-tag">&lt;?php</span> <span class="hl-kw">endif</span>; <span class="hl-tag">?&gt;</span>
<span class="hl-tag">&lt;script</span> <span class="hl-attr">src</span>=<span class="hl-str">"{{ url('widget.js') }}"</span> <span class="hl-attr">async</span><span class="hl-tag">&gt;&lt;/script&gt;</span></pre>
            </div>
        </div>

        {{-- Boas práticas --}}
        <div class="card">
            <div class="section-label">Boas práticas</div>
            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach ([
                    ['circle-check','#43aa8b','Defina <code>window.noviddaConfig</code> <strong>antes</strong> do script do widget. A ordem importa — o widget lê a config no carregamento.'],
                    ['circle-check','#43aa8b','Somente passe atributos que você coleta de forma consentida e de acordo com a LGPD. O Novidda armazena um snapshot anonimizado para cálculo de alcance.'],
                    ['circle-check','#43aa8b','Use <code>auth()->check()</code> / <code>isset($_SESSION)</code> para garantir que o bloco <code>user</code> só aparece para sessões autenticadas.'],
                    ['triangle-exclamation','#f4845f','Não exponha dados sensíveis desnecessários (CPF, senha, token de API). O payload trafega pelo browser e pode ser inspecionado.'],
                    ['triangle-exclamation','#f4845f','O campo <code>attributes</code> deve ser um objeto plano (key → scalar ou array de scalars). Objetos aninhados dentro de <code>attributes</code> são ignorados.'],
                ] as [$icon, $color, $text])
                <div style="display:flex;gap:10px;align-items:flex-start;">
                    <i class="fa-solid fa-{{ $icon }}" style="color:{{ $color }};margin-top:2px;flex-shrink:0;"></i>
                    <span style="font-size:13px;color:var(--ink);line-height:1.6;">{!! $text !!}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Token da conta (sempre visível) --}}
    <div class="card" style="margin-top:var(--gap);">
        <div class="section-label">Token da conta</div>
        <div class="token-row" style="margin-bottom:12px;">
            <div>
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--mute);margin-bottom:4px;">widget_token</div>
                <code id="token-val">{{ $token }}</code>
            </div>
            <button class="copy-btn" onclick="nvCopy(this, 'token-val')">
                <i class="fa-solid fa-copy"></i> Copiar
            </button>
        </div>
        <p style="font-size:12px;color:var(--mute);margin:0;">
            <i class="fa-solid fa-circle-info" style="color:var(--primary);margin-right:4px;"></i>
            Este token identifica sua conta publicamente. Ele não é um segredo — pode aparecer no HTML do seu site.
            Para proteger a integridade dos dados do usuário, a verificação via HMAC estará disponível em breve.
        </p>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // ── Tabs ──
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

    // ── Copy helper ──
    function nvCopy(btn, targetId) {
        var el = document.getElementById(targetId);
        if (!el) return;
        // Extrai texto sem tags HTML
        var text = (el.innerText || el.textContent || '').trim();

        function showCopied() {
            var orig = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Copiado!';
            btn.classList.add('copy-ok');
            setTimeout(function () {
                btn.innerHTML = orig;
                btn.classList.remove('copy-ok');
            }, 2000);
        }

        function fallbackCopy() {
            try {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed'; ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showCopied();
            } catch (e) {}
        }

        // navigator.clipboard só existe em contexto seguro (HTTPS/localhost).
        // Em HTTP puro ele vem undefined, então caímos direto no fallback.
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(showCopied).catch(fallbackCopy);
        } else {
            fallbackCopy();
        }
    }
</script>
@endpush
