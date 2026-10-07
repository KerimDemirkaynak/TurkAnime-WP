# TürkAnime — WordPress Anime Teması

WordPress üzerinde anime ve dizi arşivi oluşturmak için geliştirilmiş bir tema.

[![Latest Release](https://img.shields.io/github/v/release/KerimDemirkaynak/TurkAnime-WP?label=Sürüm&color=blue)](https://github.com/KerimDemirkaynak/TurkAnime-WP/releases)
[![License](https://img.shields.io/github/license/KerimDemirkaynak/TurkAnime-WP?label=Lisans)](https://github.com/KerimDemirkaynak/TurkAnime-WP/blob/main/LICENSE)
[![GitHub Release Date](https://img.shields.io/github/release-date/KerimDemirkaynak/TurkAnime-WP?label=Yayın%20Tarihi)](https://github.com/KerimDemirkaynak/TurkAnime-WP/releases)
[![GitHub Stars](https://img.shields.io/github/stars/KerimDemirkaynak/TurkAnime-WP?label=Yıldız)](https://github.com/KerimDemirkaynak/TurkAnime-WP/stargazers)
[![GitHub Issues](https://img.shields.io/github/issues/KerimDemirkaynak/TurkAnime-WP?label=Issues)](https://github.com/KerimDemirkaynak/TurkAnime-WP/issues)

<img width="1919" height="1079" alt="Ekran görüntüsü 2026-10-06 154532" src="https://github.com/user-attachments/assets/0ef83a74-2740-42a1-aa63-4283a30d4c78" />


Bu tema hazırlanırken, artık aktif olmayan **TürkAnime** sitesinin kullandığı arayüz ve kullanım deneyiminden önemli ölçüde esinlenilmiştir. Özellikle anime listeleme, anime detay sayfaları, bölüm yapısı, video oynatıcı ve kullanıcı tarafındaki bazı özellikler bu yapıya yakın tutulmuştur. Bununla birlikte tema, eski sitenin birebir kopyası olmak yerine WordPress'e uygun yeni bir altyapı üzerine kurulmuştur.

Temel amacı, klasik bir WordPress blogunu anime sitesine çevirmekten ziyade; animelerin, bölümlerin, video kaynaklarının ve kullanıcı özelliklerinin ayrı ayrı yönetilebildiği bir anime arşiv sistemi sunmaktır.

## Özellikler

### Anime ve bölüm sistemi

Animeler ve bölümler WordPress'in özel içerik türleri kullanılarak birbirinden ayrı yönetilir.

* Anime ve bölüm içerikleri ayrı tutulur.
* Bölümler ilgili animeyle ilişkilendirilebilir.
* Bölüm isimleri belirlenen şablona göre otomatik oluşturulabilir.
* Animeye ait bilgiler tek bir yerden yönetilebilir.
* Bölüm sayısı arttıkça içerikleri tek tek oluşturmak zorunda kalmazsınız.

### Video oynatıcı

Bir bölüm için birden fazla video kaynağı eklenebilir.

<img width="1919" height="1079" alt="Ekran görüntüsü 2026-10-06 154608" src="https://github.com/user-attachments/assets/1d76d33a-07f7-4d37-953e-9f537100094f" />

Desteklenen kaynaklar:

* HLS (`.m3u8`)
* MP4
* WebM
* Iframe
* Embed kodları

Bir bölüme istediğiniz kadar kaynak ekleyebilir ve bunların sırasını sürükle-bırak ile değiştirebilirsiniz.

Ayrıca oynatma sırasında:

* Sonraki bölüme otomatik geçiş
* Sinema modu
* Birden fazla sunucu seçeneği

gibi özellikler kullanılabilir.

### AniList entegrasyonu

Anime eklerken AniList üzerinden bilgi çekilebilir.

Anime adı veya AniList ID'si girilip **"AniList'ten Çek"** seçeneği kullanıldığında uygun bilgiler otomatik olarak doldurulur.

Örneğin:

* Kapak görseli
* Banner
* Puan
* Bölüm sayısı
* Yayın yılı
* Stüdyo bilgileri
* Türler

Anime türleri AniList'ten İngilizce geldiğinde, tema bunları Türkçe karşılıklarıyla gösterebilir.

### Üyelik ve kullanıcı özellikleri

Tema yalnızca içerik göstermeye değil, kullanıcı tarafındaki özelliklere de odaklanır.

Kullanıcılar:

* Animeyi favorilerine ekleyebilir.
* Animeleri kendi listelerine ekleyebilir.
* Bölüm ve anime içeriklerini beğenebilir.
* İzleme geçmişlerini görebilir.
* Yarım bıraktıkları animelere kaldıkları yerden devam edebilir.
* Diğer kullanıcıların yorumlarını beğenebilir.

İzleme geçmişi sayesinde kullanıcı daha önce izlediği animeleri ana sayfada veya profilinde görebilir.

### Toplu bölüm ekleme

Çok bölümlü animelerde bölümleri tek tek oluşturmak yerine toplu olarak ekleyebilirsiniz.

Örneğin 1–100 arasındaki bölümleri oluşturmak istediğinizde başlangıç ve bitiş numarasını girmeniz yeterlidir. Tema bölümleri otomatik olarak oluşturur.

Bölüm adları da belirlenen şablona göre oluşturulabilir:

`Anime Adı 1. Bölüm`
`Anime Adı 2. Bölüm`
`Anime Adı 3. Bölüm`

şeklinde devam eder.

### Hata bildirme

Bir videonun çalışmadığını fark eden ziyaretçiler bölüm sayfasından **"Video Bozuk"** bildirimi gönderebilir.

Gönderilen bildirimler yönetici panelinden takip edilebilir. İstenirse e-posta bildirimi de kullanılabilir.

Bu sayede özellikle çok sayıda bölüm ve video kaynağı bulunan sitelerde bozuk bağlantıları takip etmek kolaylaşır.

### Bakım ve onarım

Tema, anime ve bölüm arasındaki bağlantıların bozulabileceği durumlar için basit bir onarım aracı içerir.

Örneğin yanlışlıkla bir anime silinip daha sonra yeniden oluşturulduğunda veya bölüm-anime ilişkilerinde sorun oluştuğunda, mevcut isimlerden yararlanılarak bağlantıların yeniden kurulması denenebilir.

### Performans ve SEO

Tema mümkün olduğunca gereksiz sorgulardan ve işlemlerden kaçınacak şekilde hazırlanmıştır.

İzlenme sayaçları AJAX kullanılarak çalıştırılabilir ve önbellekleme sistemleriyle daha uyumlu kullanılabilir.

Ayrıca temel SEO ihtiyaçları için temiz HTML yapısı ve uygun meta bilgileri kullanılmaktadır. Bunun amacı, siteyi çalıştırmak için gereksiz sayıda eklentiye ihtiyaç duyulmamasıdır.

---

## Kurulum

1. GitHub deposundan temanın `.zip` dosyasını indirin.
2. WordPress yönetim paneline (`/wp-admin`) giriş yapın.
3. **Görünüm > Temalar > Yeni Ekle** bölümüne gidin.
4. **Tema Yükle** seçeneğine tıklayın.
5. İndirdiğiniz `.zip` dosyasını seçin.
6. **Hemen Kur** seçeneğine tıklayın.
7. Kurulum tamamlandıktan sonra temayı etkinleştirin.

### Gereksinimler

* WordPress 5.8 veya üzeri
* PHP 7.4 veya üzeri

Daha yeni PHP ve WordPress sürümleri önerilir.

---

## Kullanım

Tema etkinleştirildikten sonra WordPress yönetim panelinde anime sitesi için gerekli yeni bölümler görünür.

### Anime ekleme

**Animeler > Yeni Ekle** bölümünden yeni bir anime oluşturabilirsiniz.

AniList ID'sini girerek anime bilgilerini otomatik olarak çekebilir; fansub, dil, yayın yılı, fragman ve diğer bilgileri düzenleyebilirsiniz.

### Bölüm ekleme

**Bölümler > Yeni Ekle** bölümünden bir bölüm oluşturabilirsiniz.

İlgili animeyi seçtikten sonra bölüm numarasını girin ve **Video Kaynakları** bölümünden kullanacağınız Iframe, M3U8 veya diğer video kaynaklarını ekleyin.

### Toplu bölüm oluşturma

Çok sayıda bölüm eklemek için:

**Animeler > Toplu Bölüm & Bakım**

menüsüne gidin.

Buradan hangi anime için işlem yapılacağını ve kaçıncı bölümden kaçıncı bölüme kadar içerik oluşturulacağını belirleyebilirsiniz.

### Kullanıcı listeleri

Tema tarafından sağlanan `/listem/` sayfası üzerinden kullanıcılar kendi listelerine ekledikleri animeleri görebilir.

Tema ayrıca WordPress özelleştiricisi üzerinden bazı görünüm ayarlarının değiştirilmesine izin verir. Koyu ve açık renk seçenekleri de buradan düzenlenebilir.

---

## TürkAnime'den Esinlenme

Bu projenin arayüz tasarımında ve bazı kullanıcı deneyimi kararlarında, artık aktif olmayan **TürkAnime** sitesinden esinlenilmiştir.

TürkAnime'nin anime listeleme biçimi, anime detay sayfaları, bölüm düzeni ve genel site kullanımı bu temanın tasarımında referans alınan başlıca noktalar arasındadır.

Ancak bu proje eski sitenin dosyalarının veya altyapısının doğrudan devamı değildir. WordPress için sıfırdan geliştirilen, farklı özellikler ve ayrı bir veri yapısı kullanan bağımsız bir temadır.

---

## Lisans

Bu tema **GNU General Public License v2 or later (GPL-2.0+)** kapsamında dağıtılan açık kaynaklı bir projedir.

GPL lisansı kapsamında temayı kişisel veya ticari projelerinizde kullanabilir, değiştirebilir ve geliştirebilirsiniz.
