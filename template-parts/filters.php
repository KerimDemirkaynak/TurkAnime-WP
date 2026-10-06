<?php
if (!defined('ABSPATH')) exit;
$cur = function ($k) { return isset($_GET[$k]) ? sanitize_text_field(wp_unslash($_GET[$k])) : ''; };
$base = is_tax() ? get_term_link(get_queried_object()) : get_post_type_archive_link('anime');
$genres = is_tax('genre') ? [] : get_terms(['taxonomy' => 'genre', 'hide_empty' => true, 'orderby' => 'name']);
$sorts = ['' => __('Yeni eklenen', 'anizen'), 'guncel' => __('Son güncellenen', 'anizen'), 'populer' => __('En popüler', 'anizen'), 'puan' => __('En yüksek puan', 'anizen'), 'az' => __('A → Z', 'anizen')];
$active_letter = mb_strtoupper($cur('harf'));
?>
<form class="filters panel" method="get" action="<?php echo esc_url($base); ?>">
    <?php if (!is_tax('genre') && !is_wp_error($genres) && $genres) : ?>
    <label><span><?php esc_html_e('Tür', 'anizen'); ?></span>
        <select name="genre" data-autosubmit><option value=""><?php esc_html_e('Tümü', 'anizen'); ?></option>
        <?php foreach ($genres as $g) echo '<option value="' . esc_attr($g->slug) . '"' . selected($cur('genre'), $g->slug, false) . '>' . esc_html($g->name) . '</option>'; ?>
        </select></label>
    <?php endif; ?>
    <label><span><?php esc_html_e('Yıl', 'anizen'); ?></span>
        <select name="yil" data-autosubmit><option value=""><?php esc_html_e('Tümü', 'anizen'); ?></option>
        <?php foreach (anizen_years() as $y) echo '<option value="' . esc_attr($y) . '"' . selected($cur('yil'), $y, false) . '>' . esc_html($y) . '</option>'; ?>
        </select></label>
    <label><span><?php esc_html_e('Durum', 'anizen'); ?></span>
        <select name="durum" data-autosubmit><option value=""><?php esc_html_e('Tümü', 'anizen'); ?></option>
        <?php foreach (anizen_statuses() as $k => $v) echo '<option value="' . esc_attr($k) . '"' . selected($cur('durum'), $k, false) . '>' . esc_html($v) . '</option>'; ?>
        </select></label>
    <label><span><?php esc_html_e('Format', 'anizen'); ?></span>
        <select name="tip" data-autosubmit><option value=""><?php esc_html_e('Tümü', 'anizen'); ?></option>
        <?php foreach (anizen_types() as $k => $v) echo '<option value="' . esc_attr($k) . '"' . selected($cur('tip'), $k, false) . '>' . esc_html($v) . '</option>'; ?>
        </select></label>
    <label><span><?php esc_html_e('Sırala', 'anizen'); ?></span>
        <select name="sirala" data-autosubmit>
        <?php foreach ($sorts as $k => $v) echo '<option value="' . esc_attr($k) . '"' . selected($cur('sirala'), $k, false) . '>' . esc_html($v) . '</option>'; ?>
        </select></label>
    <?php if ($active_letter !== '') : ?><input type="hidden" name="harf" value="<?php echo esc_attr($active_letter); ?>"><?php endif; ?>
    <div class="filter-actions">
        <button class="btn btn-primary btn-sm" type="submit"><?php esc_html_e('Uygula', 'anizen'); ?></button>
        <?php if ($_GET): ?><a class="btn btn-ghost btn-sm" href="<?php echo esc_url($base); ?>"><?php esc_html_e('Sıfırla', 'anizen'); ?></a><?php endif; ?>
    </div>
</form>
