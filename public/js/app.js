/* Novidda — JS do painel admin. Vanilla, sem dependências de build. */
(function () {
  'use strict';

  // ---- Modo escuro: localStorage + prefers-color-scheme ----
  var KEY = 'novidda-theme';

  function apply(theme) {
    document.body.classList.toggle('dark', theme === 'dark');
    var icon = document.querySelector('[data-theme-icon]');
    if (icon) {
      icon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }
  }

  function current() {
    var saved = localStorage.getItem(KEY);
    if (saved) return saved;
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  // Aplica o quanto antes para evitar flash.
  apply(current());

  document.addEventListener('DOMContentLoaded', function () {
    apply(current());

    var toggle = document.querySelector('[data-theme-toggle]');
    if (toggle) {
      toggle.addEventListener('click', function () {
        var next = document.body.classList.contains('dark') ? 'light' : 'dark';
        localStorage.setItem(KEY, next);
        apply(next);
      });
    }

    // Confirmação para ações destrutivas (forms com data-confirm).
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        if (!window.confirm(form.getAttribute('data-confirm'))) e.preventDefault();
      });
    });
  });
})();
