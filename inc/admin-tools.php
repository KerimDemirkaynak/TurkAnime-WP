<?php
if (!defined('ABSPATH')) exit;

/* ------------------------------------------------------------------
   Anime listesi sütunları
------------------------------------------------------------------- */
add_filter('manage_anime_posts_columns', function ($cols) {
    $out = [];
    foreach ($cols as $k => $v) {
        if ($k === 'title') $out['anizen_cover'] = '';
        $out[$k] = $v;
        if ($k === 'title') {
            $out['anizen_id'] = __('ID', 'anizen');
            $out['anizen_eps'] = __('Bölüm', 'anizen');
            $out['anizen_status'] = __('Durum', 'anizen');
            $out['anizen_score'] = __('Puan', 'anizen');
            $out['anizen_views'] = __('İzlenme', 'anizen');
        }
    }
    return $out;
});

add_action('manage_anime_posts_custom_column', function ($col, $id) {
    switch ($col) {
        case 'anizen_cover':
            echo '<img src="' . esc_url(anizen_cover_url($id, 'thumbnail')) . '" width="40" height="56" style="object-fit:cover;border-radius:4px" alt="">';
            break;
        case 'anizen_id':
            echo '<code>' . (int) $id . '</code>';
            break;
        case 'anizen_eps':
            $c = anizen_episode_count($id);
            echo '<a href="' . esc_url(admin_url('edit.php?post_type=episode&anizen_anime=' . $id)) . '">' . (int) $c . '</a>';
            break;
        case 'anizen_status':
            echo esc_html(anizen_status_label(get_post_meta($id, '_anime_status', true)) ?: '—');
            if (get_post_meta($id, '_featured', true) === '1') echo ' <span title="Vitrinde" style="color:#dba617">★</span>';
            break;
        case 'anizen_score':
            echo esc_html(get_post_meta($id, '_anime_score', true) ?: '—');
            break;
        case 'anizen_views':
            echo number_format_i18n(anizen_views($id));
            break;
    }
}, 10, 2);

add_filter('manage_edit-anime_sortable_columns', function ($c) {
    $c['anizen_id'] = 'ID';
    $c['anizen_eps'] = 'anizen_eps';
    $c['anizen_score'] = 'anizen_score';
    $c['anizen_views'] = 'anizen_views';
    return $c;
});

add_filter('post_row_actions', function ($actions, $post) {
    if ($post->post_type === 'anime') {
        $actions['anizen_id'] = '<span>ID: ' . (int) $post->ID . '</span>';
        $actions['anizen_add_ep'] = '<a href="' . esc_url(admin_url('post-new.php?post_type=episode&anime_id=' . $post->ID)) . '">' . esc_html__('Bölüm Ekle', 'anizen') . '</a>';
    }
    return $actions;
}, 10, 2);

/* ------------------------------------------------------------------
   Bölüm listesi sütunları + anime'ye göre süzme
------------------------------------------------------------------- */
add_filter('manage_episode_posts_columns', function ($cols) {
    $out = [];
    foreach ($cols as $k => $v) {
        $out[$k] = $v;
        if ($k === 'title') {
            $out['anizen_anime'] = __('Anime', 'anizen');
            $out['anizen_num'] = __('No', 'anizen');
            $out['anizen_src'] = __('Kaynak', 'anizen');
            $out['anizen_rep'] = __('Hata', 'anizen');
        }
    }
    return $out;
});

add_action('manage_episode_posts_custom_column', function ($col, $id) {
    switch ($col) {
        case 'anizen_anime':
            $aid = (int) get_post_meta($id, '_episode_anime_id', true);
            if ($aid && get_post_type($aid) !== 'anime') echo '<span style="color:#d63638">' . esc_html(sprintf(__('Geçersiz anime ID: %d', 'anizen'), $aid)) . '</span>';
            elseif ($aid) echo '<a href="' . esc_url(admin_url('edit.php?post_type=episode&anizen_anime=' . $aid)) . '">' . esc_html(get_the_title($aid)) . '</a> <small style="color:#646970">ID ' . (int) $aid . '</small>';
            else echo '<span style="color:#d63638">' . esc_html__('Bağlı değil', 'anizen') . '</span>';
            break;
        case 'anizen_num':
            echo esc_html(anizen_ep_number($id));
            break;
        case 'anizen_src':
            $n = count(anizen_get_sources($id));
            echo $n ? (int) $n : '<span style="color:#d63638">0</span>';
            break;
        case 'anizen_rep':
            $r = (int) get_post_meta($id, '_reports', true);
            echo $r ? '<strong style="color:#d63638">⚠ ' . $r . '</strong>' : '—';
            break;
    }
}, 10, 2);

add_filter('manage_edit-episode_sortable_columns', function ($c) {
    $c['anizen_num'] = 'anizen_num';
    $c['anizen_rep'] = 'anizen_rep';
    return $c;
});

add_action('pre_get_posts', function ($q) {
    if (!is_admin() || !$q->is_main_query()) return;
    $type = $q->get('post_type');
    if (!in_array($type, ['anime', 'episode'], true)) return;

    $mq = [];
    if ($type === 'episode' && !empty($_GET['anizen_anime'])) {
        $mq[] = ['key' => '_episode_anime_id', 'value' => (string) absint($_GET['anizen_anime'])];
    }
    if ($type === 'episode' && !empty($_GET['anizen_reported'])) {
        $mq[] = ['key' => '_reports', 'value' => 0, 'type' => 'NUMERIC', 'compare' => '>'];
    }
    $ob = (string) $q->get('orderby');
    $map = [
        'anizen_eps' => ['_ep_count', 'NUMERIC'], 'anizen_views' => ['_views', 'NUMERIC'], 'anizen_score' => ['_anime_score', 'DECIMAL(4,1)'],
        'anizen_num' => ['_episode_sort', 'DECIMAL(10,2)'], 'anizen_rep' => ['_reports', 'NUMERIC'],
    ];
    if (isset($map[$ob])) {
        list($k, $t) = $map[$ob];
        $dir = strtoupper((string) $q->get('order')) === 'ASC' ? 'ASC' : 'DESC';
        $mq['sortgrp'] = ['relation' => 'OR', 'srt' => ['key' => $k, 'type' => $t, 'compare' => 'EXISTS'], ['key' => $k, 'compare' => 'NOT EXISTS']];
        $q->set('orderby', ['srt' => $dir, 'title' => 'ASC']);
    }
    if ($mq) {
        $mq['relation'] = 'AND';
        $q->set('meta_query', $mq);
    }
});

add_action('admin_notices', function () {
    $s = get_current_screen();
    if (!$s || $s->post_type !== 'episode' || $s->base !== 'edit' || empty($_GET['anizen_anime'])) return;
    $aid = absint($_GET['anizen_anime']);
    echo '<div class="notice notice-info"><p><strong>' . esc_html(get_the_title($aid)) . '</strong> — '
        . esc_html__('bölümleri gösteriliyor.', 'anizen') . ' <a href="' . esc_url(admin_url('edit.php?post_type=episode')) . '">' . esc_html__('Süzmeyi kaldır', 'anizen') . '</a> · '
        . '<a href="' . esc_url(admin_url('post-new.php?post_type=episode&anime_id=' . $aid)) . '">' . esc_html__('Bu animeye bölüm ekle', 'anizen') . '</a></p></div>';
});

/* ------------------------------------------------------------------
   Toplu bölüm ekleme + bakım sayfası
------------------------------------------------------------------- */
add_action('admin_menu', function () {
    add_submenu_page('edit.php?post_type=anime', __('Toplu Bölüm & Bakım', 'anizen'), __('Toplu Bölüm & Bakım', 'anizen'), 'edit_posts', 'anizen-bulk', 'anizen_bulk_page');
});

function anizen_bulk_page() {
    if (!current_user_can('edit_posts')) return;
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Toplu Bölüm Ekle', 'anizen'); ?></h1>
        <?php if (isset($_GET['created'])) : ?>
            <div class="notice notice-success is-dismissible"><p><?php printf(esc_html__('%1$d bölüm oluşturuldu, %2$d bölüm zaten var olduğu için atlandı.', 'anizen'), (int) $_GET['created'], isset($_GET['skipped']) ? (int) $_GET['skipped'] : 0); ?></p></div>
        <?php elseif (isset($_GET['bulk_error'])) : ?>
            <div class="notice notice-error"><p><?php esc_html_e('Anime seçilmedi veya bölüm aralığı geçersiz.', 'anizen'); ?></p></div>
        <?php endif; ?>
        <?php if (isset($_GET['tr_done'])) : ?>
            <div class="notice notice-success is-dismissible"><p><?php printf(esc_html__('%d tür Türkçeleştirildi / birleştirildi.', 'anizen'), (int) $_GET['tr_done']); ?></p></div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="anizen-box" style="max-width:820px;background:#fff;padding:16px;border:1px solid #c3c4c7">
            <input type="hidden" name="action" value="anizen_bulk">
            <?php wp_nonce_field('anizen_bulk'); ?>
            <div class="anizen-grid">
                <p class="wide"><strong><?php esc_html_e('Anime', 'anizen'); ?></strong><br><?php anizen_picker_field('anime_id', isset($_GET['anime_id']) ? absint($_GET['anime_id']) : 0); ?></p>
                <p><label><strong><?php esc_html_e('İlk bölüm no', 'anizen'); ?></strong><br><input type="number" name="from" value="1" min="0" class="widefat"></label></p>
                <p><label><strong><?php esc_html_e('Son bölüm no', 'anizen'); ?></strong><br><input type="number" name="to" value="12" min="0" class="widefat"></label></p>
                <p><label><strong><?php esc_html_e('Durum', 'anizen'); ?></strong><br>
                    <select name="status" class="widefat"><option value="publish"><?php esc_html_e('Yayınla', 'anizen'); ?></option><option value="draft"><?php esc_html_e('Taslak olarak kaydet', 'anizen'); ?></option></select></label></p>
                <p><label><strong><?php esc_html_e('Başlık biçimi', 'anizen'); ?></strong><br><input type="text" name="title_fmt" value="{anime} {n}. Bölüm" class="widefat"></label>
                    <span class="description">{anime} {n} {nn}</span></p>
                <p><label><strong><?php esc_html_e('Kaynak adı (isteğe bağlı)', 'anizen'); ?></strong><br><input type="text" name="src_name" class="widefat" placeholder="SIBNET"></label></p>
                <p><label><strong><?php esc_html_e('Kaynak türü', 'anizen'); ?></strong><br>
                    <select name="src_type" class="widefat"><option value="auto">Otomatik</option><option value="iframe">Iframe / sayfa URL</option><option value="mp4">MP4 / WebM</option><option value="hls">HLS (.m3u8)</option></select></label></p>
                <p class="wide"><label><strong><?php esc_html_e('Kaynak adresi şablonu (isteğe bağlı)', 'anizen'); ?></strong><br>
                    <input type="text" name="src_tpl" class="widefat" placeholder="https://ornek.com/embed/naruto-{nn}"></label>
                    <span class="description"><?php esc_html_e('{n} = 5, {nn} = 05. Boş bırakırsan bölümler kaynaksız oluşturulur; sonra tek tek doldurabilirsin.', 'anizen'); ?></span></p>
            </div>
            <p><button type="submit" class="button button-primary"><?php esc_html_e('Bölümleri Oluştur', 'anizen'); ?></button>
            <span class="description"><?php esc_html_e('Aynı numaralı bölüm varsa atlanır. Tek seferde en fazla 500 bölüm.', 'anizen'); ?></span></p>
        </form>

        <h2 style="margin-top:32px"><?php esc_html_e('Bakım', 'anizen'); ?></h2>
        <?php anizen_repair_section(); ?>
        <?php if (current_user_can('manage_categories')) : ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="anizen_genres_tr">
            <?php wp_nonce_field('anizen_genres_tr'); ?>
            <p><?php esc_html_e('AniList’ten gelen İngilizce tür adlarını (Action, Comedy…) Türkçeye çevirir; aynı tür zaten Türkçe varsa ikisini birleştirir.', 'anizen'); ?></p>
            <p><button type="submit" class="button"><?php esc_html_e('Türleri Türkçeleştir', 'anizen'); ?></button></p>
        </form>
        <?php endif; ?>
    </div>
    <?php
}

add_action('admin_post_anizen_bulk', function () {
    if (!current_user_can('publish_posts')) wp_die(esc_html__('Yetkiniz yok.', 'anizen'));
    check_admin_referer('anizen_bulk');
    global $wpdb;
    $back = admin_url('edit.php?post_type=anime&page=anizen-bulk');
    $aid = isset($_POST['anime_id']) ? absint($_POST['anime_id']) : 0;
    $from = isset($_POST['from']) ? (int) $_POST['from'] : -1;
    $to = isset($_POST['to']) ? (int) $_POST['to'] : -1;
    if (!$aid || get_post_type($aid) !== 'anime' || $from < 0 || $to < $from) {
        wp_safe_redirect(add_query_arg('bulk_error', 1, $back));
        exit;
    }
    $to = min($to, $from + 499);
    $status = (isset($_POST['status']) && $_POST['status'] === 'draft') ? 'draft' : 'publish';
    $fmt = isset($_POST['title_fmt']) && trim($_POST['title_fmt']) !== '' ? sanitize_text_field(wp_unslash($_POST['title_fmt'])) : '{anime} {n}. Bölüm';
    $name = isset($_POST['src_name']) ? sanitize_text_field(wp_unslash($_POST['src_name'])) : '';
    $stype = (isset($_POST['src_type']) && in_array($_POST['src_type'], ['auto', 'iframe', 'mp4', 'hls'], true)) ? $_POST['src_type'] : 'auto';
    $tpl = isset($_POST['src_tpl']) ? trim(wp_unslash($_POST['src_tpl'])) : '';
    $raw_ok = current_user_can('unfiltered_html');
    $title = get_the_title($aid);

    $exist = $wpdb->get_col($wpdb->prepare(
        "SELECT n.meta_value FROM {$wpdb->postmeta} n
         JOIN {$wpdb->postmeta} a ON a.post_id = n.post_id AND a.meta_key = '_episode_anime_id' AND a.meta_value = %s
         JOIN {$wpdb->posts} p ON p.ID = n.post_id AND p.post_status NOT IN ('trash','auto-draft')
         WHERE n.meta_key = '_episode_number'", (string) $aid
    ));
    $have = [];
    foreach ($exist as $e) $have[(string) (float) $e] = true;

    $created = 0; $skipped = 0;
    for ($n = $from; $n <= $to; $n++) {
        if (isset($have[(string) (float) $n])) { $skipped++; continue; }
        $meta = ['_episode_anime_id' => (string) $aid, '_episode_number' => (string) $n, '_episode_sort' => (string) (float) $n, '_episode_key' => (string) $n];
        if ($tpl !== '') {
            $code = str_replace(['{nn}', '{n}'], [sprintf('%02d', $n), (string) $n], $tpl);
            if (!$raw_ok) $code = wp_kses($code, anizen_embed_kses());
            $meta['_episode_sources'] = [['name' => $name, 'type' => $stype, 'code' => $code]];
        }
        $id = wp_insert_post([
            'post_type' => 'episode', 'post_status' => $status,
            'post_title' => str_replace(['{anime}', '{nn}', '{n}'], [$title, sprintf('%02d', $n), (string) $n], $fmt),
            'meta_input' => $meta,
        ]);
        if ($id && !is_wp_error($id)) $created++;
    }
    anizen_recount($aid);
    wp_safe_redirect(add_query_arg(['created' => $created, 'skipped' => $skipped], $back));
    exit;
});

add_action('admin_post_anizen_genres_tr', function () {
    if (!current_user_can('manage_categories')) wp_die(esc_html__('Yetkiniz yok.', 'anizen'));
    check_admin_referer('anizen_genres_tr');
    $done = 0;
    foreach (anizen_genre_tr() as $en => $tr) {
        $old = get_term_by('name', $en, 'genre');
        if (!$old) continue;
        $new = get_term_by('name', $tr, 'genre');
        if ($new && (int) $new->term_id !== (int) $old->term_id) {
            foreach ((array) get_objects_in_term($old->term_id, 'genre') as $pid) wp_set_object_terms((int) $pid, [(int) $new->term_id], 'genre', true);
            wp_delete_term($old->term_id, 'genre');
        } elseif (!$new) {
            wp_update_term($old->term_id, 'genre', ['name' => $tr, 'slug' => sanitize_title($tr)]);
        }
        $done++;
    }
    wp_safe_redirect(add_query_arg('tr_done', $done, admin_url('edit.php?post_type=anime&page=anizen-bulk')));
    exit;
});

/* ------------------------------------------------------------------
   Bölüm ↔ anime bağlantı onarımı
   Bölüm başlığı "Anime Adı 5. Bölüm" biçimindeyse, başlığın başındaki anime adından doğru animeyi bulur.
   - Bağlantısız / geçersiz ID'li bölümler  -> önerilen animeye bağlanır
   - Başlığı başka animeyi gösterenler      -> yalnızca işaretlenirse düzeltilir
------------------------------------------------------------------- */
function anizen_norm_title($s) {
    $s = html_entity_decode((string) $s, ENT_QUOTES, 'UTF-8');
    $s = preg_replace('/\s+/u', ' ', trim($s));
    return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
}

function anizen_repair_scan() {
    global $wpdb;
    $animes = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'anime' AND post_status NOT IN ('trash', 'auto-draft')");
    $valid = []; $list = [];
    foreach ((array) $animes as $a) {
        $valid[(int) $a->ID] = $a->post_title;
        $n = anizen_norm_title($a->post_title);
        if ($n !== '') $list[] = [(int) $a->ID, $n, strlen($n)];
    }
    /* Uzun ad önce: "Naruto Shippuden 5. Bölüm" -> "Naruto" değil "Naruto Shippuden" */
    usort($list, function ($x, $y) { return $y[2] <=> $x[2]; });

    $eps = $wpdb->get_results(
        "SELECT p.ID, p.post_title, p.post_status, m.meta_value aid FROM {$wpdb->posts} p
         LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_episode_anime_id'
         WHERE p.post_type = 'episode' AND p.post_status NOT IN ('trash', 'auto-draft') ORDER BY p.ID ASC"
    );
    $rows = []; $seen = []; $total = 0;
    foreach ((array) $eps as $e) {
        $eid = (int) $e->ID;
        if (isset($seen[$eid])) continue;
        $seen[$eid] = true;
        $total++;
        $cur = (int) $e->aid;
        $t = anizen_norm_title($e->post_title);
        $guess = 0;
        foreach ($list as $it) {
            if (strpos($t, $it[1] . ' ') === 0) { $guess = $it[0]; break; }
        }
        $kind = '';
        if (!$cur || !isset($valid[$cur])) $kind = $guess ? 'fix' : 'manual';
        elseif ($guess && $guess !== $cur) $kind = 'mismatch';
        if ($kind === '') continue;
        $rows[$eid] = ['id' => $eid, 'title' => $e->post_title, 'status' => $e->post_status, 'cur' => $cur, 'guess' => $guess, 'kind' => $kind];
    }
    return ['rows' => array_values($rows), 'valid' => $valid, 'total' => $total];
}

function anizen_repair_section() {
    if (isset($_GET['repaired'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(sprintf(__('%d bölümün anime bağlantısı onarıldı.', 'anizen'), (int) $_GET['repaired'])) . '</p></div>';
    }
    $scan = anizen_repair_scan();
    $rows = $scan['rows']; $valid = $scan['valid'];
    $c = ['fix' => 0, 'mismatch' => 0, 'manual' => 0];
    foreach ($rows as $r) $c[$r['kind']]++;
    echo '<div style="max-width:980px;background:#fff;padding:16px;border:1px solid #c3c4c7;margin-bottom:16px">';
    echo '<h3 style="margin-top:0">' . esc_html__('Bölüm ↔ Anime bağlantılarını denetle / onar', 'anizen') . '</h3>';
    echo '<p>' . esc_html(sprintf(__('%1$d bölüm tarandı.', 'anizen'), $scan['total'])) . '</p>';
    if (!$rows) {
        echo '<p style="color:#116329"><strong>' . esc_html__('Sorun bulunamadı: tüm bölümler geçerli bir animeye bağlı ve başlıklarıyla uyumlu.', 'anizen') . '</strong></p></div>';
        return;
    }
    echo '<ul style="list-style:disc;margin-left:20px">';
    echo '<li>' . esc_html(sprintf(__('Bağlantısız veya geçersiz ID’li, otomatik eşleşen: %d', 'anizen'), $c['fix'])) . '</li>';
    echo '<li>' . esc_html(sprintf(__('Bağlı ama başlığı başka animeyi gösteren: %d', 'anizen'), $c['mismatch'])) . '</li>';
    echo '<li>' . esc_html(sprintf(__('Bağlantısız ve başlıktan eşleşmeyen (elle düzeltilmeli): %d', 'anizen'), $c['manual'])) . '</li>';
    echo '</ul>';
    $labels = ['fix' => __('Bağlanacak', 'anizen'), 'mismatch' => __('Başlık farklı animeyi gösteriyor', 'anizen'), 'manual' => __('Elle düzelt', 'anizen')];
    echo '<table class="widefat striped" style="margin:10px 0"><thead><tr><th>' . esc_html__('Bölüm', 'anizen') . '</th><th>' . esc_html__('Durum', 'anizen') . '</th><th>' . esc_html__('Şu an bağlı', 'anizen') . '</th><th>' . esc_html__('Önerilen', 'anizen') . '</th></tr></thead><tbody>';
    foreach (array_slice($rows, 0, 150) as $r) {
        $cur = $r['cur'] ? (isset($valid[$r['cur']]) ? $valid[$r['cur']] . ' (ID ' . $r['cur'] . ')' : sprintf(__('Geçersiz ID %d', 'anizen'), $r['cur'])) : '—';
        $gs = $r['guess'] ? $valid[$r['guess']] . ' (ID ' . $r['guess'] . ')' : '—';
        echo '<tr><td><a href="' . esc_url(get_edit_post_link($r['id'])) . '">' . esc_html($r['title'] !== '' ? $r['title'] : '#' . $r['id']) . '</a> <small>#' . (int) $r['id'] . ' · ' . esc_html($r['status']) . '</small></td>'
            . '<td>' . esc_html($labels[$r['kind']]) . '</td><td>' . esc_html($cur) . '</td><td>' . esc_html($gs) . '</td></tr>';
    }
    echo '</tbody></table>';
    if (count($rows) > 150) echo '<p class="description">' . esc_html(sprintf(__('İlk 150 kayıt gösteriliyor (toplam %d).', 'anizen'), count($rows))) . '</p>';
    if (($c['fix'] + $c['mismatch']) > 0 && current_user_can('manage_options')) {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="anizen_repair">';
        wp_nonce_field('anizen_repair');
        echo '<p><label><input type="checkbox" name="fix_mismatch" value="1"> ' . esc_html__('Başlığı başka animeyi gösterenleri de başlığa göre düzelt (yalnızca başlıkları doğruysa işaretle)', 'anizen') . '</label></p>';
        echo '<p><button type="submit" class="button button-primary">' . esc_html__('Eşleşenleri Onar', 'anizen') . '</button> <span class="description">' . esc_html__('Elle düzelt satırlarına dokunulmaz. İşlemden önce veritabanı yedeği almanız önerilir.', 'anizen') . '</span></p>';
        echo '</form>';
    }
    echo '</div>';
}

add_action('admin_post_anizen_repair', function () {
    if (!current_user_can('manage_options')) wp_die(esc_html__('Yetkiniz yok.', 'anizen'));
    check_admin_referer('anizen_repair');
    if (function_exists('set_time_limit')) @set_time_limit(0);
    $with_mm = !empty($_POST['fix_mismatch']);
    $scan = anizen_repair_scan();
    $fixed = 0; $touched = [];
    foreach ($scan['rows'] as $r) {
        if (!$r['guess']) continue;
        if ($r['kind'] !== 'fix' && !($r['kind'] === 'mismatch' && $with_mm)) continue;
        anizen_ep_log($r['id'], 'onarım aracı: anime ' . (int) $r['cur'] . ' → ' . (int) $r['guess']);
        update_post_meta($r['id'], '_episode_anime_id', (string) $r['guess']);
        $raw = (string) get_post_meta($r['id'], '_episode_number', true);
        if ($raw === '' && preg_match('/(\d+(?:[.,]\d+)?)\s*\.?\s*(?:b[öo]l[üu]m)?\s*$/iu', html_entity_decode((string) $r['title'], ENT_QUOTES, 'UTF-8'), $m)) $raw = $m[1];
        if ($raw !== '') anizen_ep_save_meta($r['id'], $r['guess'], $raw);
        if ($r['cur']) $touched[$r['cur']] = true;
        $touched[$r['guess']] = true;
        $fixed++;
    }
    foreach (array_keys($touched) as $a) anizen_recount((int) $a);
    wp_safe_redirect(add_query_arg('repaired', $fixed, admin_url('edit.php?post_type=anime&page=anizen-bulk')));
    exit;
});
