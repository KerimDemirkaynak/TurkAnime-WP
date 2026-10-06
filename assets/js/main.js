(function () {
  'use strict';
  var d = document, root = d.documentElement;
  var $ = function (s, c) { return (c || d).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); };
  var store = {
    get: function (k, def) { try { var v = JSON.parse(localStorage.getItem(k)); return v == null ? def : v; } catch (e) { return def; } },
    set: function (k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} }
  };
  window.AnizenStore = store;

  /* tema */
  var tt = $('.theme-toggle');
  if (tt) tt.addEventListener('click', function () {
    var n = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
    root.setAttribute('data-theme', n);
    try { localStorage.setItem('anizen-theme', n); } catch (e) {}
  });

  /* mobil menü */
  var nav = $('#site-nav'), nt = $('.nav-toggle'), bd = $('.nav-backdrop');
  function navSet(o) {
    if (!nav) return;
    nav.classList.toggle('is-open', o);
    if (bd) bd.hidden = !o;
    if (nt) nt.setAttribute('aria-expanded', o ? 'true' : 'false');
  }
  if (nt) nt.addEventListener('click', function () { navSet(!nav.classList.contains('is-open')); });
  if (bd) bd.addEventListener('click', function () { navSet(false); });
  d.addEventListener('keydown', function (e) { if (e.key === 'Escape') { navSet(false); closeResults(); } });
  $$('.nav-list .menu-item-has-children > a').forEach(function (a) {
    a.addEventListener('click', function (e) {
      if (matchMedia('(max-width:860px)').matches) return;
      if (e.target !== a) return;
    });
  });

  /* arama (mobilde aç/kapa + canlı sonuç) */
  var form = $('.header-tools .search'), q = $('#q'), box = $('#search-results'), st = $('.search-toggle');
  if (st && form) st.addEventListener('click', function () {
    var o = !form.classList.contains('is-open');
    form.classList.toggle('is-open', o);
    st.setAttribute('aria-expanded', o ? 'true' : 'false');
    if (o && q) q.focus();
  });
  function closeResults() { if (box) { box.hidden = true; box.innerHTML = ''; } }
  var timer, ctrl, sel = -1;
  function esc(s) { var x = d.createElement('div'); x.textContent = s == null ? '' : s; return x.innerHTML; }
  function escAttr(s) { return esc(s).replace(/"/g, '&quot;'); }
  if (q && box && window.ANIZEN) {
    q.addEventListener('input', function () {
      clearTimeout(timer);
      var v = q.value.trim();
      if (v.length < 2) { closeResults(); return; }
      timer = setTimeout(function () {
        if (ctrl && ctrl.abort) ctrl.abort();
        ctrl = window.AbortController ? new AbortController() : null;
        fetch(ANIZEN.rest + 'search?q=' + encodeURIComponent(v), ctrl ? { signal: ctrl.signal } : {})
          .then(function (r) { return r.json(); })
          .then(function (list) {
            sel = -1;
            var h = '';
            if (!list.length) h = '<span class="sr-empty">' + esc(ANIZEN.i18n.noResult) + '</span>';
            else {
              list.forEach(function (it) {
                h += '<a class="sr-item" href="' + escAttr(it.url) + '"><img src="' + escAttr(it.cover) + '" alt="" loading="lazy"><span><strong>' + esc(it.title) + '</strong><small>' + esc([it.year, it.eps ? it.eps + ' ' + ANIZEN.i18n.episode.replace('. ', '') : ''].filter(Boolean).join(' · ')) + '</small></span></a>';
              });
              h += '<a class="sr-all" href="' + escAttr(ANIZEN.home + '?s=' + encodeURIComponent(v)) + '">&rarr; ' + esc(v) + '</a>';
            }
            box.innerHTML = h; box.hidden = false;
          }).catch(function () {});
      }, 220);
    });
    q.addEventListener('keydown', function (e) {
      var items = $$('.sr-item', box);
      if (!items.length || (e.key !== 'ArrowDown' && e.key !== 'ArrowUp' && e.key !== 'Enter')) return;
      if (e.key === 'Enter') { if (sel > -1) { e.preventDefault(); location.href = items[sel].href; } return; }
      e.preventDefault();
      sel = (sel + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
      items.forEach(function (i, n) { i.classList.toggle('is-sel', n === sel); });
    });
    d.addEventListener('click', function (e) { if (!e.target.closest('.search')) closeResults(); });
  }

  /* süzgeç otomatik gönder */
  $$('select[data-autosubmit]').forEach(function (s) { s.addEventListener('change', function () { s.form.submit(); }); });

  /* yukarı çık */
  var tp = $('.to-top');
  if (tp) {
    window.addEventListener('scroll', function () { tp.classList.toggle('on', window.scrollY > 500); }, { passive: true });
    tp.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
  }

  /* sekmeler */
  $$('[data-tabs]').forEach(function (box) {
    var lis = $$('.panel-tabs li', box);
    if (lis.length && !lis.some(function (l) { return l.classList.contains('active'); })) lis[0].classList.add('active');
    function show(id) {
      lis.forEach(function (l) { var a = l.querySelector('a'); l.classList.toggle('active', a && a.dataset.tab === id); });
      $$('.tab-pane', box).forEach(function (p) { p.hidden = p.id !== id; });
      /* mobilde kaydırılan sekme çubuğunda aktif sekmeyi görünür tut */
      var al = lis.filter(function (l) { return l.classList.contains('active'); })[0], bar = al && al.parentNode;
      if (bar && bar.scrollWidth > bar.clientWidth) bar.scrollTo({ left: Math.max(0, al.offsetLeft - 12), behavior: 'smooth' });
    }
    var act = lis.filter(function (l) { return l.classList.contains('active'); })[0];
    if (act) show(act.querySelector('a').dataset.tab);
    $$('.panel-tabs a', box).forEach(function (a) { a.addEventListener('click', function (e) { e.preventDefault(); show(a.dataset.tab); }); });
  });

  /* tür kutusunu kapat */
  var gp = $('#genres-panel');
  if (gp) {
    try { if (sessionStorage.getItem('anizen-gp') === '1') gp.hidden = true; } catch (e) {}
    $('.gp-close', gp).addEventListener('click', function () { gp.hidden = true; try { sessionStorage.setItem('anizen-gp', '1'); } catch (e) {} });
  }

  /* yorumları görüntüle */
  var sc2 = $('#show-comments'), cw = $('#comments-wrap');
  function revealComments() { if (cw) { cw.hidden = false; } if (sc2) sc2.hidden = true; }
  if (sc2) {
    sc2.addEventListener('click', revealComments);
    if (/^#(comment|respond|comments)/.test(location.hash) || /replytocom/.test(location.search)) { revealComments(); var t = $(location.hash); if (t && t.scrollIntoView) setTimeout(function () { t.scrollIntoView(); }, 50); }
  }

  var U = window.ANIZEN_USER || { in: 0 };
  var needLogin = function () {
    var old = $('.toast'); if (old) old.remove();
    var t = d.createElement('div'); t.className = 'toast';
    var sp = d.createElement('span'); sp.textContent = (U.i18n && U.i18n.needLogin) || '';
    var a = d.createElement('a'); a.href = U.login || '#'; a.textContent = (U.i18n && U.i18n.login) || 'Login';
    t.appendChild(sp); t.appendChild(a);
    if (U.register) { var r = d.createElement('a'); r.href = U.register; r.textContent = (U.i18n && U.i18n.register) || 'Register'; t.appendChild(r); }
    d.body.appendChild(t);
    setTimeout(function () { if (t.parentNode) t.remove(); }, 6000);
  };
  var postUser = function (action, id) {
    var fd = new FormData(); fd.append('action', action); fd.append('id', id); fd.append('nonce', U.nonce || '');
    return fetch(ANIZEN.ajax, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); });
  };
  var inList = function (name, id) { return (U[name] || []).indexOf(id) > -1; };

  /* beğeni (anime / bölüm), izledim işareti, takip — üyeler için sunucuda saklanır */
  var watchedAll = U.in ? (U.watched || []) : [];
  $$('.mark-watched').forEach(function (b) { if (watchedAll.indexOf(parseInt(b.dataset.id, 10)) > -1) { b.classList.add('is-on'); b.setAttribute('aria-pressed', 'true'); } });
  var follows = U.in ? (U.follow || []) : [];
  $$('.follow-btn').forEach(function (b) { if (follows.indexOf(parseInt(b.dataset.id, 10)) > -1) { b.classList.add('is-on'); b.setAttribute('aria-pressed', 'true'); b.querySelector('span').textContent = '✓'; } });
  $$('.post-like').forEach(function (b) { if (inList('likes', parseInt(b.dataset.id, 10))) b.classList.add('is-liked'); });
  $$('.cm-like').forEach(function (b) { if (inList('clikes', parseInt(b.dataset.id, 10))) b.classList.add('is-liked'); });
  d.addEventListener('click', function (e) {
    var pl = e.target.closest('.post-like');
    if (pl && window.ANIZEN) {
      if (!U.in) { needLogin(); return; }
      if (pl.dataset.busy) return; pl.dataset.busy = '1';
      postUser('anizen_post_like', pl.dataset.id).then(function (r) {
        if (r && r.success) { pl.classList.toggle('is-liked', !!r.data.on); var n = pl.querySelector('.n'); if (n) n.textContent = r.data.likes; }
        else if (r && r.data && r.data.login) needLogin();
      }).catch(function () {}).then(function () { delete pl.dataset.busy; });
      return;
    }
    var mw = e.target.closest('.mark-watched');
    if (mw) {
      var id = parseInt(mw.dataset.id, 10);
      if (!U.in) { needLogin(); return; }
      postUser('anizen_mark', id).then(function (r) {
        if (r && r.success) { mw.classList.toggle('is-on', !!r.data.on); mw.setAttribute('aria-pressed', r.data.on ? 'true' : 'false'); }
        else if (r && r.data && r.data.login) needLogin();
      });
      return;
    }
    var fb = e.target.closest('.follow-btn');
    if (fb) {
      if (!U.in) { needLogin(); return; }
      postUser('anizen_follow', fb.dataset.id).then(function (r) {
        if (r && r.success) {
          fb.classList.toggle('is-on', !!r.data.on); fb.setAttribute('aria-pressed', r.data.on ? 'true' : 'false');
          fb.querySelector('span').textContent = r.data.on ? '✓' : (fb.dataset.label || 'Takip Et');
        } else if (r && r.data && r.data.login) needLogin();
      });
    }
  });
  var curEp = $('.ep-item.is-current');
  if (curEp && curEp.parentNode && curEp.parentNode.parentNode) { var lst = curEp.parentNode.parentNode; lst.scrollTop = curEp.parentNode.offsetTop - lst.clientHeight / 2; }

  /* hero slider (eski) */
  var hero = $('.hero');
  if (hero) {
    var slides = $$('.hero-slide', hero), dots = $$('.hero-dots button', hero), cur = 0, iv = 0, ms = (parseInt(hero.dataset.autoplay, 10) || 0) * 1000;
    var go = function (n) {
      cur = (n + slides.length) % slides.length;
      slides.forEach(function (s, i) { s.classList.toggle('is-active', i === cur); });
      dots.forEach(function (s, i) { s.classList.toggle('is-active', i === cur); });
    };
    var play = function () { stop(); if (ms && slides.length > 1) iv = setInterval(function () { go(cur + 1); }, ms); };
    var stop = function () { clearInterval(iv); };
    var pv = $('.hero-nav.prev', hero), nx = $('.hero-nav.next', hero);
    if (pv) pv.addEventListener('click', function () { go(cur - 1); play(); });
    if (nx) nx.addEventListener('click', function () { go(cur + 1); play(); });
    dots.forEach(function (b, i) { b.addEventListener('click', function () { go(i); play(); }); });
    hero.addEventListener('mouseenter', stop); hero.addEventListener('mouseleave', play);
    var sx = 0;
    hero.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; stop(); }, { passive: true });
    hero.addEventListener('touchend', function (e) { var dx = e.changedTouches[0].clientX - sx; if (Math.abs(dx) > 50) go(cur + (dx < 0 ? 1 : -1)); play(); });
    play();
  }

  /* izleme geçmişi: "devam et" satırı */
  var hist = U.in ? (U.history || []) : [];
  var cl = $('#continue-list');
  if (cl && hist.length) {
    var h = '';
    hist.slice(0, 8).forEach(function (it) {
      h += '<article class="card card-ep"><a class="card-thumb" href="' + escAttr(it.u) + '"><img src="' + escAttr(it.c) + '" alt="" loading="lazy"><span class="card-num">' + esc(it.l || (it.n + '. ' + ANIZEN.i18n.episode.replace(/^\.\s*/, ''))) + '</span><span class="card-play"><svg width="30" height="30" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 3 20 12 6 21"/></svg></span></a><h3 class="card-title"><a href="' + escAttr(it.u) + '">' + esc(it.t) + '</a></h3><p class="card-meta">' + esc(ANIZEN.i18n.resume) + '</p></article>';
    });
    cl.innerHTML = h; $('#continue').hidden = false;
  }

  /* anime sayfası: izlenenler, devam et, bölüm süzgeci/sıralama, özet */
  var epl = $('#ep-list');
  if (epl) {
    var watched = U.in ? (U.watched || []) : [];
    $$('li', epl).forEach(function (li) { if (watched.indexOf(parseInt(li.dataset.id, 10)) > -1) li.querySelector('.ep-item').classList.add('is-watched'); });
    var fe = $('[data-first-ep]');
    if (fe) {
      var ids = $$('li', epl).map(function (li) { return parseInt(li.dataset.id, 10); });
      var aid = parseInt(fe.dataset.anime, 10);
      /* Yalnızca bu seriye ait kayıt; bağlantı ve etiket kayıttan değil, sayfadaki güncel bölüm listesinden alınır */
      var found = hist.filter(function (it) { return parseInt(it.a, 10) === aid && ids.indexOf(parseInt(it.e, 10)) > -1; })[0];
      if (found) {
        var li = $$('li', epl).filter(function (x) { return parseInt(x.dataset.id, 10) === parseInt(found.e, 10); })[0];
        var link = li && li.querySelector('a.ep-item');
        if (link) {
          var lab = (li.querySelector('.ep-t') || {}).textContent || (found.n + '. ' + ANIZEN.i18n.episode.replace(/^\.\s*/, ''));
          fe.href = link.href; fe.lastChild.textContent = ' ' + ANIZEN.i18n.resume + ' (' + lab.trim() + ')';
        }
      }
    }
    var ef = $('#ep-filter');
    if (ef) ef.addEventListener('input', function () {
      var v = ef.value.trim();
      $$('li', epl).forEach(function (li) { li.hidden = v !== '' && li.dataset.n.indexOf(v) !== 0 && li.dataset.n !== v; });
    });
    var es = $('#ep-sort');
    if (es) es.addEventListener('click', function () {
      var on = es.getAttribute('aria-pressed') !== 'true';
      es.setAttribute('aria-pressed', on ? 'true' : 'false');
      $$('li', epl).reverse().forEach(function (li) { epl.appendChild(li); });
    });
  }
  var syn = $('#synopsis');
  if (syn && syn.scrollHeight > 190) {
    syn.classList.add('is-clamped');
    var mb = d.createElement('button'); mb.type = 'button'; mb.className = 'btn btn-ghost btn-sm syn-more'; mb.textContent = '+';
    mb.addEventListener('click', function () { var c = syn.classList.toggle('is-clamped'); mb.textContent = c ? '+' : '−'; });
    syn.parentNode.insertBefore(mb, syn.nextSibling);
  }

  /* yorumlar: spoiler, beğeni */
  d.addEventListener('click', function (e) {
    var sc = e.target.closest('.spoiler-cover');
    if (sc) { sc.parentNode.classList.add('is-revealed'); return; }
    var sp = e.target.closest('.spoiler');
    if (sp) { sp.classList.toggle('is-open'); return; }
    var lk = e.target.closest('.cm-like');
    if (lk && window.ANIZEN) {
      if (!U.in) { needLogin(); return; }
      if (lk.dataset.busy) return; lk.dataset.busy = '1';
      postUser('anizen_like', lk.dataset.id).then(function (r) {
        if (r && r.success) { lk.classList.toggle('is-liked', !!r.data.on); lk.querySelector('.n').textContent = r.data.likes; }
        else if (r && r.data && r.data.login) needLogin();
      }).catch(function () {}).then(function () { delete lk.dataset.busy; });
    }
  });
  d.addEventListener('keydown', function (e) { if ((e.key === 'Enter' || e.key === ' ') && e.target.classList && e.target.classList.contains('spoiler')) { e.preventDefault(); e.target.classList.toggle('is-open'); } });
})();
