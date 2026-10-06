<?php
if (!defined('ABSPATH')) exit;
get_header();
$uid = get_current_user_id(); $user = wp_get_current_user();
$hist = anizen_user_history_items($uid, 24);
$follow = array_reverse(anizen_ulist('anizen_follow', $uid));
$watched_all = array_reverse(anizen_ulist('anizen_watched', $uid));
$watched = [];
foreach ($watched_all as $eid) {
    if (get_post_type($eid) !== 'episode' || get_post_status($eid) !== 'publish') continue;
    $aid = (int) get_post_meta($eid, '_episode_anime_id', true);
    if (!$aid || get_post_status($aid) !== 'publish') continue;
    $watched[] = ['u' => get_permalink($eid), 'l' => anizen_ep_label($eid), 't' => get_the_title($aid), 'c' => anizen_cover_url($aid, 'thumbnail')];
    if (count($watched) >= 48) break;
}
?>
<section class="panel">
    <div class="panel-ust"><div class="panel-title"><?php echo anizen_icon('user', 18); ?> <?php esc_html_e('Listem', 'anizen'); ?> <span class="count">(<?php echo esc_html($user->display_name); ?>)</span></div>
        <a class="btn btn-ghost btn-sm" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>"><?php esc_html_e('Çıkış', 'anizen'); ?></a></div>
</section>

<section class="panel">
    <div class="panel-ust"><div class="panel-title"><?php esc_html_e('İzlemeye Devam Et', 'anizen'); ?></div></div>
    <div class="panel-body">
    <?php if ($hist) : ?>
        <div class="grid grid-ep">
        <?php foreach ($hist as $it) : ?>
            <article class="card card-ep"><a class="card-thumb" href="<?php echo esc_url($it['u']); ?>"><img src="<?php echo esc_url($it['c']); ?>" alt="" loading="lazy"><span class="card-num"><?php echo esc_html($it['l']); ?></span></a>
            <h3 class="card-title"><a href="<?php echo esc_url($it['u']); ?>"><?php echo esc_html($it['t']); ?></a></h3></article>
        <?php endforeach; ?>
        </div>
    <?php else : ?><p class="muted"><?php esc_html_e('Henüz bir bölüm izlemedin. İzlediğin son bölümler burada saklanır.', 'anizen'); ?></p><?php endif; ?>
    </div>
</section>

<section class="panel">
    <div class="panel-ust"><div class="panel-title"><?php esc_html_e('Takip Ettiklerim', 'anizen'); ?> <span class="count">(<?php echo count($follow); ?>)</span></div></div>
    <div class="panel-body">
    <?php
    $shown = 0;
    if ($follow) {
        echo '<div class="grid">';
        foreach ($follow as $aid) {
            if (get_post_type($aid) !== 'anime' || get_post_status($aid) !== 'publish') continue;
            get_template_part('template-parts/card', 'anime', ['id' => $aid]); $shown++;
        }
        echo '</div>';
    }
    if (!$shown) echo '<p class="muted">' . esc_html__('Henüz takip ettiğin anime yok. Bir animenin sayfasındaki “Takip Et” düğmesiyle buraya ekleyebilirsin.', 'anizen') . '</p>';
    ?>
    </div>
</section>
<section class="panel">
    <div class="panel-ust"><div class="panel-title"><?php esc_html_e('İzlediklerim', 'anizen'); ?> <span class="count">(<?php echo count($watched_all); ?>)</span></div></div>
    <div class="panel-body">
    <?php if ($watched) : ?>
        <div class="grid grid-ep">
        <?php foreach ($watched as $it) : ?>
            <article class="card card-ep"><a class="card-thumb" href="<?php echo esc_url($it['u']); ?>"><img src="<?php echo esc_url($it['c']); ?>" alt="" loading="lazy"><span class="card-num"><?php echo esc_html($it['l']); ?></span></a>
            <h3 class="card-title"><a href="<?php echo esc_url($it['u']); ?>"><?php echo esc_html($it['t']); ?></a></h3></article>
        <?php endforeach; ?>
        </div>
    <?php else : ?><p class="muted"><?php esc_html_e('Henüz izledim olarak işaretlediğin bölüm yok.', 'anizen'); ?></p><?php endif; ?>
    </div>
</section>
<?php get_footer();
