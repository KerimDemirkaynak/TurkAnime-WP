<?php
if (!defined('ABSPATH')) exit;

function anizen_sanitize_checkbox($v) { return empty($v) ? 0 : 1; }
function anizen_sanitize_code($v) { return current_user_can('unfiltered_html') ? (string) $v : wp_kses_post((string) $v); }

add_action('customize_register', function ($wp_customize) {
    $d = anizen_defaults();

    $wp_customize->add_panel('anizen', ['title' => __('TürkAnime Tema Ayarları', 'anizen'), 'priority' => 30]);
    $sections = [
        'colors'  => __('Renkler & Görünüm', 'anizen'),
        'header'  => __('Üst Bilgi', 'anizen'),
        'home'    => __('Ana Sayfa', 'anizen'),
        'archive' => __('Liste & Arşiv Sayfaları', 'anizen'),
        'player'  => __('Oynatıcı', 'anizen'),
        'comments' => __('Yorumlar', 'anizen'),
        'footer'  => __('Alt Bilgi & Sosyal', 'anizen'),
        'advanced' => __('Gelişmiş (SEO, Kod)', 'anizen'),
    ];
    $prio = 10;
    foreach ($sections as $id => $title) {
        $wp_customize->add_section('anizen_' . $id, ['title' => $title, 'panel' => 'anizen', 'priority' => $prio++]);
    }

    // [id, section, label, type, choices|null, description]
    $fields = [
        ['accent', 'colors', 'Vurgu rengi', 'color'],
        ['accent2', 'colors', 'İkinci vurgu rengi (gradyan)', 'color'],
        ['bg_dark', 'colors', 'Koyu tema: arka plan', 'color'],
        ['surface_dark', 'colors', 'Koyu tema: kart / panel rengi', 'color'],
        ['bg_light', 'colors', 'Açık tema: arka plan', 'color'],
        ['surface_light', 'colors', 'Açık tema: kart / panel rengi', 'color'],
        ['default_mode', 'colors', 'Varsayılan tema', 'select', ['dark' => 'Koyu', 'light' => 'Açık', 'auto' => 'Cihaza göre']],
        ['font', 'colors', 'Yazı tipi', 'select', ['system' => 'Sistem (en hızlı)', 'inter' => 'Inter', 'poppins' => 'Poppins', 'nunito' => 'Nunito', 'roboto' => 'Roboto'], 'Google Fonts seçeneklerinde dış yazı tipi yüklenir.'],
        ['radius', 'colors', 'Köşe yuvarlaklığı', 'select', ['4' => 'Keskin', '8' => 'Hafif', '12' => 'Orta', '16' => 'Yuvarlak', '22' => 'Çok yuvarlak']],
        ['container', 'colors', 'İçerik genişliği', 'select', ['1140' => '1140 px', '1320' => '1320 px', '1490' => '1490 px (TürkAnime)', '1600' => '1600 px']],
        ['columns', 'colors', 'Kart sütunu (geniş ekran)', 'select', ['4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8']],

        ['sticky_header', 'header', 'Üst menü kaydırınca sabit kalsın', 'checkbox'],
        ['nav_random', 'header', '“Rastgele” butonunu göster', 'checkbox'],
        ['announce_text', 'header', 'Duyuru çubuğu metni (boşsa gizli)', 'text'],
        ['announce_url', 'header', 'Duyuru bağlantısı', 'url'],

        ['brand_show', 'home', 'Logo + harf çubuğunu göster (ana sayfa ve liste sayfaları)', 'checkbox'],
        ['hero_enable', 'home', 'Yuvarlak vitrin (daire resimler) göster', 'checkbox'],
        ['hero_source', 'home', 'Vitrin içeriği', 'select', ['featured' => 'Vitrine eklenenler (yoksa son eklenenler)', 'popular' => 'En popülerler', 'updated' => 'Son güncellenenler', 'latest' => 'Son eklenenler', 'random' => 'Rastgele']],
        ['hero_count', 'home', 'Vitrinde kaç anime', 'number', ['min' => 1, 'max' => 12]],
        ['board_enable', 'home', 'Duyuru panosu (sağ sütun) göster — içerik: Yazılar', 'checkbox'],
        ['board_title', 'home', 'Duyuru panosu başlığı', 'text'],
        ['board_count', 'home', 'Duyuru panosu kaç yazı', 'number', ['min' => 1, 'max' => 20]],
        ['sec_day_enable', 'home', '“Günün Animesi / En Çok İzlenen / En Yüksek Puan” kutusu', 'checkbox'],
        ['continue_enable', 'home', '“İzlemeye devam et” satırı (ziyaretçinin tarayıcısından)', 'checkbox'],
        ['home_html', 'home', 'Vitrinin altına özel HTML bloğu', 'textarea'],
        ['sec_latest_enable', 'home', 'Son bölümler bölümü', 'checkbox'],
        ['sec_latest_title', 'home', 'Son bölümler başlığı', 'text'],
        ['sec_latest_count', 'home', 'Son bölümler adedi', 'number', ['min' => 6, 'max' => 48]],
        ['sec_popular_enable', 'home', 'Popüler animeler bölümü', 'checkbox'],
        ['sec_popular_title', 'home', 'Popüler animeler başlığı', 'text'],
        ['sec_popular_count', 'home', 'Popüler animeler adedi', 'number', ['min' => 6, 'max' => 48]],
        ['sec_new_enable', 'home', 'Yeni eklenen animeler bölümü', 'checkbox'],
        ['sec_new_title', 'home', 'Yeni eklenenler başlığı', 'text'],
        ['sec_new_count', 'home', 'Yeni eklenenler adedi', 'number', ['min' => 6, 'max' => 48]],
        ['sec_genres_enable', 'home', 'Türler bölümü', 'checkbox'],
        ['sec_genres_title', 'home', 'Türler başlığı', 'text'],

        ['per_page', 'archive', 'Sayfa başına anime', 'number', ['min' => 6, 'max' => 100]],
        ['show_filters', 'archive', 'Süzgeç çubuğunu (yıl, durum, sıralama…) göster', 'checkbox'],

        ['player_autoload', 'player', 'Oynatıcı sayfa açılınca otomatik yüklensin', 'checkbox'],
        ['player_autonext', 'player', 'Video bitince sonraki bölüme geç (yalnızca MP4/HLS kaynaklarda çalışır)', 'checkbox'],
        ['player_remember', 'player', 'Ziyaretçinin son seçtiği sunucuyu hatırla', 'checkbox'],
        ['player_cinema', 'player', 'Sinema modu varsayılan açık', 'checkbox'],
        ['player_report', 'player', '“Hata bildir” butonunu göster', 'checkbox'],
        ['report_email', 'player', 'Hata bildirimi e-postası (boşsa yönetici e-postasına gider)', 'text'],
        ['player_ratio', 'player', 'Oynatıcı oranı', 'select', ['16/9' => '16:9', '21/9' => '21:9 (geniş)', '4/3' => '4:3']],
        ['player_notice', 'player', 'Oynatıcı altı bilgi metni', 'textarea'],

        ['comments_collapsed', 'comments', 'Bölüm sayfasında yorumlar “Yorumları Görüntüle” butonunun arkasında olsun', 'checkbox'],
        ['ep_info_html', 'player', 'Bölüm sayfası bilgi kutusu (HTML)', 'textarea'],
        ['ep_notice_html', 'player', 'Bölüm sayfası “DİKKAT” kutusu ({fansub} kullanılabilir; boşsa gizli)', 'textarea'],
        ['fansub_label', 'player', 'Kartlardaki grup etiketi', 'text'],
        ['comments_spoiler', 'comments', '“Spoiler içerir” seçeneği ve [spoiler] etiketi', 'checkbox'],
        ['comments_likes', 'comments', 'Yorum beğenisi', 'checkbox'],

        ['footer_text', 'footer', 'Telif metni ({year} ve {site} kullanılabilir)', 'textarea'],
        ['footer_disclaimer', 'footer', 'Alt bilgi uyarı metni', 'textarea'],
        ['social_discord', 'footer', 'Discord bağlantısı', 'url'],
        ['social_telegram', 'footer', 'Telegram bağlantısı', 'url'],
        ['social_twitter', 'footer', 'X / Twitter bağlantısı', 'url'],
        ['social_instagram', 'footer', 'Instagram bağlantısı', 'url'],
        ['social_youtube', 'footer', 'YouTube bağlantısı', 'url'],
        ['totop', 'footer', '“Yukarı çık” butonu', 'checkbox'],
        ['show_auth', 'header', 'Üst menüde Üye Girişi / Kayıt Ol bağlantıları', 'checkbox'],
        ['show_genres_panel', 'footer', '“Ne Çıkarsa Bahtıma!” tür kutusu (sayfa altı)', 'checkbox'],

        ['seo_meta', 'advanced', 'Otomatik meta açıklama + Open Graph (SEO eklentisi yoksa)', 'checkbox'],
        ['schema', 'advanced', 'Yapılandırılmış veri (JSON-LD)', 'checkbox'],
        ['head_code', 'advanced', '<head> içine kod (ör. analitik)', 'code'],
        ['footer_code', 'advanced', 'Sayfa sonuna kod', 'code'],
    ];

    foreach ($fields as $f) {
        $id = $f[0];
        $type = $f[3];
        $extra = isset($f[4]) ? $f[4] : null;
        $desc = isset($f[5]) ? $f[5] : '';
        $setting = 'anizen_' . $id;
        $default = isset($d[$id]) ? $d[$id] : '';

        switch ($type) {
            case 'color':    $san = 'sanitize_hex_color'; break;
            case 'checkbox': $san = 'anizen_sanitize_checkbox'; break;
            case 'url':      $san = 'esc_url_raw'; break;
            case 'number':   $san = 'absint'; break;
            case 'textarea': $san = 'wp_kses_post'; break;
            case 'code':     $san = 'anizen_sanitize_code'; break;
            case 'select':
                $choices = $extra;
                $san = function ($v) use ($choices, $default) { return isset($choices[$v]) ? $v : $default; };
                break;
            default:         $san = 'sanitize_text_field';
        }
        $wp_customize->add_setting($setting, ['default' => $default, 'sanitize_callback' => $san, 'transport' => 'refresh']);

        $args = ['label' => __($f[2], 'anizen'), 'description' => $desc ? __($desc, 'anizen') : '', 'section' => 'anizen_' . $f[1], 'settings' => $setting];
        if ($type === 'color') {
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, $setting, $args));
        } else {
            $args['type'] = ($type === 'code') ? 'textarea' : $type;
            if ($type === 'select') $args['choices'] = $extra;
            if ($type === 'number' && is_array($extra)) $args['input_attrs'] = $extra;
            $wp_customize->add_control($setting, $args);
        }
    }
});
