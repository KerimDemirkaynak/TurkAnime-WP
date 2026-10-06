<?php
if (!defined('ABSPATH')) exit;

/* ------------------------------------------------------------------
   Admin betikleri
------------------------------------------------------------------- */
add_action('admin_enqueue_scripts', function ($hook) {
    $screen = get_current_screen();
    $ok = in_array($hook, ['post.php', 'post-new.php'], true) && $screen && in_array($screen->post_type, ['anime', 'episode'], true);
    if (!$ok && $hook !== 'anime_page_anizen-bulk') return;
    wp_enqueue_media();
    wp_enqueue_style('anizen-admin', ANIZEN_URI . '/assets/css/admin.css', [], ANIZEN_VERSION);
    wp_enqueue_script('anizen-admin', ANIZEN_URI . '/assets/js/admin.js', [], ANIZEN_VERSION, true);
    wp_localize_script('anizen-admin', 'ANIZEN_ADMIN', [
        'ajax' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('anizen_admin'),
    ]);
});

/* Medya penceresinde poster boyutu da seçilebilsin (Cihazdan yükle düğmesi bunu kullanır) */
add_filter('image_size_names_choose', function ($s) {
    $s['anizen-poster'] = __('Poster', 'anizen');
    return $s;
});

/* ------------------------------------------------------------------
   Yardımcılar
------------------------------------------------------------------- */
function anizen_next_episode_number($aid) {
    global $wpdb;
    $max = $wpdb->get_var($wpdb->prepare(
        "SELECT MAX(CAST(n.meta_value AS DECIMAL(10,2))) FROM {$wpdb->postmeta} n
         JOIN {$wpdb->postmeta} a ON a.post_id = n.post_id AND a.meta_key = '_episode_anime_id' AND a.meta_value = %s
         JOIN {$wpdb->posts} p ON p.ID = n.post_id AND p.post_status NOT IN ('trash','auto-draft')
         WHERE n.meta_key = '_episode_number'",
        (string) $aid
    ));
    return $max === null ? 1 : (int) floor((float) $max) + 1;
}

function anizen_picker_field($name, $aid = 0) {
    $title = $aid ? get_the_title($aid) : '';
    echo '<div class="anizen-picker">';
    echo '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr($aid) . '">';
    echo '<input type="hidden" class="picker-clear" name="' . esc_attr($name . '_clear') . '" value="0">';
    echo '<input type="text" class="picker-input regular-text" autocomplete="off" placeholder="' . esc_attr__('Anime adını yazmaya başlayın…', 'anizen') . '" value="' . esc_attr($title) . '">';
    echo '<span class="picker-id ' . ($aid ? 'is-set' : 'is-empty') . '">' . ($aid ? 'ID: ' . (int) $aid : esc_html__('Anime seçilmedi', 'anizen')) . '</span>';
    echo '<ul class="picker-list" hidden></ul></div>';
}

function anizen_valid_youtube_id($v) {
    $v = trim((string) $v);
    if (preg_match('~^[A-Za-z0-9_-]{11}$~', $v)) return $v;
    if (preg_match('~(?:youtu\.be/|v=|embed/|shorts/)([A-Za-z0-9_-]{11})~', $v, $m)) return $m[1];
    return '';
}

add_action('add_meta_boxes', function () {
    add_meta_box('anizen_anime', __('Anime Bilgileri', 'anizen'), 'anizen_anime_box', 'anime', 'normal', 'high');
    add_meta_box('anizen_episode', __('Bölüm Bilgileri & Video Kaynakları', 'anizen'), 'anizen_episode_box', 'episode', 'normal', 'high');
    add_meta_box('anizen_anime_id', __('Anime ID', 'anizen'), 'anizen_anime_id_box', 'anime', 'side', 'high');
});

/** Anime düzenleme ekranında: animenin ID'si ve ona bağlı bölümlerin duruma göre sayısı. */
function anizen_anime_id_box($post) {
    global $wpdb;
    $id = (int) $post->ID;
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT p.post_status s, COUNT(*) c FROM {$wpdb->posts} p
         JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_episode_anime_id' AND m.meta_value = %s
         WHERE p.post_type = 'episode' AND p.post_status NOT IN ('trash', 'auto-draft') GROUP BY p.post_status",
        (string) $id
    ));
    echo '<p style="margin:0 0 8px"><input type="text" readonly class="widefat" style="font-size:18px;font-weight:600" value="' . (int) $id . '" onfocus="this.select()"></p>';
    echo '<p class="description" style="margin:0 0 8px">' . esc_html__('Bölümler bu animeye bu numarayla bağlanır.', 'anizen') . '</p>';
    if ($rows) {
        echo '<ul style="margin:0 0 8px">';
        foreach ($rows as $r) {
            $o = get_post_status_object($r->s);
            echo '<li>' . esc_html($o ? $o->label : $r->s) . ': <strong>' . (int) $r->c . '</strong></li>';
        }
        echo '</ul>';
    } else {
        echo '<p style="margin:0 0 8px;color:#b32d2e">' . esc_html__('Bu ID’ye bağlı hiç bölüm kaydı yok.', 'anizen') . '</p>';
    }
    echo '<p style="margin:0"><a href="' . esc_url(admin_url('edit.php?post_type=episode&anizen_anime=' . $id)) . '">' . esc_html__('Bağlı bölümleri listele', 'anizen') . '</a></p>';
}

/* ------------------------------------------------------------------
   Anime meta kutusu
------------------------------------------------------------------- */
function anizen_anime_box($post) {
    wp_nonce_field('anizen_save_anime', 'anizen_anime_nonce');
    $g = function ($k) use ($post) { return get_post_meta($post->ID, $k, true); };
    $status = anizen_normalize_status($g('_anime_status'));
    $type = $g('_anime_type');
    ?>
    <div class="anizen-box">
        <div class="anizen-al">
            <strong><?php esc_html_e('AniList ile otomatik doldur', 'anizen'); ?></strong>
            <p class="description"><?php esc_html_e('Adını ara, sonucu seç, “AniList’ten Çek”e bas. Alanlar doldurulur; kaydetmeden önce düzenleyebilirsin. Başlık, özet ve görseller dolu ise varsayılan olarak değiştirilmez.', 'anizen'); ?></p>
            <div class="al-row">
                <input type="search" id="al-q" class="regular-text" placeholder="<?php esc_attr_e('Anime adı (ör. Fullmetal Alchemist)', 'anizen'); ?>">
                <button type="button" class="button" id="al-search"><?php esc_html_e('Ara', 'anizen'); ?></button>
            </div>
            <div id="al-results" class="al-results"></div>
            <div class="al-row">
                <label>AniList ID <input type="number" name="anilist_id" id="al-id" value="<?php echo esc_attr($g('_anilist_id')); ?>" class="small-text"></label>
                <label><?php esc_html_e('Başlık dili', 'anizen'); ?>
                    <select id="al-mode"><option value="romaji">Romaji</option><option value="english">English</option><option value="native">日本語</option></select>
                </label>
                <label><input type="checkbox" id="al-overwrite"> <?php esc_html_e('Dolu alanların üzerine yaz', 'anizen'); ?></label>
                <button type="button" class="button button-primary" id="al-import"><?php esc_html_e('AniList’ten Çek', 'anizen'); ?></button>
                <span id="al-status" class="al-status"></span>
            </div>
            <input type="hidden" name="anizen_al_genres" id="al-genres" value="">
            <input type="hidden" name="anizen_al_studios" id="al-studios" value="">
        </div>

        <div class="anizen-grid">
            <p class="wide"><label><strong><?php esc_html_e('Alternatif isimler', 'anizen'); ?></strong><br>
                <input type="text" name="anime_alt_title" id="f-alt" value="<?php echo esc_attr($g('_anime_alt_title')); ?>" class="widefat"></label></p>
            <p><label><strong><?php esc_html_e('Tür (format)', 'anizen'); ?></strong><br>
                <select name="anime_type" id="f-type" class="widefat"><option value="">—</option>
                <?php foreach (anizen_types() as $k => $v) echo '<option value="' . esc_attr($k) . '"' . selected($type, $k, false) . '>' . esc_html($v) . '</option>'; ?>
                </select></label></p>
            <p><label><strong><?php esc_html_e('Yayın durumu', 'anizen'); ?></strong><br>
                <select name="anime_status" id="f-status" class="widefat"><option value="">—</option>
                <?php foreach (anizen_statuses() as $k => $v) echo '<option value="' . esc_attr($k) . '"' . selected($status, $k, false) . '>' . esc_html($v) . '</option>'; ?>
                </select></label></p>
            <p><label><strong><?php esc_html_e('Çıkış yılı', 'anizen'); ?></strong><br>
                <input type="number" name="anime_year" id="f-year" min="1950" max="2100" value="<?php echo esc_attr($g('_anime_year')); ?>" class="widefat"></label></p>
            <p><label><strong><?php esc_html_e('Puan (0–10)', 'anizen'); ?></strong><br>
                <input type="text" name="anime_score" id="f-score" value="<?php echo esc_attr($g('_anime_score')); ?>" placeholder="8.5" class="widefat"></label></p>
            <p><label><strong><?php esc_html_e('Toplam bölüm (planlanan)', 'anizen'); ?></strong><br>
                <input type="number" name="anime_total_eps" id="f-eps" min="0" value="<?php echo esc_attr($g('_anime_total_eps')); ?>" class="widefat"></label></p>
            <p><label><strong><?php esc_html_e('Bölüm süresi (dk)', 'anizen'); ?></strong><br>
                <input type="number" name="anime_duration" id="f-dur" min="0" value="<?php echo esc_attr($g('_anime_duration')); ?>" class="widefat"></label></p>
            <p><label><strong><?php esc_html_e('Fansub / çeviri grubu', 'anizen'); ?></strong><br>
                <input type="text" name="anime_fansub" value="<?php echo esc_attr($g('_anime_fansub')); ?>" placeholder="Anizm Çeviri Ekibi" class="widefat"></label></p>
            <p><label><strong><?php esc_html_e('Dil / altyazı etiketi', 'anizen'); ?></strong><br>
                <input type="text" name="anime_lang" value="<?php echo esc_attr($g('_anime_lang')); ?>" placeholder="Türkçe Altyazılı" class="widefat"></label></p>
            <p class="wide"><label><strong><?php esc_html_e('Kapak görseli URL', 'anizen'); ?></strong> <span class="description"><?php esc_html_e('(Cihazından yüklemek için “Cihazdan yükle”ye bas. Öne çıkan görsel seçiliyse o kullanılır)', 'anizen'); ?></span><br>
                <input type="url" name="anilist_cover" id="f-cover" value="<?php echo esc_attr($g('_anilist_cover')); ?>" class="widefat"></label></p>
            <p class="wide"><label><strong><?php esc_html_e('Banner görseli URL', 'anizen'); ?></strong> <span class="description"><?php esc_html_e('(isteğe bağlı)', 'anizen'); ?></span><br>
                <input type="url" name="anilist_banner" id="f-banner" value="<?php echo esc_attr($g('_anilist_banner')); ?>" class="widefat"></label></p>
            <p class="wide"><label><strong><?php esc_html_e('Fragman (YouTube bağlantısı veya ID)', 'anizen'); ?></strong><br>
                <input type="text" name="anime_trailer" id="f-trailer" value="<?php echo esc_attr($g('_anime_trailer')); ?>" class="widefat"></label></p>
            <p class="wide"><label><input type="checkbox" name="anime_featured" value="1" <?php checked($g('_featured'), '1'); ?>>
                <strong><?php esc_html_e('Ana sayfa vitrininde (hero slider) göster', 'anizen'); ?></strong></label></p>
        </div>
    </div>
    <?php
}

add_action('save_post_anime', function ($post_id) {
    if (!isset($_POST['anizen_anime_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['anizen_anime_nonce'])), 'anizen_save_anime')) return;
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) return;

    $put = function ($key, $val) use ($post_id) {
        if ($val === '' || $val === null) delete_post_meta($post_id, $key);
        else update_post_meta($post_id, $key, $val);
    };
    $txt = function ($k) { return isset($_POST[$k]) ? sanitize_text_field(wp_unslash($_POST[$k])) : ''; };
    $num = function ($k) use ($txt) { $v = $txt($k); return $v === '' ? '' : (string) absint($v); };

    $put('_anilist_id', $num('anilist_id'));
    $put('_anime_alt_title', $txt('anime_alt_title'));
    $put('_anime_year', $num('anime_year'));
    $put('_anime_total_eps', $num('anime_total_eps'));
    $put('_anime_duration', $num('anime_duration'));
    $put('_anime_lang', $txt('anime_lang'));
    $put('_anime_fansub', $txt('anime_fansub'));

    $score = str_replace(',', '.', $txt('anime_score'));
    $put('_anime_score', is_numeric($score) ? number_format(max(0, min(10, (float) $score)), 1, '.', '') : '');

    $st = $txt('anime_status');
    $put('_anime_status', isset(anizen_statuses()[$st]) ? $st : '');
    $ty = $txt('anime_type');
    $put('_anime_type', isset(anizen_types()[$ty]) ? $ty : '');

    $put('_anilist_cover', isset($_POST['anilist_cover']) ? esc_url_raw(wp_unslash($_POST['anilist_cover'])) : '');
    $put('_anilist_banner', isset($_POST['anilist_banner']) ? esc_url_raw(wp_unslash($_POST['anilist_banner'])) : '');
    $put('_anime_trailer', anizen_valid_youtube_id($txt('anime_trailer')));
    $put('_featured', !empty($_POST['anime_featured']) ? '1' : '');

    // AniList'ten gelen tür/stüdyo adları: mevcutlara eklenir
    foreach (['anizen_al_genres' => 'genre', 'anizen_al_studios' => 'studio'] as $field => $tax) {
        if (empty($_POST[$field])) continue;
        $names = array_filter(array_map('sanitize_text_field', explode('|', wp_unslash($_POST[$field]))));
        if ($names) wp_set_object_terms($post_id, array_values($names), $tax, true);
    }
});

/* ------------------------------------------------------------------
   Bölüm meta kutusu
------------------------------------------------------------------- */
function anizen_source_row($i, $s = []) {
    $name = isset($s['name']) ? $s['name'] : '';
    $type = isset($s['type']) ? $s['type'] : 'auto';
    $code = isset($s['code']) ? $s['code'] : '';
    $types = ['auto' => 'Otomatik algıla', 'iframe' => 'Iframe / sayfa URL', 'embed' => 'Embed kodu', 'mp4' => 'MP4 / WebM', 'hls' => 'HLS (.m3u8)'];
    $i = esc_attr($i);
    echo '<div class="anizen-src"><div class="src-head">';
    echo '<input type="text" class="src-name" list="anizen-servers" name="anizen_sources[' . $i . '][name]" value="' . esc_attr($name) . '" placeholder="' . esc_attr__('Sunucu adı (ör. SIBNET)', 'anizen') . '">';
    echo '<select name="anizen_sources[' . $i . '][type]">';
    foreach ($types as $k => $v) echo '<option value="' . esc_attr($k) . '"' . selected($type, $k, false) . '>' . esc_html($v) . '</option>';
    echo '</select><span class="src-actions"><button type="button" class="button src-up" title="Yukarı">↑</button><button type="button" class="button src-down" title="Aşağı">↓</button><button type="button" class="button src-del">' . esc_html__('Sil', 'anizen') . '</button></span></div>';
    echo '<textarea name="anizen_sources[' . $i . '][code]" rows="2" placeholder="' . esc_attr__('Iframe kodu, embed URL, .mp4 veya .m3u8 bağlantısı', 'anizen') . '">' . esc_textarea($code) . '</textarea></div>';
}

function anizen_episode_box($post) {
    wp_nonce_field('anizen_save_episode', 'anizen_ep_nonce');
    $aid = (int) get_post_meta($post->ID, '_episode_anime_id', true);
    if (!$aid && isset($_GET['anime_id'])) $aid = absint($_GET['anime_id']);
    $num = get_post_meta($post->ID, '_episode_number', true);
    if ($num === '' && $aid && $post->post_status === 'auto-draft') $num = anizen_next_episode_number($aid);
    $sources = anizen_get_sources($post->ID);
    if (!$sources) $sources = [['name' => '', 'type' => 'auto', 'code' => '']];
    $reports = (int) get_post_meta($post->ID, '_reports', true);
    ?>
    <div class="anizen-box">
        <div class="anizen-grid">
            <p class="wide"><strong><?php esc_html_e('Bağlı olduğu anime', 'anizen'); ?></strong><br><?php anizen_picker_field('episode_anime_id', $aid); ?></p>
            <p><label><strong><?php esc_html_e('Bölüm numarası', 'anizen'); ?></strong><br>
                <input type="text" name="episode_number" value="<?php echo esc_attr($num); ?>" placeholder="1 · 12.5 · OVA 1 · Özel" class="widefat"></label>
                <span class="description"><?php esc_html_e('Normal bölüm için sayı yaz (12.5 gibi buçuklu olabilir). OVA / Özel gibi bölümler için yazı yaz (ör. OVA 1, Özel); bunlar listenin sonunda görünür. Adres: /anime/seri-adi/bolum/numara/', 'anizen'); ?></span></p>
            <p class="wide"><label><strong><?php esc_html_e('Fansub / çeviri grubu', 'anizen'); ?></strong><br>
                <input type="text" name="episode_fansub" value="<?php echo esc_attr(get_post_meta($post->ID, '_episode_fansub', true)); ?>" placeholder="<?php echo esc_attr($aid ? (anizen_fansub($aid) ?: 'Anizm Çeviri Ekibi') : 'Anizm Çeviri Ekibi'); ?>" class="widefat"></label>
                <span class="description"><?php esc_html_e('Bu bölümü çeviren grup. Birden fazla ise virgülle ayır. Boş bırakırsan animenin varsayılan fansub\'ı kullanılır.', 'anizen'); ?></span></p>
            <?php if ($reports > 0) : ?>
            <p><strong style="color:#d63638">⚠ <?php echo (int) $reports; ?> <?php esc_html_e('hata bildirimi', 'anizen'); ?></strong><br>
                <label><input type="checkbox" name="anizen_reset_reports" value="1"> <?php esc_html_e('Düzelttim, bildirimleri sıfırla', 'anizen'); ?></label></p>
            <?php endif; ?>
        </div>

        <p class="description"><?php echo esc_html(sprintf(__('Bu bölümün kayıt (yazı) ID’si: %d', 'anizen'), (int) $post->ID)); ?></p>
        <?php $elog = get_post_meta($post->ID, '_episode_log', true); if (is_array($elog) && $elog) : ?>
        <details open style="margin:6px 0 12px"><summary><strong><?php esc_html_e('Değişiklik kaydı (son 8)', 'anizen'); ?></strong></summary>
            <?php foreach (array_reverse($elog) as $l) : ?>
            <div style="font-size:12px;color:#50575e"><?php echo esc_html(wp_date('d.m.Y H:i', (int) $l['t']) . ' · ' . $l['u'] . ' · ' . $l['w'] . ' · ' . $l['p']); ?></div>
            <?php endforeach; ?>
        </details>
        <?php endif; ?>
        <h4><?php esc_html_e('Video kaynakları', 'anizen'); ?></h4>
        <p class="description"><?php esc_html_e('Sınırsız kaynak ekleyebilirsin. Sıra, oynatıcıdaki sıradır; ilki varsayılandır. Iframe kodu yapıştırırsan yalnızca adresi alınır (güvenlik için).', 'anizen'); ?></p>
        <div id="anizen-sources" data-next="<?php echo (int) count($sources); ?>">
            <?php foreach ($sources as $i => $s) anizen_source_row($i, $s); ?>
        </div>
        <p><button type="button" class="button button-secondary" id="anizen-add-src">+ <?php esc_html_e('Kaynak ekle', 'anizen'); ?></button></p>
        <script type="text/template" id="anizen-src-tpl"><?php anizen_source_row('__i__'); ?></script>
        <datalist id="anizen-servers">
            <?php foreach (['SIBNET', 'MP4Upload', 'Google Drive', 'Doodstream', 'Streamtape', 'Filemoon', 'Mixdrop', 'Ok.ru', 'Vidmoly', 'Dailymotion', 'YouTube', 'VK', 'Mail.ru', 'Alucard', 'Fansub'] as $n) echo '<option value="' . esc_attr($n) . '">'; ?>
        </datalist>
    </div>
    <?php
}

add_action('save_post_episode', function ($post_id) {
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id)) return;
    $old = (int) get_post_meta($post_id, '_episode_anime_id', true);
    $new = $old;

    if (isset($_POST['anizen_ep_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['anizen_ep_nonce'])), 'anizen_save_episode') && current_user_can('edit_post', $post_id)) {
        /* Anime bağlantısı yalnızca geçerli bir anime seçildiyse değişir; alan boş gelirse eski bağlantı KORUNUR.
           Bağlantıyı kaldırmak için seçicideki yazıyı bilerek silip kaydetmek gerekir (episode_anime_id_clear=1). */
        $posted = isset($_POST['episode_anime_id']) ? absint($_POST['episode_anime_id']) : 0;
        $clear  = isset($_POST['episode_anime_id_clear']) && $_POST['episode_anime_id_clear'] === '1';
        if ($posted && get_post_type($posted) === 'anime') {
            $new = $posted;
            if ($old && $old !== $new) anizen_ep_log($post_id, 'anime: ' . $old . ' → ' . $new);
            update_post_meta($post_id, '_episode_anime_id', (string) $new);
        } elseif ($clear) {
            $new = 0;
            if ($old) anizen_ep_log($post_id, 'anime bağlantısı kaldırıldı (eski: ' . $old . ')');
            delete_post_meta($post_id, '_episode_anime_id');
        }

        $n = isset($_POST['episode_number']) ? str_replace(',', '.', sanitize_text_field(wp_unslash($_POST['episode_number']))) : '';
        if ($n === '' && $new) $n = (string) anizen_next_episode_number($new);
        $raw_n = isset($_POST['episode_number']) ? trim(sanitize_text_field(wp_unslash($_POST['episode_number']))) : '';
        anizen_ep_save_meta($post_id, $new, $raw_n !== '' ? $raw_n : $n);

        $fs = isset($_POST['episode_fansub']) ? trim(sanitize_text_field(wp_unslash($_POST['episode_fansub']))) : '';
        if ($fs !== '') update_post_meta($post_id, '_episode_fansub', $fs); else delete_post_meta($post_id, '_episode_fansub');

        $rows = (isset($_POST['anizen_sources']) && is_array($_POST['anizen_sources'])) ? wp_unslash($_POST['anizen_sources']) : [];
        $types = ['auto', 'iframe', 'embed', 'mp4', 'hls'];
        $raw_ok = current_user_can('unfiltered_html');
        $clean = [];
        foreach ($rows as $r) {
            if (!is_array($r)) continue;
            $code = isset($r['code']) ? trim((string) $r['code']) : '';
            if ($code === '') continue;
            $type = (isset($r['type']) && in_array($r['type'], $types, true)) ? $r['type'] : 'auto';
            $clean[] = [
                'name' => sanitize_text_field(isset($r['name']) ? $r['name'] : ''),
                'type' => $type,
                'code' => $raw_ok ? $code : wp_kses($code, anizen_embed_kses()),
            ];
        }
        update_post_meta($post_id, '_episode_sources', $clean);
        for ($i = 1; $i <= 5; $i++) {
            delete_post_meta($post_id, '_episode_source_name_' . $i);
            delete_post_meta($post_id, '_episode_source_code_' . $i);
        }
        if (!empty($_POST['anizen_reset_reports'])) {
            delete_post_meta($post_id, '_reports');
            delete_post_meta($post_id, '_report_log');
            delete_post_meta($post_id, '_report_users');
        }
    }
    foreach (array_unique([$old, $new]) as $a) if ($a) anizen_recount($a);
});
