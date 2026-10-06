<?php get_header();
while (have_posts()) : the_post();
$ep_id = get_the_ID(); $aid = (int) get_post_meta($ep_id, '_episode_anime_id', true);
$num = anizen_ep_number($ep_id); $label = anizen_ep_label($ep_id); $sources = anizen_sources_for_player($ep_id);
$all = $aid ? anizen_episodes($aid, 'ASC', 'ids') : []; $pos = array_search($ep_id, $all, true);
$prev = ($pos !== false && $pos > 0) ? $all[$pos - 1] : 0; $next = ($pos !== false && isset($all[$pos + 1])) ? $all[$pos + 1] : 0;
$atitle = $aid ? get_the_title($aid) : get_the_title();
$fansub = $aid ? anizen_fansub($aid, $ep_id) : '';
$likes = (int) get_post_meta($ep_id, '_likes', true);
$js = [];
foreach ($sources as $s) $js[] = ['name' => $s['name'], 'kind' => $s['kind'], 'url' => $s['url']];
$cfg = ['id' => $ep_id, 'anime' => $aid, 'num' => $num, 'label' => $label, 'title' => $atitle, 'sources' => $js, 'next' => $next ? get_permalink($next) : '', 'prev' => $prev ? get_permalink($prev) : '',
    'cover' => $aid ? anizen_cover_url($aid, 'thumbnail') : '', 'permalink' => get_permalink($ep_id),
    'autoload' => (bool) anizen_opt('player_autoload'), 'autonext' => (bool) anizen_opt('player_autonext'), 'remember' => (bool) anizen_opt('player_remember'),
    'cinema' => (bool) anizen_opt('player_cinema'), 'report' => (bool) anizen_opt('player_report')];
$notice = str_replace('{fansub}', esc_html($fansub), (string) anizen_opt('ep_notice_html'));
$info = (string) anizen_opt('ep_info_html');
$status = $aid ? anizen_status_label(get_post_meta($aid, '_anime_status', true)) : '';
$year = $aid ? get_post_meta($aid, '_anime_year', true) : '';
?>
<div class="row-ta">
<div class="col-main">
    <section class="panel">
        <div class="panel-ust ep-bar">
            <div class="panel-title"><?php echo anizen_icon('chevron-right', 16); ?><?php if ($aid) : ?> <a href="<?php echo esc_url(get_permalink($aid)); ?>"><?php echo esc_html($atitle); ?></a> <span class="slash">/</span><?php endif; ?> <h1><?php echo esc_html($atitle . ' ' . $label); ?></h1></div>
            <?php if ($sources) : ?>
            <div class="ep-nav" role="group" aria-label="<?php esc_attr_e('Bölüm kısayolları', 'anizen'); ?>">
                <?php if ($prev) : ?><a href="<?php echo esc_url(get_permalink($prev)); ?>" rel="prev" title="<?php esc_attr_e('Önceki bölüm', 'anizen'); ?>" aria-label="<?php esc_attr_e('Önceki bölüm', 'anizen'); ?>"><?php echo anizen_icon('chevron-left', 18); ?><?php echo anizen_icon('chevron-left', 18); ?></a>
                <?php else : ?><span class="is-off" aria-hidden="true"><?php echo anizen_icon('chevron-left', 18); ?><?php echo anizen_icon('chevron-left', 18); ?></span><?php endif; ?>
                <button type="button" id="cinema-btn" aria-pressed="false" title="<?php esc_attr_e('Sinema modu', 'anizen'); ?>" aria-label="<?php esc_attr_e('Sinema modu', 'anizen'); ?>"><?php echo anizen_icon('bulb', 20); ?></button>
                <?php if ($next) : ?><a href="<?php echo esc_url(get_permalink($next)); ?>" rel="next" title="<?php esc_attr_e('Sonraki bölüm', 'anizen'); ?>" aria-label="<?php esc_attr_e('Sonraki bölüm', 'anizen'); ?>"><?php echo anizen_icon('chevron-right', 18); ?><?php echo anizen_icon('chevron-right', 18); ?></a>
                <?php else : ?><span class="is-off" aria-hidden="true"><?php echo anizen_icon('chevron-right', 18); ?><?php echo anizen_icon('chevron-right', 18); ?></span><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="panel-body ep-main">
            <?php if ($fansub) : ?>
            <div class="fansub-row"><?php foreach (array_filter(array_map('trim', explode(',', $fansub))) as $f) echo '<span class="btn-fansub">' . anizen_icon('heart', 15) . ' ' . esc_html($f) . '</span>'; ?></div>
            <?php endif; ?>

            <section class="player-wrap" id="player">
                <?php if ($sources) : ?>
                <div class="player-stage" id="player-stage" style="--poster:url('<?php echo esc_url($aid ? (anizen_banner_url($aid) ?: anizen_cover_url($aid, 'large')) : ''); ?>')">
                    <button type="button" class="player-start" id="player-start" aria-label="<?php esc_attr_e('Oynat', 'anizen'); ?>"><?php echo anizen_icon('play', 48); ?></button>
                </div>
                <div class="server-row">
                    <span class="server-label"><?php echo anizen_icon('server', 18); ?> <?php esc_html_e('SUNUCU', 'anizen'); ?></span>
                    <div class="servers" role="tablist" aria-label="<?php esc_attr_e('Video kaynakları', 'anizen'); ?>">
                        <?php foreach ($sources as $i => $s) : ?><button type="button" role="tab" class="server<?php echo $i === 0 ? ' is-active' : ''; ?>" data-i="<?php echo (int) $i; ?>" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"><?php echo esc_html($s['name']); ?></button><?php endforeach; ?>
                    </div>
                </div>
                <div class="tool-row">
                    <button type="button" class="tool" id="fs-btn"><?php echo anizen_icon('maximize', 18); ?><span><?php esc_html_e('Tam ekran', 'anizen'); ?></span></button>
                    <?php if (anizen_opt('player_report')) : ?><button type="button" class="tool" id="report-btn"><?php echo anizen_icon('flag', 18); ?><span><?php esc_html_e('Hata bildir', 'anizen'); ?></span></button><?php endif; ?>
                </div>
                <div class="report-box" id="report-box" hidden>
                    <strong><?php esc_html_e('Sorun nedir?', 'anizen'); ?></strong>
                    <?php foreach (['Video açılmıyor', 'Yanlış bölüm', 'Altyazı / ses sorunu', 'Kalite çok düşük', 'Diğer'] as $r) echo '<button type="button" class="btn-dark" data-reason="' . esc_attr($r) . '">' . esc_html($r) . '</button>'; ?>
                </div>
                <?php if (anizen_opt('player_notice')) : ?><p class="player-notice"><?php echo wp_kses_post(anizen_opt('player_notice')); ?></p><?php endif; ?>
                <?php foreach ($sources as $i => $s) if ($s['kind'] === 'html') echo '<template id="src-html-' . (int) $i . '">' . $s['html'] . '</template>'; ?>
                <?php else : ?>
                <div class="player-empty"><?php echo anizen_icon('tv', 40); ?><p><?php esc_html_e('Bu bölüm için henüz video kaynağı eklenmemiş.', 'anizen'); ?></p>
                    <?php if (current_user_can('edit_post', $ep_id)) : ?><a class="btn btn-primary" href="<?php echo esc_url(get_edit_post_link($ep_id)); ?>"><?php esc_html_e('Kaynak ekle', 'anizen'); ?></a><?php endif; ?></div>
                <?php endif; ?>
                <script type="application/json" id="player-config"><?php echo wp_json_encode($cfg, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?></script>
            </section>

            <div class="ep-info-row">
                <?php if ($aid) : ?>
                <section class="panel mini-panel">
                    <div class="panel-ust"><div class="panel-title"><a href="<?php echo esc_url(get_permalink($aid)); ?>"><?php echo esc_html($fansub ?: $atitle); ?></a></div></div>
                    <table class="ainfo small">
                        <tr><th><?php esc_html_e('Bölüm', 'anizen'); ?></th><td><?php echo (int) anizen_episode_count($aid); ?></td></tr>
                        <?php if ($year) : ?><tr><th><?php esc_html_e('Yıl', 'anizen'); ?></th><td><?php echo esc_html($year); ?></td></tr><?php endif; ?>
                        <?php if ($status) : ?><tr><th><?php esc_html_e('Durum', 'anizen'); ?></th><td><?php echo esc_html($status); ?></td></tr><?php endif; ?>
                        <tr><th><?php esc_html_e('İzlenme', 'anizen'); ?></th><td><?php echo esc_html(number_format_i18n(anizen_views($aid))); ?></td></tr>
                    </table>
                </section>
                <?php endif; ?>
                <?php if ($info) : ?><div class="info-box"><?php echo wp_kses_post($info); ?></div><?php endif; ?>
            </div>
            <?php if (trim($notice) !== '') : ?><div class="notice-box"><?php echo wp_kses_post($notice); ?></div><?php endif; ?>

            <div class="ep-foot">
                <span class="ep-date"><strong><?php esc_html_e('Tarih', 'anizen'); ?></strong> : <?php echo esc_html(get_the_date('j F Y, H:i:s')); ?></span>
                <div class="ep-foot-btns">
                    <button type="button" class="act post-like" data-id="<?php echo (int) $ep_id; ?>"><?php echo anizen_icon('thumbs-up', 16); ?> <b><?php esc_html_e('Beğen', 'anizen'); ?></b> <span class="n"><?php echo $likes; ?></span></button>
                    <span class="act act-box" title="<?php esc_attr_e('İzlenme', 'anizen'); ?>"><?php echo anizen_icon('eye', 16); ?> <?php echo esc_html(number_format_i18n(anizen_views($ep_id))); ?></span>
                    <?php if ($prev) : ?><a class="act" href="<?php echo esc_url(get_permalink($prev)); ?>"><?php echo anizen_icon('chevron-left', 15); ?><?php echo anizen_icon('chevron-left', 15); ?> <b><?php esc_html_e('Önceki Bölüm', 'anizen'); ?></b></a><?php endif; ?>
                    <?php if ($next) : ?><a class="act" href="<?php echo esc_url(get_permalink($next)); ?>"><b><?php esc_html_e('Sonraki Bölüm', 'anizen'); ?></b> <?php echo anizen_icon('chevron-right', 15); ?><?php echo anizen_icon('chevron-right', 15); ?></a><?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php if (comments_open() || get_comments_number()) : $collapse = (bool) anizen_opt('comments_collapsed'); ?>
    <section class="panel" id="yorumlar">
        <div class="panel-ust"><div class="panel-title"><?php echo anizen_icon('message', 16); ?> <?php esc_html_e('YORUMLAR', 'anizen'); ?></div></div>
        <div class="panel-body">
            <?php if ($collapse) : ?>
                <button type="button" class="btn-red-wide" id="show-comments"><?php esc_html_e('Yorumları Görüntüle', 'anizen'); ?> (<?php echo (int) get_comments_number(); ?>)</button>
                <div id="comments-wrap" hidden><?php comments_template(); ?></div>
            <?php else : comments_template(); endif; ?>
        </div>
    </section>
    <?php endif; ?>
</div>
<aside class="col-side">
    <?php get_template_part('template-parts/board'); ?>
    <?php if ($aid) get_template_part('template-parts/side-episodes', null, ['anime' => $aid, 'current' => $ep_id]); ?>
    <?php get_sidebar(); ?>
</aside>
</div>
<?php endwhile; get_footer();
