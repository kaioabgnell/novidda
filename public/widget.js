/*!
 * Novidda \u2014 Widget Loader v2
 * Zero depend\u00EAncias. N\u00E3o bloqueia o carregamento da p\u00E1gina hospedeira.
 * Aplica posi\u00E7\u00E3o, cor e \u00EDcone do bot\u00E3o imediatamente via unread-count.
 */
(function () {
  'use strict';
  if (window.__noviddaLoaded) return;
  window.__noviddaLoaded = true;

  var self = document.currentScript ||
    (function () {
      var s = document.querySelectorAll('script[src*="widget.js"]');
      return s[s.length - 1];
    })();
  if (!self) return;

  // Config oficial de integração (ver tela "Instalação & Documentação").
  // O host define window.noviddaConfig = { token, user: {...} } ANTES do script.
  var cfg = {};
  try { cfg = window.noviddaConfig || {}; } catch (e) { cfg = {}; }

  var src   = self.getAttribute('src');
  var url   = new URL(src, location.href);
  var token = cfg.token || url.searchParams.get('token') || self.getAttribute('data-token');
  if (!token) return;

  var origin = url.origin;
  var base   = origin + '/api/v1/widget/' + token;

  function readerId() {
    var key = 'novidda_reader';
    try {
      var v = localStorage.getItem(key);
      if (!v) {
        v = 'r-' + Math.random().toString(36).slice(2) + Date.now().toString(36);
        localStorage.setItem(key, v);
      }
      return v;
    } catch (e) {
      var m = document.cookie.match(/novidda_reader=([^;]+)/);
      if (m) return m[1];
      var id = 'r-' + Math.random().toString(36).slice(2);
      document.cookie = key + '=' + id + ';path=/;max-age=31536000';
      return id;
    }
  }

  var reader = readerId();

  // Identidade do usuário logado no sistema hospedeiro.
  // Quando informada, o estado de "lido/não-lido" passa a ser por usuário
  // (e não por navegador), resolvendo contadores compartilhados quando
  // vários logins usam o mesmo navegador.
  //
  // Fonte de verdade: window.noviddaConfig.user (contrato documentado).
  // Fallbacks legados: data-user-id / window.noviddaSettings.user.
  var hostUser = (cfg.user && typeof cfg.user === 'object') ? cfg.user : null;

  function hostUserId() {
    if (hostUser) {
      if (hostUser.id != null && hostUser.id !== '') return String(hostUser.id);
      if (hostUser.email) return String(hostUser.email);
    }
    var fromAttr = self.getAttribute('data-user-id');
    if (fromAttr) return String(fromAttr);
    try {
      var s = window.noviddaSettings;
      if (s && s.user && s.user.id != null && s.user.id !== '') return String(s.user.id);
    } catch (e) {}
    return '';
  }

  var userId = hostUserId();

  function withIdentity(qs) {
    qs += 'reader_id=' + encodeURIComponent(reader);
    if (userId) qs += '&user_id=' + encodeURIComponent(userId);
    return qs;
  }

  // ------------------------------------------------------------------
  //  Font Awesome isolado do site host
  // ------------------------------------------------------------------
  // O all.min.css NUNCA entra no documento do host: seus seletores globais
  // (.fa-solid:before{content:var(--fa)}, as variaveis --fa e as familias de
  // compatibilidade v4/v5) sobrescreveriam o Font Awesome do cliente e quebrariam
  // os icones dele. No documento entra somente nv-fontface.css, que tem apenas
  // regras @font-face com nomes de familia privados -- sem seletores, sem colisao.
  function nvFontFaceOnce() {
    if (document.querySelector('link[data-nv-fa-font]')) return;
    var l = document.createElement('link');
    l.rel  = 'stylesheet';
    l.href = origin + '/vendor/fontawesome/css/nv-fontface.css';
    l.setAttribute('data-nv-fa-font', '1');
    document.head.appendChild(l);
  }

  // Aplica os seletores do Font Awesome dentro de um shadow root (encapsulado,
  // nao vaza para o host) e remapeia as familias para as fontes privadas
  // registradas por nvFontFaceOnce() -- @font-face em shadow DOM tem suporte
  // inconsistente entre navegadores, por isso o registro fica no documento.
  function nvInjectFa(root) {
    if (!root || root.querySelector('link[data-nv-fa]')) return;
    nvFontFaceOnce();
    var l = document.createElement('link');
    l.rel  = 'stylesheet';
    l.href = origin + '/vendor/fontawesome/css/all.min.css';
    l.setAttribute('data-nv-fa', '1');
    root.appendChild(l);
    var s = document.createElement('style');
    s.textContent =
      '.fa,.fas,.fa-solid{font-family:"NvFA6Free"!important;font-weight:900!important}' +
      '.far,.fa-regular{font-family:"NvFA6Free"!important;font-weight:400!important}' +
      '.fab,.fa-brands{font-family:"NvFA6Brands"!important;font-weight:400!important}';
    root.appendChild(s);
  }

  // Container fixo \u2014 posi\u00E7\u00E3o aplicada ap\u00F3s resposta da API.
  // O botao vive dentro de um shadow root para que o CSS do Font Awesome usado
  // pelo icone customizado fique isolado do site host.
  var host = document.createElement('div');
  host.id  = 'novidda-widget';
  host.setAttribute('style', 'position:fixed;bottom:24px;right:24px;z-index:2147483000;');
  document.body.appendChild(host);
  var btnRoot = host.attachShadow({ mode: 'open' });

  // ------------------------------------------------------------------
  //  Minimizar / restaurar o botao flutuante
  // ------------------------------------------------------------------
  // Regra de produto: o usuario pode recolher o botao para a lateral, mas
  // apenas quando nao ha novidades pendentes. Como minimizar so e permitido
  // com zero nao-lidos, qualquer contagem > 0 significa changelog novo desde
  // entao -- por isso a mesma verificacao resolve os dois requisitos:
  // restaurar automaticamente quando sai um changelog e bloquear o recolhimento
  // enquanto houver algo por ler. Nao e preciso rastrear ids.
  var minKey = 'novidda_min_' + token + (userId ? '_' + userId : '');

  function minRead() {
    try { return localStorage.getItem(minKey) === '1'; } catch (e) { return false; }
  }
  function minWrite(v) {
    // Sem localStorage a preferencia vale so para a sessao \u2014 degradacao aceitavel.
    try { v ? localStorage.setItem(minKey, '1') : localStorage.removeItem(minKey); } catch (e) {}
  }

  var minimized = minRead();
  var unread    = 0;
  var side      = 'right';

  var chevron = {
    right: '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>',
    left:  '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>'
  };

  // CSS no shadow root do botao: permite hover/focus/media queries (impossivel
  // com style inline) sem vazar nada para o site host.
  var btnStyle = document.createElement('style');
  btnStyle.textContent =
    '.nv-shell{position:relative;display:block}' +
    '.nv-hide{position:absolute;top:-6px;left:-6px;width:20px;height:20px;padding:0;border:none;' +
      'border-radius:50%;cursor:pointer;background:#2d3436;color:#fff;box-shadow:0 2px 6px rgba(0,0,0,.3);' +
      'display:flex;align-items:center;justify-content:center;opacity:0;transform:scale(.8);' +
      'pointer-events:none;transition:opacity .15s,transform .15s}' +
    '.nv-shell:hover .nv-hide,.nv-hide:focus-visible{opacity:1;transform:scale(1);pointer-events:auto}' +
    '@media (hover:none){.nv-hide{opacity:.9;transform:scale(1);pointer-events:auto}}' +
    '.nv-handle{width:20px;height:44px;padding:0;border:none;cursor:pointer;background:#6c5ce7;color:#fff;' +
      'display:none;align-items:center;justify-content:center;box-shadow:0 3px 12px rgba(0,0,0,.22);' +
      'transition:width .15s,filter .15s}' +
    '.nv-handle:hover{width:26px;filter:brightness(1.08)}' +
    '.nv-handle.nv-right{border-radius:10px 0 0 10px}' +
    '.nv-handle.nv-left{border-radius:0 10px 10px 0}' +
    '@media (prefers-reduced-motion:reduce){.nv-hide,.nv-handle{transition:none}}';
  btnRoot.appendChild(btnStyle);

  // O botao fica num shell para que o chip de minimizar seja irmao (e nao filho)
  // do botao: widget.js e widget-app.js reescrevem btn.innerHTML ao aplicar o
  // icone customizado, o que apagaria um filho.
  var shell = document.createElement('div');
  shell.className = 'nv-shell';

  // Alca do estado minimizado
  var handle = document.createElement('button');
  handle.className = 'nv-handle nv-right';
  handle.setAttribute('aria-label', 'Mostrar novidades');
  handle.title = 'Mostrar novidades';
  handle.innerHTML = chevron.left;

  // Chip que recolhe o botao
  var hideBtn = document.createElement('button');
  hideBtn.className = 'nv-hide';
  hideBtn.setAttribute('aria-label', 'Ocultar \u2014 fica recolhido na lateral');
  hideBtn.title = 'Ocultar \u2014 fica recolhido na lateral';
  hideBtn.innerHTML = chevron.right;

  // Fonte unica de verdade do layout: posicao lateral + estado minimizado.
  function applyMinState() {
    var canMin = unread === 0;

    // Novidade pendente: restaura e impede recolher.
    if (minimized && !canMin) { minimized = false; minWrite(false); }

    hideBtn.style.display = canMin ? '' : 'none';
    shell.style.display   = minimized ? 'none' : '';
    handle.style.display  = minimized ? 'flex' : 'none';

    handle.className = 'nv-handle nv-' + side;
    handle.innerHTML = side === 'left' ? chevron.right : chevron.left;
    hideBtn.innerHTML = side === 'left' ? chevron.left : chevron.right;

    // Minimizado encosta na borda; normal mantem o respiro de 24px.
    var off = minimized ? '0px' : '24px';
    if (side === 'left') { host.style.left = off;  host.style.right = 'auto'; }
    else                 { host.style.right = off; host.style.left  = 'auto'; }
  }

  hideBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    if (unread > 0) return;          // trava de seguranca
    minimized = true;
    minWrite(true);
    applyMinState();
    handle.focus();
  });

  handle.addEventListener('click', function (e) {
    e.stopPropagation();
    minimized = false;
    minWrite(false);
    applyMinState();
    btn.focus();
  });

  // Bot\u00E3o flutuante
  var btn = document.createElement('button');
  btn.setAttribute('aria-label', 'Novidades');
  btn.style.cssText =
    'position:relative;width:50px;height:50px;border:none;border-radius:50%;cursor:pointer;' +
    'background:#6c5ce7;color:#fff;font-size:22px;box-shadow:0 6px 18px rgba(0,0,0,.22);' +
    'display:flex;align-items:center;justify-content:center;transition:transform .15s,box-shadow .15s;';
  btn.onmouseenter = function () { btn.style.transform = 'scale(1.08)'; btn.style.boxShadow = '0 8px 24px rgba(0,0,0,.3)'; };
  btn.onmouseleave = function () { btn.style.transform = 'scale(1)';    btn.style.boxShadow = '0 6px 18px rgba(0,0,0,.22)'; };

  // SVG de sino \u2014 padr\u00E3o enquanto o \u00EDcone customizado n\u00E3o carrega
  var bellSVG =
    '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
    '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>' +
    '<path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>';
  btn.innerHTML = bellSVG;

  var badge = document.createElement('span');
  badge.style.cssText =
    'position:absolute;top:-4px;right:-4px;min-width:20px;height:20px;padding:0 5px;border-radius:10px;' +
    'background:#e74c3c;color:#fff;font:700 12px/20px system-ui,sans-serif;text-align:center;display:none;';
  btn.appendChild(badge);
  shell.appendChild(btn);
  shell.appendChild(hideBtn);
  btnRoot.appendChild(shell);
  btnRoot.appendChild(handle);
  applyMinState();

  // Aplica config vinda do unread-count (posi\u00E7\u00E3o, cor, \u00EDcone)
  function applyBootstrap(d) {
    side = d.position === 'left' ? 'left' : 'right';
    if (d.accent) {
      btn.style.background    = d.accent;
      handle.style.background = d.accent;
    }

    // \u00CDcone customizado (FontAwesome class)
    if (d.button_icon) {
      // FA aplicado apenas no shadow root do bot\u00E3o \u2014 nunca no documento do host
      nvInjectFa(btnRoot);
      var iconEl = document.createElement('i');
      d.button_icon.split(' ').forEach(function (c) { if (c) iconEl.classList.add(c); });
      btn.innerHTML = '';
      btn.appendChild(iconEl);
      btn.appendChild(badge);
    }

    window.__novidda = window.__novidda || {};
    window.__novidda.unreadIds  = d.unread_ids || [];
    window.__novidda.buttonIcon = d.button_icon || null;

    applyMinState();
  }

  // Atualiza a contagem de nao-lidos: pinta o badge e reavalia o estado
  // minimizado (changelog novo => restaura e bloqueia o recolhimento).
  function setUnread(n) {
    unread = n > 0 ? n : 0;
    if (unread > 0) {
      badge.textContent   = unread > 99 ? '99+' : unread;
      badge.style.display = 'block';
    } else {
      badge.style.display = 'none';
    }
    applyMinState();
  }

  fetch(base + '/unread-count?' + withIdentity(''))
    .then(function (r) { return r.json(); })
    .then(function (d) {
      applyBootstrap(d);
      setUnread(d.count || 0);
      // Lazy-load módulo de banners contextuais
      if (d.has_contextual_banners) {
        window.__novidda = window.__novidda || {};
        window.__novidda.base   = window.__novidda.base   || base;
        window.__novidda.origin = window.__novidda.origin || origin;
        window.__novidda.reader = window.__novidda.reader || reader;
        window.__novidda.userId = window.__novidda.userId || userId;
        window.__novidda.user   = window.__novidda.user   || hostUser;
        var cs = document.createElement('script');
        cs.src = origin + '/widget-contextual.js?v=' + encodeURIComponent(host.dataset.v || '1');
        document.head.appendChild(cs);
      }
    })
    .catch(function () {});

  // Lazy: carrega o app de conte\u00FAdo s\u00F3 no primeiro clique
  var appLoaded = false;
  btn.addEventListener('click', function () {
    if (appLoaded) {
      if (window.__novidda && window.__novidda.toggle) window.__novidda.toggle();
      return;
    }
    appLoaded = true;
    window.__novidda        = window.__novidda || {};
    window.__novidda.base   = base;
    window.__novidda.origin = origin;
    window.__novidda.reader = reader;
    window.__novidda.userId = userId;
    window.__novidda.user   = hostUser;
    window.__novidda.host     = host;
    window.__novidda.button   = btn;
    window.__novidda.badge    = badge;
    window.__novidda.btnRoot  = btnRoot;
    window.__novidda.injectFa = nvInjectFa;
    // O loader e a fonte unica de verdade do layout do botao: o app usa estas
    // funcoes em vez de mexer em host.style, que sobrescreveria o estado minimizado.
    window.__novidda.setSide   = function (pos) { side = pos === 'left' ? 'left' : 'right'; applyMinState(); };
    window.__novidda.setUnread = setUnread;

    var s   = document.createElement('script');
    s.src   = origin + '/widget-app.js?v=10';
    s.async = true;
    document.head.appendChild(s);
  });
})();
