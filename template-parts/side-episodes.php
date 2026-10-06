<?php
if (!defined('ABSPATH')) return;
$aid = isset($args['anime']) ? (int) $args['anime'] : 0;
$cur = isset($args['current']) ? (int) $args['current'] : 0;
if (!$aid) return;
$eps = anizen_episodes($aid, 'ASC'); /* her iki sayfada da 1,2,3… sırası */
$genres = get_the_terms($aid, 'genre');
$year = get_post_meta($aid, '_anime_year', true);
$same_genre = $same_year = [];
if ($genres && !is_wp_error($genres)) {
    $same_genre = get_posts(['post_type' => 'anime', 'post_status' => 'publish', 'posts_per_page' => 8, 'post__not_in' => [$aid], 'orderby' => 'rand', 'no_found_rows' => true,
        'tax_query' => [['taxonomy' => 'genre', 'field' => 'term_id', 'terms' => wp_list_pluck($genres, 'term_id')]]]);
}
if ($year) {
    $same_year = get_posts(['post_type' => 'anime', 'post_status' => 'publish', 'posts_per_page' => 8, 'post__not_in' => [$aid], 'orderby' => 'rand', 'no_found_rows' => true,
        'meta_query' => [['key' => '_anime_year', 'value' => (string) $year]]]);
}
?>
<section class="panel side-eps" id="episodes" data-tabs>
    <ul class="panel-tabs">
        <li class="active"><a href="#" data-tab="se-1"><?php esc_html_e('Bölümler', 'anizen'); ?></a></li>
        <?php if ($same_genre) : ?><li><a href="#" data-tab="se-2"><?php esc_html_e('Türüne Göre', 'anizen'); ?></a></li><?php endif; ?>
        <?php if ($same_year) : ?><li><a href="#" data-tab="se-3"><?php esc_html_e('Yılına Göre', 'anizen'); ?></a></li><?php endif; ?>
    </ul>
    <div class="panel-body tab-pane" id="se-1">
        <?php if (count($eps) > 8) : ?>
        <div class="ep-tools">
            <input type="search" id="ep-filter" placeholder="<?php esc_attr_e('Bölüm no ara…', 'anizen'); ?>" inputmode="numeric" aria-label="<?php esc_attr_e('Bölüm ara', 'anizen'); ?>">
            <button type="button" class="btn-dark" id="ep-sort" aria-pressed="false"><?php esc_html_e('Ters sırala', 'anizen'); ?></button>
        </div>
        <?php endif; ?>
        <?php if ($eps) : ?>
        <ul class="ep-list" id="ep-list">
            <?php foreach ($eps as $e) : $n = anizen_ep_number($e->ID); ?>
            <li data-n="<?php echo esc_attr($n); ?>" data-id="<?php echo (int) $e->ID; ?>"><a class="ep-item<?php echo $e->ID === $cur ? ' is-current' : ''; ?>" href="<?php echo esc_url(get_permalink($e)); ?>">
                <span class="ep-t"><?php echo esc_html(anizen_ep_label($e->ID)); ?></span>
                <?php if (anizen_is_final($e->ID)) : ?><span class="label label-final">Final</span><?php endif; ?>
                <span class="ep-watched" hidden><?php echo anizen_icon('check', 14); ?></span>
            </a></li>
            <?php endforeach; ?>
        </ul>
        <?php if (current_user_can('manage_options')) : $atn = anizen_norm_title(get_the_title($aid)); ?>
        <details class="muted" style="font-size:12px;margin-top:8px"><summary>[Yönetici teşhisi] Bu sayfa: anime ID <?php echo (int) $aid; ?> · <?php echo count($eps); ?> bölüm listelendi</summary>
            <?php foreach (array_slice($eps, 0, 40) as $e) : $ok = strpos(anizen_norm_title($e->post_title) . ' ', $atn . ' ') === 0; ?>
            <div<?php echo $ok ? '' : ' style="color:#d63638"'; ?>>#<?php echo (int) $e->ID; ?> · bağlı anime ID: <?php echo (int) get_post_meta($e->ID, '_episode_anime_id', true); ?> · <?php echo esc_html($e->post_title); ?><?php echo $ok ? '' : ' ⚠ başlık bu animeyle başlamıyor'; ?></div>
            <?php endforeach; ?>
        </details>
        <?php endif; ?>
        <?php else : ?><p class="muted"><?php esc_html_e('Henüz bölüm eklenmemiş.', 'anizen'); ?></p>
        <?php if (current_user_can('manage_options')) : global $wpdb; $dbg = $wpdb->get_results($wpdb->prepare("SELECT p.ID, p.post_status FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_episode_anime_id' AND m.meta_value = %s WHERE p.post_type = 'episode'", (string) $aid)); ?>
        <p class="muted" style="font-size:12px">[Yönetici teşhisi] Anime ID: <?php echo (int) $aid; ?> · Bu animeye bağlı bölüm kaydı: <?php echo count($dbg); ?><?php foreach ($dbg as $r) echo ' · #' . (int) $r->ID . ' (' . esc_html($r->post_status) . ')'; ?></p>
        <?php endif; endif; ?>
    </div>
    <?php foreach ([['se-2', $same_genre], ['se-3', $same_year]] as $pane) : if (!$pane[1]) continue; ?>
    <div class="panel-body tab-pane" id="<?php echo esc_attr($pane[0]); ?>" hidden><ul class="mini-list"><?php foreach ($pane[1] as $p) anizen_mini_item($p->ID); ?></ul></div>
    <?php endforeach; ?>
</section>
