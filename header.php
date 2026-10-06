<?php if (!defined('ABSPATH')) exit; $ann = anizen_opt('announce_text'); $ann_url = anizen_opt('announce_url'); ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="color-scheme" content="dark light">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e('İçeriğe geç', 'anizen'); ?></a>

<?php if ($ann) : ?>
<div class="announce"><?php if ($ann_url) : ?><a href="<?php echo esc_url($ann_url); ?>"><?php echo esc_html($ann); ?></a><?php else : echo esc_html($ann); endif; ?></div>
<?php endif; ?>

<header class="site-header navbar" id="site-header">
    <div class="container header-in">
        <button type="button" class="icon-btn nav-toggle" aria-label="<?php esc_attr_e('Menüyü aç', 'anizen'); ?>" aria-expanded="false" aria-controls="site-nav"><?php echo anizen_icon('menu', 22); ?></button>
        <a class="nav-home" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('Ana sayfa', 'anizen'); ?>" title="<?php echo esc_attr(get_bloginfo('name')); ?>"><?php echo anizen_icon('home', 26); ?></a>

        <nav class="site-nav" id="site-nav" aria-label="<?php esc_attr_e('Ana menü', 'anizen'); ?>">
            <?php wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'menu_class' => 'nav-list', 'fallback_cb' => 'anizen_default_menu', 'depth' => 2]); ?>
        </nav>

        <div class="header-tools">
            <button type="button" class="icon-btn search-toggle" aria-label="<?php esc_attr_e('Ara', 'anizen'); ?>" aria-expanded="false"><?php echo anizen_icon('search', 20); ?></button>
            <form role="search" method="get" class="search" action="<?php echo esc_url(home_url('/')); ?>">
                <span class="search-ico"><?php echo anizen_icon('search', 17); ?></span>
                <input type="search" id="q" name="s" placeholder="<?php esc_attr_e('Anime ara...', 'anizen'); ?>" autocomplete="off" spellcheck="false" value="<?php echo esc_attr(get_search_query()); ?>" aria-label="<?php esc_attr_e('Anime ara', 'anizen'); ?>" aria-controls="search-results">
                <div class="search-results" id="search-results" hidden></div>
            </form>
            <?php if (anizen_opt('nav_random')) : ?>
                <a class="icon-btn" href="<?php echo esc_url(add_query_arg('rastgele', '1', home_url('/'))); ?>" rel="nofollow" title="<?php esc_attr_e('Rastgele anime', 'anizen'); ?>" aria-label="<?php esc_attr_e('Rastgele anime', 'anizen'); ?>"><?php echo anizen_icon('shuffle', 20); ?></a>
            <?php endif; ?>
            <button type="button" class="icon-btn theme-toggle" aria-label="<?php esc_attr_e('Temayı değiştir', 'anizen'); ?>" title="<?php esc_attr_e('Koyu / açık tema', 'anizen'); ?>">
                <span class="when-dark"><?php echo anizen_icon('sun', 22); ?></span><span class="when-light"><?php echo anizen_icon('moon', 22); ?></span>
            </button>
            <?php if (anizen_opt('show_auth')) : ?>
            <a class="icon-btn auth-icon" href="<?php echo esc_url(is_user_logged_in() ? anizen_list_url() : anizen_login_url()); ?>" aria-label="<?php echo esc_attr(is_user_logged_in() ? __('Listem', 'anizen') : __('Üye Girişi', 'anizen')); ?>" title="<?php echo esc_attr(is_user_logged_in() ? __('Listem', 'anizen') : __('Üye Girişi', 'anizen')); ?>"><?php echo anizen_icon('user', 22); ?></a>
            <span class="auth">
                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo esc_url(anizen_list_url()); ?>"><?php esc_html_e('Listem', 'anizen'); ?></a>
                    <?php if (current_user_can('edit_posts')) : ?><a href="<?php echo esc_url(admin_url()); ?>"><?php esc_html_e('Yönetim', 'anizen'); ?></a><?php endif; ?>
                    <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>"><?php esc_html_e('Çıkış', 'anizen'); ?></a>
                <?php else : ?>
                    <a href="<?php echo esc_url(wp_login_url()); ?>"><?php esc_html_e('Üye Girişi', 'anizen'); ?></a>
                    <?php if (get_option('users_can_register')) : ?><a href="<?php echo esc_url(wp_registration_url()); ?>"><?php esc_html_e('Kayıt Ol', 'anizen'); ?></a><?php endif; ?>
                <?php endif; ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
</header>
<div class="nav-backdrop" hidden></div>

<main id="main" class="site-main container">
<?php get_template_part('template-parts/brand'); ?>
