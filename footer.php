<?php if (!defined('ABSPATH')) exit;
$has_widgets = is_active_sidebar('footer-1') || is_active_sidebar('footer-2') || is_active_sidebar('footer-3');
$copy = str_replace(['{year}', '{site}'], [date_i18n('Y'), get_bloginfo('name')], (string) anizen_opt('footer_text'));
$disc = (string) anizen_opt('footer_disclaimer');
$social = anizen_social_links();
if (anizen_opt('show_genres_panel')) get_template_part('template-parts/genres-panel');
?>
</main>

<footer class="site-footer">
    <?php if ($has_widgets) : ?>
    <div class="container footer-widgets">
        <?php for ($i = 1; $i <= 3; $i++) if (is_active_sidebar('footer-' . $i)) { echo '<div class="fw">'; dynamic_sidebar('footer-' . $i); echo '</div>'; } ?>
    </div>
    <?php endif; ?>
    <div class="Altkisim">
        <div class="container foot-row">
            <span class="copy"><?php echo wp_kses_post($copy); ?><?php if (current_user_can('edit_posts')) : ?> | <a href="<?php echo esc_url(admin_url()); ?>">Admin</a><?php endif; ?></span>
            <?php if (has_nav_menu('footer')) wp_nav_menu(['theme_location' => 'footer', 'container' => 'nav', 'container_class' => 'footer-nav', 'menu_class' => 'footer-menu', 'depth' => 1]); ?>
            <?php if ($social) : ?><span class="socials"><?php echo $social; // anizen_social_links() içinde kaçışlanır ?></span><?php endif; ?>
        </div>
        <?php if ($disc) : ?><div class="container"><p class="disclaimer"><?php echo wp_kses_post($disc); ?></p></div><?php endif; ?>
    </div>
</footer>

<?php if (anizen_opt('totop')) : ?>
<button type="button" class="to-top" aria-label="<?php esc_attr_e('Yukarı çık', 'anizen'); ?>"><?php echo anizen_icon('arrow-up', 22); ?></button>
<?php endif; ?>
<div class="cinema-shade" hidden></div>
<?php wp_footer(); ?>
</body>
</html>
