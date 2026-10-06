<?php get_header(); global $wp_query; ?>
<div class="page-head">
    <h1><?php printf(esc_html__('“%s” için sonuçlar', 'anizen'), esc_html(get_search_query())); ?></h1>
    <p class="count"><?php echo esc_html(sprintf(_n('%s anime bulundu', '%s anime bulundu', (int) $wp_query->found_posts, 'anizen'), number_format_i18n((int) $wp_query->found_posts))); ?></p>
</div>
<?php if (have_posts()) : ?>
    <div class="grid"><?php while (have_posts()) { the_post(); get_template_part('template-parts/card', 'anime'); } ?></div>
    <?php the_posts_pagination(['prev_text' => anizen_icon('chevron-left', 16) . ' ' . __('Önceki', 'anizen'), 'next_text' => __('Sonraki', 'anizen') . ' ' . anizen_icon('chevron-right', 16)]); ?>
<?php else : ?>
    <div class="panel empty">
        <p><?php esc_html_e('Aramana uyan anime bulunamadı. Yazımı kontrol et ya da alternatif adıyla dene.', 'anizen'); ?></p>
        <p><a class="btn btn-primary" href="<?php echo esc_url(get_post_type_archive_link('anime')); ?>"><?php esc_html_e('Tüm animelere göz at', 'anizen'); ?></a></p>
    </div>
<?php endif; ?>
<?php get_footer(); ?>
