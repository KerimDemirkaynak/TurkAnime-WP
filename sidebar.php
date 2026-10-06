<?php
if (!defined('ABSPATH')) exit;
if (is_active_sidebar('sidebar-main')) {
    dynamic_sidebar('sidebar-main');
    return;
}
/* Parça eklenmemişse boş kalmasın */
$wargs = ['before_widget' => '<section class="widget panel">', 'after_widget' => '</section>', 'before_title' => '<h3 class="widget-title">', 'after_title' => '</h3>'];
foreach (anizen_default_sidebar_widgets() as $d) {
    $class = 'Anizen_Widget_' . ucfirst(substr($d[0], 7));
    if (class_exists($class)) the_widget($class, $d[1], $wargs);
}
