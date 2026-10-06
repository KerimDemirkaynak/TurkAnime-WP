<?php get_header();

/* Yuvarlak vitrin */
$circ = [];
if (anizen_opt('hero_enable')) {
    $src = anizen_opt('hero_source'); $n = max(1, (int) anizen_opt('hero_count'));
    $q = anizen_query_animes($src, $n);
    if (!$q->have_posts() && $src === 'featured') $q = anizen_query_animes('updated', $n);
    $circ = $q->posts;
    /* Seçilen kaynak yetmezse (ör. tek anime "vitrinde") kalan daireleri son güncellenenlerle doldur */
    if (count($circ) < $n) {
        $have = wp_list_pluck($circ, 'ID');
        $fill = anizen_query_animes('updated', $n + count($have));
        foreach ($fill->posts as $fp) {
            if (count($circ) >= $n) break;
            if (!in_array($fp->ID, $have, true)) { $circ[] = $fp; $have[] = $fp->ID; }
        }
    }
}
if ($circ) : ?>
<div class="circles" aria-label="<?php esc_attr_e('Öne çıkanlar', 'anizen'); ?>">
    <?php foreach ($circ as $p) : ?>
        <a href="<?php echo esc_url(get_permalink($p)); ?>" title="<?php echo esc_attr(get_the_title($p)); ?>"><img src="<?php echo esc_url(anizen_cover_url($p->ID, 'anizen-poster')); ?>" alt="<?php echo esc_attr(get_the_title($p)); ?>" width="150" height="150" loading="lazy"></a>
    <?php endforeach; ?>
</div>
<?php endif;

if (anizen_opt('home_html')) echo '<div class="home-html">' . anizen_opt('home_html') . '</div>';
?>
<div class="row-ta">
<div class="col-main">

<?php if (anizen_opt('continue_enable')) : ?>
<section class="panel" id="continue" hidden>
    <div class="panel-ust"><div class="panel-title"><?php esc_html_e('İzlemeye Devam Et', 'anizen'); ?></div></div>
    <div class="panel-body"><div class="grid grid-ep" id="continue-list"></div></div>
</section>
<?php endif; ?>

<section class="panel tabs-panel" data-tabs>
    <ul class="panel-tabs">
        <?php if (anizen_opt('sec_latest_enable')) : ?><li class="active"><a href="#" data-tab="ht-1"><?php echo esc_html(anizen_opt('sec_latest_title')); ?></a></li><?php endif; ?>
        <?php if (anizen_opt('sec_new_enable')) : ?><li><a href="#" data-tab="ht-2"><?php echo esc_html(anizen_opt('sec_new_title')); ?></a></li><?php endif; ?>
        <?php if (anizen_opt('sec_popular_enable')) : ?><li><a href="#" data-tab="ht-3"><?php echo esc_html(anizen_opt('sec_popular_title')); ?></a></li><?php endif; ?>
    </ul>
    <?php
    $first = true;
    if (anizen_opt('sec_latest_enable')) :
        $eps = get_posts(['post_type' => 'episode', 'post_status' => 'publish', 'posts_per_page' => (int) anizen_opt('sec_latest_count'), 'no_found_rows' => true]); ?>
    <div class="panel-body tab-pane" id="ht-1">
        <?php if ($eps) : ?><div class="cols-2"><?php foreach ($eps as $e) get_template_part('template-parts/card', 'episode', ['id' => $e->ID]); ?></div>
        <p class="more"><a href="<?php echo esc_url(get_post_type_archive_link('episode')); ?>"><?php esc_html_e('Tüm bölümler', 'anizen'); ?> &raquo;</a></p>
        <?php else : ?><p class="muted"><?php esc_html_e('Henüz bölüm eklenmemiş.', 'anizen'); ?></p><?php endif; ?>
    </div>
    <?php $first = false; endif;
    foreach (['new' => ['ht-2', 'latest'], 'popular' => ['ht-3', 'popular']] as $key => $cfg) :
        if (!anizen_opt('sec_' . $key . '_enable')) continue;
        $q = anizen_query_animes($cfg[1], (int) anizen_opt('sec_' . $key . '_count')); ?>
    <div class="panel-body tab-pane" id="<?php echo esc_attr($cfg[0]); ?>"<?php echo $first ? '' : ' hidden'; ?>>
        <?php if ($q->have_posts()) : ?><div class="grid"><?php foreach ($q->posts as $p) get_template_part('template-parts/card', 'anime', ['id' => $p->ID]); ?></div>
        <p class="more"><a href="<?php echo esc_url(get_post_type_archive_link('anime')); ?>"><?php esc_html_e('Tüm animeler', 'anizen'); ?> &raquo;</a></p>
        <?php else : ?><p class="muted"><?php esc_html_e('Henüz anime eklenmemiş.', 'anizen'); ?></p><?php endif; ?>
    </div>
    <?php $first = false; endforeach; ?>
</section>

<?php if (anizen_opt('sec_day_enable')) :
    $day = get_transient('anizen_day_anime');
    if (!$day || get_post_status($day) !== 'publish') {
        $r = get_posts(['post_type' => 'anime', 'post_status' => 'publish', 'posts_per_page' => 1, 'orderby' => 'rand', 'fields' => 'ids', 'no_found_rows' => true]);
        $day = $r ? (int) $r[0] : 0;
        if ($day) set_transient('anizen_day_anime', $day, DAY_IN_SECONDS);
    }
    $top_views = anizen_query_animes('popular', 6); $top_score = new WP_Query(['post_type' => 'anime', 'post_status' => 'publish', 'posts_per_page' => 6, 'no_found_rows' => true,
        'meta_query' => ['relation' => 'AND', 'srt' => ['key' => '_anime_score', 'type' => 'DECIMAL(4,1)', 'compare' => 'EXISTS']], 'orderby' => ['srt' => 'DESC']]);
    if ($day) :
        $tt = get_post_meta($day, '_anime_type', true); $types = anizen_types(); ?>
<section class="panel tabs-panel" data-tabs>
    <ul class="panel-tabs">
        <li class="active"><a href="#" data-tab="dt-1"><?php esc_html_e('Günün Animesi', 'anizen'); ?></a></li>
        <li><a href="#" data-tab="dt-2"><?php esc_html_e('En Çok İzlenen', 'anizen'); ?></a></li>
        <li><a href="#" data-tab="dt-3"><?php esc_html_e('En Yüksek Puan', 'anizen'); ?></a></li>
    </ul>
    <div class="panel-body tab-pane day-anime" id="dt-1">
        <a class="thumbnail" href="<?php echo esc_url(get_permalink($day)); ?>"><img src="<?php echo esc_url(anizen_cover_url($day)); ?>" alt="" width="180" height="256" loading="lazy"></a>
        <div>
            <h3><a href="<?php echo esc_url(get_permalink($day)); ?>"><?php echo esc_html(get_the_title($day)); ?></a></h3>
            <p class="muted"><?php echo esc_html(implode(' · ', array_filter([anizen_alt_name($day) !== get_the_title($day) ? anizen_alt_name($day) : '', isset($types[$tt]) ? $types[$tt] : '', get_post_meta($day, '_anime_year', true), anizen_status_label(get_post_meta($day, '_anime_status', true))]))); ?></p>
            <p><?php echo esc_html(anizen_synopsis($day, 60)); ?></p>
            <p class="muted"><?php echo anizen_icon('eye', 14); ?> <?php echo esc_html(sprintf(__('%s izlenme', 'anizen'), number_format_i18n(anizen_views($day)))); ?></p>
        </div>
    </div>
    <div class="panel-body tab-pane" id="dt-2" hidden><ul class="mini-list mini-2"><?php foreach ($top_views->posts as $p) anizen_mini_item($p->ID); ?></ul></div>
    <div class="panel-body tab-pane" id="dt-3" hidden><ul class="mini-list mini-2"><?php foreach ($top_score->posts as $p) anizen_mini_item($p->ID); ?></ul></div>
</section>
<?php endif; endif; ?>

<?php if (!get_posts(['post_type' => 'anime', 'posts_per_page' => 1, 'fields' => 'ids']) && current_user_can('edit_posts')) : ?>
<div class="panel"><div class="panel-body empty"><p><?php esc_html_e('Henüz anime eklenmemiş.', 'anizen'); ?></p>
<a class="btn btn-primary" href="<?php echo esc_url(admin_url('post-new.php?post_type=anime')); ?>"><?php esc_html_e('İlk animeyi ekle', 'anizen'); ?></a></div></div>
<?php endif; ?>

</div>
<aside class="col-side">
    <?php get_template_part('template-parts/board'); get_sidebar(); ?>
</aside>
</div>
<?php get_footer();
