<?php get_header(); ?>
<section class="panel notfound">
    <p class="nf-code">404</p>
    <h1><?php esc_html_e('Aradığın sayfa bulunamadı', 'anizen'); ?></h1>
    <p class="muted"><?php esc_html_e('Bağlantı değişmiş ya da içerik kaldırılmış olabilir. Aşağıdan aramayı dene.', 'anizen'); ?></p>
    <form class="search search-inline" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
        <input type="search" name="s" placeholder="<?php esc_attr_e('Anime ara…', 'anizen'); ?>" aria-label="<?php esc_attr_e('Anime ara', 'anizen'); ?>">
        <button type="submit" class="btn btn-primary"><?php esc_html_e('Ara', 'anizen'); ?></button>
    </form>
    <p><a class="btn btn-ghost" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Ana sayfaya dön', 'anizen'); ?></a></p>
</section>
<?php $pop = anizen_query_animes('popular', 6); if ($pop->have_posts()) : ?>
<section class="section">
    <?php anizen_section_head(__('Popüler Animeler', 'anizen')); ?>
    <div class="grid"><?php foreach ($pop->posts as $p) get_template_part('template-parts/card', 'anime', ['id' => $p->ID]); ?></div>
</section>
<?php endif; ?>
<?php get_footer(); ?>
