(function () {
  'use strict';
  var d = document, A = window.ANIZEN_ADMIN || {};
  var $ = function (s, c) { return (c || d).querySelector(s); };
  var $$ = function (s, c) { return [].slice.call((c || d).querySelectorAll(s)); };
  function api(params) {
    var u = A.ajax + '?nonce=' + encodeURIComponent(A.nonce);
    for (var k in params) u += '&' + k + '=' + encodeURIComponent(params[k]);
    return fetch(u, { credentials: 'same-origin' }).then(function (r) { return r.json(); });
  }
  function el(html) { var t = d.createElement('div'); t.innerHTML = html.trim(); return t.firstChild; }

  /* anime seçici
     ID alanı yalnızca listeden seçimle değişir. Yazı kutusuna dokunmak bağlantıyı silmez;
     bağlantıyı kaldırmak için kutuyu bilerek boşaltıp kaydet. */
  $$('.anizen-picker').forEach(function (p) {
    var hid = $('input[type=hidden]', p), clr = $('.picker-clear', p), badge = $('.picker-id', p),
        inp = $('.picker-input', p), list = $('.picker-list', p), t, chosen = inp.value;
    function setBadge(warn) {
      if (!badge) return;
      if (hid.value) { badge.textContent = 'ID: ' + hid.value; badge.className = 'picker-id is-set'; }
      else { badge.textContent = warn ? 'Listeden bir anime seçin' : 'Anime seçilmedi'; badge.className = 'picker-id is-empty'; }
    }
    setBadge();
    inp.addEventListener('input', function () {
      clearTimeout(t);
      var v = inp.value.trim();
      if (v === '') { hid.value = ''; chosen = ''; setBadge(); list.hidden = true; return; }
      t = setTimeout(function () {
        api({ action: 'anizen_search_anime', q: v }).then(function (r) {
          list.innerHTML = '';
          ((r && r.data) || []).forEach(function (it) {
            var li = d.createElement('li'); li.textContent = it.title + '  ·  ID ' + it.id;
            li.addEventListener('mousedown', function (e) {
              e.preventDefault(); hid.value = it.id; inp.value = it.title; chosen = it.title;
              if (clr) clr.value = '0'; setBadge(); list.hidden = true;
            });
            list.appendChild(li);
          });
          list.hidden = !list.children.length;
        });
      }, 250);
    });
    inp.addEventListener('blur', function () {
      setTimeout(function () {
        list.hidden = true;
        /* Kutuda görünen ad ile kaydedilecek ID aynı animeyi göstersin */
        if (hid.value && inp.value.trim() !== '' && inp.value !== chosen) inp.value = chosen;
        else if (!hid.value && inp.value.trim() !== '') setBadge(true);
      }, 150);
    });
    var form = inp.closest('form');
    if (form) form.addEventListener('submit', function (e) {
      var typed = inp.value.trim() !== '';
      if (!hid.value && typed) {
        e.preventDefault();
        setBadge(true); inp.focus();
        alert('Anime adını yazdınız ama listeden seçmediniz. Lütfen açılan listeden animeyi seçin.');
        return;
      }
      if (clr) clr.value = (!hid.value && !typed) ? '1' : '0';
    });
  });

  /* kaynak satırları */
  var wrap = $('#anizen-sources');
  if (wrap) {
    var tpl = $('#anizen-src-tpl').textContent, n = parseInt(wrap.dataset.next, 10) || 1;
    $('#anizen-add-src').addEventListener('click', function () { wrap.appendChild(el(tpl.replace(/__i__/g, n++))); });
    wrap.addEventListener('click', function (e) {
      var row = e.target.closest('.anizen-src'); if (!row) return;
      if (e.target.closest('.src-del')) { if (wrap.children.length > 1) wrap.removeChild(row); else $('textarea', row).value = ''; }
      if (e.target.closest('.src-up') && row.previousElementSibling) wrap.insertBefore(row, row.previousElementSibling);
      if (e.target.closest('.src-down') && row.nextElementSibling) wrap.insertBefore(row.nextElementSibling, row);
    });
  }

  /* AniList */
  var q = $('#al-q'); if (!q) return;
  var status = $('#al-status'), res = $('#al-results');
  function say(t, c) { status.textContent = t; status.className = 'al-status ' + (c || ''); }
  function search() {
    if (q.value.trim().length < 2) return;
    say('…'); res.innerHTML = '';
    api({ action: 'anizen_anilist_search', q: q.value.trim() }).then(function (r) {
      if (!r.success) return say(r.data && r.data.message || 'Hata', 'err');
      say(r.data.length ? '' : 'Sonuç yok', r.data.length ? '' : 'err');
      r.data.forEach(function (it) {
        var b = d.createElement('button'); b.type = 'button'; b.className = 'al-hit';
        b.innerHTML = '<img alt="" src="' + it.cover + '"><span><strong></strong><small></small></span>';
        b.querySelector('strong').textContent = it.title; b.querySelector('small').textContent = it.meta + ' · ID ' + it.id;
        b.addEventListener('click', function () { $('#al-id').value = it.id; res.innerHTML = ''; importIt(); });
        res.appendChild(b);
      });
    }).catch(function () { say('Bağlantı hatası', 'err'); });
  }
  $('#al-search').addEventListener('click', search);
  q.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); search(); } });

  function setF(sel, v, force) { var f = $(sel); if (f && v !== '' && v != null && (force || !f.value)) f.value = v; }
  function importIt() {
    var id = $('#al-id').value; if (!id) return say('AniList ID gerekli', 'err');
    var force = $('#al-overwrite').checked; say('Çekiliyor…');
    api({ action: 'anizen_anilist_fetch', id: id, mode: $('#al-mode').value }).then(function (r) {
      if (!r.success) return say(r.data && r.data.message || 'Hata', 'err');
      var m = r.data;
      var title = $('#title'); if (title && (force || !title.value.trim())) { title.value = m.title; var pl = $('#title-prompt-text'); if (pl) pl.classList.add('screen-reader-text'); }
      setF('#f-alt', m.alt, force); setF('#f-year', m.year, force); setF('#f-score', m.score, force);
      setF('#f-status', m.status, true); setF('#f-type', m.type, force); setF('#f-eps', m.eps, force); setF('#f-dur', m.duration, force);
      setF('#f-cover', m.cover, force); setF('#f-banner', m.banner, force); setF('#f-trailer', m.trailer, force);
      var ed = $('#content');
      if (m.desc) {
        if (window.tinymce && tinymce.get('content') && !tinymce.get('content').isHidden()) {
          var t = tinymce.get('content'); if (force || !t.getContent().trim()) t.setContent('<p>' + m.desc.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/\n+/g, '</p><p>') + '</p>');
        } else if (ed && (force || !ed.value.trim())) ed.value = m.desc;
      }
      $('#al-genres').value = (m.genres || []).join('|'); $('#al-studios').value = (m.studios || []).join('|');
      say('✓ Alanlar dolduruldu — Güncelle/Yayınla’ya basınca kaydedilir. Türler: ' + (m.genres || []).join(', '), 'ok');
    }).catch(function () { say('Bağlantı hatası', 'err'); });
  }
  $('#al-import').addEventListener('click', importIt);

  /* Poster / banner: URL yazmak yerine cihazdan yükleme (isteğe bağlı) */
  [['#f-cover', 'anizen-poster', 'Cihazdan poster yükle'], ['#f-banner', 'large', 'Cihazdan banner yükle']].forEach(function (c) {
    var inp = $(c[0]);
    if (!inp || !window.wp || !wp.media) return;
    var b = d.createElement('button'); b.type = 'button'; b.className = 'button'; b.style.marginTop = '6px'; b.textContent = c[2];
    var frame;
    b.addEventListener('click', function () {
      if (!frame) {
        frame = wp.media({ title: c[2], button: { text: 'Bunu kullan' }, library: { type: 'image' }, multiple: false });
        frame.on('select', function () {
          var a = frame.state().get('selection').first().toJSON();
          var u = (a.sizes && a.sizes[c[1]] && a.sizes[c[1]].url) || a.url;
          inp.value = u; inp.dispatchEvent(new Event('input', { bubbles: true }));
        });
      }
      frame.open();
    });
    inp.parentNode.parentNode.appendChild(b);
  });
})();
