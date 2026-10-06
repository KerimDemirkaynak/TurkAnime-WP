<?php get_header(); ?>
<div class="page-head"><h1><?php echo is_home() && !is_front_page() ? esc_html(single_post_title('', false)) : esc_html__('Yazılar', 'anizen'); ?></h1></div>
<?php if (have_posts()) : ?>
    <div class="post-list">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('panel post-item'); ?>>
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <p class="muted"><?php echo esc_html(get_the_date()); ?></p>
            <div class="entry"><?php the_excerpt(); ?></div>
        </article>
    <?php endwhile; ?>
    </div>
    <?php the_posts_pagination(['prev_text' => anizen_icon('chevron-left', 16) . ' ' . __('Önceki', 'anizen'), 'next_text' => __('Sonraki', 'anizen') . ' ' . anizen_icon('chevron-right', 16)]); ?>
<?php else : ?>
    <div class="panel empty"><?php esc_html_e('Henüz içerik yok.', 'anizen'); ?></div>
<?php endif; ?>
<?php get_footer(); ?>
