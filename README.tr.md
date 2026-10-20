# 🌿 Aurivita · Yüksek Dönüşümlü WooCommerce Tek Ürün Teması

[🇬🇧 English](README.md) · [🇹🇷 Türkçe](README.tr.md)

> Tek ürüne odaklanan markalar için özel olarak tasarlanmış, doğrudan dönüşüm ve mobil kullanıcı deneyimini merkeze alan hafif WooCommerce teması.

Geliştirici ve Bakım Sorumlusu: **[Harun Sekmen](https://github.com/harunsekmen)** — E-Ticaret Sistem Mühendisi & WooCommerce Uzmanı.

[✉️ İletişime Geç](mailto:sekmenwp@gmail.com) · [💼 Upwork Profili](https://www.upwork.com/freelancers/~01a7a8b58043e6e6d9) · [🔗 GitHub Ana Profil](https://github.com/harunsekmen)

---

## 📸 Görsel Önizlemeler ve Yönetim Paneli

### 1. Tek Ürün Odaklı Açılış Sayfası
Ziyaretçiyi doğrudan ürünün faydalarına, bilimsel kanıtlarına ve sosyal kanıtlarına odaklayan modern tasarım:

![Aurivita Tek Ürün Sayfası Önizlemesi](docs/assets/aurivita-storefront-hero.png)

### 2. Tek Sayfada Hızlı Ödeme Akışı
Sepet adımını atlayarak doğrudan ödeme adımına geçen, dönüşüm kaybını sıfırlayan akıcı ödeme formu:

![Aurivita Ödeme Akışı Önizlemesi](docs/assets/aurivita-checkout-flow.png)

---

## ⚡ Çözdüğü Kritik Sorun

Tek bir amiral gemisi ürünü pazarlayan D2C markaları, standart çok ürünlü mağaza şablonlarında ciddi dönüşüm kaybı yaşar:
- **Dikkati Dağıtan Gezinti Menüleri:** Birden fazla kategori ve alakasız linkler müşterinin satın alma odağını dağıtır.
- **Çok Adımlı Sepet-Ödeme Süreci:** Ürünü sepete ekle, sepete git, ödemeye ilerle gibi gereksiz adımlar mobilde terk oranını %60'ın üzerine çıkarır.
- **Yetersiz Sosyal Kanıt ve Güven Alanları:** Standart temalarda yorumlar, sertifikalar ve içerik listesi için dönüşüm odaklı özel alanlar bulunmaz.

**Aurivita**, tüm sayfayı tek bir amiral gemisi ürünün satış hunisine dönüştürür. Açılış sayfası mantığıyla çalışan yapısı, ziyaretçiyi ikna eden modüllerle donatılmıştır.

---

## 🛠️ Öne Çıkan Özellikler

- **Tek Ürün Satış Hunisi:** Hero alanından ödeme formuna kadar tüm sayfa tek bir ürünün değer önerisini anlatır.
- **Dönüşüm Tetikleyicileri:** Müşteri yorumları karuseli, içerik/fayda karşılaştırma tablosu ve güven rozetleri yerel olarak entegredir.
- **Hızlı Mobil Ödeme:** Müşteriyi sepet sayfasında bekletmeden doğrudan ödeme formuna yönlendiren akıcı akış.
- **Sıfır Sayfa Oluşturucu Bağımlılığı:** Elementor veya WPBakery gibi ağır kütüphaneler olmadan saf ve hafif kod yapısı.

---

## 🏗️ Teknik Mimari ve Standartlar

`PHP 8.1+` • `WooCommerce Standartları` • `Vanilla JavaScript` • `Responsive CSS Grid/Flexbox`

---

## 🚀 Kurulum ve Test

1. Temayı sitenize kopyalayın:
   ```bash
   git clone https://github.com/harunsekmen/aurivita-single-product-theme.git wp-content/themes/aurivita
   ```
2. WP-CLI üzerinden etkinleştirin:
   ```bash
   wp theme activate aurivita
   ```
3. Test paketini çalıştırın:
   ```bash
   php tests/test-aurivita.php
   ```

---

## 📄 Lisans

Bu proje **GPL-3.0 Lisansı** ile açık kaynak olarak sunulmaktadır.
