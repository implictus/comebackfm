/**
 * Player UI — binds DOM to PKRadio engine.
 */
(function () {
  'use strict';

  function qs(sel, root) { return (root || document).querySelector(sel); }
  function qsa(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  function setText(nodes, value) {
    nodes.forEach(function (el) {
      if (el && value !== undefined && value !== null) el.textContent = String(value);
    });
  }

  function PlayerUI() {
    this.engine = window.PKRadio;
  }

  PlayerUI.prototype.bind = function () {
    var engine = this.engine;
    if (!engine || !window.PK_RADIO_CONFIG) return;
    if (!engine._started) engine.init(window.PK_RADIO_CONFIG);

    var self = this;
    document.body.addEventListener('click', function (e) {
      var playBtn = e.target.closest('[data-pk-play]');
      if (playBtn) {
        e.preventDefault();
        var sid = playBtn.getAttribute('data-pk-stream-id');
        if (sid) engine.play(sid);
        else engine.toggle();
        return;
      }
      var muteBtn = e.target.closest('[data-pk-mute]');
      if (muteBtn) {
        e.preventDefault();
        engine.toggleMute();
        return;
      }
      var prev = e.target.closest('[data-pk-stream-prev]');
      var next = e.target.closest('[data-pk-stream-next]');
      if (prev || next) {
        e.preventDefault();
        var ids = Object.keys((window.PK_RADIO_CONFIG && window.PK_RADIO_CONFIG.streams) || {});
        if (!ids.length) return;
        var i = ids.indexOf(engine.state.streamId);
        if (i < 0) i = 0;
        i = prev ? (i - 1 + ids.length) % ids.length : (i + 1) % ids.length;
        engine.switchStream(ids[i], engine.state.playing);
      }
    });

    qsa('[data-pk-volume]').forEach(function (input) {
      input.value = String(engine.state.volume);
      input.addEventListener('input', function () {
        engine.setVolume(input.value);
      });
    });

    qsa('[data-pk-stream]').forEach(function (select) {
      select.value = engine.state.streamId;
      select.addEventListener('change', function () {
        engine.switchStream(select.value, engine.state.playing);
      });
    });

    engine.on('play', function () { self.sync(); });
    engine.on('pause', function () { self.sync(); });
    engine.on('nowplaying', function () { self.sync(); });
    engine.on('status', function () { self.sync(); });
    engine.on('volume', function () { self.sync(); });
    engine.on('offline', function () { self.sync(); });
    engine.on('online', function () { self.sync(); });
    engine.on('error', function (err) {
      self.sync();
      if (err && err.code === 'unconfigured') {
        qsa('[data-pk-live-region]').forEach(function (el) {
          el.textContent = err.message;
        });
      }
    });

    this.sync();
    document.body.classList.add('pk-player-ready');
  };

  PlayerUI.prototype.sync = function () {
    var st = this.engine.getState();
    var np = st.nowPlaying || {};
    var i18n = (window.PK_RADIO_CONFIG && window.PK_RADIO_CONFIG.i18n) || {};
    var fallbackArt = (window.PK_RADIO_CONFIG && window.PK_RADIO_CONFIG.fallbackArtwork) || '';

    document.body.classList.toggle('is-playing', !!st.playing);
    document.body.classList.toggle('is-offline', !!st.offline);
    document.body.classList.toggle('pk-demo', !!np.demo);
    document.body.classList.toggle('is-loading-stream', !!st.loading);

    qsa('[data-pk-play]').forEach(function (btn) {
      var label = st.playing ? (i18n.pause || 'Pauzeren') : (i18n.play || 'Afspelen');
      btn.setAttribute('aria-label', label);
      btn.setAttribute('aria-pressed', st.playing ? 'true' : 'false');
    });

    qsa('[data-pk-mute]').forEach(function (btn) {
      btn.setAttribute('aria-pressed', st.muted ? 'true' : 'false');
      btn.setAttribute('aria-label', st.muted ? (i18n.unmute || 'Geluid aan') : (i18n.mute || 'Dempen'));
      btn.classList.toggle('is-muted', !!st.muted);
    });

    setText(qsa('[data-pk-field="artist"]'), np.artist || i18n.unknownArtist || 'PiratenKrakers.nl');
    setText(qsa('[data-pk-field="title"]'), np.title || i18n.unknownTitle || 'Live radio');
    setText(qsa('[data-pk-field="dj"]'), np.dj || '');
    setText(qsa('[data-pk-field="show"]'), np.show || '');
    setText(qsa('[data-pk-field="listeners"]'), np.listeners != null ? np.listeners : '0');
    setText(qsa('[data-pk-field="streamName"]'), np.stream_name || '');

    var art = np.artwork || fallbackArt;
    if (art) {
      qsa('[data-pk-artwork]').forEach(function (img) {
        if (img.getAttribute('src') !== art) img.setAttribute('src', art);
      });
    }

    qsa('[data-pk-live-badge]').forEach(function (el) {
      el.classList.toggle('is-live', !st.offline);
      el.classList.toggle('is-offline', !!st.offline);
    });
    setText(qsa('[data-pk-live-label]'), st.offline ? 'OFFLINE' : (i18n.live || 'LIVE'));

    qsa('[data-pk-offline-msg]').forEach(function (el) {
      if (el.hasAttribute('hidden') || el.classList.contains('pk-hero-offline') || el.classList.contains('pk-player-offline')) {
        if (el.hasAttribute('hidden')) {
          if (st.offline) el.removeAttribute('hidden');
          else el.setAttribute('hidden', '');
        }
        el.classList.toggle('is-on', !!st.offline);
      }
    });

    qsa('[data-pk-volume]').forEach(function (input) {
      if (document.activeElement !== input) input.value = String(st.volume);
    });

    var song = [np.artist, np.title].filter(Boolean).join(' — ');
    qsa('[data-pk-live-region]').forEach(function (el) {
      if (song && el.textContent !== song) el.textContent = (st.offline ? (i18n.offline + ' ') : '') + song;
    });
  };

  window.PKPlayerUI = new PlayerUI();
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { window.PKPlayerUI.bind(); });
  } else {
    window.PKPlayerUI.bind();
  }
})();
