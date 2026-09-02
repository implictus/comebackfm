/**
 * PiratenKrakers Radio Engine
 *
 * Appearance-agnostic audio + metadata client.
 * The theme UI binds to events; this module never paints the page.
 *
 * Events: ready, play, pause, nowplaying, status, error, volume, streamchange, offline, online
 *
 * Replace this file to swap the client without touching templates.
 */
(function (root) {
  'use strict';

  var MIME = {
    mp3: 'audio/mpeg',
    aac: 'audio/aac',
    ogg: 'audio/ogg',
    opus: 'audio/ogg; codecs=opus'
  };

  function emit(map, event, payload) {
    (map[event] || []).forEach(function (fn) {
      try { fn(payload); } catch (err) { /* keep engine alive */ }
    });
  }

  function RadioEngine() {
    this.version = '1.0.0';
    this.config = {};
    this.audio = null;
    this._poll = null;
    this._listeners = {};
    this._started = false;
    this.state = {
      playing: false,
      loading: false,
      offline: false,
      muted: false,
      volume: 0.8,
      streamId: null,
      nowPlaying: {},
      error: null,
      configured: false
    };
  }

  RadioEngine.prototype.on = function (event, fn) {
    if (!this._listeners[event]) this._listeners[event] = [];
    this._listeners[event].push(fn);
    return this;
  };

  RadioEngine.prototype.off = function (event, fn) {
    this._listeners[event] = (this._listeners[event] || []).filter(function (x) { return x !== fn; });
    return this;
  };

  RadioEngine.prototype._emit = function (event, payload) {
    emit(this._listeners, event, payload === undefined ? this.getState() : payload);
  };

  RadioEngine.prototype.init = function (config) {
    if (this._started) return this;
    this.config = config || {};
    this.state.streamId = this.config.defaultStream || 'main';
    this.state.volume = typeof this.config.volume === 'number' ? this.config.volume : 0.8;

    try {
      var storedVol = parseFloat(localStorage.getItem('pk_radio_volume'));
      if (!isNaN(storedVol)) this.state.volume = Math.min(1, Math.max(0, storedVol));
      var storedStream = localStorage.getItem('pk_radio_stream');
      if (storedStream && this.config.streams && this.config.streams[storedStream]) {
        this.state.streamId = storedStream;
      }
    } catch (e) { /* private mode */ }

    this.audio = new Audio();
    this.audio.preload = 'none';
    this.audio.crossOrigin = 'anonymous';
    this.audio.setAttribute('playsinline', 'true');
    this.audio.volume = this.state.volume;

    var self = this;
    this.audio.addEventListener('playing', function () {
      self.state.playing = true;
      self.state.loading = false;
      self._emit('play');
    });
    this.audio.addEventListener('pause', function () {
      self.state.playing = false;
      self.state.loading = false;
      self._emit('pause');
    });
    this.audio.addEventListener('waiting', function () {
      self.state.loading = true;
      self._emit('status');
    });
    this.audio.addEventListener('error', function () {
      self.state.playing = false;
      self.state.loading = false;
      self.state.error = 'stream';
      self._emit('error', { code: 'stream', message: 'Stream kon niet worden gestart.' });
    });

    this._started = true;
    this.poll();
    this._poll = setInterval(function () { self.poll(); }, this.config.updateInterval || 12000);
    this._emit('ready');
    return this;
  };

  RadioEngine.prototype.getStream = function (id) {
    var streams = this.config.streams || {};
    return streams[id || this.state.streamId] || null;
  };

  RadioEngine.prototype.streamUrl = function (id) {
    var stream = this.getStream(id);
    if (!stream || !stream.url) return '';
    var url = stream.url;
    if (this.config.cacheBust) {
      url += (url.indexOf('?') === -1 ? '?' : '&') + '_pk=' + Date.now();
    }
    return url;
  };

  RadioEngine.prototype.play = function (streamId) {
    if (streamId && streamId !== this.state.streamId) {
      this.switchStream(streamId, true);
      return;
    }
    var url = this.streamUrl();
    if (!url) {
      this.state.configured = false;
      this._emit('error', { code: 'unconfigured', message: (this.config.i18n && this.config.i18n.notConfigured) || 'Stream nog niet ingesteld.' });
      return;
    }
    var stream = this.getStream();
    this.state.loading = true;
    this.state.error = null;
    this.audio.src = url;
    if (stream && stream.format && MIME[stream.format]) {
      try { this.audio.type = MIME[stream.format]; } catch (e) { /* ignore */ }
    }
    var playPromise = this.audio.play();
    if (playPromise && playPromise.catch) {
      var self = this;
      playPromise.catch(function () {
        self.state.loading = false;
        self.state.playing = false;
        self._emit('error', { code: 'autoplay', message: 'Klik op play om te luisteren.' });
      });
    }
  };

  RadioEngine.prototype.pause = function () {
    if (this.audio) this.audio.pause();
  };

  RadioEngine.prototype.toggle = function () {
    if (this.state.playing) this.pause();
    else this.play();
  };

  RadioEngine.prototype.setVolume = function (value) {
    var v = Math.min(1, Math.max(0, Number(value)));
    this.state.volume = v;
    if (this.audio) this.audio.volume = v;
    if (v > 0 && this.state.muted) {
      this.state.muted = false;
      if (this.audio) this.audio.muted = false;
    }
    try { localStorage.setItem('pk_radio_volume', String(v)); } catch (e) { /* ignore */ }
    this._emit('volume', v);
  };

  RadioEngine.prototype.mute = function () {
    this.state.muted = true;
    if (this.audio) this.audio.muted = true;
    this._emit('volume', 0);
  };

  RadioEngine.prototype.unmute = function () {
    this.state.muted = false;
    if (this.audio) this.audio.muted = false;
    this._emit('volume', this.state.volume);
  };

  RadioEngine.prototype.toggleMute = function () {
    if (this.state.muted) this.unmute();
    else this.mute();
  };

  RadioEngine.prototype.switchStream = function (id, autoplay) {
    this.state.streamId = id;
    try { localStorage.setItem('pk_radio_stream', id); } catch (e) { /* ignore */ }
    this._emit('streamchange', id);
    this.poll(id);
    if (autoplay || this.state.playing) this.play();
  };

  RadioEngine.prototype.poll = function (id) {
    var cfg = this.config;
    if (!cfg.restNowPlaying) return;
    var streamId = id || this.state.streamId;
    var url = cfg.restNowPlaying.replace(/\/$/, '') + '/' + encodeURIComponent(streamId);
    var self = this;
    fetch(url, { credentials: 'same-origin', headers: { 'X-WP-Nonce': cfg.restNonce || '' } })
      .then(function (res) { return res.ok ? res.json() : Promise.reject(res); })
      .then(function (data) { self.applyNowPlaying(data); })
      .catch(function () { /* keep last known payload */ });
  };

  RadioEngine.prototype.applyNowPlaying = function (data) {
    if (!data || typeof data !== 'object') return;
    var prev = this.state.nowPlaying || {};
    this.state.nowPlaying = data;
    this.state.offline = !!data.offline;
    this.state.configured = !!data.configured;
    this._emit('nowplaying', data);
    if (data.offline && !prev.offline) this._emit('offline', data);
    if (!data.offline && prev.offline) this._emit('online', data);
    this._emit('status');
  };

  RadioEngine.prototype.getState = function () {
    return {
      playing: this.state.playing,
      loading: this.state.loading,
      offline: this.state.offline,
      muted: this.state.muted,
      volume: this.state.volume,
      streamId: this.state.streamId,
      nowPlaying: this.state.nowPlaying,
      error: this.state.error,
      configured: this.state.configured
    };
  };

  RadioEngine.prototype.destroy = function () {
    if (this._poll) clearInterval(this._poll);
    if (this.audio) {
      this.audio.pause();
      this.audio.src = '';
    }
    this._started = false;
  };

  if (!root.PKRadio) {
    root.PKRadio = new RadioEngine();
  }
})(window);
