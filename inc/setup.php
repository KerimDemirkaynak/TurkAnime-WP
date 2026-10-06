<?php
if (!defined('ABSPATH')) exit;

/* ------------------------------------------------------------------
   Tema desteği
------------------------------------------------------------------- */
add_action('after_setup_theme', function () {
    load_theme_textdomain('anizen', ANIZEN_DIR . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('responsive-embeds');
    add_theme_support('html5', ['comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo', ['height' => 60, 'width' => 220, 'flex-width' => true, 'flex-height' => true]);
    add_image_size('anizen-poster', 360, 520, true);
    add_image_size('anizen-wide', 640, 360, true);
    register_nav_menus([
        'primary' => __('Üst Menü', 'anizen'),
        'footer'  => __('Alt Menü', 'anizen'),
    ]);
});

add_action('widgets_init', function () {
    $common = ['before_widget' => '<section id="%1$s" class="widget panel %2$s">', 'after_widget' => '</section>', 'before_title' => '<h3 class="widget-title">', 'after_title' => '</h3>'];
    register_sidebar(['name' => __('Kenar Çubuğu', 'anizen'), 'id' => 'sidebar-main', 'description' => __('Anime, bölüm ve sayfa kenar çubuğu.', 'anizen')] + $common);
    for ($i = 1; $i <= 3; $i++) {
        register_sidebar(['name' => sprintf(__('Alt Bilgi %d', 'anizen'), $i), 'id' => 'footer-' . $i] + $common);
    }
});

/* ------------------------------------------------------------------
   Stil ve betikler
------------------------------------------------------------------- */
function anizen_font_map() {
    return [
        'inter' => ['Inter', 'Inter:wght@400;500;600;700;800'],
        'poppins' => ['Poppins', 'Poppins:wght@400;500;600;700'],
        'nunito' => ['Nunito', 'Nunito:wght@400;600;700;800'],
        'roboto' => ['Roboto', 'Roboto:wght@400;500;700'],
    ];
}

add_action('wp_enqueue_scripts', function () {
    $font = anizen_opt('font');
    $map = anizen_font_map();
    if (isset($map[$font])) {
        wp_enqueue_style('anizen-font', 'https://fonts.googleapis.com/css2?family=' . $map[$font][1] . '&display=swap', [], null);
    }
    wp_enqueue_style('anizen-main', ANIZEN_URI . '/assets/css/main.css', [], ANIZEN_VERSION);
    wp_add_inline_style('anizen-main', anizen_dynamic_css());

    wp_enqueue_script('anizen-main', ANIZEN_URI . '/assets/js/main.js', [], ANIZEN_VERSION, true);
    wp_localize_script('anizen-main', 'ANIZEN', [
        'ajax' => admin_url('admin-ajax.php'),
        'rest' => esc_url_raw(rest_url('anizen/v1/')),
        'home' => home_url('/'),
        'i18n' => [
            'noResult' => __('Sonuç bulunamadı', 'anizen'),
            'episode'  => __('. Bölüm', 'anizen'),
            'resume'   => __('Devam et', 'anizen'),
            'thanks'   => __('Bildirimin için teşekkürler!', 'anizen'),
            'error'    => __('Bir hata oluştu, lütfen tekrar dene.', 'anizen'),
        ],
    ]);
    if (is_singular('episode')) {
        wp_enqueue_script('anizen-player', ANIZEN_URI . '/assets/js/player.js', ['anizen-main'], ANIZEN_VERSION, true);
    }
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
});

function anizen_dynamic_css() {
    $hex = function ($key) {
        $v = sanitize_hex_color(anizen_opt($key));
        $d = anizen_defaults();
        return $v ? $v : $d[$key];
    };
    $stacks = [
        'system' => 'system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif',
        'inter' => '"Inter",system-ui,sans-serif', 'poppins' => '"Poppins",system-ui,sans-serif',
        'nunito' => '"Nunito",system-ui,sans-serif', 'roboto' => '"Roboto",system-ui,sans-serif',
    ];
    $font = anizen_opt('font');
    $stack = isset($stacks[$font]) ? $stacks[$font] : $stacks['system'];
    $radius = in_array((string) anizen_opt('radius'), ['4', '8', '12', '16', '22'], true) ? anizen_opt('radius') : '4';
    $container = (int) anizen_opt('container');
    if ($container < 960 || $container > 1920) $container = 1320;
    $cols = (int) anizen_opt('columns');
    if ($cols < 3 || $cols > 8) $cols = 6;
    $ratio = preg_match('~^\d{1,2}/\d{1,2}$~', (string) anizen_opt('player_ratio')) ? anizen_opt('player_ratio') : '16/9';

    return ':root{--accent:' . $hex('accent') . ';--accent2:' . $hex('accent2') . ';--radius:' . (int) $radius . 'px;--container:' . $container . 'px;--cols:' . $cols . ';--player-ratio:' . $ratio . ';--font:' . $stack . ';}'
        . ':root,[data-theme=dark]{--bg:' . $hex('bg_dark') . ';--surface:' . $hex('surface_dark') . ';}'
        . '[data-theme=light]{--bg:' . $hex('bg_light') . ';--surface:' . $hex('surface_light') . ';}'
        . (anizen_opt('sticky_header') ? '' : '.site-header{position:static}');
}

/* Boyama öncesi tema (yanıp sönmeyi önler) */
add_action('wp_head', function () {
    echo '<script>(function(){try{var m=localStorage.getItem("anizen-theme")||' . wp_json_encode((string) anizen_opt('default_mode')) . ';if(m==="auto")m=matchMedia("(prefers-color-scheme:light)").matches?"light":"dark";document.documentElement.setAttribute("data-theme",m)}catch(e){}})()</script>' . "\n";
}, 1);

add_action('wp_head', function () {
    $c = anizen_opt('head_code');
    if ($c) echo $c . "\n"; // yalnızca unfiltered_html yetkisi olan yönetici kaydedebilir
}, 99);
add_action('wp_footer', function () {
    $c = anizen_opt('footer_code');
    if ($c) echo $c . "\n";
}, 99);

/* ------------------------------------------------------------------
   Sorgular: arama, filtreler, harf, sıralama
------------------------------------------------------------------- */
add_action('pre_get_posts', function ($q) {
    if (is_admin() || !$q->is_main_query()) return;

    if ($q->is_search()) {
        $q->set('post_type', 'anime');
        $q->set('anizen_search', 1);
        $q->set('posts_per_page', max(6, (int) anizen_opt('per_page')));
        return;
    }
    if ($q->is_post_type_archive('episode')) {
        $q->set('posts_per_page', 24);
        return;
    }
    if (!($q->is_post_type_archive('anime') || $q->is_tax(['genre', 'studio']))) return;

    $q->set('posts_per_page', max(6, (int) anizen_opt('per_page')));
    $mq = ['relation' => 'AND'];

    $yil = isset($_GET['yil']) ? absint($_GET['yil']) : 0;
    if ($yil) $mq[] = ['key' => '_anime_year', 'value' => (string) $yil];

    $durum = isset($_GET['durum']) ? sanitize_key(wp_unslash($_GET['durum'])) : '';
    if ($durum && isset(anizen_statuses()[$durum])) $mq[] = ['key' => '_anime_status', 'value' => $durum];

    $tip = isset($_GET['tip']) ? sanitize_key(wp_unslash($_GET['tip'])) : '';
    if ($tip && isset(anizen_types()[$tip])) $mq[] = ['key' => '_anime_type', 'value' => $tip];

    $harf = isset($_GET['harf']) ? mb_strtoupper(sanitize_text_field(wp_unslash($_GET['harf']))) : '';
    if ($harf !== '' && preg_match('/^([A-ZÇĞİÖŞÜ]|0)$/u', $harf)) $q->set('anizen_letter', $harf);

    $sirala = isset($_GET['sirala']) ? sanitize_key(wp_unslash($_GET['sirala'])) : '';
    $meta_sorts = ['puan' => ['_anime_score', 'DECIMAL(4,1)'], 'populer' => ['_views', 'NUMERIC'], 'guncel' => ['_last_ep_time', 'NUMERIC']];
    if ($sirala === 'az') {
        $q->set('orderby', 'title');
        $q->set('order', 'ASC');
    } elseif (isset($meta_sorts[$sirala])) {
        list($k, $t) = $meta_sorts[$sirala];
        $mq['sortgrp'] = ['relation' => 'OR', 'srt' => ['key' => $k, 'type' => $t, 'compare' => 'EXISTS'], ['key' => $k, 'compare' => 'NOT EXISTS']];
        $q->set('orderby', ['srt' => 'DESC', 'title' => 'ASC']);
    }
    if (count($mq) > 1) $q->set('meta_query', $mq);
});

add_filter('posts_where', function ($where, $q) {
    $h = $q->get('anizen_letter');
    if (!$h) return $where;
    global $wpdb;
    if ($h === '0') return $where . " AND {$wpdb->posts}.post_title REGEXP '^[0-9]'";
    return $where . $wpdb->prepare(" AND {$wpdb->posts}.post_title LIKE %s", $wpdb->esc_like($h) . '%');
}, 10, 2);

/* Arama: başlık + alternatif isim */
add_filter('posts_search', function ($search, $q) {
    if (!$q->get('anizen_search')) return $search;
    $term = trim((string) $q->get('s'));
    if ($term === '') return $search;
    global $wpdb;
    $like = '%' . $wpdb->esc_like($term) . '%';
    $clause = $wpdb->prepare(
        " AND ({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_anime_alt_title' AND meta_value LIKE %s)) ",
        $like, $like
    );
    if (!is_user_logged_in()) $clause .= " AND ({$wpdb->posts}.post_password = '') ";
    return $clause;
}, 10, 2);

/* Rastgele anime: /?rastgele=1 */
add_action('template_redirect', function () {
    if (!isset($_GET['rastgele'])) return;
    $p = get_posts(['post_type' => 'anime', 'post_status' => 'publish', 'posts_per_page' => 1, 'orderby' => 'rand', 'fields' => 'ids', 'no_found_rows' => true]);
    nocache_headers();
    wp_safe_redirect($p ? get_permalink($p[0]) : home_url('/'));
    exit;
});

/* Arama sonuçları ve süzülmüş liste sayfaları indekslenmesin */
add_filter('wp_robots', function ($robots) {
    $filtered = false;
    foreach (['yil', 'durum', 'tip', 'sirala', 'harf'] as $k) if (isset($_GET[$k])) $filtered = true;
    if (is_search() || $filtered) {
        $robots['noindex'] = true;
        $robots['follow'] = true;
        unset($robots['max-image-preview']);
    }
    return $robots;
});

/* ------------------------------------------------------------------
   Canlı arama (REST)
------------------------------------------------------------------- */
add_action('rest_api_init', function () {
    register_rest_route('anizen/v1', '/search', [
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => 'anizen_rest_search',
        'args' => ['q' => ['sanitize_callback' => 'sanitize_text_field']],
    ]);
});

function anizen_rest_search($req) {
    $q = trim((string) $req->get_param('q'));
    if (mb_strlen($q) < 2) return [];
    $query = new WP_Query([
        'post_type' => 'anime', 'post_status' => 'publish', 's' => $q, 'anizen_search' => 1,
        'posts_per_page' => 8, 'no_found_rows' => true, 'orderby' => 'title', 'order' => 'ASC',
    ]);
    $out = [];
    foreach ($query->posts as $p) {
        $out[] = [
            'title' => get_the_title($p),
            'url'   => get_permalink($p),
            'cover' => anizen_cover_url($p->ID, 'thumbnail'),
            'year'  => (string) get_post_meta($p->ID, '_anime_year', true),
            'eps'   => anizen_episode_count($p->ID),
        ];
    }
    return $out;
}

/* ------------------------------------------------------------------
   Kurulum / güncelleme: kalıcı bağlantılar + eski veriyi uyumlu hale getirme
------------------------------------------------------------------- */
add_action('after_switch_theme', function () {
    if (function_exists('anizen_register_types')) anizen_register_types();
    flush_rewrite_rules(false);
    delete_option('anizen_db_version');
});

add_action('admin_init', function () {
    if ((int) get_option('anizen_db_version', 0) >= 1) return;
    global $wpdb;

    // 1) Eski serbest metin durumları ("FINISHED", "Tamamlandı"…) tek biçime çevir
    $vals = $wpdb->get_col("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_anime_status'");
    foreach ((array) $vals as $raw) {
        $n = anizen_normalize_status($raw);
        if ($n && $n !== $raw) $wpdb->update($wpdb->postmeta, ['meta_value' => $n], ['meta_key' => '_anime_status', 'meta_value' => $raw]);
    }
    // 2) Bölüm sayaçlarını tek sorguyla hesapla
    $rows = $wpdb->get_results(
        "SELECT m.meta_value aid, COUNT(*) c, MAX(p.post_date_gmt) l FROM {$wpdb->posts} p
         JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_episode_anime_id'
         WHERE p.post_type = 'episode' AND p.post_status = 'publish' GROUP BY m.meta_value"
    );
    foreach ((array) $rows as $r) {
        $id = (int) $r->aid;
        if (!$id) continue;
        update_post_meta($id, '_ep_count', (int) $r->c);
        update_post_meta($id, '_last_ep_time', $r->l ? (int) strtotime($r->l . ' UTC') : 0);
    }
    flush_rewrite_rules(false);
    update_option('anizen_db_version', 1);
});


/* WordPress'in varsayılan blok widget'larındaki İngilizce başlıkları Türkçeleştir, aramaya ipucu ekle */
add_filter('widget_block_content', function ($html) {
    $tr = ['Search' => 'Ara', 'Recent Posts' => 'Son Yazılar', 'Recent Comments' => 'Son Yorumlar', 'Archives' => 'Arşivler', 'Categories' => 'Kategoriler', 'Tag Cloud' => 'Etiketler', 'Meta' => 'Meta', 'Calendar' => 'Takvim', 'Pages' => 'Sayfalar'];
    foreach ($tr as $en => $t) {
        $html = preg_replace('/(<h[1-6][^>]*>)\s*' . preg_quote($en, '/') . '\s*(<\/h[1-6]>)/i', '$1' . $t . '$2', $html);
    }
    $html = preg_replace('/(<input[^>]*class="[^"]*wp-block-search__input[^"]*"[^>]*?)placeholder=""/', '$1placeholder="' . esc_attr__('Anime ara...', 'anizen') . '"', $html);
    return $html;
});
