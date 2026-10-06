<?php
if (!defined('ABSPATH')) exit;

/** AniList tür adları → Türkçe */
function anizen_genre_tr() {
    return [
        'Action' => 'Aksiyon', 'Adventure' => 'Macera', 'Comedy' => 'Komedi', 'Drama' => 'Dram', 'Ecchi' => 'Ecchi',
        'Fantasy' => 'Fantastik', 'Hentai' => 'Yetişkin', 'Horror' => 'Korku', 'Mahou Shoujo' => 'Büyülü Kız', 'Mecha' => 'Mecha',
        'Music' => 'Müzik', 'Mystery' => 'Gizem', 'Psychological' => 'Psikolojik', 'Romance' => 'Romantizm', 'Sci-Fi' => 'Bilim Kurgu',
        'Slice of Life' => 'Günlük Yaşam', 'Sports' => 'Spor', 'Supernatural' => 'Doğaüstü', 'Thriller' => 'Gerilim',
    ];
}

function anizen_anilist_request($query, $vars) {
    $res = wp_remote_post('https://graphql.anilist.co', [
        'timeout' => 15,
        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        'body'    => wp_json_encode(['query' => $query, 'variables' => $vars]),
    ]);
    if (is_wp_error($res)) return $res;
    $code = (int) wp_remote_retrieve_response_code($res);
    $body = json_decode(wp_remote_retrieve_body($res), true);
    if ($code === 429) return new WP_Error('rate', __('AniList istek sınırına ulaşıldı; bir dakika sonra tekrar dene.', 'anizen'));
    if ($code >= 400 || !is_array($body) || !empty($body['errors'])) {
        $m = isset($body['errors'][0]['message']) ? $body['errors'][0]['message'] : sprintf(__('AniList hatası (%d)', 'anizen'), $code);
        return new WP_Error('anilist', $m);
    }
    return isset($body['data']) ? $body['data'] : [];
}

function anizen_anilist_media($id) {
    $id = absint($id);
    if (!$id) return new WP_Error('id', __('Geçersiz AniList ID', 'anizen'));
    $key = 'anizen_al_' . $id;
    $cached = get_transient($key);
    if ($cached) return $cached;
    $q = 'query($id:Int){Media(id:$id,type:ANIME){id title{romaji english native} description(asHtml:false) format status episodes duration seasonYear startDate{year} averageScore genres studios{nodes{name isAnimationStudio}} coverImage{extraLarge large} bannerImage trailer{id site}}}';
    $d = anizen_anilist_request($q, ['id' => $id]);
    if (is_wp_error($d)) return $d;
    if (empty($d['Media'])) return new WP_Error('nf', __('AniList’te bu ID bulunamadı.', 'anizen'));
    set_transient($key, $d['Media'], 6 * HOUR_IN_SECONDS);
    return $d['Media'];
}

/** AniList verisini form alanlarına uygun hale getirir (kaydetmez; alanlar tarayıcıda doldurulur). */
function anizen_anilist_normalize($m, $title_mode = 'romaji') {
    $t = isset($m['title']) ? $m['title'] : [];
    $romaji = isset($t['romaji']) ? (string) $t['romaji'] : '';
    $english = isset($t['english']) ? (string) $t['english'] : '';
    $native = isset($t['native']) ? (string) $t['native'] : '';
    $order = ['english' => [$english, $romaji, $native], 'native' => [$native, $romaji, $english]];
    $cands = isset($order[$title_mode]) ? $order[$title_mode] : [$romaji, $english, $native];
    $main = '';
    foreach ($cands as $c) if ($c !== '') { $main = $c; break; }
    $alts = [];
    foreach ([$english, $romaji, $native] as $c) if ($c !== '' && $c !== $main && !in_array($c, $alts, true)) $alts[] = $c;

    $desc = isset($m['description']) ? (string) $m['description'] : '';
    $desc = preg_replace('~<br\s*/?>~i', "\n", $desc);
    $desc = preg_replace('#~!.*?!~#s', '', $desc);
    $desc = trim(preg_replace("~\n{3,}~", "\n\n", wp_strip_all_tags($desc)));

    $tr = anizen_genre_tr();
    $genres = [];
    foreach ((array) ($m['genres'] ?? []) as $g) $genres[] = isset($tr[$g]) ? $tr[$g] : $g;
    $studios = [];
    foreach ((array) ($m['studios']['nodes'] ?? []) as $s) if (!empty($s['isAnimationStudio']) && !empty($s['name'])) $studios[] = $s['name'];

    $fmt = ['TV' => 'tv', 'TV_SHORT' => 'tv', 'MOVIE' => 'movie', 'SPECIAL' => 'special', 'OVA' => 'ova', 'ONA' => 'ona', 'MUSIC' => 'music'];
    $trailer = (!empty($m['trailer']['id']) && isset($m['trailer']['site']) && $m['trailer']['site'] === 'youtube') ? $m['trailer']['id'] : '';
    $year = !empty($m['seasonYear']) ? $m['seasonYear'] : (isset($m['startDate']['year']) ? $m['startDate']['year'] : '');

    return [
        'id' => (string) $m['id'], 'title' => $main, 'alt' => implode(' / ', $alts), 'desc' => $desc,
        'year' => $year ? (string) $year : '',
        'score' => !empty($m['averageScore']) ? number_format($m['averageScore'] / 10, 1, '.', '') : '',
        'status' => anizen_normalize_status(isset($m['status']) ? $m['status'] : ''),
        'type' => isset($fmt[$m['format'] ?? '']) ? $fmt[$m['format']] : '',
        'eps' => !empty($m['episodes']) ? (string) $m['episodes'] : '',
        'duration' => !empty($m['duration']) ? (string) $m['duration'] : '',
        'cover' => isset($m['coverImage']['extraLarge']) ? (string) $m['coverImage']['extraLarge'] : '',
        'banner' => isset($m['bannerImage']) ? (string) $m['bannerImage'] : '',
        'trailer' => $trailer, 'genres' => $genres, 'studios' => $studios,
    ];
}

/* ---- AJAX: arama + getir (yalnızca düzenleme yetkisi olanlar) ---- */
add_action('wp_ajax_anizen_anilist_search', function () {
    check_ajax_referer('anizen_admin', 'nonce');
    if (!current_user_can('edit_posts')) wp_send_json_error(['message' => 'Yetkisiz'], 403);
    $s = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
    if (mb_strlen($s) < 2) wp_send_json_success([]);
    $key = 'anizen_als_' . md5(mb_strtolower($s));
    $out = get_transient($key);
    if (!$out) {
        $q = 'query($s:String){Page(perPage:6){media(search:$s,type:ANIME,sort:SEARCH_MATCH){id title{romaji english} format seasonYear coverImage{medium}}}}';
        $d = anizen_anilist_request($q, ['s' => $s]);
        if (is_wp_error($d)) wp_send_json_error(['message' => $d->get_error_message()]);
        $out = [];
        foreach ((array) ($d['Page']['media'] ?? []) as $m) {
            $out[] = [
                'id' => (int) $m['id'],
                'title' => $m['title']['english'] ?: $m['title']['romaji'],
                'romaji' => $m['title']['romaji'],
                'meta' => trim(($m['format'] ?? '') . ' · ' . ($m['seasonYear'] ?? ''), ' ·'),
                'cover' => $m['coverImage']['medium'] ?? '',
            ];
        }
        set_transient($key, $out, HOUR_IN_SECONDS);
    }
    wp_send_json_success($out);
});

add_action('wp_ajax_anizen_anilist_fetch', function () {
    check_ajax_referer('anizen_admin', 'nonce');
    if (!current_user_can('edit_posts')) wp_send_json_error(['message' => 'Yetkisiz'], 403);
    $id = isset($_GET['id']) ? absint($_GET['id']) : 0;
    $mode = isset($_GET['mode']) ? sanitize_key(wp_unslash($_GET['mode'])) : 'romaji';
    $m = anizen_anilist_media($id);
    if (is_wp_error($m)) wp_send_json_error(['message' => $m->get_error_message()]);
    wp_send_json_success(anizen_anilist_normalize($m, $mode));
});
