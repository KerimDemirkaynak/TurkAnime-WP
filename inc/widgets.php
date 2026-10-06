<?php
if (!defined('ABSPATH')) exit;

/* Ortak mini liste öğesi */
function anizen_mini_item($id, $sub = '') {
    $eps = anizen_episode_count($id);
    $score = get_post_meta($id, '_anime_score', true);
    echo '<li><a class="mini" href="' . esc_url(get_permalink($id)) . '">';
    echo '<img src="' . esc_url(anizen_cover_url($id, 'thumbnail')) . '" width="46" height="66" loading="lazy" alt="">';
    echo '<span class="mini-t"><strong>' . esc_html(get_the_title($id)) . '</strong>';
    echo '<small>' . esc_html($sub !== '' ? $sub : sprintf(_n('%s bölüm', '%s bölüm', $eps, 'anizen'), number_format_i18n($eps))) . '</small></span>';
    if ($score !== '') echo '<span class="mini-s">' . anizen_icon('star', 12) . esc_html($score) . '</span>';
    echo '</a></li>';
}

class Anizen_Widget_Popular extends WP_Widget {
    public function __construct() {
        parent::__construct('anizen_popular', __('TürkAnime: Popüler Animeler', 'anizen'), ['description' => __('En çok izlenen animeler.', 'anizen')]);
    }
    public function widget($args, $inst) {
        $n = !empty($inst['count']) ? min(15, max(1, (int) $inst['count'])) : 6;
        $q = anizen_query_animes('popular', $n);
        if (!$q->have_posts()) return;
        echo $args['before_widget'];
        echo $args['before_title'] . esc_html(!empty($inst['title']) ? $inst['title'] : __('Popüler Animeler', 'anizen')) . $args['after_title'];
        echo '<ul class="mini-list">';
        foreach ($q->posts as $p) anizen_mini_item($p->ID);
        echo '</ul>';
        echo $args['after_widget'];
    }
    public function form($inst) {
        $t = isset($inst['title']) ? $inst['title'] : __('Popüler Animeler', 'anizen');
        $c = isset($inst['count']) ? (int) $inst['count'] : 6;
        echo '<p><label>' . esc_html__('Başlık', 'anizen') . '<input class="widefat" name="' . esc_attr($this->get_field_name('title')) . '" value="' . esc_attr($t) . '"></label></p>';
        echo '<p><label>' . esc_html__('Adet', 'anizen') . '<input class="tiny-text" type="number" min="1" max="15" name="' . esc_attr($this->get_field_name('count')) . '" value="' . esc_attr($c) . '"></label></p>';
    }
    public function update($new, $old) {
        return ['title' => sanitize_text_field($new['title']), 'count' => min(15, max(1, (int) $new['count']))];
    }
}

class Anizen_Widget_Latest extends WP_Widget {
    public function __construct() {
        parent::__construct('anizen_latest', __('TürkAnime: Son Bölümler', 'anizen'), ['description' => __('En son eklenen bölümler.', 'anizen')]);
    }
    public function widget($args, $inst) {
        $n = !empty($inst['count']) ? min(15, max(1, (int) $inst['count'])) : 6;
        $eps = get_posts(['post_type' => 'episode', 'post_status' => 'publish', 'posts_per_page' => $n, 'no_found_rows' => true]);
        if (!$eps) return;
        echo $args['before_widget'];
        echo $args['before_title'] . esc_html(!empty($inst['title']) ? $inst['title'] : __('Son Bölümler', 'anizen')) . $args['after_title'];
        echo '<ul class="mini-list">';
        foreach ($eps as $e) {
            $aid = (int) get_post_meta($e->ID, '_episode_anime_id', true);
            if (!$aid) continue;
            echo '<li><a class="mini" href="' . esc_url(get_permalink($e)) . '">';
            echo '<img src="' . esc_url(anizen_cover_url($aid, 'thumbnail')) . '" width="46" height="66" loading="lazy" alt="">';
            echo '<span class="mini-t"><strong>' . esc_html(get_the_title($aid)) . '</strong><small>' . esc_html(anizen_ep_label($e->ID) . ' · ' . anizen_time_ago($e->ID)) . '</small></span></a></li>';
        }
        echo '</ul>';
        echo $args['after_widget'];
    }
    public function form($inst) {
        $t = isset($inst['title']) ? $inst['title'] : __('Son Bölümler', 'anizen');
        $c = isset($inst['count']) ? (int) $inst['count'] : 6;
        echo '<p><label>' . esc_html__('Başlık', 'anizen') . '<input class="widefat" name="' . esc_attr($this->get_field_name('title')) . '" value="' . esc_attr($t) . '"></label></p>';
        echo '<p><label>' . esc_html__('Adet', 'anizen') . '<input class="tiny-text" type="number" min="1" max="15" name="' . esc_attr($this->get_field_name('count')) . '" value="' . esc_attr($c) . '"></label></p>';
    }
    public function update($new, $old) {
        return ['title' => sanitize_text_field($new['title']), 'count' => min(15, max(1, (int) $new['count']))];
    }
}

/* Son Yorumlar: avatar + "kullanıcı · seri adı bölüm" + kısa yorum */
class Anizen_Widget_Comments extends WP_Widget {
    public function __construct() {
        parent::__construct('anizen_comments', __('TürkAnime: Son Yorumlar', 'anizen'), ['description' => __('Avatarlı son yorumlar (kullanıcı, seri, bölüm).', 'anizen')]);
    }
    public function widget($args, $inst) {
        $n = !empty($inst['count']) ? min(15, max(1, (int) $inst['count'])) : 6;
        $cs = get_comments(['status' => 'approve', 'type' => 'comment', 'number' => $n, 'post_status' => 'publish', 'post_type' => ['anime', 'episode'], 'orderby' => 'comment_date_gmt', 'order' => 'DESC']);
        echo $args['before_widget'];
        echo $args['before_title'] . esc_html(!empty($inst['title']) ? $inst['title'] : __('Son Yorumlar', 'anizen')) . $args['after_title'];
        if (!$cs) {
            echo '<p class="muted">' . esc_html__('Henüz yorum yok.', 'anizen') . '</p>';
        } else {
            echo '<ul class="cmt-list">';
            foreach ($cs as $c) {
                $pid = (int) $c->comment_post_ID;
                if (get_post_type($pid) === 'episode') {
                    $aid = (int) get_post_meta($pid, '_episode_anime_id', true);
                    $where = trim(($aid ? get_the_title($aid) : get_the_title($pid)) . ' · ' . anizen_ep_label($pid), ' ·');
                } else {
                    $where = get_the_title($pid);
                }
                $txt = wp_trim_words(wp_strip_all_tags($c->comment_content), 10, '…');
                echo '<li><a class="cmt" href="' . esc_url(get_comment_link($c)) . '">';
                echo '<span class="cmt-av">' . get_avatar($c, 38, '', '', ['loading' => 'lazy']) . '</span>';
                echo '<span class="cmt-b"><span class="cmt-top"><strong>' . esc_html(get_comment_author($c)) . '</strong><time datetime="' . esc_attr(get_comment_date('c', $c)) . '">' . esc_html(sprintf(__('%s önce', 'anizen'), human_time_diff(get_comment_time('U', true, true, $c), time()))) . '</time></span>';
                echo '<span class="cmt-where">' . anizen_icon('play', 11) . esc_html($where) . '</span>';
                if ($txt !== '') echo '<span class="cmt-txt">' . anizen_icon('message', 12) . esc_html($txt) . '</span>';
                echo '</span></a></li>';
            }
            echo '</ul>';
        }
        echo $args['after_widget'];
    }
    public function form($inst) {
        $t = isset($inst['title']) ? $inst['title'] : __('Son Yorumlar', 'anizen');
        $c = isset($inst['count']) ? (int) $inst['count'] : 6;
        echo '<p><label>' . esc_html__('Başlık', 'anizen') . '<input class="widefat" name="' . esc_attr($this->get_field_name('title')) . '" value="' . esc_attr($t) . '"></label></p>';
        echo '<p><label>' . esc_html__('Adet', 'anizen') . '<input class="tiny-text" type="number" min="1" max="15" name="' . esc_attr($this->get_field_name('count')) . '" value="' . esc_attr($c) . '"></label></p>';
    }
    public function update($new, $old) {
        return ['title' => sanitize_text_field($new['title']), 'count' => min(15, max(1, (int) $new['count']))];
    }
}

/* Türler: etiket bulutu (kategoriler/arşivler yerine) */
class Anizen_Widget_Genres extends WP_Widget {
    public function __construct() {
        parent::__construct('anizen_genres', __('TürkAnime: Türler', 'anizen'), ['description' => __('Anime türleri, sayılarıyla.', 'anizen')]);
    }
    public function widget($args, $inst) {
        $terms = get_terms(['taxonomy' => 'genre', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 30]);
        if (is_wp_error($terms) || !$terms) return;
        echo $args['before_widget'];
        echo $args['before_title'] . esc_html(!empty($inst['title']) ? $inst['title'] : __('Türler', 'anizen')) . $args['after_title'];
        echo '<div class="tag-cloud">';
        foreach ($terms as $t) echo '<a class="tag-chip" href="' . esc_url(get_term_link($t)) . '">' . anizen_icon('tag', 12) . esc_html($t->name) . ' <small>' . (int) $t->count . '</small></a>';
        echo '</div>';
        echo $args['after_widget'];
    }
    public function form($inst) {
        $t = isset($inst['title']) ? $inst['title'] : __('Türler', 'anizen');
        echo '<p><label>' . esc_html__('Başlık', 'anizen') . '<input class="widefat" name="' . esc_attr($this->get_field_name('title')) . '" value="' . esc_attr($t) . '"></label></p>';
    }
    public function update($new, $old) {
        return ['title' => sanitize_text_field($new['title'])];
    }
}

/* Rastgele öneri */
class Anizen_Widget_Random extends WP_Widget {
    public function __construct() {
        parent::__construct('anizen_random', __('TürkAnime: Rastgele Öneri', 'anizen'), ['description' => __('Her sayfa açılışında farklı animeler.', 'anizen')]);
    }
    public function widget($args, $inst) {
        $n = !empty($inst['count']) ? min(10, max(1, (int) $inst['count'])) : 4;
        $ps = get_posts(['post_type' => 'anime', 'post_status' => 'publish', 'posts_per_page' => $n, 'orderby' => 'rand', 'no_found_rows' => true]);
        if (!$ps) return;
        echo $args['before_widget'];
        echo $args['before_title'] . esc_html(!empty($inst['title']) ? $inst['title'] : __('Rastgele Öneri', 'anizen')) . $args['after_title'];
        echo '<ul class="mini-list">';
        foreach ($ps as $p) anizen_mini_item($p->ID);
        echo '</ul>';
        echo $args['after_widget'];
    }
    public function form($inst) {
        $t = isset($inst['title']) ? $inst['title'] : __('Rastgele Öneri', 'anizen');
        $c = isset($inst['count']) ? (int) $inst['count'] : 4;
        echo '<p><label>' . esc_html__('Başlık', 'anizen') . '<input class="widefat" name="' . esc_attr($this->get_field_name('title')) . '" value="' . esc_attr($t) . '"></label></p>';
        echo '<p><label>' . esc_html__('Adet', 'anizen') . '<input class="tiny-text" type="number" min="1" max="10" name="' . esc_attr($this->get_field_name('count')) . '" value="' . esc_attr($c) . '"></label></p>';
    }
    public function update($new, $old) {
        return ['title' => sanitize_text_field($new['title']), 'count' => min(10, max(1, (int) $new['count']))];
    }
}

add_action('widgets_init', function () {
    register_widget('Anizen_Widget_Popular');
    register_widget('Anizen_Widget_Latest');
    register_widget('Anizen_Widget_Comments');
    register_widget('Anizen_Widget_Genres');
    register_widget('Anizen_Widget_Random');
});

/* ------------------------------------------------------------------
   Kenar çubuğu varsayılanları
   WordPress kurulumunda gelen Son Yazılar / Son Yorumlar / Arşivler / Kategoriler / Meta / Arama
   parçaları kenar çubuğundan temizlenir, yerine anime sitesine uygun parçalar eklenir.
   (Tek seferlik; sonradan elle eklediklerine dokunulmaz.)
------------------------------------------------------------------- */
function anizen_default_sidebar_widgets() {
    return [
        ['anizen_latest',   ['title' => __('Son Bölümler', 'anizen'),     'count' => 6]],
        ['anizen_popular',  ['title' => __('Popüler Animeler', 'anizen'), 'count' => 6]],
        ['anizen_comments', ['title' => __('Son Yorumlar', 'anizen'),     'count' => 6]],
        ['anizen_genres',   ['title' => __('Türler', 'anizen')]],
    ];
}

function anizen_is_core_default_widget($wid) {
    if (preg_match('/^(recent-posts|recent-comments|archives|categories|meta|search)-\d+$/', $wid)) return true;
    if (preg_match('/^block-(\d+)$/', $wid, $m)) {
        $blocks = get_option('widget_block', []);
        $c = isset($blocks[(int) $m[1]]['content']) ? (string) $blocks[(int) $m[1]]['content'] : '';
        return (bool) preg_match('/<!--\s*wp:(latest-posts|latest-comments|archives|categories|search)\b/', $c);
    }
    return false;
}

function anizen_add_widget_instance($base, $settings) {
    $opt = get_option('widget_' . $base, []);
    if (!is_array($opt)) $opt = [];
    $ints = array_filter(array_keys($opt), 'is_int');
    $n = $ints ? max($ints) + 1 : 2;
    $opt[$n] = $settings;
    $opt['_multiwidget'] = 1;
    update_option('widget_' . $base, $opt);
    return $base . '-' . $n;
}

add_action('init', function () {
    if (get_option('anizen_sidebar_seeded')) return;
    $sw = wp_get_sidebars_widgets();
    $cur = isset($sw['sidebar-main']) && is_array($sw['sidebar-main']) ? $sw['sidebar-main'] : [];
    $keep = [];
    foreach ($cur as $wid) if (!anizen_is_core_default_widget($wid)) $keep[] = $wid;
    if (!$keep) {
        foreach (anizen_default_sidebar_widgets() as $d) $keep[] = anizen_add_widget_instance($d[0], $d[1]);
    }
    $sw['sidebar-main'] = $keep;
    wp_set_sidebars_widgets($sw);
    update_option('anizen_sidebar_seeded', 1);
}, 130);

add_action('after_switch_theme', function () {
    delete_option('anizen_sidebar_seeded');
});
