<?php
if (!defined('ABSPATH')) exit;

add_action('init', 'anizen_register_types');
function anizen_register_types() {
    register_post_type('anime', [
        'labels' => [
            'name' => __('Animeler', 'anizen'), 'singular_name' => __('Anime', 'anizen'), 'menu_name' => __('Animeler', 'anizen'),
            'add_new' => __('Yeni Anime Ekle', 'anizen'), 'add_new_item' => __('Yeni Anime Ekle', 'anizen'),
            'edit_item' => __('Animeyi Düzenle', 'anizen'), 'new_item' => __('Yeni Anime', 'anizen'),
            'view_item' => __('Animeyi Görüntüle', 'anizen'), 'search_items' => __('Anime Ara', 'anizen'),
            'not_found' => __('Anime bulunamadı', 'anizen'), 'all_items' => __('Tüm Animeler', 'anizen'),
        ],
        'public' => true, 'has_archive' => true, 'menu_position' => 5, 'menu_icon' => 'dashicons-video-alt3',
        'rewrite' => ['slug' => 'anime', 'with_front' => false],
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'comments'],
    ]);
    register_post_type('episode', [
        'labels' => [
            'name' => __('Bölümler', 'anizen'), 'singular_name' => __('Bölüm', 'anizen'), 'menu_name' => __('Bölümler', 'anizen'),
            'add_new' => __('Yeni Bölüm Ekle', 'anizen'), 'add_new_item' => __('Yeni Bölüm Ekle', 'anizen'),
            'edit_item' => __('Bölümü Düzenle', 'anizen'), 'new_item' => __('Yeni Bölüm', 'anizen'),
            'view_item' => __('Bölümü Görüntüle', 'anizen'), 'search_items' => __('Bölüm Ara', 'anizen'),
            'not_found' => __('Bölüm bulunamadı', 'anizen'), 'all_items' => __('Tüm Bölümler', 'anizen'),
        ],
        'public' => true, 'has_archive' => 'bolumler', 'menu_icon' => 'dashicons-controls-play',
        'show_in_menu' => 'edit.php?post_type=anime',
        'rewrite' => ['slug' => 'bolum', 'with_front' => false],
        'supports' => ['title', 'comments'],
    ]);
    register_taxonomy('genre', ['anime'], [
        'hierarchical' => true, 'show_ui' => true, 'show_admin_column' => true,
        'labels' => ['name' => __('Türler', 'anizen'), 'singular_name' => __('Tür', 'anizen'), 'add_new_item' => __('Yeni Tür Ekle', 'anizen'), 'search_items' => __('Tür Ara', 'anizen'), 'all_items' => __('Tüm Türler', 'anizen')],
        'rewrite' => ['slug' => 'tur', 'with_front' => false],
    ]);
    register_taxonomy('studio', ['anime'], [
        'hierarchical' => false, 'show_ui' => true, 'show_admin_column' => false,
        'labels' => ['name' => __('Stüdyolar', 'anizen'), 'singular_name' => __('Stüdyo', 'anizen'), 'add_new_item' => __('Yeni Stüdyo Ekle', 'anizen'), 'search_items' => __('Stüdyo Ara', 'anizen'), 'all_items' => __('Tüm Stüdyolar', 'anizen')],
        'rewrite' => ['slug' => 'studyo', 'with_front' => false],
    ]);
}

/* Klasik düzenleyici: meta kutuları ve otomatik başlık tutarlı çalışsın */
add_filter('use_block_editor_for_post_type', function ($use, $type) {
    return in_array($type, ['anime', 'episode'], true) ? false : $use;
}, 10, 2);

/* Bölüm başlığı boşsa "Anime Adı 5. Bölüm" olarak otomatik oluştur */
add_filter('wp_insert_post_data', function ($data, $postarr) {
    if ($data['post_type'] !== 'episode' || in_array($data['post_status'], ['auto-draft', 'trash'], true)) return $data;
    if (trim($data['post_title']) !== '') return $data;
    if (!isset($_POST['anizen_ep_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['anizen_ep_nonce'])), 'anizen_save_episode')) return $data;
    $aid = isset($_POST['episode_anime_id']) ? absint($_POST['episode_anime_id']) : 0;
    if (!$aid && !empty($postarr['ID'])) $aid = (int) get_post_meta((int) $postarr['ID'], '_episode_anime_id', true);
    $num = isset($_POST['episode_number']) ? sanitize_text_field(wp_unslash($_POST['episode_number'])) : '';
    $p = $num !== '' ? anizen_ep_parse($num) : null;
    if ($aid && $p) {
        $data['post_title'] = is_numeric($p['number']) ? sprintf('%s %s. Bölüm', get_the_title($aid), str_replace('.', ',', $p['number'])) : sprintf('%s %s', get_the_title($aid), $p['number']);
    }
    return $data;
}, 10, 2);

/* Sayaçlar: bölüm eklenince/silinince/durumu değişince anime sayacını güncelle */
add_action('transition_post_status', function ($new, $old, $post) {
    if ($post->post_type !== 'episode' || $new === $old) return;
    $aid = (int) get_post_meta($post->ID, '_episode_anime_id', true);
    if ($aid) anizen_recount($aid);
}, 10, 3);

add_action('before_delete_post', function ($id) {
    if (get_post_type($id) !== 'episode') return;
    $aid = (int) get_post_meta($id, '_episode_anime_id', true);
    if ($aid) {
        // Silme tamamlanınca sayacı yeniden hesapla
        add_action('deleted_post', function () use ($aid) { anizen_recount($aid); });
    }
});
