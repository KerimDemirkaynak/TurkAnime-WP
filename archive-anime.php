<?php get_header(); global $wp_query;
$title = is_tax() ? single_term_title('', false) : __('TÜM ANİMELER', 'anizen');
?>
<section class="panel">
    <div class="panel-ust"><div class="panel-title"><?php echo esc_html($title); ?> <span class="count">(<?php echo esc_html(number_format_i18n((int) $wp_query->found_posts)); ?>)</span></div></div>
    <div class="panel-body">
        <?php if (is_tax() && term_description()) : ?><div class="term-desc"><?php echo wp_kses_post(term_description()); ?></div><?php endif; ?>
        <?php if (anizen_opt('show_filters')) get_template_part('template-parts/filters'); ?>
        <?php if (have_posts()) : ?>
            <div class="grid"><?php while (have_posts()) { the_post(); get_template_part('template-parts/card', 'anime'); } ?></div>
            <?php the_posts_pagination(['mid_size' => 2, 'prev_text' => anizen_icon('chevron-left', 16) . ' ' . __('Önceki', 'anizen'), 'next_text' => __('Sonraki', 'anizen') . ' ' . anizen_icon('chevron-right', 16)]); ?>
        <?php else : ?>
            <p class="muted empty"><?php esc_html_e('Bu süzgeçlere uyan anime yok.', 'anizen'); ?></p>
        <?php endif; ?>
    </div>
</section>
<?php get_footer(); ?>
