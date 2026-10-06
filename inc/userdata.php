<?php
if (!defined('ABSPATH')) exit;

/* ------------------------------------------------------------------
   Üyeye bağlı veriler (sunucu tarafında, kullanıcı meta olarak)
   anizen_likes   : beğenilen anime/bölüm kimlikleri
   anizen_clikes  : beğenilen yorum kimlikleri
   anizen_follow  : takip edilen (listeye eklenen) animeler
   anizen_watched : izledim işaretli bölümler
   anizen_history : anime kimliği => ['e' => son izlenen bölüm, 'ts' => zaman]
   Beğeni, listeye ekleme ve hata bildirimi yalnızca üyelere açıktır;
   her üye bir şeyi bir kez beğenebilir (tekrar basarsa beğeni geri alınır).
------------------------------------------------------------------- */
function anizen_ulist($key, $uid = 0) {
    $v = get_user_meta($uid ?: get_current_user_id(), $key, true);
    return is_array($v) ? array_values(array_map('intval', $v)) : [];
}
function anizen_ulist_save($key, $list, $uid, $cap = 3000) {
    update_user_meta($uid, $key, array_slice(array_values(array_unique(array_map('intval', $list))), -$cap));
}
function anizen_utoggle($key, $id, $uid, $cap = 3000) {
    $l = anizen_ulist($key, $uid);
    $i = array_search((int) $id, $l, true);
    if ($i !== false) { unset($l[$i]); $on = false; } else { $l[] = (int) $id; $on = true; }
    anizen_ulist_save($key, $l, $uid, $cap);
    return $on;
}

function anizen_user_guard() {
    if (!is_user_logged_in()) wp_send_json_error(['login' => true], 401);
    check_ajax_referer('anizen_user', 'nonce');
}
function anizen_ajax_need_login() {
    wp_send_json_error(['login' => true], 401);
}

function anizen_list_url() {
    return get_option('permalink_structure') ? home_url('/listem/') : add_query_arg('anizen_page', 'listem', home_url('/'));
}
function anizen_login_url() {
    return wp_login_url(home_url(add_query_arg([])));
}
function anizen_register_url() {
    return get_option('users_can_register') ? wp_registration_url() : '';
}

/* Yorum yalnızca üyelere açık (sunucu tarafında zorunlu) */
add_filter('pre_option_comment_registration', '__return_true');

/* /listem/ adresi için kural bir kez yenilenir */
add_action('init', function () {
    if (get_option('anizen_list_rule') === '1') return;
    update_option('anizen_list_rule', '1');
    flush_rewrite_rules(false);
}, 99);

/* --- Beğeni (anime / bölüm) --- */
add_action('wp_ajax_anizen_post_like', function () {
    anizen_user_guard();
    $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
    if (!$id || !in_array(get_post_type($id), ['anime', 'episode'], true) || get_post_status($id) !== 'publish') wp_send_json_error();
    $on = anizen_utoggle('anizen_likes', $id, get_current_user_id());
    $n = max(0, (int) get_post_meta($id, '_likes', true) + ($on ? 1 : -1));
    update_post_meta($id, '_likes', $n);
    wp_send_json_success(['likes' => $n, 'on' => $on]);
});
add_action('wp_ajax_nopriv_anizen_post_like', 'anizen_ajax_need_login');

/* --- Yorum beğenisi --- */
add_action('wp_ajax_anizen_like', function () {
    if (!anizen_opt('comments_likes')) wp_send_json_error();
    anizen_user_guard();
    $cid = isset($_POST['id']) ? absint($_POST['id']) : 0;
    $c = $cid ? get_comment($cid) : null;
    if (!$c || $c->comment_approved !== '1') wp_send_json_error();
    $on = anizen_utoggle('anizen_clikes', $cid, get_current_user_id());
    $n = max(0, (int) get_comment_meta($cid, '_likes', true) + ($on ? 1 : -1));
    update_comment_meta($cid, '_likes', $n);
    wp_send_json_success(['likes' => $n, 'on' => $on]);
});
add_action('wp_ajax_nopriv_anizen_like', 'anizen_ajax_need_login');

/* --- Takip / listeye ekle --- */
add_action('wp_ajax_anizen_follow', function () {
    anizen_user_guard();
    $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
    if (!$id || get_post_type($id) !== 'anime' || get_post_status($id) !== 'publish') wp_send_json_error();
    wp_send_json_success(['on' => anizen_utoggle('anizen_follow', $id, get_current_user_id(), 1000)]);
});
add_action('wp_ajax_nopriv_anizen_follow', 'anizen_ajax_need_login');

/* --- İzledim işareti --- */
add_action('wp_ajax_anizen_mark', function () {
    anizen_user_guard();
    $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
    if (!$id || get_post_type($id) !== 'episode') wp_send_json_error();
    wp_send_json_success(['on' => anizen_utoggle('anizen_watched', $id, get_current_user_id())]);
});
add_action('wp_ajax_nopriv_anizen_mark', 'anizen_ajax_need_login');

/* --- Son izlenen bölüm (bölüm sayfası açılınca) --- */
add_action('wp_ajax_anizen_progress', function () {
    anizen_user_guard();
    $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
    if (!$id || get_post_type($id) !== 'episode' || get_post_status($id) !== 'publish') wp_send_json_error();
    $aid = (int) get_post_meta($id, '_episode_anime_id', true);
    if (!$aid) wp_send_json_success();
    $uid = get_current_user_id();
    $h = get_user_meta($uid, 'anizen_history', true);
    $h = is_array($h) ? $h : [];
    $h[$aid] = ['e' => $id, 'ts' => time()];
    uasort($h, function ($a, $b) { return (int) $b['ts'] <=> (int) $a['ts']; });
    update_user_meta($uid, 'anizen_history', array_slice($h, 0, 40, true));
    $w = anizen_ulist('anizen_watched', $uid);
    if (!in_array($id, $w, true)) { $w[] = $id; anizen_ulist_save('anizen_watched', $w, $uid); }
    wp_send_json_success();
});
add_action('wp_ajax_nopriv_anizen_progress', 'anizen_ajax_need_login');

/** Üyenin son izlediği bölümler, kartlarda göstermeye hazır. */
function anizen_user_history_items($uid, $limit = 8) {
    $h = get_user_meta($uid, 'anizen_history', true);
    if (!is_array($h)) return [];
    $out = [];
    foreach ($h as $aid => $it) {
        $aid = (int) $aid; $e = isset($it['e']) ? (int) $it['e'] : 0;
        if (!$aid || !$e || get_post_status($aid) !== 'publish' || get_post_status($e) !== 'publish') continue;
        if ((int) get_post_meta($e, '_episode_anime_id', true) !== $aid) continue;
        $out[] = ['a' => $aid, 'e' => $e, 'n' => anizen_ep_number($e), 'l' => anizen_ep_label($e), 't' => get_the_title($aid),
                  'c' => anizen_cover_url($aid, 'thumbnail'), 'u' => get_permalink($e)];
        if (count($out) >= $limit) break;
    }
    return $out;
}

/* --- Sayfaya üye bilgisini ver --- */
add_action('wp_enqueue_scripts', function () {
    $d = [
        'in' => is_user_logged_in() ? 1 : 0, 'login' => anizen_login_url(), 'register' => anizen_register_url(), 'list' => anizen_list_url(),
        'i18n' => ['needLogin' => __('Bu işlem için giriş yapmalısın veya kaydolmalısın.', 'anizen'), 'login' => __('Giriş yap', 'anizen'), 'register' => __('Kaydol', 'anizen')],
    ];
    if (is_user_logged_in()) {
        $uid = get_current_user_id();
        $d += ['nonce' => wp_create_nonce('anizen_user'), 'likes' => anizen_ulist('anizen_likes', $uid), 'clikes' => anizen_ulist('anizen_clikes', $uid),
               'follow' => anizen_ulist('anizen_follow', $uid), 'watched' => anizen_ulist('anizen_watched', $uid), 'history' => anizen_user_history_items($uid, 12)];
    }
    wp_add_inline_script('anizen-main', 'window.ANIZEN_USER=' . wp_json_encode($d) . ';', 'before');
}, 20);

/* --- Listem sayfası: /listem/ --- */
add_filter('query_vars', function ($v) { $v[] = 'anizen_page'; return $v; });
add_action('init', function () {
    add_rewrite_rule('^listem/?$', 'index.php?anizen_page=listem', 'top');
}, 11);
add_action('template_redirect', function () {
    if (get_query_var('anizen_page') !== 'listem') return;
    if (!is_user_logged_in()) { wp_safe_redirect(wp_login_url(anizen_list_url())); exit; }
    global $wp_query;
    $wp_query->is_404 = false; $wp_query->is_home = false;
    status_header(200); nocache_headers();
    add_filter('pre_get_document_title', function () { return __('Listem', 'anizen') . ' – ' . get_bloginfo('name'); });
    include ANIZEN_DIR . '/user-list.php';
    exit;
}, 2);

/* ------------------------------------------------------------------
   Yönetim: hata bildirimleri nerede görünür?
   1) Bölümler menüsünde sayı rozeti  2) Bölümler listesinde "Hata" sütunu ve süzgeç
   3) Panel (Dashboard) kutusu  4) E-posta (Özelleştirici'deki adres, boşsa yönetici e-postası)
------------------------------------------------------------------- */
function anizen_reported_count() {
    global $wpdb;
    return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} m JOIN {$wpdb->posts} p ON p.ID = m.post_id
        WHERE m.meta_key = '_reports' AND CAST(m.meta_value AS UNSIGNED) > 0 AND p.post_type = 'episode' AND p.post_status = 'publish'");
}

add_action('admin_menu', function () {
    global $submenu;
    $n = anizen_reported_count();
    if (!$n || empty($submenu['edit.php?post_type=anime'])) return;
    foreach ($submenu['edit.php?post_type=anime'] as &$it) {
        if (isset($it[2]) && $it[2] === 'edit.php?post_type=episode') {
            $it[0] .= ' <span class="update-plugins count-' . $n . '"><span class="plugin-count">' . $n . '</span></span>';
        }
    }
}, 99);

add_action('wp_dashboard_setup', function () {
    if (!current_user_can('edit_posts')) return;
    wp_add_dashboard_widget('anizen_reports', __('Hata bildirimleri', 'anizen'), function () {
        $ids = get_posts(['post_type' => 'episode', 'post_status' => 'publish', 'posts_per_page' => 10, 'no_found_rows' => true, 'fields' => 'ids',
            'meta_key' => '_reports', 'orderby' => 'meta_value_num', 'order' => 'DESC',
            'meta_query' => [['key' => '_reports', 'value' => 0, 'type' => 'NUMERIC', 'compare' => '>']]]);
        if (!$ids) { echo '<p>' . esc_html__('Bekleyen hata bildirimi yok.', 'anizen') . '</p>'; return; }
        echo '<ul>';
        foreach ($ids as $id) {
            $log = get_post_meta($id, '_report_log', true); $last = is_array($log) && $log ? end($log) : null;
            echo '<li><strong>⚠ ' . (int) get_post_meta($id, '_reports', true) . '</strong> <a href="' . esc_url(get_edit_post_link($id)) . '">' . esc_html(get_the_title($id)) . '</a>';
            if ($last) echo '<br><span class="description">' . esc_html(trim(($last['reason'] ?? '') . ' · ' . ($last['server'] ?? '') . ' · ' . ($last['u'] ?? ''), ' ·')) . '</span>';
            echo '</li>';
        }
        echo '</ul><p><a href="' . esc_url(admin_url('edit.php?post_type=episode&anizen_reported=1')) . '">' . esc_html__('Tümünü gör', 'anizen') . '</a></p>';
    });
});
