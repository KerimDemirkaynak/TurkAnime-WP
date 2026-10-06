<?php
if (!defined('ABSPATH')) exit;

/* ------------------------------------------------------------------
   Seri sayfası önbellek sorunu
   Seri sayfası (/anime/<ad>/) ilk ziyarette önbelleğe alınırsa, sonradan
   eklenen bölümler görünmez; bölüm sayfası ise taze üretildiği için doğru
   görünür. Burada (1) seri/bölüm sayfaları önbelleğe alınmasın,
   (2) bölüm eklenince/değişince seri sayfasının önbelleği temizlensin.
------------------------------------------------------------------- */

/* 1) Seri ve bölüm sayfalarını sayfa önbelleklerinden (eklenti/CDN/sunucu) hariç tut */
add_action('template_redirect', function () {
    if (!is_singular(['anime', 'episode'])) return;
    if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
    if (!defined('DONOTCACHEOBJECT')) define('DONOTCACHEOBJECT', true);
    if (!defined('DONOTCACHEDB')) define('DONOTCACHEDB', true);
    nocache_headers();
    header('X-LiteSpeed-Cache-Control: no-cache');
}, 0);

/* 2) Bir animenin önbelleğini temizle */
function anizen_purge_anime_cache($aid) {
    $aid = (int) $aid;
    if (!$aid) return;
    clean_post_cache($aid);
    $url = get_permalink($aid);

    // Yaygın önbellek eklentileri (hangisi kuruluysa o çalışır)
    if (function_exists('rocket_clean_post'))            rocket_clean_post($aid);
    if (function_exists('w3tc_flush_post'))              w3tc_flush_post($aid);
    if (function_exists('wp_cache_post_change'))         wp_cache_post_change($aid);          // WP Super Cache
    if (function_exists('wpfc_clear_post_cache_by_id'))  wpfc_clear_post_cache_by_id($aid);   // WP Fastest Cache
    if (function_exists('sg_cachepress_purge_cache') && $url) sg_cachepress_purge_cache($url); // SiteGround
    if (has_action('litespeed_purge_post'))              do_action('litespeed_purge_post', $aid);
    if (has_action('litespeed_purge_url') && $url)       do_action('litespeed_purge_url', $url);
    if (class_exists('autoptimizeCache'))                autoptimizeCache::clearall();
    do_action('anizen_anime_cache_purged', $aid, $url);
}

add_action('transition_post_status', function ($new, $old, $post) {
    if ($post->post_type !== 'episode') return;
    anizen_purge_anime_cache((int) get_post_meta($post->ID, '_episode_anime_id', true));
}, 20, 3);

add_action('save_post_episode', function ($post_id) {
    if (wp_is_post_revision($post_id)) return;
    anizen_purge_anime_cache((int) get_post_meta($post_id, '_episode_anime_id', true));
}, 99);

add_action('before_delete_post', function ($id) {
    if (get_post_type($id) !== 'episode') return;
    $aid = (int) get_post_meta($id, '_episode_anime_id', true);
    if ($aid) add_action('deleted_post', function () use ($aid) { anizen_purge_anime_cache($aid); });
});
