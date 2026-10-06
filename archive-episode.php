<?php get_header(); ?>
<section class="panel">
    <div class="panel-ust"><div class="panel-title"><?php esc_html_e('YENİ EKLENEN BÖLÜMLER', 'anizen'); ?></div></div>
    <div class="panel-body">
    <?php if (have_posts()) : ?>
        <div class="cols-2"><?php while (have_posts()) { the_post(); get_template_part('template-parts/card', 'episode'); } ?></div>
        <?php the_posts_pagination(['mid_size' => 2, 'prev_text' => anizen_icon('chevron-left', 16) . ' ' . __('Önceki', 'anizen'), 'next_text' => __('Sonraki', 'anizen') . ' ' . anizen_icon('chevron-right', 16)]); ?>
    <?php else : ?><p class="muted empty"><?php esc_html_e('Henüz bölüm eklenmemiş.', 'anizen'); ?></p><?php endif; ?>
    </div>
</section>
<?php get_footer(); ?>
