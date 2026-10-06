<?php
if (!defined('ABSPATH')) return;
$terms = get_terms(['taxonomy' => 'genre', 'hide_empty' => true, 'orderby' => 'name', 'order' => 'ASC']);
if (is_wp_error($terms) || !$terms) return;
?>
<section class="panel genres-panel" id="genres-panel">
    <button type="button" class="gp-close" aria-label="<?php esc_attr_e('Kapat', 'anizen'); ?>">&times;</button>
    <h2><?php esc_html_e('Ne Çıkarsa Bahtıma!', 'anizen'); ?></h2>
    <p><?php esc_html_e('Karar vermekte güçlük mü çekiyorsunuz? Beğendiğiniz kategoriyi seçin size en uygun sonucu bulalım.', 'anizen'); ?></p>
    <div class="tag-list">
        <?php foreach ($terms as $t) echo '<a class="tag-item" href="' . esc_url(get_term_link($t)) . '">' . anizen_icon('tag', 22) . '<span>' . esc_html($t->name) . '</span></a>'; ?>
    </div>
</section>
