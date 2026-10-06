<?php
if (!defined('ABSPATH')) exit;

/* ------------------------------------------------------------------
   Varsayılan ayarlar & erişim
------------------------------------------------------------------- */
function anizen_defaults() {
    return [
        'accent' => '#b22222', 'accent2' => '#3b82c4',
        'bg_dark' => '#2e3039', 'surface_dark' => '#1e2026',
        'bg_light' => '#d7d7d7', 'surface_light' => '#ffffff',
        'default_mode' => 'dark', 'font' => 'system', 'radius' => '4',
        'container' => '1320', 'columns' => '6',
        'sticky_header' => 1, 'nav_random' => 1,
        'announce_text' => '', 'announce_url' => '',
        'hero_enable' => 1, 'hero_source' => 'featured', 'hero_count' => 8, 'brand_show' => 1, 'show_auth' => 1, 'show_genres_panel' => 1,
        'board_enable' => 1, 'board_title' => 'DUYURU PANOSU', 'board_count' => 8, 'sec_day_enable' => 1, 'fansub_label' => 'Çeviri Fansub',
        'comments_collapsed' => 1,
        'ep_info_html' => '<strong>BD (Bluray Disc):</strong> Görüntü, sahne ve animasyonların kalitesi iyileştirilebilir, ek sahneler eklenebilir, kan ve çıplaklık vb. içeren sahnelerde sansür bulunmaz.<br><strong>SSZ (Sansürsüz):</strong> Kan ve/veya çıplaklık içeren sahnelerde sansür bulunmaz.',
        'ep_notice_html' => '<strong>DİKKAT!</strong><br>Yayınladığımız bu anime aşağıda belirtilen grup veya çevirmeye aittir.<br>Proje bize ait olmayıp burası sadece online izleme alternatifi üzerine kurulmuş bir sitedir.<br>Arşiv yapmak ya da yüksek kalitede izlemek istiyorsanız grubun kendi sitesinden indirmeyi unutmayın!<br><strong>{fansub}</strong>',
        'home_html' => '', 'continue_enable' => 1,
        'sec_latest_enable' => 1, 'sec_latest_title' => 'Yeni Eklenen Bölümler', 'sec_latest_count' => 20,
        'sec_popular_enable' => 1, 'sec_popular_title' => 'Popüler Animeler', 'sec_popular_count' => 20,
        'sec_new_enable' => 1, 'sec_new_title' => 'Yeni Eklenen Animeler', 'sec_new_count' => 20,
        'sec_genres_enable' => 1, 'sec_genres_title' => 'Türlere Göz At',
        'per_page' => 24, 'show_filters' => 1,
        'player_autoload' => 1, 'player_autonext' => 1, 'player_remember' => 1, 'player_cinema' => 0,
        'player_report' => 1, 'report_email' => '', 'player_ratio' => '16/9',
        'player_notice' => 'Video açılmıyorsa yukarıdan başka bir kaynak seçmeyi deneyin.',
        'comments_spoiler' => 1, 'comments_likes' => 1,
        'footer_text' => '© {year} {site}',
        'footer_disclaimer' => 'Bu sitede yer alan videolar üçüncü taraf platformlardan gömülmektedir; hiçbir video dosyası sunucumuzda barındırılmaz.',
        'social_discord' => '', 'social_telegram' => '', 'social_twitter' => '', 'social_instagram' => '', 'social_youtube' => '',
        'totop' => 1, 'head_code' => '', 'footer_code' => '', 'seo_meta' => 1, 'schema' => 1,
    ];
}

function anizen_opt($key) {
    $d = anizen_defaults();
    return get_theme_mod('anizen_' . $key, isset($d[$key]) ? $d[$key] : '');
}

/* ------------------------------------------------------------------
   SVG ikonlar (harici ikon fontu yok)
------------------------------------------------------------------- */
function anizen_icon($name, $size = 20) {
    static $i = null;
    if ($i === null) {
        $i = [
            'play' => '<polygon points="6 3 20 12 6 21 6 3"/>',
            'search' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
            'menu' => '<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>',
            'x' => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
            'moon' => '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
            'sun' => '<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>',
            'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
            'list' => '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>',
            'chevron-left' => '<polyline points="15 18 9 12 15 6"/>',
            'chevron-right' => '<polyline points="9 18 15 12 9 6"/>',
            'chevron-down' => '<polyline points="6 9 12 15 18 9"/>',
            'eye' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
            'flag' => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>',
            'maximize' => '<path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>',
            'bulb' => '<path d="M9 18h6"/><path d="M10 22h4"/><path d="M12 2a7 7 0 0 0-4 12.7c.6.5 1 1.3 1 2.3h6c0-1 .4-1.8 1-2.3A7 7 0 0 0 12 2z"/>',
            'heart' => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
            'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
            'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
            'shuffle' => '<polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/><line x1="4" y1="4" x2="9" y2="9"/>',
            'arrow-up' => '<line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/>',
            'message' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
            'thumbs-up' => '<path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>',
            'check' => '<polyline points="20 6 9 17 4 12"/>',
            'tv' => '<rect x="2" y="7" width="20" height="15" rx="2" ry="2"/><polyline points="17 2 12 7 7 2"/>',
            'trending' => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
            'skip-next' => '<polygon points="5 4 15 12 5 20 5 4"/><line x1="19" y1="5" x2="19" y2="19"/>',
            'home' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'tag' => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
            'eye-off' => '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>',
            'server' => '<rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/>',
            'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'link' => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
            'layers' => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
            'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'skip-prev' => '<polygon points="19 20 9 12 19 4 19 20"/><line x1="5" y1="19" x2="5" y2="5"/>',
        ];
    }
    if (!isset($i[$name])) return '';
    return '<svg class="ico ico-' . esc_attr($name) . '" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $i[$name] . '</svg>';
}

/* ------------------------------------------------------------------
   Durum / tür etiketleri
------------------------------------------------------------------- */
function anizen_statuses() {
    return ['ongoing' => 'Devam Ediyor', 'completed' => 'Tamamlandı', 'upcoming' => 'Yakında', 'hiatus' => 'Ara Verdi'];
}

function anizen_types() {
    return ['tv' => 'TV', 'movie' => 'Film', 'ova' => 'OVA', 'ona' => 'ONA', 'special' => 'Özel', 'music' => 'Müzik'];
}

function anizen_normalize_status($v) {
    $v = mb_strtolower(trim((string) $v));
    if ($v === '') return '';
    $map = [
        'releasing' => 'ongoing', 'devam ediyor' => 'ongoing', 'devam' => 'ongoing', 'ongoing' => 'ongoing', 'airing' => 'ongoing',
        'finished' => 'completed', 'tamamlandı' => 'completed', 'tamamlandi' => 'completed', 'completed' => 'completed', 'bitti' => 'completed', 'cancelled' => 'completed',
        'not_yet_released' => 'upcoming', 'yakında' => 'upcoming', 'yakinda' => 'upcoming', 'upcoming' => 'upcoming',
        'hiatus' => 'hiatus', 'ara verdi' => 'hiatus',
    ];
    return isset($map[$v]) ? $map[$v] : '';
}

function anizen_status_label($raw) {
    $slug = anizen_normalize_status($raw);
    $all = anizen_statuses();
    if ($slug && isset($all[$slug])) return $all[$slug];
    return (string) $raw;
}

/* ------------------------------------------------------------------
   Görseller
------------------------------------------------------------------- */
function anizen_cover_url($id, $size = 'anizen-poster') {
    if (has_post_thumbnail($id)) {
        $u = get_the_post_thumbnail_url($id, $size);
        if ($u) return $u;
    }
    $u = get_post_meta($id, '_anilist_cover', true);
    if ($u) return $u;
    return ANIZEN_URI . '/assets/img/placeholder.svg';
}

function anizen_banner_url($id) {
    return (string) get_post_meta($id, '_anilist_banner', true);
}

/* ------------------------------------------------------------------
   Bölüm sorguları, sayaçlar
------------------------------------------------------------------- */
function anizen_recount($aid) {
    global $wpdb;
    $aid = (int) $aid;
    if (!$aid) return;
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT COUNT(*) c, MAX(p.post_date_gmt) l FROM {$wpdb->posts} p
         JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_episode_anime_id' AND m.meta_value = %s
         WHERE p.post_type = 'episode' AND p.post_status = 'publish'",
        (string) $aid
    ));
    update_post_meta($aid, '_ep_count', $row ? (int) $row->c : 0);
    update_post_meta($aid, '_last_ep_time', ($row && $row->l) ? (int) strtotime($row->l . ' UTC') : 0);
}

function anizen_episode_count($aid) {
    $c = get_post_meta($aid, '_ep_count', true);
    if ($c === '') {
        anizen_recount($aid);
        $c = get_post_meta($aid, '_ep_count', true);
    }
    return (int) $c;
}

/**
 * Bir animenin yayındaki bölümleri (numaraya göre sıralı).
 * Yalnızca _episode_anime_id'si bu animeye eşit olanlar döner (PHP tarafında ayrıca doğrulanır).
 * Sıralama/adres meta'sı eksik eski bölümler listeden düşmez; eksik meta ilk görüldüğünde tamamlanır.
 */
function anizen_episodes($aid, $order = 'ASC', $fields = '') {
    $aid = (int) $aid;
    if (!$aid) return [];
    global $wpdb;
    /* Doğrudan SQL: eklentilerin/filtrelerin/önbelleğin sorguyu daraltmasına izin vermez */
    $ids = $wpdb->get_col($wpdb->prepare(
        "SELECT p.ID FROM {$wpdb->posts} p
         JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_episode_anime_id' AND m.meta_value = %s
         WHERE p.post_type = 'episode' AND p.post_status = 'publish' ORDER BY p.ID ASC",
        (string) $aid
    ));
    $posts = [];
    if ($ids) {
        _prime_post_caches(array_map('intval', $ids), false, true);
        foreach ($ids as $pid) { $po = get_post((int) $pid); if ($po) $posts[] = $po; }
    }
    $rows = [];
    foreach ($posts as $p) {
        if ((int) get_post_meta($p->ID, '_episode_anime_id', true) !== $aid) continue;
        $sort = get_post_meta($p->ID, '_episode_sort', true);
        if ($sort === '' || get_post_meta($p->ID, '_episode_key', true) === '') {
            $raw = (string) get_post_meta($p->ID, '_episode_number', true);
            if ($raw !== '' && anizen_ep_parse($raw)) {
                anizen_ep_save_meta($p->ID, $aid, $raw);
                $sort = get_post_meta($p->ID, '_episode_sort', true);
            }
        }
        $rows[] = [(float) $sort, $p];
    }
    usort($rows, function ($x, $y) {
        if ($x[0] == $y[0]) return $x[1]->ID <=> $y[1]->ID;
        return $x[0] < $y[0] ? -1 : 1;
    });
    if (strtoupper($order) === 'DESC') $rows = array_reverse($rows);
    $out = [];
    foreach ($rows as $r) $out[] = $fields === 'ids' ? (int) $r[1]->ID : $r[1];
    return $out;
}

function anizen_ep_number($ep_id) {
    $n = get_post_meta($ep_id, '_episode_number', true);
    if ($n === '' || !is_numeric($n)) return (string) $n;
    return rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
}

/* ------------------------------------------------------------------
   Bölüm numarası / URL anahtarı
   _episode_number : yöneticinin yazdığı değer ("5", "12.5", "OVA 1", "Özel")
   _episode_sort   : sıralama değeri (normal bölümler numara, özel bölümler listenin sonu)
   _episode_key    : adreste kullanılan parça ("5", "12-5", "ova-1")
------------------------------------------------------------------- */
function anizen_ep_parse($raw) {
    $raw = trim(wp_strip_all_tags((string) $raw));
    if ($raw === '') return null;
    $num = str_replace(',', '.', $raw);
    if (is_numeric($num) && (float) $num >= 0) {
        $c = rtrim(rtrim(number_format((float) $num, 2, '.', ''), '0'), '.');
        if ($c === '') $c = '0';
        return ['number' => $c, 'sort' => (float) $c, 'key' => str_replace('.', '-', $c)];
    }
    $key = sanitize_title($raw);
    if ($key === '') $key = 'ozel';
    $off = preg_match('/(\d+(?:[.,]\d+)?)\s*$/', $raw, $m) ? (float) str_replace(',', '.', $m[1]) : 0;
    return ['number' => $raw, 'sort' => 1000000 + min($off, 99999), 'key' => $key];
}

/** Aynı animede aynı adres parçası iki kez kullanılmasın. */
function anizen_ep_unique_key($aid, $key, $exclude = 0) {
    $try = $key; $i = 1;
    while (true) {
        $ids = get_posts([
            'post_type' => 'episode', 'post_status' => ['publish', 'draft', 'pending', 'future', 'private'], 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => true,
            'post__not_in' => $exclude ? [(int) $exclude] : [],
            'meta_query' => [['key' => '_episode_anime_id', 'value' => (string) $aid], ['key' => '_episode_key', 'value' => $try]],
        ]);
        if (!$ids) return $try;
        $i++; $try = $key . '-v' . $i;
    }
}

/** Bölümde yapılan bağlantı/numara değişikliklerinin son 8'ini kaydeder (kim, nereden, ne). */
function anizen_ep_log($post_id, $what) {
    $log = get_post_meta($post_id, '_episode_log', true);
    $log = is_array($log) ? $log : [];
    $u = wp_get_current_user();
    $act = (isset($_REQUEST['action']) && is_string($_REQUEST['action'])) ? sanitize_key(wp_unslash($_REQUEST['action'])) : '';
    $pg = isset($GLOBALS['pagenow']) ? (string) $GLOBALS['pagenow'] : '';
    $log[] = ['t' => time(), 'u' => ($u && $u->exists()) ? $u->user_login : '-', 'p' => trim($pg . ' ' . $act . ' [' . current_action() . ']'), 'w' => (string) $what];
    update_post_meta($post_id, '_episode_log', array_slice($log, -8));
}

/** Bir bölüme ait üç meta değerini (numara, sıralama, adres) yazar. */
function anizen_ep_save_meta($post_id, $aid, $raw) {
    $p = anizen_ep_parse($raw);
    if (!$p) return false;
    $oldn = (string) get_post_meta($post_id, '_episode_number', true);
    if ($oldn !== '' && $oldn !== (string) $p['number']) anizen_ep_log($post_id, 'no: ' . $oldn . ' → ' . $p['number']);
    update_post_meta($post_id, '_episode_number', $p['number']);
    update_post_meta($post_id, '_episode_sort', (string) $p['sort']);
    update_post_meta($post_id, '_episode_key', $aid ? anizen_ep_unique_key($aid, $p['key'], $post_id) : $p['key']);
    return true;
}

/** Listede/başlıkta görünen ad: "5. Bölüm", "12,5. Bölüm", "OVA 1". */
function anizen_ep_label($ep_id) {
    $n = anizen_ep_number($ep_id);
    if ($n === '') return '';
    return is_numeric($n) ? str_replace('.', ',', $n) . '. ' . __('Bölüm', 'anizen') : $n;
}

/** Yalnızca numara kısmı (kartlardaki rozet vb. için): "5", "12,5", "OVA 1". */
function anizen_ep_short($ep_id) {
    $n = anizen_ep_number($ep_id);
    return is_numeric($n) ? str_replace('.', ',', $n) : $n;
}

function anizen_views($id) {
    return (int) get_post_meta($id, '_views', true);
}

function anizen_time_ago($post_id) {
    $t = get_post_time('U', true, $post_id);
    if (!$t) return '';
    return sprintf(__('%s önce', 'anizen'), human_time_diff($t, time()));
}

function anizen_synopsis($id, $words = 40) {
    return wp_trim_words(wp_strip_all_tags(strip_shortcodes(get_post_field('post_content', $id))), $words, '…');
}

/** Ana sayfa / widget sorguları */
function anizen_query_animes($mode, $n) {
    $a = [
        'post_type' => 'anime', 'post_status' => 'publish', 'posts_per_page' => (int) $n,
        'no_found_rows' => true, 'ignore_sticky_posts' => true,
    ];
    switch ($mode) {
        case 'featured':
            $a['meta_query'] = [['key' => '_featured', 'value' => '1']];
            $a['orderby'] = 'date';
            break;
        case 'popular':
        case 'updated':
            $k = ($mode === 'popular') ? '_views' : '_last_ep_time';
            $a['meta_query'] = [
                'relation' => 'OR',
                'srt' => ['key' => $k, 'type' => 'NUMERIC', 'compare' => 'EXISTS'],
                ['key' => $k, 'compare' => 'NOT EXISTS'],
            ];
            $a['orderby'] = ['srt' => 'DESC', 'date' => 'DESC'];
            break;
        case 'random':
            $a['orderby'] = 'rand';
            break;
        default:
            $a['orderby'] = 'date';
    }
    return new WP_Query($a);
}

/* ------------------------------------------------------------------
   Video kaynakları
------------------------------------------------------------------- */
function anizen_embed_kses() {
    $iframe = ['src' => true, 'width' => true, 'height' => true, 'frameborder' => true, 'allow' => true, 'allowfullscreen' => true,
        'scrolling' => true, 'referrerpolicy' => true, 'loading' => true, 'sandbox' => true, 'style' => true, 'title' => true, 'name' => true];
    return [
        'iframe' => $iframe,
        'video' => ['src' => true, 'controls' => true, 'poster' => true, 'width' => true, 'height' => true, 'preload' => true, 'playsinline' => true],
        'source' => ['src' => true, 'type' => true],
        'div' => ['class' => true, 'style' => true, 'id' => true], 'span' => ['class' => true], 'p' => [], 'br' => [],
        'a' => ['href' => true, 'target' => true, 'rel' => true], 'img' => ['src' => true, 'alt' => true, 'width' => true, 'height' => true],
    ];
}

/** Kaynak listesi — yeni biçim yoksa eski (5 alanlı) biçimden okunur. */
function anizen_get_sources($ep) {
    if (metadata_exists('post', $ep, '_episode_sources')) {
        $s = get_post_meta($ep, '_episode_sources', true);
        return is_array($s) ? array_values(array_filter($s, 'is_array')) : [];
    }
    $out = [];
    for ($i = 1; $i <= 5; $i++) {
        $n = get_post_meta($ep, '_episode_source_name_' . $i, true);
        $c = get_post_meta($ep, '_episode_source_code_' . $i, true);
        if ($n !== '' && $c !== '') $out[] = ['name' => $n, 'type' => 'auto', 'code' => $c];
    }
    return $out;
}

/** Ham kaynağı oynatıcının anlayacağı biçime çevirir: iframe | video | hls | html */
function anizen_prepare_source($src) {
    $code = trim((string) ($src['code'] ?? ''));
    $type = $src['type'] ?? 'auto';
    $name = trim((string) ($src['name'] ?? ''));
    if ($code === '') return null;
    $r = ['name' => $name !== '' ? $name : __('Kaynak', 'anizen'), 'kind' => 'iframe', 'url' => '', 'html' => ''];

    $fix = function ($u) {
        $u = trim(html_entity_decode($u));
        if (strpos($u, '//') === 0) $u = 'https:' . $u;
        return esc_url_raw($u, ['http', 'https']);
    };

    if (stripos($code, '<iframe') !== false) {
        if (preg_match('~<iframe[^>]+src\s*=\s*["\']([^"\']+)["\']~i', $code, $m)) {
            $u = $fix($m[1]);
            if ($u) { $r['url'] = $u; return $r; }
        }
        $r['kind'] = 'html';
        $r['html'] = wp_kses($code, anizen_embed_kses());
        return $r['html'] !== '' ? $r : null;
    }

    if (preg_match('~^(https?:)?//~i', $code)) {
        $u = $fix($code);
        if (!$u) return null;
        $r['url'] = $u;
        $path = (string) wp_parse_url($u, PHP_URL_PATH);
        if ($type === 'hls' || ($type !== 'iframe' && preg_match('~\.m3u8$~i', $path))) $r['kind'] = 'hls';
        elseif ($type === 'mp4' || ($type !== 'iframe' && preg_match('~\.(mp4|webm|ogv|m4v)$~i', $path))) $r['kind'] = 'video';
        return $r;
    }

    $r['kind'] = 'html';
    $r['html'] = wp_kses($code, anizen_embed_kses());
    return $r['html'] !== '' ? $r : null;
}

function anizen_sources_for_player($ep) {
    $out = [];
    foreach (anizen_get_sources($ep) as $s) {
        $p = anizen_prepare_source($s);
        if ($p) $out[] = $p;
    }
    return $out;
}

/* ------------------------------------------------------------------
   Menü yedeği (menü atanmamışsa), logo, sosyal bağlantılar
------------------------------------------------------------------- */
function anizen_default_menu() {
    echo '<ul class="nav-list">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('Ana Sayfa', 'anizen') . '</a></li>';
    echo '<li><a href="' . esc_url(get_post_type_archive_link('anime')) . '">' . esc_html__('Animeler', 'anizen') . '</a></li>';
    echo '<li><a href="' . esc_url(get_post_type_archive_link('episode')) . '">' . esc_html__('Son Bölümler', 'anizen') . '</a></li>';
    $genres = get_terms(['taxonomy' => 'genre', 'hide_empty' => true, 'number' => 30, 'orderby' => 'count', 'order' => 'DESC']);
    if (!is_wp_error($genres) && $genres) {
        echo '<li class="menu-item-has-children"><a href="' . esc_url(get_post_type_archive_link('anime')) . '">' . esc_html__('Türler', 'anizen') . '</a><ul class="sub-menu">';
        foreach ($genres as $g) echo '<li><a href="' . esc_url(get_term_link($g)) . '">' . esc_html($g->name) . '</a></li>';
        echo '</ul></li>';
    }
    echo '</ul>';
}

function anizen_logo() {
    if (has_custom_logo()) return get_custom_logo();
    $legacy = get_theme_mod('turkanime_logo'); // eski temadan gelen ayar
    if ($legacy) {
        return '<a class="custom-logo-link" href="' . esc_url(home_url('/')) . '"><img class="custom-logo" src="' . esc_url($legacy) . '" alt="' . esc_attr(get_bloginfo('name')) . '" height="40"></a>';
    }
    if (file_exists(ANIZEN_DIR . '/assets/img/logo.png')) {
        return '<a class="custom-logo-link" href="' . esc_url(home_url('/')) . '"><img class="custom-logo" src="' . esc_url(ANIZEN_URI . '/assets/img/logo.png') . '" alt="' . esc_attr(get_bloginfo('name')) . '"></a>';
    }
    return '<a class="logo-text" href="' . esc_url(home_url('/')) . '">' . esc_html(get_bloginfo('name')) . '</a>';
}

function anizen_social_links() {
    $labels = ['discord' => 'Discord', 'telegram' => 'Telegram', 'twitter' => 'X / Twitter', 'instagram' => 'Instagram', 'youtube' => 'YouTube'];
    $out = '';
    foreach ($labels as $k => $label) {
        $u = anizen_opt('social_' . $k);
        if ($u) $out .= '<a class="social" href="' . esc_url($u) . '" target="_blank" rel="noopener nofollow">' . esc_html($label) . '</a>';
    }
    return $out;
}

/* ------------------------------------------------------------------
   Şablon yardımcıları
------------------------------------------------------------------- */
function anizen_first_episode_id($aid) {
    $ids = anizen_episodes($aid, 'ASC', 'ids');
    return $ids ? (int) $ids[0] : 0;
}

function anizen_section_head($title, $link = '', $link_text = '') {
    echo '<div class="sec-head"><h2 class="sec-title">' . esc_html($title) . '</h2>';
    if ($link) echo '<a class="sec-more" href="' . esc_url($link) . '">' . esc_html($link_text !== '' ? $link_text : __('Tümü', 'anizen')) . ' ' . anizen_icon('chevron-right', 16) . '</a>';
    echo '</div>';
}

/** Liste sayfası süzgeç adresi: mevcut süzgeçleri korur, verilenleri değiştirir. */
function anizen_filter_url($over = []) {
    $base = is_tax() ? get_term_link(get_queried_object()) : get_post_type_archive_link('anime');
    if (is_wp_error($base)) $base = home_url('/');
    $keep = [];
    foreach (['genre', 'yil', 'durum', 'tip', 'sirala', 'harf'] as $k) {
        if (isset($_GET[$k]) && $_GET[$k] !== '') $keep[$k] = sanitize_text_field(wp_unslash($_GET[$k]));
    }
    $params = array_merge($keep, $over);
    foreach ($params as $k => $v) if ($v === '' || $v === null) unset($params[$k]);
    return $params ? add_query_arg($params, $base) : $base;
}

function anizen_years() {
    $years = get_transient('anizen_years');
    if ($years === false) {
        global $wpdb;
        $years = $wpdb->get_col("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_anime_year' AND meta_value REGEXP '^[0-9]{4}$' ORDER BY meta_value DESC");
        set_transient('anizen_years', $years, 12 * HOUR_IN_SECONDS);
    }
    return $years;
}
add_action('save_post_anime', function () { delete_transient('anizen_years'); });

/** Bölüm, animenin son (final) bölümü mü? Planlanan bölüm sayısı girildiyse ona göre. */
function anizen_is_final($ep_id) {
    $aid = (int) get_post_meta($ep_id, '_episode_anime_id', true);
    $total = $aid ? (int) get_post_meta($aid, '_anime_total_eps', true) : 0;
    $n = get_post_meta($ep_id, '_episode_number', true);
    return $total > 0 && is_numeric($n) && (float) $n >= $total;
}

/** Bölümde fansub yazılıysa o, yoksa animenin varsayılan fansub'ı. Birden fazlası virgülle ayrılır. */
function anizen_fansub($aid, $ep_id = 0) {
    if ($ep_id) {
        $e = trim((string) get_post_meta($ep_id, '_episode_fansub', true));
        if ($e !== '') return $e;
    }
    return trim((string) get_post_meta($aid, '_anime_fansub', true));
}

function anizen_flag_tr() {
    // Türkiye bayrağı (resmi oranlı SVG) — eski ☪ karakteri bazı cihazlarda Tunus bayrağı gibi görünüyordu.
    return '<span class="flag-tr" title="Türkçe" role="img" aria-label="Türkçe"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -30000 90000 60000" aria-hidden="true" focusable="false"><path fill="#e30a17" d="M0-30000h90000v60000H0z"/><path fill="#fff" d="m41750 0 13568-4408-8386 11568v-14320l8386 11568zm925 8021a15000 15000 0 1 1 0-16042 12000 12000 0 1 0 0 16042z"/></svg></span>';
}

function anizen_alt_name($aid) {
    $alt = (string) get_post_meta($aid, '_anime_alt_title', true);
    if ($alt === '') return get_the_title($aid);
    $parts = preg_split('~\s*/\s*~', $alt);
    return $parts[0];
}
