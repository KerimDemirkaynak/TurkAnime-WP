<?php
if (!defined('ABSPATH') || !anizen_opt('brand_show')) return;
$cur = isset($_GET['harf']) ? mb_strtoupper(sanitize_text_field(wp_unslash($_GET['harf']))) : '';
$base = get_post_type_archive_link('anime');
?>
<div class="brand"><?php echo anizen_logo(); ?></div>
<div class="panel alpha-bar">
    <div class="alphabet" role="navigation" aria-label="<?php esc_attr_e('Harfe göre', 'anizen'); ?>">
        <?php foreach (array_merge(['0'], range('A', 'Z')) as $l) : ?>
            <a class="<?php echo $cur === (string) $l ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('harf', $l, $base)); ?>"><b><?php echo $l === '0' ? '#0-9' : esc_html($l); ?></b></a>
        <?php endforeach; ?>
    </div>
</div>
