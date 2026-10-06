<?php
if (!defined('ABSPATH')) exit;

function anizen_ip_hash() {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    return md5($ip . wp_salt());
}

function anizen_is_bot() {
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
    return $ua === '' || (bool) preg_match('~bot|crawl|spider|slurp|facebookexternalhit|preview|headless~', $ua);
}

/* Yönetim: anime arama (bölüm ekranındaki seçici) */
add_action('wp_ajax_anizen_search_anime', function () {
    check_ajax_referer('anizen_admin', 'nonce');
    if (!current_user_can('edit_posts')) wp_send_json_error([], 403);
    $q = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
    $posts = get_posts([
        'post_type' => 'anime', 'post_status' => ['publish', 'draft', 'pending', 'private', 'future'],
        's' => $q, 'posts_per_page' => 10, 'no_found_rows' => true, 'orderby' => 'title', 'order' => 'ASC',
    ]);
    $out = [];
    foreach ($posts as $p) $out[] = ['id' => $p->ID, 'title' => html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8')];
    wp_send_json_success($out);
});

/* İzlenme sayacı */
function anizen_ajax_track_view() {
    $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
    if (!$id || anizen_is_bot() || !in_array(get_post_type($id), ['episode', 'anime'], true) || get_post_status($id) !== 'publish') wp_send_json_success();
    $key = 'anizen_v_' . md5($id . anizen_ip_hash());
    if (get_transient($key)) wp_send_json_success();
    set_transient($key, 1, 30 * MINUTE_IN_SECONDS);
    update_post_meta($id, '_views', anizen_views($id) + 1);
    if (get_post_type($id) === 'episode') {
        $aid = (int) get_post_meta($id, '_episode_anime_id', true);
        if ($aid) update_post_meta($aid, '_views', anizen_views($aid) + 1);
    }
    wp_send_json_success();
}
add_action('wp_ajax_anizen_track_view', 'anizen_ajax_track_view');
add_action('wp_ajax_nopriv_anizen_track_view', 'anizen_ajax_track_view');

/* Bozuk video bildirimi */
function anizen_ajax_report() {
    if (!anizen_opt('player_report')) wp_send_json_error();
    anizen_user_guard();
    $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
    if (!$id || get_post_type($id) !== 'episode' || get_post_status($id) !== 'publish') wp_send_json_error();
    /* Her üye, bir bölümü yalnızca bir kez bildirebilir (yönetici sıfırlayana kadar) */
    $uid = get_current_user_id();
    $ru = get_post_meta($id, '_report_users', true);
    $ru = is_array($ru) ? array_map('intval', $ru) : [];
    if (in_array($uid, $ru, true)) wp_send_json_success(['dup' => true]);
    $ru[] = $uid;
    update_post_meta($id, '_report_users', $ru);

    $reasons = ['Video açılmıyor', 'Yanlış bölüm', 'Altyazı / ses sorunu', 'Kalite çok düşük', 'Diğer'];
    $reason = isset($_POST['reason']) ? sanitize_text_field(wp_unslash($_POST['reason'])) : '';
    if (!in_array($reason, $reasons, true)) $reason = 'Diğer';
    $server = isset($_POST['server']) ? mb_substr(sanitize_text_field(wp_unslash($_POST['server'])), 0, 60) : '';

    update_post_meta($id, '_reports', (int) get_post_meta($id, '_reports', true) + 1);
    $log = get_post_meta($id, '_report_log', true);
    $log = is_array($log) ? $log : [];
    $u = wp_get_current_user();
    $log[] = ['t' => time(), 'server' => $server, 'reason' => $reason, 'u' => $u->display_name];
    update_post_meta($id, '_report_log', array_slice($log, -20));

    $to = sanitize_email(anizen_opt('report_email'));
    if (!$to) $to = sanitize_email(get_option('admin_email'));
    if ($to) {
        wp_mail($to, '[' . get_bloginfo('name') . '] Bölüm hata bildirimi',
            get_the_title($id) . "\nSunucu: " . $server . "\nSebep: " . $reason . "\nBildiren: " . $u->display_name . "\n" . get_edit_post_link($id, 'raw'));
    }
    wp_send_json_success();
}
add_action('wp_ajax_anizen_report', 'anizen_ajax_report');
add_action('wp_ajax_nopriv_anizen_report', 'anizen_ajax_need_login');
