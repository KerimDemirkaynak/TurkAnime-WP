<?php
if (!defined('ABSPATH')) exit;
$anime_id = isset($args['id']) ? (int) $args['id'] : get_the_ID();
$title = get_the_title($anime_id);
$eps = anizen_episode_count($anime_id);
$score = get_post_meta($anime_id, '_anime_score', true);
$status = anizen_normalize_status(get_post_meta($anime_id, '_anime_status', true));
$type = get_post_meta($anime_id, '_anime_type', true);
$types = anizen_types();
$year = get_post_meta($anime_id, '_anime_year', true);
$meta = array_filter([$year, isset($types[$type]) ? $types[$type] : '']);
?>
<article class="card">
    <a class="card-poster" href="<?php echo esc_url(get_permalink($anime_id)); ?>" aria-label="<?php echo esc_attr($title); ?>">
        <img src="<?php echo esc_url(anizen_cover_url($anime_id)); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy" decoding="async" width="360" height="520">
        <?php if ($score !== '') : ?><span class="card-score"><?php echo anizen_icon('star', 12); ?><?php echo esc_html($score); ?></span><?php endif; ?>
        <?php if ($status === 'ongoing') : ?><span class="card-flag"><?php esc_html_e('Devam', 'anizen'); ?></span><?php endif; ?>
        <span class="card-eps"><?php echo esc_html(sprintf(_n('%s Bölüm', '%s Bölüm', $eps, 'anizen'), number_format_i18n($eps))); ?></span>
        <span class="card-play"><?php echo anizen_icon('play', 30); ?></span>
    </a>
    <h3 class="card-title"><a href="<?php echo esc_url(get_permalink($anime_id)); ?>"><?php echo esc_html($title); ?></a></h3>
    <?php if ($meta) : ?><p class="card-meta"><?php echo esc_html(implode(' · ', $meta)); ?></p><?php endif; ?>
</article>
