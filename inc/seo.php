<?php
if (!defined('ABSPATH')) exit;

function anizen_seo_plugin_active() {
    return defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('AIOSEO_VERSION') || defined('SEOPRESS_VERSION') || class_exists('The_SEO_Framework\Load');
}

/* Bölüm başlığı: "Anime 5. Bölüm İzle" */
add_filter('document_title_parts', function ($p) {
    if (is_singular('episode')) {
        $aid = (int) get_post_meta(get_the_ID(), '_episode_anime_id', true);
        if ($aid) $p['title'] = trim(get_the_title($aid) . ' ' . anizen_ep_label(get_the_ID())) . ' ' . __('İzle', 'anizen');
    } elseif (is_singular('anime')) {
        $p['title'] = sprintf(__('%s İzle', 'anizen'), get_the_title());
    }
    return $p;
});

function anizen_page_description() {
    if (is_singular('anime')) {
        $d = anizen_synopsis(get_the_ID(), 28);
        return $d ?: sprintf(__('%s animesini Türkçe altyazılı izle.', 'anizen'), get_the_title());
    }
    if (is_singular('episode')) {
        $aid = (int) get_post_meta(get_the_ID(), '_episode_anime_id', true);
        return sprintf(__('%1$s %2$s izle.', 'anizen'), $aid ? get_the_title($aid) : get_the_title(), mb_strtolower(anizen_ep_label(get_the_ID()), 'UTF-8'));
    }
    if (is_tax('genre') || is_tax('studio')) {
        $t = term_description();
        return $t ? wp_strip_all_tags($t) : sprintf(__('%s kategorisindeki animeler.', 'anizen'), single_term_title('', false));
    }
    return get_bloginfo('description');
}

add_action('wp_head', function () {
    if (!anizen_opt('seo_meta') || anizen_seo_plugin_active()) return;
    $desc = esc_attr(wp_html_excerpt(anizen_page_description(), 200, '…'));
    $title = esc_attr(wp_get_document_title());
    $url = esc_url(is_singular() ? get_permalink() : home_url(add_query_arg([])));
    $img = '';
    if (is_singular('anime')) $img = anizen_banner_url(get_the_ID()) ?: anizen_cover_url(get_the_ID(), 'large');
    elseif (is_singular('episode')) {
        $aid = (int) get_post_meta(get_the_ID(), '_episode_anime_id', true);
        if ($aid) $img = anizen_banner_url($aid) ?: anizen_cover_url($aid, 'large');
    } elseif (has_site_icon()) $img = get_site_icon_url(512);

    if ($desc) echo '<meta name="description" content="' . $desc . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    echo '<meta property="og:locale" content="' . esc_attr(get_locale()) . '">' . "\n";
    echo '<meta property="og:type" content="' . (is_singular('episode') ? 'video.episode' : (is_singular('anime') ? 'video.tv_show' : 'website')) . '">' . "\n";
    echo '<meta property="og:title" content="' . $title . '">' . "\n";
    if ($desc) echo '<meta property="og:description" content="' . $desc . '">' . "\n";
    echo '<meta property="og:url" content="' . $url . '">' . "\n";
    if ($img) echo '<meta property="og:image" content="' . esc_url($img) . '">' . "\n";
    echo '<meta name="twitter:card" content="' . ($img ? 'summary_large_image' : 'summary') . '">' . "\n";
}, 5);

/* JSON-LD */
add_action('wp_head', function () {
    if (!anizen_opt('schema')) return;
    $data = null;
    if (is_singular('anime')) {
        $id = get_the_ID();
        $terms = get_the_terms($id, 'genre');
        $genres = ($terms && !is_wp_error($terms)) ? wp_list_pluck($terms, 'name') : [];
        $data = [
            '@context' => 'https://schema.org', '@type' => 'TVSeries',
            'name' => get_the_title(), 'url' => get_permalink(), 'image' => anizen_cover_url($id, 'large'),
            'description' => anizen_synopsis($id, 50), 'numberOfEpisodes' => anizen_episode_count($id),
        ];
        if ($genres) $data['genre'] = array_values($genres);
    } elseif (is_singular('episode')) {
        $id = get_the_ID();
        $aid = (int) get_post_meta($id, '_episode_anime_id', true);
        $data = [
            '@context' => 'https://schema.org', '@type' => 'TVEpisode',
            'name' => get_the_title(), 'url' => get_permalink(), 'episodeNumber' => anizen_ep_number($id),
            'datePublished' => get_the_date('c'),
        ];
        if ($aid) {
            $data['partOfSeries'] = ['@type' => 'TVSeries', 'name' => get_the_title($aid), 'url' => get_permalink($aid)];
            $data['image'] = anizen_cover_url($aid, 'large');
        }
    }
    if ($data) echo '<script type="application/ld+json">' . wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . "</script>\n";
}, 6);
