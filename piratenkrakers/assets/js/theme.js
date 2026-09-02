/**
 * Theme chrome: nav, PJAX (keeps stream alive), request form.
 */
(function () {
  'use strict';

  function qs(sel, root) { return (root || document).querySelector(sel); }
  function qsa(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  function closeNav() {
    var toggle = qs('[data-pk-nav-toggle]');
    var nav = qs('#pk-nav');
    document.body.classList.remove('nav-open');
    if (toggle) toggle.setAttribute('aria-expanded', 'false');
    if (nav) nav.classList.remove('is-open');
  }

  function openNav() {
    var toggle = qs('[data-pk-nav-toggle]');
    var nav = qs('#pk-nav');
    document.body.classList.add('nav-open');
    if (toggle) toggle.setAttribute('aria-expanded', 'true');
    if (nav) nav.classList.add('is-open');
  }

  function bindNav() {
    var toggle = qs('[data-pk-nav-toggle]');
    if (!toggle || toggle.dataset.bound) return;
    toggle.dataset.bound = '1';
    toggle.addEventListener('click', function () {
      if (document.body.classList.contains('nav-open')) closeNav();
      else openNav();
    });
    document.addEventListener('keyup', function (e) {
      if (e.key === 'Escape') closeNav();
    });
    var nav = qs('#pk-nav');
    if (nav) {
      nav.addEventListener('click', function (e) {
        var a = e.target.closest('a');
        if (a) closeNav();
      });
    }
  }

  function sameOrigin(url) {
    try {
      var u = new URL(url, window.location.href);
      return u.origin === window.location.origin;
    } catch (e) {
      return false;
    }
  }

  function shouldIntercept(a, event) {
    if (!a || event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
    if (a.target && a.target !== '_self') return false;
    if (a.hasAttribute('download')) return false;
    var href = a.getAttribute('href');
    if (!href || href.charAt(0) === '#' || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) return false;
    if (!sameOrigin(href)) return false;
    if (/\/wp-admin|\/wp-login|\/feed|wp-json/.test(href)) return false;
    if (/\.(pdf|zip|mp3|jpg|png|svg)(\?|$)/i.test(href)) return false;
    return true;
  }

  function updateNav(url) {
    qsa('.pk-nav-list a').forEach(function (a) {
      var active = a.href === url || (a.pathname !== '/' && url.indexOf(a.href) === 0);
      a.classList.toggle('current', active);
      if (a.parentElement) a.parentElement.classList.toggle('current-menu-item', active);
    });
  }

  function runScripts(container) {
    qsa('script', container).forEach(function (old) {
      var s = document.createElement('script');
      Array.prototype.forEach.call(old.attributes, function (attr) {
        s.setAttribute(attr.name, attr.value);
      });
      s.textContent = old.textContent;
      old.parentNode.replaceChild(s, old);
    });
  }

  function navigate(url, push) {
    document.body.classList.add('pk-navigating');
    fetch(url, { credentials: 'same-origin', headers: { 'X-PK-PJAX': '1' } })
      .then(function (res) { return res.text().then(function (html) { return { res: res, html: html }; }); })
      .then(function (pack) {
        var doc = new DOMParser().parseFromString(pack.html, 'text/html');
        var nextApp = doc.querySelector('#pk-app');
        var app = qs('#pk-app');
        if (!nextApp || !app) {
          window.location.href = url;
          return;
        }
        app.innerHTML = nextApp.innerHTML;
        document.title = doc.title;
        var htmlClass = doc.documentElement.getAttribute('class');
        if (htmlClass) document.documentElement.className = htmlClass;
        document.body.className = doc.body.className;
        document.body.classList.add('pk-player-ready');
        if (window.PKRadio && window.PKRadio.state.playing) document.body.classList.add('is-playing');
        if (push) history.pushState({ pk: true }, '', pack.res.url || url);
        updateNav(window.location.href);
        closeNav();
        window.scrollTo(0, 0);
        if (window.PKPlayerUI) window.PKPlayerUI.sync();
        bindRequestForm();
        runScripts(app);
        document.body.classList.remove('pk-navigating');
        document.dispatchEvent(new CustomEvent('pk:navigated', { detail: { url: url } }));
      })
      .catch(function () {
        window.location.href = url;
      });
  }

  function bindPjax() {
    if (document.documentElement.dataset.pkPjax) return;
    document.documentElement.dataset.pkPjax = '1';
    document.addEventListener('click', function (e) {
      var a = e.target.closest('a');
      if (!shouldIntercept(a, e)) return;
      e.preventDefault();
      navigate(a.href, true);
    });
    window.addEventListener('popstate', function () {
      navigate(window.location.href, false);
    });
  }

  function bindRequestForm() {
    var form = qs('#pk-request-form');
    if (!form || form.dataset.bound) return;
    form.dataset.bound = '1';
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var status = qs('[data-pk-form-status]', form);
      var cfg = window.PK_RADIO_CONFIG || {};
      var data = {
        name: (form.elements.name && form.elements.name.value) || '',
        place: (form.elements.place && form.elements.place.value) || '',
        song: (form.elements.song && form.elements.song.value) || '',
        message: (form.elements.message && form.elements.message.value) || '',
        phone: (form.elements.phone && form.elements.phone.value) || '',
        consent: form.elements.consent && form.elements.consent.checked ? 1 : 0,
        website: (form.elements.website && form.elements.website.value) || '',
        nonce: cfg.requestNonce || '',
        _wpnonce: (form.elements._wpnonce && form.elements._wpnonce.value) || cfg.requestNonce || ''
      };
      if (status) {
        status.textContent = 'Versturen…';
        status.className = 'pk-form-status is-pending';
      }
      fetch((cfg.homeUrl || '/') + 'wp-json/pk/v1/request', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': cfg.restNonce || ''
        },
        body: JSON.stringify(data)
      })
        .then(function (res) { return res.json().then(function (json) { return { res: res, json: json }; }); })
        .then(function (pack) {
          if (status) {
            status.textContent = pack.json.message || (pack.res.ok ? 'Verstuurd.' : 'Er ging iets mis.');
            status.className = 'pk-form-status ' + (pack.json.ok ? 'is-ok' : 'is-err');
          }
          if (pack.json.ok) form.reset();
        })
        .catch(function () {
          if (status) {
            status.textContent = 'Verbinding mislukt. Probeer het later.';
            status.className = 'pk-form-status is-err';
          }
        });
    });
  }

  function headerScroll() {
    var last = 0;
    window.addEventListener('scroll', function () {
      var y = window.scrollY || 0;
      document.body.classList.toggle('pk-scrolled', y > 12);
      last = y;
    }, { passive: true });
  }

  function boot() {
    bindNav();
    bindPjax();
    bindRequestForm();
    headerScroll();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
