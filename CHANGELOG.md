# Changelog

Tüm önemli değişiklikler bu dosyada belgelenir.

Format [Keep a Changelog](https://keepachangelog.com/tr/1.0.0/) standardına uygundur.

## [1.0.0] - 2024-12-16

### Eklendi
- 🎨 8 interaktif tema (Doğum günü, Yılbaşı, Sevgililer, Cadılar Bayramı, Bebek Görme, Mezuniyet, Veda, Geçmiş Olsun)
- 🎁 Her tema için 8 hediye seçeneği (toplam 64 hediye)
- 🔒 Zamanlı hediye açılış sistemi
- 📱 Tam mobil ve tablet uyumluluk
- 🔐 CSRF koruması ve rate limiting
- 🧮 Matematik tabanlı CAPTCHA sistemi
- 📤 Sosyal medya paylaşım butonları (WhatsApp, X, Instagram, Telegram, LinkedIn)
- 🎯 Tema bazlı hediye konumlandırma (drop zones)
- ⚡ Responsive position boundaries (cihaz boyutuna göre)

### Güvenlik
- Security headers eklendi (CSP, X-Frame-Options, X-Content-Type-Options)
- .htaccess ile hassas dosya koruması
- Input sanitization helper fonksiyonları
- Session güvenlik ayarları

### Dokümantasyon
- README.md eklendi
- Kurulum talimatları
- Proje yapısı açıklaması
- Katkıda bulunma rehberi

---

## [Unreleased]

### Planlanıyor
- [ ] Admin paneli
- [ ] Kullanıcı kayıt sistemi
- [ ] Özel tema oluşturma
- [ ] E-posta bildirimleri
- [ ] QR kod oluşturma
