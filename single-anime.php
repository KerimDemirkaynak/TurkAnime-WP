<?php get_header();
while (have_posts()) : the_post();
$anime_id = get_the_ID(); $g = function ($k) use ($anime_id) { return get_post_meta($anime_id, $k, true); };
$eps = anizen_episodes($anime_id, 'ASC'); $count = count($eps);
$status = anizen_status_label($g('_anime_status')); $types = anizen_types(); $type = $g('_anime_type');
$total = (int) $g('_anime_total_eps'); $trailer = $g('_anime_trailer'); $first = $eps ? $eps[0] : null; $last = $eps ? end($eps) : null;
$genres = get_the_terms($anime_id, 'genre'); $studios = get_the_terms($anime_id, 'studio'); $fansub = anizen_fansub($anime_id);
$score = $g('_anime_score'); $likes = (int) $g('_likes');
$rel = [];
if ($genres && !is_wp_error($genres)) {
    $rel = get_posts(['post_type' => 'anime', 'post_status' => 'publish', 'posts_per_page' => 6, 'post__not_in' => [$anime_id], 'orderby' => 'rand', 'no_found_rows' => true,
        'tax_query' => [['taxonomy' => 'genre', 'field' => 'term_id', 'terms' => wp_list_pluck($genres, 'term_id')]]]);
}
$rows = [];
if (isset($types[$type])) $rows[__('Kategori', 'anizen')] = esc_html($types[$type]);
$rows[__('Anime Adı', 'anizen')] = esc_html(get_the_title());
if ($g('_anime_alt_title')) $rows[__('Diğer Adları', 'anizen')] = esc_html($g('_anime_alt_title'));
if ($genres && !is_wp_error($genres)) { $h = ''; foreach ($genres as $t) $h .= '<a class="tag-chip" href="' . esc_url(get_term_link($t)) . '">' . anizen_icon('tag', 14) . ' ' . esc_html($t->name) . '</a> '; $rows[__('Anime Türü', 'anizen')] = $h; }
$rows[__('Bölüm Sayısı', 'anizen')] = esc_html($count . ' / ' . $total);
if ($g('_anime_year')) $rows[__('Yıl', 'anizen')] = esc_html($g('_anime_year'));
if ($status) $rows[__('Yayın Durumu', 'anizen')] = esc_html($status);
if ($g('_anime_duration')) $rows[__('Bölüm Süresi', 'anizen')] = esc_html($g('_anime_duration') . ' ' . __('dk', 'anizen'));
if ($g('_anime_lang')) $rows[__('Dil', 'anizen')] = esc_html($g('_anime_lang'));
if ($studios && !is_wp_error($studios)) { $h = ''; foreach ($studios as $s) $h .= '<a href="' . esc_url(get_term_link($s)) . '">' . esc_html($s->name) . '</a>, '; $rows[__('Stüdyo', 'anizen')] = rtrim($h, ', '); }
if ($fansub) $rows[__('Fansub', 'anizen')] = '<span class="tag-chip">' . anizen_icon('heart', 14) . ' ' . esc_html($fansub) . '</span>';
$rows[__('Beğeniler', 'anizen')] = '<button type="button" class="btn-blue post-like" data-id="' . (int) $anime_id . '">' . anizen_icon('thumbs-up', 16) . ' ' . esc_html__('Beğen', 'anizen') . ' <span class="n">' . $likes . '</span></button> <button type="button" class="btn-dark follow-btn" data-id="' . (int) $anime_id . '" aria-pressed="false">' . anizen_icon('heart', 15) . ' <span>' . esc_html__('Takip Et', 'anizen') . '</span></button>';
?>
<div class="row-ta">
<div class="col-main">
    <section class="panel" id="detay">
        <div class="panel-ust"><div class="panel-title"><?php the_title(); ?></div></div>
        <div class="panel-body a-detail">
            <div class="a-left">
                <div class="a-poster-wrap">
                    <img class="a-poster" src="<?php echo esc_url(anizen_cover_url($anime_id, 'large')); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" width="330" height="470" fetchpriority="high">
                    <?php if ($score !== '') : ?><span class="a-score"><?php echo esc_html(number_format((float) $score, 2, ',', '')); ?></span><?php endif; ?>
                </div>
                <ul class="a-menu">
                    <?php if ($first) : ?><li><a href="<?php echo esc_url(get_permalink($first)); ?>" data-first-ep="<?php echo (int) $first->ID; ?>" data-anime="<?php echo (int) $anime_id; ?>"><?php echo anizen_icon('play', 20); ?><span><?php echo esc_html(is_numeric(anizen_ep_number($first->ID)) ? sprintf(__('%s. Bölümü İzle', 'anizen'), anizen_ep_short($first->ID)) : sprintf(__('%s İzle', 'anizen'), anizen_ep_short($first->ID))); ?></span></a></li><?php endif; ?>
                    <?php if ($last && $first && $last->ID !== $first->ID) : ?><li><a href="<?php echo esc_url(get_permalink($last)); ?>"><?php echo anizen_icon('skip-next', 20); ?><span><?php echo esc_html(sprintf(__('Son Bölüm (%s)', 'anizen'), anizen_ep_short($last->ID))); ?></span></a></li><?php endif; ?>
                    <?php if ($trailer) : ?><li><a href="#fragman"><?php echo anizen_icon('tv', 20); ?><span><?php esc_html_e('Fragman', 'anizen'); ?></span></a></li><?php endif; ?>
                    <?php if ($rel) : ?><li><a href="#benzer"><?php echo anizen_icon('layers', 20); ?><span><?php esc_html_e('Benzer Animeler', 'anizen'); ?></span></a></li><?php endif; ?>
                    <li><a href="#comments"><?php echo anizen_icon('message', 20); ?><span><?php esc_html_e('Yorumlar', 'anizen'); ?></span></a></li>
                    <?php if (current_user_can('edit_post', $anime_id)) : ?><li><a href="<?php echo esc_url(get_edit_post_link($anime_id)); ?>"><?php echo anizen_icon('link', 20); ?><span><?php esc_html_e('Düzenle', 'anizen'); ?></span></a></li><?php endif; ?>
                </ul>
            </div>
            <div class="a-right">
                <table class="ainfo">
                    <?php foreach ($rows as $k => $v) echo '<tr><th>' . esc_html($k) . '</th><td class="sep">:</td><td>' . $v . '</td></tr>'; ?>
                </table>
                <h2 class="ozet-h"><?php esc_html_e('Özet;', 'anizen'); ?></h2>
                <div class="entry synopsis" id="synopsis"><?php the_content(); ?></div>
            </div>
        </div>
    </section>

    <?php if ($trailer) : ?>
    <section class="panel" id="fragman">
        <div class="panel-ust"><div class="panel-title"><?php esc_html_e('Fragman', 'anizen'); ?></div></div>
        <div class="panel-body"><div class="trailer"><iframe src="https://www.youtube-nocookie.com/embed/<?php echo esc_attr($trailer); ?>" title="<?php esc_attr_e('Fragman', 'anizen'); ?>" loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe></div></div>
    </section>
    <?php endif; ?>

    <?php if ($rel) : ?>
    <section class="panel" id="benzer">
        <div class="panel-ust"><div class="panel-title"><?php esc_html_e('Benzer Animeler', 'anizen'); ?></div></div>
        <div class="panel-body"><div class="grid"><?php foreach ($rel as $p) get_template_part('template-parts/card', 'anime', ['id' => $p->ID]); ?></div></div>
    </section>
    <?php endif; ?>

    <?php if (comments_open() || get_comments_number()) comments_template(); ?>
</div>
<aside class="col-side">
    <?php get_template_part('template-parts/board'); ?>
    <?php get_template_part('template-parts/side-episodes', null, ['anime' => $anime_id, 'current' => 0]); ?>
    <?php get_sidebar(); ?>
</aside>
</div>
<?php endwhile; get_footer();
