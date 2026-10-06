<?php
/**
 * TürkAnime — Anime Teması
 */
if (!defined('ABSPATH')) exit;

define('ANIZEN_VERSION', '2.5.0');
define('ANIZEN_DIR', get_template_directory());
define('ANIZEN_URI', get_template_directory_uri());

foreach (['helpers', 'setup', 'post-types', 'permalinks', 'userdata', 'anilist', 'admin-meta', 'admin-tools', 'ajax', 'customizer', 'comments', 'seo', 'widgets', 'cache-fix'] as $anizen_file) {
    require_once ANIZEN_DIR . '/inc/' . $anizen_file . '.php';
}
unset($anizen_file);
