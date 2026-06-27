(function () {
  'use strict';

  var nv     = window.__novidda || {};
  var base   = nv.base;
  var origin = nv.origin;
  var reader = nv.reader;

  if (!base || !reader) return;

  var STORAGE_KEY = 'nv_ctxb_' + btoa(base).replace(/=/g, '');

  // ── Persistência (localStorage) ──────────────────────────────────────────
  function getState() {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'); } catch (e) { return {}; }
  }
  function setState(s) {
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(s)); } catch (e) {}
  }

  // ── Correspondência de URL ────────────────────────────────────────────────
  function matchRule(rule, url) {
    var p = rule.pattern;
    // API returns {mode, pattern} in sub-arrays (no match_mode key)
    switch (rule.mode || rule.match_mode) {
      case 'exact':       return url === p || location.pathname === p;
      case 'contains':    return url.indexOf(p) !== -1;
      case 'starts_with': return url.indexOf(p) === 0 || location.href.indexOf(p) !== -1;
      case 'regex':
        try { return new RegExp(p).test(url); } catch (e) { return false; }
      default: return false;
    }
  }

  function bannerMatchesPage(banner) {
    var fullUrl  = location.href;
    var path     = location.pathname + location.search;
    // API returns banner.rules = { include: [{mode, pattern}], exclude: [...] }
    var includes = (banner.rules && banner.rules.include) ? banner.rules.include : [];
    var excludes = (banner.rules && banner.rules.exclude) ? banner.rules.exclude : [];

    // Sem regras de inclusão → não exibe
    if (!includes.length) return false;

    var included = includes.some(function (r) { return matchRule(r, path) || matchRule(r, fullUrl); });
    if (!included) return false;

    var excluded = excludes.some(function (r) { return matchRule(r, path) || matchRule(r, fullUrl); });
    return !excluded;
  }

  // ── Frequência ────────────────────────────────────────────────────────────
  function shouldShow(banner, state) {
    var s = state[banner.id] || {};
    if (s.clicked) return false; // always suppress after click
    switch (banner.frequency) {
      case 'once_per_user': return !s.shown;
      case 'until_clicked': return !s.clicked;
      case 'times_capped':  return (s.count || 0) < (banner.frequency_cap || 3);
      default: return true;
    }
  }

  function recordShown(banner, state) {
    var s  = state[banner.id] || {};
    s.shown = true;
    s.count = (s.count || 0) + 1;
    state[banner.id] = s;
    setState(state);
  }

  function recordDismissed(banner, state) {
    var s = state[banner.id] || {};
    s.dismissed = true;
    state[banner.id] = s;
    setState(state);
  }

  function recordClicked(banner, state) {
    var s = state[banner.id] || {};
    s.clicked = true;
    state[banner.id] = s;
    setState(state);
  }

  // ── Eventos para o servidor ───────────────────────────────────────────────
  function sendEvent(bannerId, type) {
    try {
      fetch(base + '/contextual/event', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ banner_id: bannerId, event: type, reader_id: reader }),
        keepalive: true,
      }).catch(function () {});
    } catch (e) {}
  }

  // ── CSS para o Shadow DOM ─────────────────────────────────────────────────
  function buildCSS(accent, dark) {
    var bg      = dark ? '#1e1e2e' : '#ffffff';
    var ink     = dark ? '#e2e2f0' : '#1a1a2e';
    var mute    = dark ? '#888' : '#888';
    var shadow  = dark ? '0 4px 24px rgba(0,0,0,.5)' : '0 4px 24px rgba(0,0,0,.18)';
    var barBg   = accent;
    var barTxt  = '#ffffff';
    return [
      ':host { all: initial; }',
      '.nv-toast {',
      '  position: fixed;',
      '  max-width: 320px;',
      '  background: ' + bg + ';',
      '  color: ' + ink + ';',
      '  border-radius: 12px;',
      '  box-shadow: ' + shadow + ';',
      '  padding: 16px 18px 16px 16px;',
      '  z-index: 2147483640;',
      '  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;',
      '  font-size: 13px;',
      '  line-height: 1.45;',
      '  border-left: 3px solid ' + accent + ';',
      '  transition: opacity .25s, transform .3s;',
      '  opacity: 0;',
      '  pointer-events: none;',
      '}',
      '.nv-toast.nv-in { opacity: 1; pointer-events: auto; }',
      '.nv-toast.nv-bottom-right { bottom: 82px; right: 16px; transform: translateY(12px); }',
      '.nv-toast.nv-bottom-right.nv-in { transform: translateY(0); }',
      '.nv-toast.nv-bottom-left  { bottom: 82px; left:  16px; transform: translateY(12px); }',
      '.nv-toast.nv-bottom-left.nv-in  { transform: translateY(0); }',
      '.nv-toast.nv-top-right    { top: 16px; right: 16px; transform: translateY(-12px); }',
      '.nv-toast.nv-top-right.nv-in    { transform: translateY(0); }',
      '.nv-toast.nv-top-left     { top: 16px; left: 16px; transform: translateY(-12px); }',
      '.nv-toast.nv-top-left.nv-in     { transform: translateY(0); }',
      '.nv-bar {',
      '  position: fixed;',
      '  left: 0; right: 0;',
      '  background: ' + barBg + ';',
      '  color: ' + barTxt + ';',
      '  display: flex;',
      '  align-items: center;',
      '  justify-content: space-between;',
      '  padding: 10px 18px;',
      '  z-index: 2147483640;',
      '  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;',
      '  font-size: 13px;',
      '  transition: opacity .25s, transform .3s;',
      '  opacity: 0; pointer-events: none;',
      '}',
      '.nv-bar.nv-in { opacity: 1; pointer-events: auto; }',
      '.nv-bar.nv-top-bar    { top: 0; transform: translateY(-100%); }',
      '.nv-bar.nv-top-bar.nv-in { transform: translateY(0); }',
      '.nv-bar.nv-bottom-bar { bottom: 0; transform: translateY(100%); }',
      '.nv-bar.nv-bottom-bar.nv-in { transform: translateY(0); }',
      '.nv-close {',
      '  background: none; border: none; cursor: pointer;',
      '  font-size: 18px; line-height: 1; padding: 0 0 0 12px;',
      '  color: inherit; opacity: .7; flex-shrink: 0;',
      '}',
      '.nv-close:hover { opacity: 1; }',
      '.nv-copy { flex: 1; font-weight: 600; margin-right: 12px; }',
      '.nv-toast-copy { font-weight: 600; margin-bottom: 8px; padding-right: 20px; }',
      '.nv-cta {',
      '  display: inline-block; margin-top: 6px;',
      '  padding: 6px 14px; border-radius: 6px;',
      '  background: ' + accent + '; color: #fff;',
      '  font-size: 12px; font-weight: 700; text-decoration: none;',
      '  transition: opacity .15s;',
      '}',
      '.nv-cta:hover { opacity: .85; }',
      '.nv-bar .nv-cta { background: rgba(255,255,255,.25); color: #fff; padding: 5px 12px; margin-top: 0; }',
      '.nv-x {',
      '  position: absolute; top: 8px; right: 10px;',
      '  background: none; border: none; cursor: pointer;',
      '  font-size: 17px; line-height: 1; color: ' + mute + ';',
      '}',
      '.nv-x:hover { color: ' + ink + '; }',
    ].join('\n');
  }

  // ── Renderiza um banner ───────────────────────────────────────────────────
  function renderBanner(banner, state, shadowRoot) {
    var accent = nv.accent || '#7B61FF';
    var dark   = document.body.classList.contains('dark');

    var el = document.createElement('div');

    // API: banner.copy = { title, description, icon }, banner.cta = { text, url, new_tab } | null
    var copyText = (banner.copy && banner.copy.title) ? banner.copy.title : '';
    var ctaText  = banner.cta ? banner.cta.text : null;
    var ctaUrl   = banner.cta ? (banner.cta.url || '#') : '#';
    var ctaNew   = banner.cta ? !!banner.cta.new_tab : false;

    if (banner.style === 'toast') {
      el.className = 'nv-toast nv-' + (banner.position || 'bottom_right').replace(/_/g, '-');
      el.innerHTML =
        '<div class="nv-toast-copy">' + escHtml(copyText) + '</div>' +
        (ctaText ? '<a class="nv-cta" href="' + escAttr(ctaUrl) + '"' + (ctaNew ? ' target="_blank" rel="noopener"' : '') + '>' + escHtml(ctaText) + '</a>' : '') +
        '<button class="nv-x" aria-label="Fechar">&times;</button>';
    } else {
      var barCls = banner.style === 'top_bar' ? 'nv-bar nv-top-bar' : 'nv-bar nv-bottom-bar';
      el.className = barCls;
      el.innerHTML =
        '<span class="nv-copy">' + escHtml(copyText) + '</span>' +
        (ctaText ? '<a class="nv-cta" href="' + escAttr(ctaUrl) + '"' + (ctaNew ? ' target="_blank" rel="noopener"' : '') + '>' + escHtml(ctaText) + '</a>' : '') +
        '<button class="nv-close" aria-label="Fechar">&times;</button>';
    }

    shadowRoot.appendChild(el);

    // Anima entrada
    requestAnimationFrame(function () {
      requestAnimationFrame(function () { el.classList.add('nv-in'); });
    });

    // Evento "shown"
    recordShown(banner, state);
    sendEvent(banner.id, 'contextual_shown');

    // Auto-dismiss
    var dismissTimer = null;
    if (banner.auto_dismiss_seconds) {
      dismissTimer = setTimeout(function () { dismissEl(el, banner, state, false); }, banner.auto_dismiss_seconds * 1000);
    }

    function dismissEl(element, b, s, clicked) {
      if (dismissTimer) clearTimeout(dismissTimer);
      element.classList.remove('nv-in');
      setTimeout(function () {
        if (element.parentNode) element.parentNode.removeChild(element);
      }, 350);
      if (clicked) {
        recordClicked(b, s);
        sendEvent(b.id, 'contextual_clicked');
      } else {
        recordDismissed(b, s);
        sendEvent(b.id, 'contextual_dismissed');
      }
    }

    // Fechar (botão ×)
    var closeBtn = el.querySelector('.nv-x, .nv-close');
    if (closeBtn) {
      closeBtn.addEventListener('click', function (e) {
        e.preventDefault();
        dismissEl(el, banner, state, false);
      });
    }

    // CTA click
    var ctaEl = el.querySelector('.nv-cta');
    if (ctaEl) {
      ctaEl.addEventListener('click', function () {
        dismissEl(el, banner, state, true);
      });
    }
  }

  // ── Escaping ──────────────────────────────────────────────────────────────
  function escHtml(s) {
    return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
  function escAttr(s) {
    return String(s || '').replace(/"/g, '&quot;');
  }

  // ── Cria host com Shadow DOM ───────────────────────────────────────────────
  function createShadowHost(accent, dark) {
    var host = document.createElement('div');
    host.id  = 'nv-contextual-host';
    document.body.appendChild(host);
    var shadow = host.attachShadow({ mode: 'closed' });
    var styleEl = document.createElement('style');
    styleEl.textContent = buildCSS(accent, dark);
    shadow.appendChild(styleEl);
    return shadow;
  }

  // ── Processamento ─────────────────────────────────────────────────────────
  function processBanners(banners) {
    if (!banners || !banners.length) return;

    var state  = getState();
    var accent = nv.accent || '#7B61FF';
    var dark   = document.body.classList.contains('dark');

    var toShow = banners.filter(function (b) {
      return bannerMatchesPage(b) && shouldShow(b, state);
    });

    if (!toShow.length) return;

    var shadow = createShadowHost(accent, dark);

    // Mostra somente 1 por vez (o primeiro elegível)
    renderBanner(toShow[0], state, shadow);
  }

  // ── Busca dados e executa ─────────────────────────────────────────────────
  function run() {
    if (document.getElementById('nv-contextual-host')) return; // já inicializado
    fetch(base + '/contextual', { headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (data) {
        if (data && data.banners) processBanners(data.banners);
      })
      .catch(function () {});
  }

  // ── SPA listener ─────────────────────────────────────────────────────────
  function patchHistory() {
    var orig_push    = history.pushState;
    var orig_replace = history.replaceState;
    function onNav() {
      setTimeout(function () {
        // Remove host anterior se existir
        var old = document.getElementById('nv-contextual-host');
        if (old) old.parentNode.removeChild(old);
        run();
      }, 350);
    }
    history.pushState = function () {
      orig_push.apply(this, arguments);
      onNav();
    };
    history.replaceState = function () {
      orig_replace.apply(this, arguments);
      onNav();
    };
    window.addEventListener('popstate', onNav);
  }

  // ── Inicializa ────────────────────────────────────────────────────────────
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { run(); patchHistory(); });
  } else {
    run();
    patchHistory();
  }
})();
