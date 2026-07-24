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

  // Container fixo \u2014 posi\u00E7\u00E3o aplicada ap\u00F3s resposta da API
  var host = document.createElement('div');
  host.id  = 'novidda-widget';
  host.setAttribute('style', 'position:fixed;bottom:24px;right:24px;z-index:2147483000;');
  document.body.appendChild(host);

  // Bot\u00E3o flutuante
  var btn = document.createElement('button');
  btn.setAttribute('aria-label', 'Novidades');
  btn.style.cssText =
    'position:relative;width:56px;height:56px;border:none;border-radius:50%;cursor:pointer;' +
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
  host.appendChild(btn);

  // Aplica config vinda do unread-count (posi\u00E7\u00E3o, cor, \u00EDcone)
  function applyBootstrap(d) {
    if (d.position === 'left') {
      host.style.right = 'auto';
      host.style.left  = '24px';
    } else {
      host.style.left  = 'auto';
      host.style.right = '24px';
    }
    if (d.accent) btn.style.background = d.accent;

    // \u00CDcone customizado (FontAwesome class)
    if (d.button_icon) {
      // Garante que FA esteja no head para o \u00EDcone do bot\u00E3o flutuante
      if (!document.querySelector('link[data-nv-fa]')) {
        var faHead = document.createElement('link');
        faHead.rel  = 'stylesheet';
        faHead.href = origin + '/vendor/fontawesome/css/all.min.css';
        faHead.setAttribute('data-nv-fa', '1');
        document.head.appendChild(faHead);
      }
      var iconEl = document.createElement('i');
      d.button_icon.split(' ').forEach(function (c) { if (c) iconEl.classList.add(c); });
      btn.innerHTML = '';
      btn.appendChild(iconEl);
      btn.appendChild(badge);
    }

    window.__novidda = window.__novidda || {};
    window.__novidda.unreadIds  = d.unread_ids || [];
    window.__novidda.buttonIcon = d.button_icon || null;
  }

  fetch(base + '/unread-count?' + withIdentity(''))
    .then(function (r) { return r.json(); })
    .then(function (d) {
      applyBootstrap(d);
      if (d.count > 0) {
        badge.textContent = d.count > 99 ? '99+' : d.count;
        badge.style.display = 'block';
      }
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
    window.__novidda.host   = host;
    window.__novidda.button = btn;
    window.__novidda.badge  = badge;

    var s   = document.createElement('script');
    s.src   = origin + '/widget-app.js?v=6';
    s.async = true;
    document.head.appendChild(s);
  });
})();
