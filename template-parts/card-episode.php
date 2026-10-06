<?php
if (!defined('ABSPATH')) exit;
$ep_id = isset($args['id']) ? (int) $args['id'] : get_the_ID();
$aid = (int) get_post_meta($ep_id, '_episode_anime_id', true);
if (!$aid || get_post_status($aid) !== 'publish') return;
$atitle = get_the_title($aid);
$fansub = anizen_fansub($aid, $ep_id);
$likes = (int) get_post_meta($ep_id, '_likes', true);
$full = $atitle . ' ' . anizen_ep_label($ep_id);
?>
<div class="panel panel-visible ep-panel">
    <div class="panel-ust-ic"><div class="panel-title"><a href="<?php echo esc_url(get_permalink($ep_id)); ?>" title="<?php echo esc_attr($full); ?>"><?php echo esc_html($full); ?><?php if (anizen_is_final($ep_id)) echo ' Final'; ?></a></div></div>
    <div class="panel-body ep-body">
        <a class="thumbnail" href="<?php echo esc_url(get_permalink($ep_id)); ?>"><img src="<?php echo esc_url(anizen_cover_url($aid)); ?>" alt="<?php echo esc_attr($full); ?>" width="135" height="192" loading="lazy" decoding="async"></a>
        <div class="ep-info">
            <div class="ep-top"><span class="alt-name"><?php echo esc_html(anizen_alt_name($aid)); ?></span><?php echo anizen_flag_tr(); ?></div>
            <span class="bold"><?php echo esc_html(anizen_opt('fansub_label')); ?></span>
            <?php if ($fansub) : ?><span class="fansub"><?php echo anizen_icon('chevron-right', 13); ?> <?php echo esc_html($fansub); ?></span><?php endif; ?>
            <span class="ago"><?php echo anizen_icon('clock', 14); ?> <?php echo esc_html(sprintf(__('%s önce eklendi.', 'anizen'), human_time_diff(get_post_time('U', true, $ep_id), time()))); ?></span>
        </div>
        <div class="ep-actions">
            <button type="button" class="act post-like" data-id="<?php echo (int) $ep_id; ?>"><?php echo anizen_icon('thumbs-up', 18); ?> <b><?php esc_html_e('Beğen', 'anizen'); ?></b> <span class="n"><?php echo $likes; ?></span></button>
            <span class="act act-box" title="<?php esc_attr_e('İzlenme', 'anizen'); ?>"><?php echo number_format_i18n(anizen_views($ep_id)); ?></span>
            <button type="button" class="act mark-watched" data-id="<?php echo (int) $ep_id; ?>" title="<?php esc_attr_e('İzledim olarak işaretle', 'anizen'); ?>" aria-pressed="false"><?php echo anizen_icon('eye-off', 20); ?></button>
        </div>
    </div>
</div>
