<?php
if (!defined('ABSPATH') || !anizen_opt('board_enable')) return;
$board_posts = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'numberposts' => max(1, (int) anizen_opt('board_count')), 'ignore_sticky_posts' => true, 'no_found_rows' => true]);
?>
<section class="panel board">
    <div class="panel-ust"><div class="panel-title"><?php echo esc_html(anizen_opt('board_title')); ?></div></div>
    <div class="panel-body board-list">
        <?php if ($board_posts) : foreach ($board_posts as $p) : ?>
            <a class="board-item" href="<?php echo esc_url(get_permalink($p)); ?>">
                <strong><?php echo esc_html(get_the_title($p)); ?></strong>
                <span class="board-by"><?php echo esc_html(get_the_author_meta('display_name', $p->post_author)); ?></span>
                <time datetime="<?php echo esc_attr(get_the_date('c', $p)); ?>"><?php echo esc_html(get_the_date('', $p)); ?></time>
            </a>
        <?php endforeach; else : ?>
            <p class="muted"><?php esc_html_e('Henüz duyuru yok.', 'anizen'); ?><?php if (current_user_can('publish_posts')) : ?> <a href="<?php echo esc_url(admin_url('post-new.php')); ?>"><?php esc_html_e('Yazı ekle', 'anizen'); ?></a><?php endif; ?></p>
        <?php endif; ?>
    </div>
</section>
