<?php
if (!defined('ABSPATH')) exit;

/* ------------------------------------------------------------------
   Bölüm adresleri:  /anime/<seri-adi>/bolum/<numara>/
   Numara yerine özel bölümlerde yazı olabilir: /anime/<seri-adi>/bolum/ova-1/
   (Eski /bolum/<yazi-adi>/ adresleri yeni adrese 301 ile yönlenir.)
------------------------------------------------------------------- */
add_filter('query_vars', function ($v) {
    $v[] = 'anizen_anime';
    $v[] = 'anizen_ep';
    return $v;
});

add_action('init', function () {
    add_rewrite_rule('^anime/([^/]+)/bolum/([^/]+)/?$', 'index.php?anizen_anime=$matches[1]&anizen_ep=$matches[2]', 'top');
}, 11);

/* Adres -> bölüm kimliği. Seri adresi + bölüm anahtarı birlikte aranır; bu yüzden iki seri asla karışmaz. */
add_filter('request', function ($qv) {
    if (empty($qv['anizen_anime']) || !isset($qv['anizen_ep'])) return $qv;
    $slug = sanitize_title(wp_unslash($qv['anizen_anime']));
    $key  = sanitize_text_field(wp_unslash($qv['anizen_ep']));
    $anime = get_page_by_path($slug, OBJECT, 'anime');
    $id = 0;
    if ($anime) {
        $ids = get_posts([
            'post_type' => 'episode', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => true,
            'meta_query' => [
                'relation' => 'AND',
                ['key' => '_episode_anime_id', 'value' => (string) $anime->ID],
                ['key' => '_episode_key', 'value' => $key],
            ],
        ]);
        $id = $ids ? (int) $ids[0] : 0;
    }
    return $id ? ['post_type' => 'episode', 'p' => $id, 'anizen_ep' => $key] : ['error' => 404];
});

/* Bölümün adresi */
add_filter('post_type_link', function ($link, $post) {
    if ($post->post_type !== 'episode' || $post->post_status !== 'publish') return $link;
    $aid = (int) get_post_meta($post->ID, '_episode_anime_id', true);
    $key = (string) get_post_meta($post->ID, '_episode_key', true);
    if (!$aid || $key === '') return $link;
    $slug = get_post_field('post_name', $aid);
    if (!$slug || !get_option('permalink_structure')) return $link;
    return home_url(user_trailingslashit('anime/' . $slug . '/bolum/' . rawurlencode($key)));
}, 10, 2);

/* Eski adres -> yeni adres */
add_action('template_redirect', function () {
    if (!is_singular('episode') || get_query_var('anizen_ep') !== '' || !get_option('permalink_structure')) return;
    $new = get_permalink(get_queried_object_id());
    if ($new && strpos($new, '/bolum/') !== false && preg_match('~/anime/[^/]+/bolum/~', $new)) {
        wp_safe_redirect($new, 301);
        exit;
    }
}, 1);

/* ------------------------------------------------------------------
   Tek seferlik taşıma: mevcut bölümlere sıralama + adres anahtarı yaz, kuralları yenile.
------------------------------------------------------------------- */
add_action('init', function () {
    if ((int) get_option('anizen_ep_urls_version', 0) >= 2) return;
    if (get_transient('anizen_ep_urls_lock')) return;
    set_transient('anizen_ep_urls_lock', 1, 5 * MINUTE_IN_SECONDS);
    if (function_exists('set_time_limit')) @set_time_limit(0);

    $ids = get_posts([
        'post_type' => 'episode', 'post_status' => ['publish', 'draft', 'pending', 'future', 'private'],
        'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true, 'orderby' => 'ID', 'order' => 'ASC',
        'meta_query' => [['key' => '_episode_number', 'compare' => 'EXISTS']],
    ]);
    foreach ($ids as $id) {
        $aid = (int) get_post_meta($id, '_episode_anime_id', true);
        $raw = (string) get_post_meta($id, '_episode_number', true);
        if (get_post_meta($id, '_episode_key', true) !== '' && get_post_meta($id, '_episode_sort', true) !== '') continue;
        anizen_ep_save_meta($id, $aid, $raw);
    }
    flush_rewrite_rules(false);
    update_option('anizen_ep_urls_version', 2);
    delete_transient('anizen_ep_urls_lock');
}, 30);

/* Tema yeniden etkinleştirilince yeniden kontrol et */
add_action('after_switch_theme', function () {
    delete_option('anizen_ep_urls_version');
});

/* Kalıcı bağlantılar "Düz" ise (/?anime=ad) güzel adresler çalışmaz: yöneticiyi uyar */
add_action('admin_notices', function () {
    if (get_option('permalink_structure') || !current_user_can('manage_options')) return;
    echo '<div class="notice notice-warning"><p><strong>TürkAnime:</strong> Kalıcı bağlantılar “Düz” olarak ayarlı, bu yüzden adresler <code>/?anime=ad</code> görünüyor. '
        . '<a href="' . esc_url(admin_url('options-permalink.php')) . '">Ayarlar → Kalıcı Bağlantılar</a> sayfasında <strong>“Yazı adı”</strong>nı seçip kaydedin; '
        . 'bölümler <code>/anime/seri-adi/bolum/12/</code> olur.</p></div>';
});
