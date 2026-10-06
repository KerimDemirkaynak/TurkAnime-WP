<?php
if (!defined('ABSPATH')) exit;

/* Yorum öğesi (wp_list_comments callback) */
function anizen_comment_callback($comment, $args, $depth) {
    $cid = (int) $comment->comment_ID;
    $is_staff = $comment->user_id && user_can((int) $comment->user_id, 'edit_others_posts');
    $spoiler = get_comment_meta($cid, '_spoiler', true) === '1' && anizen_opt('comments_spoiler');
    $likes = (int) get_comment_meta($cid, '_likes', true);
    ?>
    <li id="comment-<?php echo $cid; ?>" <?php comment_class($comment->comment_parent ? 'cm is-reply' : 'cm'); ?>>
        <article class="cm-body" id="div-comment-<?php echo $cid; ?>">
            <div class="cm-avatar"><?php echo get_avatar($comment, 44, '', '', ['loading' => 'lazy']); ?></div>
            <div class="cm-main">
                <header class="cm-head">
                    <strong class="cm-author"><?php echo esc_html(get_comment_author($comment)); ?></strong>
                    <?php if ($is_staff) : ?><span class="badge badge-staff"><?php esc_html_e('Yönetim', 'anizen'); ?></span><?php endif; ?>
                    <a class="cm-date" href="<?php echo esc_url(get_comment_link($comment, $args)); ?>"><time datetime="<?php comment_time('c'); ?>"><?php echo esc_html(sprintf(__('%s önce', 'anizen'), human_time_diff(get_comment_time('U', true), time()))); ?></time></a>
                </header>
                <?php if ('0' === $comment->comment_approved) : ?>
                    <p class="cm-wait"><?php esc_html_e('Yorumun onay bekliyor.', 'anizen'); ?></p>
                <?php endif; ?>
                <div class="cm-text<?php echo $spoiler ? ' is-spoiler' : ''; ?>">
                    <?php if ($spoiler) : ?><button type="button" class="spoiler-cover"><?php esc_html_e('Spoiler içeriyor — görmek için tıkla', 'anizen'); ?></button><?php endif; ?>
                    <div class="cm-content"><?php comment_text($comment); ?></div>
                </div>
                <footer class="cm-actions">
                    <?php if (anizen_opt('comments_likes') && '0' !== $comment->comment_approved) : ?>
                        <button type="button" class="cm-like" data-id="<?php echo $cid; ?>" aria-label="<?php esc_attr_e('Beğen', 'anizen'); ?>"><?php echo anizen_icon('thumbs-up', 15); ?> <span class="n"><?php echo $likes; ?></span></button>
                    <?php endif; ?>
                    <?php comment_reply_link(array_merge($args, ['depth' => $depth, 'add_below' => 'div-comment', 'reply_text' => __('Yanıtla', 'anizen'), 'before' => '', 'after' => ''])); ?>
                    <?php edit_comment_link(__('Düzenle', 'anizen')); ?>
                </footer>
            </div>
        </article>
    <?php
    // </li> WordPress tarafından kapatılır.
}

/* Form alanları: web sitesi alanını kaldır (spam azaltır) */
add_filter('comment_form_default_fields', function ($f) {
    unset($f['url']);
    if (isset($f['author'])) $f['author'] = '<p class="cf-field"><label class="screen-reader-text" for="author">' . esc_html__('Adın', 'anizen') . '</label><input id="author" name="author" type="text" maxlength="60" required placeholder="' . esc_attr__('Adın *', 'anizen') . '" autocomplete="name"></p>';
    if (isset($f['email'])) $f['email'] = '<p class="cf-field"><label class="screen-reader-text" for="email">' . esc_html__('E-posta', 'anizen') . '</label><input id="email" name="email" type="email" maxlength="100" required placeholder="' . esc_attr__('E-posta * (yayınlanmaz)', 'anizen') . '" autocomplete="email"></p>';
    return $f;
});

/* Spoiler seçeneği + bal küpü alanı */
function anizen_comment_extra_fields() {
    $o = '<p class="hp" aria-hidden="true"><label>Bu alanı boş bırakın <input type="text" name="anizen_hp" tabindex="-1" autocomplete="off"></label></p>';
    if (anizen_opt('comments_spoiler')) {
        $o .= '<p class="cf-spoiler"><label><input type="checkbox" name="anizen_spoiler" value="1"> ' . esc_html__('Bu yorum spoiler içeriyor', 'anizen') . '</label></p>';
    }
    return $o;
}

add_action('comment_post', function ($cid) {
    if (!empty($_POST['anizen_spoiler']) && anizen_opt('comments_spoiler')) add_comment_meta($cid, '_spoiler', '1', true);
});

add_filter('preprocess_comment', function ($data) {
    if (!empty($_POST['anizen_hp'])) wp_die(esc_html__('Yorum gönderilemedi.', 'anizen'), '', ['response' => 400, 'back_link' => true]);
    return $data;
});

/* [spoiler]...[/spoiler] etiketi */
add_filter('comment_text', function ($text) {
    if (!anizen_opt('comments_spoiler') || stripos($text, '[spoiler]') === false) return $text;
    return preg_replace('~\[spoiler\](.*?)\[/spoiler\]~is', '<span class="spoiler" tabindex="0" role="button" title="' . esc_attr__('Göstermek için tıkla', 'anizen') . '">$1</span>', $text);
}, 20);
