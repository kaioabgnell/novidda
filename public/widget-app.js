/*!
 * Novidda -- Widget App v3
 * Todos os caracteres nao-ASCII usam escape \uXXXX para garantir
 * compatibilidade independentemente do charset declarado pelo servidor.
 */
(function () {
  'use strict';
  var ctx = window.__novidda;
  if (!ctx) return;

  var open     = false;
  var rendered = false;

  var wrap = document.createElement('div');
  wrap.id  = 'novidda-panel-host';
  document.body.appendChild(wrap);
  var shadow = wrap.attachShadow({ mode: 'open' });

  function api(path, opts) {
    return fetch(ctx.base + path, opts).then(function (r) { return r.json(); });
  }

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
    });
  }

  // Todos com escape Unicode: u=\u00FA  a=\u00E1  e=\u00EA  a~=\u00E3  c,=\u00E7
  var TYPE_LABELS = {
    feature:      'Feature',
    hotfix:       'Hotfix',
    improvement:  'Melhoria',
    announcement: 'An\u00FAncio'
  };

  var TYPE_COLORS = {
    feature:      '#0ea5e9',
    hotfix:       '#ef4444',
    improvement:  '#10b981',
    announcement: '#8b5cf6'
  };

  var RM_STATUS_LABELS = {
    analyzing:  'Em análise',
    developing: 'Em desenvolvimento',
    planned:    'Planejado'
  };

  var RM_STATUS_ORDER = ['analyzing', 'developing', 'planned'];

  /* ------------------------------------------------------------------ */
  /*  CSS                                                                 */
  /* ------------------------------------------------------------------ */

  function styles(cfg) {
    var ac     = cfg.accent || '#6c5ce7';
    var dark   = cfg.dark;
    var bg     = dark ? '#1a1e2e' : '#ffffff';
    var bgCard = dark ? '#232738' : '#f8fafc';
    var bgHov  = dark ? '#2a2f42' : '#f1f5f9';
    var ink    = dark ? '#e2e8f0' : '#1e293b';
    var mute   = dark ? '#8892a4' : '#64748b';
    var bdr    = dark ? '#2a2f42' : '#e2e8f0';
    var isLeft = cfg.position === 'left';
    var isDrop = cfg.open_mode === 'dropdown';

    var panelBase, panelHide, panelShow;
    if (isDrop) {
      var dSide = isLeft ? 'left:24px;right:auto;' : 'right:24px;left:auto;';
      panelBase = 'position:fixed;bottom:88px;' + dSide +
                  'width:380px;max-width:92vw;max-height:80vh;border-radius:16px;overflow:hidden;';
      panelHide = 'opacity:0;transform:scale(.95) translateY(6px);pointer-events:none;';
      panelShow = 'opacity:1;transform:scale(1) translateY(0);pointer-events:auto;';
    } else {
      var sEdge = isLeft ? 'left:0;right:auto;' : 'right:0;left:auto;';
      var sTx   = isLeft ? 'translateX(-105%)' : 'translateX(105%)';
      panelBase = 'position:fixed;top:0;' + sEdge + 'height:100%;width:400px;max-width:92vw;border-radius:0;';
      panelHide = 'transform:' + sTx + ';';
      panelShow = 'transform:translateX(0);';
    }

    return (
      ':host{all:initial;}' +
      '*{box-sizing:border-box;font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;}' +
      '.overlay{position:fixed;inset:0;background:rgba(0,0,0,.4);opacity:0;pointer-events:none;transition:opacity .25s;z-index:2147483600;}' +
      (isDrop ? '' : '.overlay.show{opacity:1;pointer-events:auto;}') +
      '.panel{' + panelBase + 'background:' + bg + ';color:' + ink + ';' +
              'box-shadow:0 12px 48px rgba(0,0,0,.22);' +
              'transition:opacity .22s ease,transform .26s ease;z-index:2147483601;' +
              'display:flex;flex-direction:column;' + panelHide + '}' +
      '.panel.show{' + panelShow + '}' +
      // Cabecalho
      '.head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid ' + bdr + ';flex-shrink:0;}' +
      '.head-left{display:flex;align-items:center;gap:10px;min-width:0;overflow:hidden;}' +
      '.head h2{margin:0;font-size:.95rem;font-weight:700;color:' + ink + ';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:flex;align-items:center;gap:6px;}' +
      '.badge-new{background:' + ac + ';color:#fff;font-size:10px;font-weight:800;padding:2px 8px;border-radius:999px;letter-spacing:.04em;white-space:nowrap;flex-shrink:0;}' +
      '.close-btn{background:none;border:none;cursor:pointer;color:' + mute + ';font-size:20px;line-height:1;padding:4px 6px;border-radius:6px;transition:background .15s,color .15s;flex-shrink:0;}' +
      '.close-btn:hover{background:' + bgHov + ';color:' + ink + ';}' +
      // Abas
      '.tabs{display:flex;gap:6px;padding:10px 20px;border-bottom:1px solid ' + bdr + ';overflow-x:auto;flex-shrink:0;scrollbar-width:none;}' +
      '.tabs::-webkit-scrollbar{display:none;}' +
      '.tab{background:none;border:1.5px solid ' + bdr + ';color:' + mute + ';font-size:11.5px;font-weight:600;padding:5px 14px;border-radius:999px;cursor:pointer;white-space:nowrap;transition:all .15s;}' +
      '.tab:hover{border-color:' + ac + ';color:' + ac + ';}' +
      '.tab.active{background:' + ac + ';border-color:' + ac + ';color:#fff;}' +
      // Corpo
      '.body{overflow-y:auto;flex:1;padding:0;scrollbar-width:thin;scrollbar-color:' + bdr + ' transparent;}' +
      '.body::-webkit-scrollbar{width:4px;}' +
      '.body::-webkit-scrollbar-thumb{background:' + bdr + ';border-radius:2px;}' +
      // Separadores
      '.sep{display:flex;align-items:center;gap:8px;padding:12px 20px 6px;font-size:10px;font-weight:800;letter-spacing:.08em;}' +
      '.sep-new{color:#ef4444;}' +
      '.sep-old{color:' + mute + ';opacity:.7;}' +
      '.sep-line-new{flex:1;height:1.5px;background:#ef4444;opacity:.4;}' +
      '.sep-line-old{flex:1;height:1px;background:' + mute + ';opacity:.3;}' +
      // Item
      '.item{padding:16px 20px;border-bottom:1px solid ' + bdr + ';border-left:3px solid var(--nv-tc,transparent);transition:background .15s;}' +
      '.item:last-child{border-bottom:none;}' +
      '.item:hover{background:' + bgHov + ';}' +
      '.item-meta{display:flex;align-items:center;gap:6px;margin-bottom:8px;flex-wrap:wrap;}' +
      '.type-tag{display:inline-flex;align-items:center;font-size:10px;font-weight:700;padding:2px 8px;border-radius:999px;letter-spacing:.04em;background:var(--nv-tc-bg,#e2e8f022);color:var(--nv-tc,#64748b);}' +
      '.cat-tag{display:inline-flex;align-items:center;gap:4px;font-size:10.5px;padding:2px 8px;border-radius:999px;border:1px solid ' + bdr + ';color:' + mute + ';}' +
      '.item-date{font-size:11px;color:' + mute + ';margin-left:auto;white-space:nowrap;}' +
      '.unread-dot{width:8px;height:8px;border-radius:50%;background:' + ac + ';flex-shrink:0;}' +
      '.item-title{font-size:.93rem;font-weight:700;color:' + ink + ';margin:0 0 6px;line-height:1.35;}' +
      '.item-desc{font-size:.85rem;line-height:1.62;color:' + mute + ';}' +
      '.item-desc img{max-width:100%;border-radius:8px;margin-top:8px;display:block;}' +
      '.item-desc a{color:' + ac + ';}' +
      '.item-desc p{margin:0 0 8px;}' +
      '.media img{max-width:100%;border-radius:10px;margin-top:10px;display:block;}' +
      // YouTube
      '.yt{position:relative;margin-top:10px;cursor:pointer;border-radius:10px;overflow:hidden;background:#000;}' +
      '.yt img{width:100%;display:block;opacity:.82;}' +
      '.yt .play{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;}' +
      '.yt .play span{width:50px;height:50px;border-radius:50%;background:rgba(0,0,0,.72);color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;}' +
      // Acoes
      '.actions{display:flex;align-items:center;gap:8px;margin-top:12px;flex-wrap:wrap;}' +
      '.react-btn{background:' + bgCard + ';border:1px solid ' + bdr + ';border-radius:999px;padding:5px 12px;cursor:pointer;font-size:.85rem;color:' + ink + ';transition:all .15s;display:inline-flex;align-items:center;gap:5px;}' +
      '.react-btn:hover{border-color:' + ac + ';}' +
      '.react-btn.active{background:' + ac + '22;border-color:' + ac + ';color:' + ac + ';}' +
      '.comment-toggle{background:none;border:1px solid ' + bdr + ';border-radius:999px;padding:5px 12px;cursor:pointer;font-size:.85rem;color:' + mute + ';transition:all .15s;}' +
      '.comment-toggle:hover,.comment-toggle.open{border-color:' + ac + ';color:' + ac + ';}' +
      '.cta{display:inline-flex;align-items:center;padding:7px 16px;border-radius:8px;background:' + ac + ';color:#fff;text-decoration:none;font-weight:600;font-size:.82rem;transition:opacity .15s;}' +
      '.cta:hover{opacity:.85;}' +
      // Comentarios
      '.comments-wrap{margin-top:12px;}' +
      '.cm-count-btn{display:inline-flex;align-items:center;gap:6px;background:' + bgCard + ';border:1.5px solid ' + bdr + ';border-radius:999px;padding:5px 12px 5px 10px;cursor:pointer;font-size:.79rem;font-weight:600;color:' + mute + ';transition:all .2s;margin-bottom:10px;user-select:none;line-height:1;}' +
      '.cm-count-btn:hover{border-color:' + ac + ';color:' + ac + ';background:' + ac + '0d;}' +
      '.cm-count-btn.open{border-color:' + ac + ';color:' + ac + ';background:' + ac + '0d;}' +
      '.cm-icon{width:13px;height:13px;flex-shrink:0;opacity:.7;}' +
      '.cm-chevron{width:11px;height:11px;flex-shrink:0;transition:transform .22s ease;opacity:.6;}' +
      '.cm-count-btn.open .cm-chevron{transform:rotate(180deg);}' +
      '.cm-list-wrap{margin:0 0 10px 4px;padding-left:14px;border-left:2px solid ' + bdr + ';}' +
      '.cm-item-hidden{display:none;}' +
      '.cm-more-btn{display:block;width:100%;margin-top:8px;background:none;border:1.5px solid ' + bdr + ';border-radius:999px;padding:5px 14px;cursor:pointer;font-size:.78rem;font-weight:600;color:' + ac + ';text-align:center;transition:all .17s;letter-spacing:.01em;}' +
      '.cm-more-btn:hover{border-color:' + ac + ';background:' + ac + '11;}' +
      '.comment-item{background:' + bgCard + ';border-radius:10px;padding:10px 12px;margin-top:6px;font-size:.82rem;}' +
      '.comment-author{font-weight:700;color:' + ink + ';margin-bottom:3px;}' +
      '.comment-body{color:' + mute + ';line-height:1.48;}' +
      // Formulario de comentario -- oculto por padrao
      '.cform{display:none;margin-top:10px;flex-direction:column;gap:8px;}' +
      '.cform.open{display:flex;}' +
      '.cform input,.cform textarea{border:1px solid ' + bdr + ';background:' + bg + ';color:' + ink + ';border-radius:8px;padding:8px 10px;font-size:.84rem;width:100%;outline:none;transition:border-color .15s;}' +
      '.cform input:focus,.cform textarea:focus{border-color:' + ac + ';}' +
      '.cform textarea{min-height:76px;resize:vertical;}' +
      '.cform .hp{position:absolute;left:-9999px;}' +
      '.cform-row{display:flex;align-items:center;gap:8px;}' +
      '.cform-submit{background:' + ac + ';color:#fff;border:none;border-radius:8px;padding:7px 16px;cursor:pointer;font-weight:600;font-size:.82rem;}' +
      '.cform-cancel{background:none;border:1px solid ' + bdr + ';color:' + mute + ';border-radius:8px;padding:7px 14px;cursor:pointer;font-size:.82rem;}' +
      '.cform-msg{font-size:.78rem;color:#10b981;margin-left:4px;}' +
      '.comment-alert{display:none;background:#10b981;color:#fff;border-radius:10px;padding:10px 14px;margin-top:8px;font-size:.84rem;font-weight:500;align-items:center;justify-content:space-between;gap:10px;line-height:1.4;}' +
      '.comment-alert.show{display:flex;}' +
      '.alert-close{background:none;border:none;color:#fff;font-size:1.2rem;cursor:pointer;padding:0 2px;line-height:1;opacity:.8;flex-shrink:0;}' +
      '.alert-close:hover{opacity:1;}' +
      // Feedback por changelog
      '.feedback-wrap{margin-top:10px;padding-top:10px;border-top:1px solid ' + bdr + ';}' +
      '.fb-label{font-size:.75rem;font-weight:600;color:' + mute + ';margin-bottom:8px;letter-spacing:.01em;}' +
      '.fb-faces{display:flex;gap:10px;}' +
      '.face-btn{background:none;border:2px solid ' + bdr + ';color:' + mute + ';border-radius:50%;width:38px;height:38px;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;}' +
      '.face-btn:hover{border-color:' + ac + ';color:' + ac + ';transform:scale(1.12);}' +
      '.face-btn.sel{border-color:' + ac + ';background:' + ac + '22;color:' + ac + ';transform:scale(1.1);}' +
      '.fb-form{display:none;flex-direction:column;gap:7px;margin-top:8px;}' +
      '.fb-form.open{display:flex;}' +
      '.fb-form textarea{border:1px solid ' + bdr + ';background:' + bg + ';color:' + ink + ';border-radius:8px;padding:8px;font-size:.8rem;width:100%;min-height:56px;resize:none;outline:none;transition:border-color .15s;}' +
      '.fb-form textarea:focus{border-color:' + ac + ';}' +
      '.fb-form-row{display:flex;gap:8px;align-items:center;}' +
      '.fb-send{background:' + ac + ';color:#fff;border:none;border-radius:8px;padding:6px 14px;cursor:pointer;font-size:.8rem;font-weight:600;}' +
      '.fb-cancel{background:none;border:1px solid ' + bdr + ';color:' + mute + ';border-radius:8px;padding:6px 12px;cursor:pointer;font-size:.8rem;}' +
      '.fb-done{font-size:.82rem;color:#10b981;margin-top:10px;padding-top:10px;border-top:1px solid ' + bdr + ';display:flex;align-items:center;gap:5px;}' +
      // Barra de navegacao inferior (Releases / Roadmap)
      '.nav-bar{display:flex;border-top:2px solid ' + bdr + ';flex-shrink:0;background:' + bg + ';}' +
      '.nav-tab{flex:1;padding:11px 8px;text-align:center;cursor:pointer;font-size:12px;font-weight:700;color:' + mute + ';border:none;background:none;border-top:2px solid transparent;margin-top:-2px;letter-spacing:.02em;transition:color .15s,border-color .15s;}' +
      '.nav-tab:hover{color:' + ink + ';}' +
      '.nav-tab.active{color:' + ac + ';border-top-color:' + ac + ';}' +
      // Filtros rapidos de status do roadmap
      '.rm-filters{display:flex;gap:6px;padding:10px 20px;border-bottom:1px solid ' + bdr + ';overflow-x:auto;flex-shrink:0;scrollbar-width:none;}' +
      '.rm-filters::-webkit-scrollbar{display:none;}' +
      // Itens de roadmap
      '.rm-item{padding:16px 20px;border-bottom:1px solid ' + bdr + ';transition:background .15s;}' +
      '.rm-item:last-child{border-bottom:none;}' +
      '.rm-item:hover{background:' + bgHov + ';}' +
      '.rm-top{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px;}' +
      '.rm-status{display:inline-flex;align-items:center;font-size:10px;font-weight:700;padding:2px 8px;border-radius:999px;letter-spacing:.04em;}' +
      '.rm-analyzing{background:#f9731620;color:#f97316;}' +
      '.rm-developing{background:#3b82f620;color:#3b82f6;}' +
      '.rm-planned{background:#8b5cf620;color:#8b5cf6;}' +
      '.rm-vote{display:flex;align-items:center;gap:4px;flex-shrink:0;}' +
      '.rm-vote-btn{display:flex;align-items:center;gap:4px;background:none;border:1.5px solid ' + bdr + ';color:' + mute + ';border-radius:999px;padding:3px 9px;font-size:11px;font-weight:700;cursor:pointer;transition:all .15s;line-height:1.4;}' +
      '.rm-vote-btn:hover{border-color:' + ac + ';color:' + ac + ';}' +
      '.rm-vote-up.active{border-color:#10b981;background:#10b98120;color:#10b981;}' +
      '.rm-vote-down.active{border-color:#ef4444;background:#ef444420;color:#ef4444;}' +
      '.rm-title{font-size:.93rem;font-weight:700;color:' + ink + ';margin:0 0 6px;line-height:1.35;}' +
      '.rm-desc{font-size:.85rem;line-height:1.62;color:' + mute + ';overflow:hidden;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:3;}' +
      '.rm-desc.expanded{display:block;-webkit-line-clamp:unset;}' +
      '.rm-desc p{margin:0 0 6px;}' +
      '.rm-desc a{color:' + ac + ';}' +
      '.rm-more{background:none;border:none;color:' + ac + ';font-size:.8rem;cursor:pointer;padding:4px 0;font-weight:600;margin-top:4px;display:block;}' +
      '.rm-empty{text-align:center;padding:48px 24px;color:' + mute + ';}' +
      '.rm-empty p{margin:12px 0 0;font-size:.9rem;}' +
      // Rodape fixo
      '.foot{padding:9px 20px;border-top:1px solid ' + bdr + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;background:' + bg + ';}' +
      '.foot a{font-size:.72rem;color:' + mute + ';text-decoration:none;display:flex;align-items:center;gap:3px;transition:color .15s;letter-spacing:.01em;}' +
      '.foot a:hover{color:' + ink + ';}' +
      '.foot a strong{color:' + ac + ';font-weight:700;}' +
      // Vazio
      '.empty{text-align:center;padding:48px 24px;color:' + mute + ';}' +
      '.empty p{margin:12px 0 0;font-size:.9rem;}' +
      (cfg.custom_css || '')
    );
  }

  /* ------------------------------------------------------------------ */
  /*  Helpers                                                             */
  /* ------------------------------------------------------------------ */

  function ytThumb(id) { return 'https://i.ytimg.com/vi/' + id + '/hqdefault.jpg'; }

  function fmtDate(iso) {
    if (!iso) return '';
    try {
      var diff  = Date.now() - new Date(iso).getTime();
      var mins  = Math.floor(diff / 60000);
      var hours = Math.floor(diff / 3600000);
      var days  = Math.floor(diff / 86400000);
      if (mins  < 1)  return 'agora mesmo';
      if (hours < 1)  return mins  + ' min atrás';
      if (hours < 24) return hours + ' hora'  + (hours !== 1 ? 's' : '') + ' atrás';
      return days + ' dia' + (days !== 1 ? 's' : '') + ' atrás';
    } catch (e) { return ''; }
  }

  function renderItem(it, isNew) {
    var typeLabel = TYPE_LABELS[it.type] || it.type;
    var typeColor = TYPE_COLORS[it.type] || '#6c5ce7';

    var media = (it.media || []).map(function (m) {
      if (m.type === 'youtube' && m.youtube_id) {
        return '<div class="yt" data-yt="' + esc(m.youtube_id) + '">' +
          '<img loading="lazy" src="' + ytThumb(m.youtube_id) + '" alt="">' +
          '<div class="play"><span>&#9654;</span></div></div>';
      }
      if (m.type === 'image' && m.url) {
        return '<div class="media"><img loading="lazy" src="' + esc(m.url) + '" alt=""></div>';
      }
      return '';
    }).join('');

    var cats = (it.categories || []).map(function (c) {
      var st = c.color ? ' style="border-color:' + esc(c.color) + ';color:' + esc(c.color) + ';"' : '';
      var iconHtml = c.icon ? '<i class="' + esc(c.icon) + '"></i> ' : '';
      return '<span class="cat-tag"' + st + '>' + iconHtml + esc(c.name) + '</span>';
    }).join('');

    var s = it.settings || {};

    var reactions = s.show_reactions
      ? '<button class="react-btn" data-react="' + it.id + '" data-emoji="' + esc(it.reaction_emoji) + '">' +
        esc(it.reaction_emoji) + ' <span class="rc">' + (it.reactions_count || 0) + '</span></button>'
      : '';

    // \uD83D\uDCAC = emoji balao de fala (\uD83D\uDCAC)
    var commentToggle = (s.show_comments && s.allow_comments)
      ? '<button class="comment-toggle" data-ctoggle="' + it.id + '">Comentar</button>'
      : '';

    var cta = s.cta_text && s.cta_url
      ? '<a class="cta" href="' + esc(s.cta_url) + '"' +
        (s.cta_new_tab ? ' target="_blank" rel="noopener"' : '') +
        (s.cta_color ? ' style="background:' + esc(s.cta_color) + '"' : '') +
        '>' + esc(s.cta_text) + '</a>'
      : '';

    var CM_PAGE = 5;
    var allCm   = it.comments || [];
    var totalCm = allCm.length;

    var commentsList = allCm.map(function (c, idx) {
      var hiddenCls = idx >= CM_PAGE ? ' cm-item-hidden' : '';
      return '<div class="comment-item' + hiddenCls + '" data-cmitem="' + it.id + '">' +
        '<div class="comment-author">' + esc(c.author_name) + '</div>' +
        '<div class="comment-body">' + esc(c.body) + '</div></div>';
    }).join('');

    var cmRemaining = Math.max(0, totalCm - CM_PAGE);
    var cmMoreBtn = totalCm > CM_PAGE
      ? '<button class="cm-more-btn" data-cmmore="' + it.id + '" data-cmshown="' + CM_PAGE + '">' +
        'Ver mais ' + cmRemaining + ' coment\u00E1rio' + (cmRemaining !== 1 ? 's' : '') + '</button>'
      : '';

    var cmBubbleSvg =
      '<svg class="cm-icon" viewBox="0 0 16 16" fill="currentColor">' +
      '<path d="M14 1H2a1 1 0 00-1 1v8a1 1 0 001 1h3l2.5 3 2.5-3H14a1 1 0 001-1V2a1 1 0 00-1-1z"/></svg>';
    var cmChevronSvg =
      '<svg class="cm-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
      '<polyline points="3 6 8 11 13 6"/></svg>';

    var cmCountBtn = (s.show_comments && totalCm > 0)
      ? '<button class="cm-count-btn" data-cmtoggle="' + it.id + '">' +
        cmBubbleSvg +
        totalCm + ' coment\u00E1rio' + (totalCm !== 1 ? 's' : '') +
        cmChevronSvg +
        '</button>'
      : '';

    var cmListWrap = (s.show_comments && totalCm > 0)
      ? '<div class="cm-list-wrap" data-cmwrap="' + it.id + '" style="display:none;">' +
        commentsList + cmMoreBtn + '</div>'
      : '';

    // \u00E1 = a com acento (coment\u00E1rio); \u2026 = reticencias (...)
    // \u00D7 = x (fecha alerta)
    var commentForm = (s.show_comments && s.allow_comments)
      ? '<form class="cform" data-cid="' + it.id + '">' +
          '<input class="hp" name="website" tabindex="-1" autocomplete="off">' +
          '<input name="author_name" placeholder="Seu nome (opcional)">' +
          '<textarea name="body" placeholder="Escreva um coment\u00E1rio\u2026" required></textarea>' +
          '<div class="cform-row">' +
            '<button type="submit" class="cform-submit">Enviar</button>' +
            '<button type="button" class="cform-cancel" data-ccid="' + it.id + '">Cancelar</button>' +
          '</div>' +
        '</form>' +
        '<div class="comment-alert" data-calert="' + it.id + '">' +
          '<i class="fa-solid fa-circle-check"></i> Coment\u00E1rio enviado! Aguardando aprova\u00E7\u00E3o.' +
          '<button class="alert-close" data-caclose="' + it.id + '" aria-label="Fechar">\u00D7</button>' +
        '</div>'
      : '';

    var commentsSection = s.show_comments
      ? '<div class="comments-wrap">' + cmCountBtn + cmListWrap + commentForm + '</div>'
      : '';

    // Feedback por item (😢=sad 😐=neutral 😊=happy)
    var feedbackSection = '';
    if (s.feedback_enabled) {
      try {
        if (localStorage.getItem('nv_fb_' + it.id)) {
          feedbackSection = '<div class="fb-done"><i class="fa-solid fa-circle-check"></i> Obrigado pelo feedback!</div>';
        } else {
          feedbackSection =
            '<div class="feedback-wrap" data-fbw="' + it.id + '">' +
              '<div class="fb-label">Avalie esta novidade:</div>' +
              '<div class="fb-faces">' +
                '<button class="face-btn" data-fbscore="sad" data-fbid="' + it.id + '" title="Insatisfeito"><i class="fa-solid fa-face-frown"></i></button>' +
                '<button class="face-btn" data-fbscore="neutral" data-fbid="' + it.id + '" title="Neutro"><i class="fa-solid fa-face-meh"></i></button>' +
                '<button class="face-btn" data-fbscore="happy" data-fbid="' + it.id + '" title="Satisfeito"><i class="fa-solid fa-face-smile"></i></button>' +
              '</div>' +
              '<form class="fb-form" data-fbf="' + it.id + '">' +
                '<textarea name="comment" placeholder="Conte mais (opcional)…"></textarea>' +
                '<div class="fb-form-row">' +
                  '<button type="submit" class="fb-send">Enviar feedback</button>' +
                  '<button type="button" class="fb-cancel" data-fbcancel="' + it.id + '">Cancelar</button>' +
                '</div>' +
              '</form>' +
            '</div>';
        }
      } catch (e) {}
    }

    var unreadDot = isNew ? ' <span class="unread-dot"></span>' : '';
    var catList   = (it.categories || []).map(function (c) { return c.name; }).join(',');

    return '<div class="item" data-id="' + it.id + '" data-cats="' + esc(catList) + '"' +
           ' style="--nv-tc:' + typeColor + ';--nv-tc-bg:' + typeColor + '22;">' +
      '<div class="item-meta">' +
        '<span class="type-tag">' + esc(typeLabel) + '</span>' +
        cats +
        '<span class="item-date">' + esc(fmtDate(it.published_at)) + '</span>' +
        unreadDot +
      '</div>' +
      '<h3 class="item-title">' + esc(it.title) + '</h3>' +
      '<div class="item-desc">' + (it.description || '') + '</div>' +
      media +
      ((reactions || commentToggle || cta) ? '<div class="actions">' + reactions + commentToggle + cta + '</div>' : '') +
      commentsSection +
      feedbackSection +
      '</div>';
  }

  /* ------------------------------------------------------------------ */
  /*  Roadmap item renderer                                               */
  /* ------------------------------------------------------------------ */

  function renderRoadmapItem(it) {
    var statusLabel = RM_STATUS_LABELS[it.status] || it.status;
    var statusClass = 'rm-' + it.status;

    var voteHtml = '';
    if (it.voting_enabled) {
      var userVote = null;
      try { userVote = localStorage.getItem('nv_rm_vote_' + it.id); } catch (e) {}
      voteHtml =
        '<div class="rm-vote" data-rmvote="' + it.id + '">' +
          '<button class="rm-vote-btn rm-vote-up' + (userVote === 'up' ? ' active' : '') + '" data-rmvotebtn="up" data-rmvoteid="' + it.id + '">' +
            '<i class="fa-solid fa-thumbs-up"></i> <span class="rm-vote-count" data-rmvotecount="up">' + (it.votes_up || 0) + '</span>' +
          '</button>' +
          '<button class="rm-vote-btn rm-vote-down' + (userVote === 'down' ? ' active' : '') + '" data-rmvotebtn="down" data-rmvoteid="' + it.id + '">' +
            '<i class="fa-solid fa-thumbs-down"></i> <span class="rm-vote-count" data-rmvotecount="down">' + (it.votes_down || 0) + '</span>' +
          '</button>' +
        '</div>';
    }

    var feedbackSection = '';
    if (it.feedback_enabled) {
      try {
        if (localStorage.getItem('nv_rm_fb_' + it.id)) {
          feedbackSection = '<div class="fb-done"><i class="fa-solid fa-circle-check"></i> Obrigado pelo feedback!</div>';
        } else {
          feedbackSection =
            '<div class="feedback-wrap" data-rmfbw="' + it.id + '">' +
              '<div class="fb-label">O que achou deste item?</div>' +
              '<div class="fb-faces">' +
                '<button class="face-btn" data-rmfbscore="sad" data-rmfbid="' + it.id + '" title="Insatisfeito"><i class="fa-solid fa-face-frown"></i></button>' +
                '<button class="face-btn" data-rmfbscore="neutral" data-rmfbid="' + it.id + '" title="Neutro"><i class="fa-solid fa-face-meh"></i></button>' +
                '<button class="face-btn" data-rmfbscore="happy" data-rmfbid="' + it.id + '" title="Satisfeito"><i class="fa-solid fa-face-smile"></i></button>' +
              '</div>' +
              '<form class="fb-form" data-rmfbf="' + it.id + '">' +
                '<textarea name="comment" placeholder="Conte mais (opcional)…"></textarea>' +
                '<div class="fb-form-row">' +
                  '<button type="submit" class="fb-send">Enviar feedback</button>' +
                  '<button type="button" class="fb-cancel" data-rmfbcancel="' + it.id + '">Cancelar</button>' +
                '</div>' +
              '</form>' +
            '</div>';
        }
      } catch (e) {}
    }

    return '<div class="rm-item" data-rmid="' + it.id + '" data-rmstatus="' + esc(it.status) + '">' +
      '<div class="rm-top">' +
        '<div class="rm-status ' + statusClass + '">' + esc(statusLabel) + '</div>' +
        voteHtml +
      '</div>' +
      '<h3 class="rm-title">' + esc(it.title) + '</h3>' +
      '<div class="rm-desc" data-rmdesc="' + it.id + '">' + (it.description || '') + '</div>' +
      '<button class="rm-more" data-rmmore="' + it.id + '">Ver mais</button>' +
      feedbackSection +
      '</div>';
  }

  /* ------------------------------------------------------------------ */
  /*  Build do painel                                                     */
  /* ------------------------------------------------------------------ */

  function build(cfg, feed) {
    // Dual FA load:
    // 1) document.head  -> registra @font-face globalmente (resolve suporte inconsistente em shadow DOM)
    // 2) shadow DOM     -> aplica seletores de classe (.fa-solid etc.) ao conteudo isolado
    if (ctx.origin) {
      var faHref = ctx.origin + '/vendor/fontawesome/css/all.min.css';
      if (!document.querySelector('link[data-nv-fa]')) {
        var faHead = document.createElement('link');
        faHead.rel  = 'stylesheet';
        faHead.href = faHref;
        faHead.setAttribute('data-nv-fa', '1');
        document.head.appendChild(faHead);
      }
      var faShad = document.createElement('link');
      faShad.rel  = 'stylesheet';
      faShad.href = faHref;
      shadow.appendChild(faShad);
    }

    var styleEl = document.createElement('style');
    styleEl.textContent = styles(cfg);
    shadow.appendChild(styleEl);

    var overlay = document.createElement('div');
    overlay.className = 'overlay';
    var panel = document.createElement('div');
    panel.className = 'panel';

    var items     = feed.items || [];
    var unreadIds = ctx.unreadIds || [];

    // Categorias unicas para abas
    var catMap = {};
    items.forEach(function (it) {
      (it.categories || []).forEach(function (c) { catMap[c.name] = c; });
    });
    var catNames = Object.keys(catMap);

    var tabsHtml = '';
    if (catNames.length > 0) {
      tabsHtml = '<div class="tabs" id="nv-tabs">' +
        '<button class="tab active" data-cat="all">Todos</button>' +
        catNames.map(function (name) {
          var c  = catMap[name];
          var st = c.color ? ' style="--cat-c:' + c.color + '"' : '';
          var tabIcon = c.icon ? '<i class="' + esc(c.icon) + '"></i> ' : '';
          return '<button class="tab" data-cat="' + esc(name) + '"' + st + '>' +
            tabIcon + esc(name) + '</button>';
        }).join('') +
        '</div>';
    }

    // Separa novos de lidos
    var newItems = items.filter(function (it) { return unreadIds.indexOf(it.id) !== -1; });
    var oldItems = items.filter(function (it) { return unreadIds.indexOf(it.id) === -1; });

    var bodyHtml = '';
    if (!items.length) {
      bodyHtml =
        '<div class="empty">' +
        '<svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity=".35">' +
        '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>' +
        '<path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>' +
        '<p>Nenhuma novidade por aqui ainda.</p></div>';
    } else {
      if (newItems.length) {
        bodyHtml +=
          '<div class="sep sep-new"><div class="sep-line-new"></div>NOVIDADES<div class="sep-line-new"></div></div>' +
          newItems.map(function (it) { return renderItem(it, true); }).join('');
      }
      if (oldItems.length) {
        bodyHtml +=
          '<div class="sep sep-old"><div class="sep-line-old"></div>ANTERIORES<div class="sep-line-old"></div></div>' +
          oldItems.map(function (it) { return renderItem(it, false); }).join('');
      }
    }

    var newCount  = unreadIds.length;
    var badgeHtml = newCount > 0
      ? '<span class="badge-new">' + newCount + ' NOVO' + (newCount !== 1 ? 'S' : '') + '</span>'
      : '';

    // Titulo renderizado como HTML para suportar icones FA (ex: <i class="fa-solid fa-star"></i>)
    var panelTitle = cfg.button_text || 'Novidades';


    var footHtml =
      '<div class="foot">' +
      '<a href="https://novidda.com.br/" target="_blank" rel="noopener">' +
      'Feito por <strong>Novidda</strong></a></div>';

    var showRoadmap = cfg.roadmap_enabled !== false;

    var navBarHtml = showRoadmap
      ? '<div class="nav-bar" id="nv-nav-bar">' +
          '<button class="nav-tab active" data-nvtab="releases">Releases</button>' +
          '<button class="nav-tab" data-nvtab="roadmap">Roadmap</button>' +
        '</div>'
      : '';

    panel.innerHTML =
      '<div class="head">' +
        '<div class="head-left"><h2>' + panelTitle + '</h2>' + badgeHtml + '</div>' +
        '<button class="close-btn" aria-label="Fechar">&times;</button>' +
      '</div>' +
      tabsHtml +
      '<div class="body" id="nv-body">' + bodyHtml + '</div>' +
      (showRoadmap
        ? '<div class="body" id="nv-rm-body" style="display:none;">' +
            '<div class="rm-empty"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity=".35"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6-10l6-3m6 3l-5.447-2.724A1 1 0 0015 5.618V16.382a1 1 0 001.447.894L21 15m0 0V4"/></svg><p>Carregando roadmap…</p></div>' +
          '</div>'
        : '') +
      navBarHtml +
      footHtml;

    shadow.appendChild(overlay);
    shadow.appendChild(panel);

    /* ---- Eventos ---- */
    overlay.addEventListener('click', toggle);
    panel.querySelector('.close-btn').addEventListener('click', toggle);

    // Abas de categoria
    var tabsEl = shadow.getElementById('nv-tabs');
    if (tabsEl) {
      tabsEl.addEventListener('click', function (e) {
        var b = e.target.closest('.tab');
        if (!b) return;
        var cat = b.getAttribute('data-cat');
        tabsEl.querySelectorAll('.tab').forEach(function (t) { t.classList.toggle('active', t === b); });
        shadow.querySelectorAll('.item').forEach(function (el) {
          if (cat === 'all') { el.style.display = ''; return; }
          var cs = (el.getAttribute('data-cats') || '').split(',');
          el.style.display = (cs.indexOf(cat) !== -1) ? '' : 'none';
        });
        shadow.querySelectorAll('.sep').forEach(function (sep) {
          var next = sep.nextElementSibling;
          var vis  = false;
          while (next && !next.classList.contains('sep')) {
            if (next.classList.contains('item') && next.style.display !== 'none') { vis = true; break; }
            next = next.nextElementSibling;
          }
          sep.style.display = vis ? '' : 'none';
        });
      });
    }

    // YouTube facade
    panel.querySelectorAll('.yt').forEach(function (el) {
      el.addEventListener('click', function () {
        var id = el.getAttribute('data-yt');
        el.innerHTML =
          '<iframe width="100%" height="200" frameborder="0" allowfullscreen' +
          ' style="display:block;border-radius:10px;" allow="autoplay;encrypted-media"' +
          ' src="https://www.youtube.com/embed/' + id + '?autoplay=1"></iframe>';
      });
    });

    // Reacoes
    panel.querySelectorAll('.react-btn').forEach(function (el) {
      el.addEventListener('click', function () {
        if (el.classList.contains('active')) return;
        var id    = el.getAttribute('data-react');
        var emoji = el.getAttribute('data-emoji');
        api('/reaction', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ reader_id: ctx.reader, changelog_id: +id, emoji: emoji })
        });
        el.classList.add('active');
        var rc = el.querySelector('.rc');
        if (rc) rc.textContent = +rc.textContent + 1;
      });
    });

    // Toggle do formulario de comentario
    panel.querySelectorAll('[data-ctoggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id   = btn.getAttribute('data-ctoggle');
        var form = shadow.querySelector('.cform[data-cid="' + id + '"]');
        if (!form) return;
        var isOpen = form.classList.toggle('open');
        btn.classList.toggle('open', isOpen);
        btn.innerHTML = isOpen ? '<i class="fa-solid fa-xmark"></i> Cancelar' : 'Comentar';
      });
    });

    // Cancelar comentario
    panel.querySelectorAll('.cform-cancel').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id  = btn.getAttribute('data-ccid');
        var f   = shadow.querySelector('.cform[data-cid="' + id + '"]');
        var tgl = shadow.querySelector('[data-ctoggle="' + id + '"]');
        if (f)   f.classList.remove('open');
        if (tgl) { tgl.classList.remove('open'); tgl.textContent = 'Comentar'; }
      });
    });

    // Submit de comentario
    panel.querySelectorAll('.cform').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var id = form.getAttribute('data-cid');
        api('/comment', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            reader_id: ctx.reader, changelog_id: +id,
            author_name: form.author_name.value,
            body: form.body.value, website: form.website.value
          })
        }).then(function () {
          // Oculta o formulario e reseta o botao toggle
          form.reset();
          form.classList.remove('open');
          var tgl = shadow.querySelector('[data-ctoggle="' + id + '"]');
          if (tgl) { tgl.classList.remove('open'); tgl.textContent = 'Comentar'; }
          // Exibe o alerta de sucesso
          var alertEl = shadow.querySelector('.comment-alert[data-calert="' + id + '"]');
          if (alertEl) alertEl.classList.add('show');
        }).catch(function () {});
      });
    });

    // Fechar alerta de sucesso
    panel.querySelectorAll('.alert-close').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-caclose');
        var alertEl = shadow.querySelector('.comment-alert[data-calert="' + id + '"]');
        if (alertEl) alertEl.classList.remove('show');
      });
    });

    // Toggle lista de comentarios colapsada
    panel.querySelectorAll('.cm-count-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id   = btn.getAttribute('data-cmtoggle');
        var wrap = shadow.querySelector('.cm-list-wrap[data-cmwrap="' + id + '"]');
        if (!wrap) return;
        var isOpen = wrap.style.display !== 'none';
        wrap.style.display = isOpen ? 'none' : '';
        btn.classList.toggle('open', !isOpen);
      });
    });

    // Ver mais comentarios (pagina de 5)
    panel.querySelectorAll('.cm-more-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id     = btn.getAttribute('data-cmmore');
        var hidden = shadow.querySelectorAll('.cm-item-hidden[data-cmitem="' + id + '"]');
        var count  = 0;
        hidden.forEach(function (el) {
          if (count < 5) { el.classList.remove('cm-item-hidden'); count++; }
        });
        var remaining = shadow.querySelectorAll('.cm-item-hidden[data-cmitem="' + id + '"]').length;
        if (remaining === 0) {
          btn.style.display = 'none';
        } else {
          btn.textContent = 'Ver mais ' + remaining + ' comentário' + (remaining !== 1 ? 's' : '');
        }
      });
    });

    // Feedback por changelog: clique na carinha abre o formulário
    panel.querySelectorAll('.face-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id   = btn.getAttribute('data-fbid');
        var wrap = shadow.querySelector('.feedback-wrap[data-fbw="' + id + '"]');
        if (!wrap) return;
        wrap.querySelectorAll('.face-btn').forEach(function (b) { b.classList.remove('sel'); });
        btn.classList.add('sel');
        var form = shadow.querySelector('.fb-form[data-fbf="' + id + '"]');
        if (form) form.classList.add('open');
      });
    });

    // Cancelar feedback
    panel.querySelectorAll('.fb-cancel').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id   = btn.getAttribute('data-fbcancel');
        var wrap = shadow.querySelector('.feedback-wrap[data-fbw="' + id + '"]');
        var form = shadow.querySelector('.fb-form[data-fbf="' + id + '"]');
        if (form) form.classList.remove('open');
        if (wrap) wrap.querySelectorAll('.face-btn').forEach(function (b) { b.classList.remove('sel'); });
      });
    });

    // Enviar feedback
    panel.querySelectorAll('.fb-form').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var id   = form.getAttribute('data-fbf');
        var wrap = shadow.querySelector('.feedback-wrap[data-fbw="' + id + '"]');
        if (!wrap) return;
        var selBtn = wrap.querySelector('.face-btn.sel');
        if (!selBtn) return;
        var score   = selBtn.getAttribute('data-fbscore');
        var comment = (form.querySelector('[name=comment]') || {}).value || '';
        api('/changelog-feedback', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ reader_id: ctx.reader, changelog_id: +id, score: score, comment: comment })
        }).then(function () {
          try { localStorage.setItem('nv_fb_' + id, '1'); } catch (e) {}
          // Substitui o bloco pelo agradecimento
          var item = shadow.querySelector('.item[data-id="' + id + '"]');
          if (item) {
            var fbWrap = item.querySelector('.feedback-wrap');
            if (fbWrap) {
              var done = document.createElement('div');
              done.className = 'fb-done';
              done.innerHTML = '<i class="fa-solid fa-circle-check"></i> Obrigado pelo feedback!';
              fbWrap.parentNode.replaceChild(done, fbWrap);
            }
          }
        }).catch(function () {});
      });
    });

    // ---- Navegação inferior: Releases / Roadmap ----
    var rmLoaded = false;
    var navBar   = shadow.getElementById('nv-nav-bar');
    var relBody  = shadow.getElementById('nv-body');
    var rmBody   = shadow.getElementById('nv-rm-body');
    var catTabs  = shadow.getElementById('nv-tabs');

    if (navBar) {
      navBar.addEventListener('click', function (e) {
        var btn = e.target.closest('.nav-tab');
        if (!btn) return;
        var tab = btn.getAttribute('data-nvtab');
        navBar.querySelectorAll('.nav-tab').forEach(function (b) { b.classList.toggle('active', b === btn); });

        if (tab === 'roadmap') {
          relBody.style.display  = 'none';
          if (catTabs) catTabs.style.display = 'none';
          rmBody.style.display   = 'flex';
          rmBody.style.flexDirection = 'column';
          rmBody.style.flex      = '1';
          rmBody.style.overflowY = 'auto';

          if (!rmLoaded) {
            rmLoaded = true;
            api('/roadmap').then(function (res) {
              var rmItems = (res && res.items) || [];
              if (!rmItems.length) {
                rmBody.innerHTML =
                  '<div class="rm-empty">' +
                  '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity=".35"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6-10l6-3m6 3l-5.447-2.724A1 1 0 0015 5.618V16.382a1 1 0 001.447.894L21 15m0 0V4"/></svg>' +
                  '<p>Nenhum item de roadmap publicado.</p></div>';
              } else {
                var presentStatuses = RM_STATUS_ORDER.filter(function (st) {
                  return rmItems.some(function (it) { return it.status === st; });
                });
                var rmFiltersHtml = presentStatuses.length > 1
                  ? '<div class="rm-filters" id="nv-rm-filters">' +
                      '<button class="tab active" data-rmfilter="all">Todos</button>' +
                      presentStatuses.map(function (st) {
                        return '<button class="tab" data-rmfilter="' + st + '">' + RM_STATUS_LABELS[st] + '</button>';
                      }).join('') +
                    '</div>'
                  : '';
                rmBody.innerHTML = rmFiltersHtml + rmItems.map(renderRoadmapItem).join('');
                bindRoadmapEvents();
              }
            }).catch(function () {
              rmBody.innerHTML = '<div class="rm-empty"><p>Erro ao carregar roadmap.</p></div>';
            });
          }
        } else {
          rmBody.style.display   = 'none';
          relBody.style.display  = '';
          if (catTabs) catTabs.style.display = '';
        }
      });
    }

    // ---- Handlers do Roadmap ----
    function bindRoadmapEvents() {
      // Filtros rapidos de status
      var rmFiltersEl = rmBody.querySelector('#nv-rm-filters');
      if (rmFiltersEl) {
        rmFiltersEl.addEventListener('click', function (e) {
          var b = e.target.closest('.tab');
          if (!b) return;
          var st = b.getAttribute('data-rmfilter');
          rmFiltersEl.querySelectorAll('.tab').forEach(function (t) { t.classList.toggle('active', t === b); });
          rmBody.querySelectorAll('.rm-item').forEach(function (el) {
            el.style.display = (st === 'all' || el.getAttribute('data-rmstatus') === st) ? '' : 'none';
          });
        });
      }

      // Votacao (like/dislike) do roadmap
      rmBody.querySelectorAll('[data-rmvotebtn]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id   = btn.getAttribute('data-rmvoteid');
          var vote = btn.getAttribute('data-rmvotebtn');
          var wrap = rmBody.querySelector('.rm-vote[data-rmvote="' + id + '"]');
          if (!wrap) return;

          api('/roadmap-vote', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reader_id: ctx.reader, roadmap_item_id: +id, vote: vote })
          }).then(function (res) {
            if (!res || !res.ok) return;
            try {
              if (res.vote) { localStorage.setItem('nv_rm_vote_' + id, res.vote); }
              else { localStorage.removeItem('nv_rm_vote_' + id); }
            } catch (e) {}

            wrap.querySelectorAll('.rm-vote-btn').forEach(function (b) {
              b.classList.toggle('active', b.getAttribute('data-rmvotebtn') === res.vote);
            });
            var upCount   = wrap.querySelector('.rm-vote-up .rm-vote-count');
            var downCount = wrap.querySelector('.rm-vote-down .rm-vote-count');
            if (upCount)   upCount.textContent   = res.votes_up;
            if (downCount) downCount.textContent = res.votes_down;
          }).catch(function () {});
        });
      });

      // Ver mais / Ver menos
      rmBody.querySelectorAll('.rm-more').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id   = btn.getAttribute('data-rmmore');
          var desc = rmBody.querySelector('.rm-desc[data-rmdesc="' + id + '"]');
          if (!desc) return;
          var expanded = desc.classList.toggle('expanded');
          btn.textContent = expanded ? 'Ver menos' : 'Ver mais';
        });
      });

      // Carinhas de feedback do roadmap
      rmBody.querySelectorAll('[data-rmfbscore]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id   = btn.getAttribute('data-rmfbid');
          var wrap = rmBody.querySelector('.feedback-wrap[data-rmfbw="' + id + '"]');
          if (!wrap) return;
          wrap.querySelectorAll('.face-btn').forEach(function (b) { b.classList.remove('sel'); });
          btn.classList.add('sel');
          var form = rmBody.querySelector('.fb-form[data-rmfbf="' + id + '"]');
          if (form) form.classList.add('open');
        });
      });

      // Cancelar feedback do roadmap
      rmBody.querySelectorAll('[data-rmfbcancel]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id   = btn.getAttribute('data-rmfbcancel');
          var wrap = rmBody.querySelector('.feedback-wrap[data-rmfbw="' + id + '"]');
          var form = rmBody.querySelector('.fb-form[data-rmfbf="' + id + '"]');
          if (form) form.classList.remove('open');
          if (wrap) wrap.querySelectorAll('.face-btn').forEach(function (b) { b.classList.remove('sel'); });
        });
      });

      // Enviar feedback do roadmap
      rmBody.querySelectorAll('.fb-form[data-rmfbf]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
          e.preventDefault();
          var id   = form.getAttribute('data-rmfbf');
          var wrap = rmBody.querySelector('.feedback-wrap[data-rmfbw="' + id + '"]');
          if (!wrap) return;
          var selBtn = wrap.querySelector('.face-btn.sel');
          if (!selBtn) return;
          var score   = selBtn.getAttribute('data-rmfbscore');
          var comment = (form.querySelector('[name=comment]') || {}).value || '';
          api('/roadmap-feedback', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reader_id: ctx.reader, roadmap_item_id: +id, score: score, comment: comment })
          }).then(function () {
            try { localStorage.setItem('nv_rm_fb_' + id, '1'); } catch (e) {}
            var rmItem = rmBody.querySelector('.rm-item[data-rmid="' + id + '"]');
            if (rmItem) {
              var fbWrap = rmItem.querySelector('.feedback-wrap');
              if (fbWrap) {
                var done = document.createElement('div');
                done.className = 'fb-done';
                done.innerHTML = '<i class="fa-solid fa-circle-check"></i> Obrigado pelo feedback!';
                fbWrap.parentNode.replaceChild(done, fbWrap);
              }
            }
          }).catch(function () {});
        });
      });
    }

    rendered = true;
  }

  /* ------------------------------------------------------------------ */
  /*  Toggle                                                              */
  /* ------------------------------------------------------------------ */

  function toggle() {
    open = !open;
    var overlay = shadow.querySelector('.overlay');
    var panel   = shadow.querySelector('.panel');
    overlay.classList.toggle('show', open);
    panel.classList.toggle('show', open);
    if (open) {
      ctx.badge.style.display = 'none';
      api('/read', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ reader_id: ctx.reader })
      }).catch(function () {});
    }
  }

  window.__novidda.toggle = function () { if (rendered) toggle(); };

  /* ------------------------------------------------------------------ */
  /*  Inicializacao                                                       */
  /* ------------------------------------------------------------------ */

  Promise.all([
    api('/config'),
    api('/feed?reader_id=' + encodeURIComponent(ctx.reader))
  ]).then(function (res) {
    var cfg = res[0] || {};

    // Reaplica posicao e cor (garante sincronismo com config atual)
    if (cfg.position === 'left') {
      ctx.host.style.right = 'auto';
      ctx.host.style.left  = '24px';
    } else {
      ctx.host.style.left  = 'auto';
      ctx.host.style.right = '24px';
    }
    if (cfg.accent) ctx.button.style.background = cfg.accent;

    // Atualiza icone do botao flutuante se mudou desde o unread-count
    if (cfg.button_icon && cfg.button_icon !== (ctx.buttonIcon || '')) {
      var iconEl = document.createElement('i');
      cfg.button_icon.split(' ').forEach(function (c) { if (c) iconEl.classList.add(c); });
      ctx.button.innerHTML = '';
      ctx.button.appendChild(iconEl);
      ctx.button.appendChild(ctx.badge);
    }

    build(cfg, res[1] || { items: [] });
    toggle();
  }).catch(function () {});
})();
