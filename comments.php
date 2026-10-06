<?php
if (!defined('ABSPATH')) exit;
if (post_password_required()) return;
$n = (int) get_comments_number();
?>
<section class="comments panel" id="comments">
    <h2 class="comments-title"><?php echo anizen_icon('message', 20); ?> <?php esc_html_e('Yorumlar', 'anizen'); ?> <span class="count">(<?php echo $n; ?>)</span></h2>

    <?php if (have_comments()) : ?>
        <ol class="comment-list">
            <?php wp_list_comments(['style' => 'ol', 'short_ping' => true, 'callback' => 'anizen_comment_callback']); ?>
        </ol>
        <?php the_comments_pagination(['prev_text' => __('Önceki', 'anizen'), 'next_text' => __('Sonraki', 'anizen')]); ?>
    <?php elseif (comments_open()) : ?>
        <p class="muted"><?php esc_html_e('Henüz yorum yok. İlk yorumu sen yaz!', 'anizen'); ?></p>
    <?php endif; ?>

    <?php if (!comments_open() && $n) : ?><p class="muted"><?php esc_html_e('Yorumlar kapalı.', 'anizen'); ?></p><?php endif; ?>

    <?php
    comment_form([
        'title_reply' => __('Yorum Yap', 'anizen'),
        'title_reply_to' => __('%s adlı kişiye yanıt ver', 'anizen'),
        'cancel_reply_link' => __('Vazgeç', 'anizen'),
        'label_submit' => __('Yorumu Gönder', 'anizen'),
        'class_form' => 'comment-form',
        'must_log_in' => '<p class="muted">' . sprintf(
            /* translators: 1: login link, 2: register link */
            esc_html__('Yorum yazmak için %1$s veya %2$s.', 'anizen'),
            '<a href="' . esc_url(wp_login_url(get_permalink())) . '">' . esc_html__('giriş yap', 'anizen') . '</a>',
            '<a href="' . esc_url(get_option('users_can_register') ? wp_registration_url() : wp_login_url(get_permalink())) . '">' . esc_html__('kaydol', 'anizen') . '</a>'
        ) . '</p>',
        'submit_button' => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>',
        'class_submit' => 'btn btn-primary',
        'comment_notes_before' => '',
        'comment_notes_after' => anizen_comment_extra_fields(),
        'comment_field' => '<p class="cf-comment"><label class="screen-reader-text" for="comment">' . esc_html__('Yorumun', 'anizen') . '</label><textarea id="comment" name="comment" rows="4" maxlength="3000" required placeholder="' . esc_attr__('Düşüncelerini paylaş…', 'anizen') . '"></textarea></p>',
    ]);
    ?>
</section>
