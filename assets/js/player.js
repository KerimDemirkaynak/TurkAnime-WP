(function () {
  'use strict';
  var d = document, $ = function (s) { return d.querySelector(s); };
  var cfgEl = $('#player-config'); if (!cfgEl) return;
  var cfg; try { cfg = JSON.parse(cfgEl.textContent); } catch (e) { return; }
  var S = window.AnizenStore, A = window.ANIZEN || {}, U = window.ANIZEN_USER || { in: 0 };
  var stage = $('#player-stage'), startBtn = $('#player-start');
  var servers = [].slice.call(d.querySelectorAll('.server'));
  if (!stage || !cfg.sources.length) return;
  var cur = -1, tracked = false, hls = null, countdown = 0;

  function clear() {
    if (hls) { hls.destroy(); hls = null; }
    clearInterval(countdown);
    [].slice.call(stage.children).forEach(function (c) { if (c !== startBtn) stage.removeChild(c); });
  }
  function msg(html, actions) {
    var m = d.createElement('div'); m.className = 'player-msg'; m.innerHTML = html;
    (actions || []).forEach(function (a) { m.appendChild(a); });
    stage.appendChild(m); return m;
  }
  function btn(label, fn, cls) { var b = d.createElement('button'); b.type = 'button'; b.className = 'btn ' + (cls || 'btn-ghost') + ' btn-sm'; b.textContent = label; b.onclick = fn; return b; }
  function post(action, data) {
    var fd = new FormData(); fd.append('action', action);
    if (U.nonce) fd.append('nonce', U.nonce);
    for (var k in data) fd.append(k, data[k]);
    return fetch(A.ajax, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); });
  }

  function remember() {
    /* Son izlenen bölüm ve izledim işareti yalnızca üyeler için, sunucuda saklanır */
    if (U.in) post('anizen_progress', { id: cfg.id });
    if (!tracked) { tracked = true; post('anizen_track_view', { id: cfg.id }); }
  }

  function showNext() {
    if (!cfg.next || !cfg.autonext) return;
    var n = 8, m = msg('<strong></strong>', []);
    var label = m.firstChild;
    var upd = function () { label.textContent = '→ ' + n; };
    upd();
    m.appendChild(btn('▶', function () { location.href = cfg.next; }, 'btn-primary'));
    m.appendChild(btn('✕', function () { clearInterval(countdown); stage.removeChild(m); }));
    countdown = setInterval(function () { n--; if (n <= 0) { clearInterval(countdown); location.href = cfg.next; } else upd(); }, 1000);
  }

  function failover(i, reason) {
    var next = cfg.sources[i + 1] ? i + 1 : -1;
    var acts = [];
    if (next > -1) acts.push(btn(cfg.sources[next].name + ' →', function () { load(next); }, 'btn-primary'));
    if (cfg.sources[i].url) { var a = d.createElement('a'); a.className = 'btn btn-ghost btn-sm'; a.href = cfg.sources[i].url; a.target = '_blank'; a.rel = 'noopener noreferrer nofollow'; a.textContent = '↗'; acts.push(a); }
    msg('<strong>' + reason + '</strong>', acts);
  }

  function load(i) {
    var s = cfg.sources[i]; if (!s) return;
    cur = i; clear();
    if (startBtn) startBtn.hidden = true;
    servers.forEach(function (b, n) { var on = n === i; b.classList.toggle('is-active', on); b.setAttribute('aria-selected', on ? 'true' : 'false'); });
    if (cfg.remember) S.set('anizen-server', s.name);
    remember();

    if (s.kind === 'iframe') {
      var f = d.createElement('iframe');
      f.src = s.url; f.allowFullscreen = true; f.setAttribute('allow', 'autoplay; fullscreen; picture-in-picture; encrypted-media');
      f.setAttribute('referrerpolicy', 'no-referrer-when-downgrade'); f.title = cfg.title + ' ' + cfg.num;
      stage.appendChild(f);
    } else if (s.kind === 'html') {
      var t = d.getElementById('src-html-' + i), w = d.createElement('div');
      if (t) w.appendChild(t.content.cloneNode(true));
      stage.appendChild(w);
    } else {
      var v = d.createElement('video'); v.controls = true; v.autoplay = true; v.playsInline = true; v.preload = 'metadata';
      stage.appendChild(v);
      v.addEventListener('ended', showNext);
      v.addEventListener('error', function () { failover(i, '⚠'); });
      if (s.kind === 'hls' && !v.canPlayType('application/vnd.apple.mpegurl')) {
        var sc = d.createElement('script'); sc.src = 'https://cdn.jsdelivr.net/npm/hls.js@1.5.15/dist/hls.min.js'; sc.crossOrigin = 'anonymous';
        sc.onload = function () {
          if (!window.Hls || !Hls.isSupported()) return failover(i, '⚠');
          hls = new Hls(); hls.loadSource(s.url); hls.attachMedia(v);
          hls.on(Hls.Events.ERROR, function (e, data) { if (data.fatal) failover(i, '⚠'); });
          v.play().catch(function () {});
        };
        sc.onerror = function () { failover(i, '⚠'); };
        d.head.appendChild(sc);
      } else { v.src = s.url; v.play().catch(function () {}); }
    }
  }

  servers.forEach(function (b) { b.addEventListener('click', function () { load(parseInt(b.dataset.i, 10)); }); });
  if (startBtn) startBtn.addEventListener('click', function () { load(cur > -1 ? cur : 0); });

  /* son seçilen sunucu */
  var first = 0;
  if (cfg.remember) { var last = S.get('anizen-server', ''); cfg.sources.forEach(function (s, i) { if (last && s.name === last && first === 0) first = i; }); }
  if (first) { cur = first; servers.forEach(function (b, n) { b.classList.toggle('is-active', n === first); b.setAttribute('aria-selected', n === first ? 'true' : 'false'); }); }
  if (cfg.autoload) load(first);

  /* sinema modu */
  var cb = $('#cinema-btn'), shade = $('.cinema-shade');
  function cinema(on) { d.body.classList.toggle('cinema', on); if (shade) shade.hidden = !on; if (cb) cb.setAttribute('aria-pressed', on ? 'true' : 'false'); S.set('anizen-cinema', on); }
  if (cb) cb.addEventListener('click', function () { cinema(!d.body.classList.contains('cinema')); });
  if (shade) shade.addEventListener('click', function () { cinema(false); });
  var cs = S.get('anizen-cinema', null); if (cs === true || (cs === null && cfg.cinema)) cinema(true);
  d.addEventListener('keydown', function (e) { if (e.key === 'Escape') cinema(false); });

  /* tam ekran */
  var fs = $('#fs-btn');
  if (fs) fs.addEventListener('click', function () {
    var el = stage.querySelector('iframe,video') || stage;
    var rq = stage.requestFullscreen || stage.webkitRequestFullscreen;
    if (d.fullscreenElement) d.exitFullscreen(); else if (rq) rq.call(stage); else if (el.webkitEnterFullscreen) el.webkitEnterFullscreen();
  });

  /* hata bildir */
  var rb = $('#report-btn'), rbox = $('#report-box');
  if (rb && rbox) {
    rb.addEventListener('click', function () { rbox.hidden = !rbox.hidden; });
    rbox.addEventListener('click', function (e) {
      var b = e.target.closest('[data-reason]'); if (!b) return;
      var s = cfg.sources[Math.max(cur, 0)];
      if (!U.in) {
        rbox.innerHTML = '';
        var st = d.createElement('strong'); st.textContent = (U.i18n && U.i18n.needLogin) || '';
        var la = d.createElement('a'); la.href = U.login || '#'; la.textContent = ' ' + ((U.i18n && U.i18n.login) || 'Login'); la.style.marginLeft = '6px';
        rbox.appendChild(st); rbox.appendChild(la);
        if (U.register) { var ra = d.createElement('a'); ra.href = U.register; ra.textContent = (U.i18n && U.i18n.register) || 'Register'; ra.style.marginLeft = '10px'; rbox.appendChild(ra); }
        return;
      }
      post('anizen_report', { id: cfg.id, reason: b.dataset.reason, server: s ? s.name : '' }).then(function (r) {
        rbox.innerHTML = '<strong>' + (r && r.success ? (A.i18n ? A.i18n.thanks : 'OK') : (A.i18n ? A.i18n.error : 'Error')) + '</strong>';
      }).catch(function () { rbox.innerHTML = '<strong>' + (A.i18n ? A.i18n.error : 'Error') + '</strong>'; });
    });
  }

  /* klavye: ← → önceki/sonraki bölüm (Shift ile) */
  d.addEventListener('keydown', function (e) {
    if (!e.shiftKey || /input|textarea|select/i.test((e.target.tagName || ''))) return;
    if (e.key === 'ArrowRight' && cfg.next) location.href = cfg.next;
    if (e.key === 'ArrowLeft' && cfg.prev) location.href = cfg.prev;
  });
  var cur_a = d.querySelector('#ep-grid a.is-current'); if (cur_a && cur_a.scrollIntoView) { var p = cur_a.parentNode.parentNode; p.scrollTop = cur_a.offsetTop - p.clientHeight / 2; }
  var wl = U.in ? (U.watched || []) : [];
  [].slice.call(d.querySelectorAll('#ep-grid a')).forEach(function (a) { if (wl.indexOf(parseInt(a.dataset.id, 10)) > -1) a.classList.add('is-watched'); });
})();
