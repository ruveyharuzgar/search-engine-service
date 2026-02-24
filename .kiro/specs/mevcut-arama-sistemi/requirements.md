# Gereksinimler Dokümanı - Mevcut Arama Sistemi

## Giriş

Bu doküman, mevcut arama motoru servisinin işlevselliğini ve gereksinimlerini belgeler. Sistem, çeşitli kaynaklardan (provider) içerik çeker, skorlar ve kullanıcılara arama sonuçları sunar.

## Sözlük

- **Arama_Motoru**: İçerik arama ve sonuç döndürme sistemi
- **İçerik**: Video veya makale türünde aranabilir veri
- **Provider**: İçerik sağlayıcı harici sistem (JSON veya XML)
- **Skor**: İçeriğin kalitesini ve alakasını gösteren sayısal değer
- **Cache**: Performans için geçici veri depolama mekanizması
- **Metrik**: İçerik istatistikleri (görüntülenme, beğeni, okuma süresi vb.)
- **Sayfalama**: Sonuçların sayfalara bölünmesi
- **Senkronizasyon**: Provider'lardan içeriklerin çekilip veritabanına kaydedilmesi

## Gereksinimler

### Gereksinim 1: Temel Arama İşlevi

**Kullanıcı Hikayesi:** Bir kullanıcı olarak, anahtar kelime ile içerik aramak istiyorum.

#### Kabul Kriterleri

1. WHEN bir kullanıcı anahtar kelime sağlar THEN Arama_Motoru başlık veya etiketlerde bu kelimeyi içeren içerikleri döndürmeli
2. WHEN anahtar kelime sağlanmaz THEN Arama_Motoru tüm içerikleri döndürmeli
3. THE Arama_Motoru arama yaparken büyük-küçük harf duyarlı olmamalı
4. WHEN arama sonucu bulunamazsa THEN sistem boş liste döndürmeli
5. THE Arama_Motoru kısmi eşleşmeleri desteklemeli

### Gereksinim 2: İçerik Türü Filtreleme

**Kullanıcı Hikayesi:** Bir kullanıcı olarak, sadece belirli türdeki içerikleri görmek istiyorum.

#### Kabul Kriterleri

1. WHEN bir kullanıcı "video" türü belirtir THEN Arama_Motoru yalnızca video içerikleri döndürmeli
2. WHEN bir kullanıcı "article" türü belirtir THEN Arama_Motoru yalnızca makale içerikleri döndürmeli
3. WHEN tür belirtilmez THEN Arama_Motoru tüm türlerdeki içerikleri döndürmeli
4. IF geçersiz tür belirtilirse THEN Arama_Motoru hata mesajı döndürmeli
5. THE desteklenen türler "video" ve "article" olmalı

### Gereksinim 3: Skor Hesaplama

**Kullanıcı Hikayesi:** Bir sistem olarak, içeriklerin kalitesini ve alakasını değerlendirmek için skor hesaplamak istiyorum.

#### Kabul Kriterleri

1. WHEN bir video içeriği skorlanır THEN sistem (görüntülenme / 1000) + (beğeni / 100) formülünü kullanmalı
2. WHEN bir makale içeriği skorlanır THEN sistem okuma_süresi + (tepki / 50) formülünü kullanmalı
3. WHEN skor hesaplanır THEN video içerikleri 1.5 katsayısı ile çarpılmalı
4. WHEN skor hesaplanır THEN makale içerikleri 1.0 katsayısı ile çarpılmalı
5. THE final skor (temel_skor * tür_katsayısı) + güncellik_puanı + etkileşim_puanı olmalı

### Gereksinim 4: Güncellik Puanı

**Kullanıcı Hikayesi:** Bir sistem olarak, yeni içeriklere daha yüksek öncelik vermek istiyorum.

#### Kabul Kriterleri

1. WHEN içerik 1 hafta içinde yayınlanmış ise THEN sistem +5 güncellik puanı eklemeli
2. WHEN içerik 1 ay içinde yayınlanmış ise THEN sistem +3 güncellik puanı eklemeli
3. WHEN içerik 3 ay içinde yayınlanmış ise THEN sistem +1 güncellik puanı eklemeli
4. WHEN içerik 3 aydan eski ise THEN sistem +0 güncellik puanı eklemeli
5. THE güncellik hesaplaması yayın tarihine göre yapılmalı

### Gereksinim 5: Etkileşim Puanı

**Kullanıcı Hikayesi:** Bir sistem olarak, kullanıcı etkileşimi yüksek içeriklere öncelik vermek istiyorum.

#### Kabul Kriterleri

1. WHEN video içeriği için etkileşim hesaplanır THEN sistem (beğeni / görüntülenme) * 10 formülünü kullanmalı
2. WHEN makale içeriği için etkileşim hesaplanır THEN sistem (tepki / okuma_süresi) * 5 formülünü kullanmalı
3. IF görüntülenme sıfır ise THEN sistem etkileşim puanını 0 olarak hesaplamalı
4. IF okuma süresi sıfır ise THEN sistem etkileşim puanını 0 olarak hesaplamalı
5. THE etkileşim puanı negatif olmamalı

### Gereksinim 6: Sıralama

**Kullanıcı Hikayesi:** Bir kullanıcı olarak, arama sonuçlarını farklı kriterlere göre sıralamak istiyorum.

#### Kabul Kriterleri

1. WHEN "score" sıralaması seçilir THEN Arama_Motoru sonuçları skordan yükseğe doğru sıralamalı
2. WHEN "date" sıralaması seçilir THEN Arama_Motoru sonuçları yayın tarihine göre yeniden eskiye sıralamalı
3. WHEN sıralama belirtilmez THEN Arama_Motoru varsayılan olarak "score" kullanmalı
4. IF geçersiz sıralama kriteri belirtilirse THEN Arama_Motoru varsayılan sıralamayı kullanmalı
5. THE sıralama tüm sonuçlara uygulanmalı

### Gereksinim 7: Sayfalama

**Kullanıcı Hikayesi:** Bir kullanıcı olarak, arama sonuçlarını sayfalara bölünmüş şekilde görmek istiyorum.

#### Kabul Kriterleri

1. WHEN sayfa numarası belirtilir THEN Arama_Motoru o sayfadaki sonuçları döndürmeli
2. WHEN sayfa başına sonuç sayısı belirtilir THEN Arama_Motoru o kadar sonuç döndürmeli
3. WHEN sayfalama parametreleri belirtilmez THEN sistem varsayılan olarak sayfa 1 ve sayfa başına 10 sonuç kullanmalı
4. THE Arama_Motoru toplam sonuç sayısını, toplam sayfa sayısını ve mevcut sayfayı döndürmeli
5. WHEN istenen sayfa mevcut değilse THEN sistem boş liste döndürmeli

### Gereksinim 8: Cache Yönetimi

**Kullanıcı Hikayesi:** Bir sistem olarak, performansı artırmak için arama sonuçlarını önbelleğe almak istiyorum.

#### Kabul Kriterleri

1. WHEN bir arama yapılır THEN Arama_Motoru önce Cache'i kontrol etmeli
2. WHEN arama sonucu Cache'de bulunur THEN sistem veritabanına gitmeden Cache'den döndürmeli
3. WHEN arama sonucu Cache'de bulunmaz THEN sistem veritabanından çekip Cache'e kaydetmeli
4. THE Cache anahtarı arama parametrelerinden (anahtar kelime, tür, sıralama, sayfa) oluşmalı
5. WHEN içerikler senkronize edilir THEN sistem tüm Cache'i temizlemeli

### Gereksinim 9: Provider Senkronizasyonu

**Kullanıcı Hikayesi:** Bir sistem yöneticisi olarak, harici kaynaklardan içerikleri çekip veritabanına kaydetmek istiyorum.

#### Kabul Kriterleri

1. WHEN senkronizasyon başlatılır THEN sistem tüm Provider'lardan içerikleri çekmeli
2. WHEN içerik çekilir THEN sistem her içeriği veritabanına kaydetmeli veya güncellemeli
3. WHEN senkronizasyon tamamlanır THEN sistem kaydedilen içerik sayısını döndürmeli
4. WHEN senkronizasyon başarılı olur THEN sistem bildirim gönderimeli
5. IF senkronizasyon başarısız olursa THEN sistem hata bildirimi göndermeli

### Gereksinim 10: JSON Provider Desteği

**Kullanıcı Hikayesi:** Bir sistem olarak, JSON formatındaki harici kaynaklardan içerik çekebilmek istiyorum.

#### Kabul Kriterleri

1. WHEN JSON Provider çağrılır THEN sistem belirtilen URL'den JSON verisi çekmeli
2. WHEN JSON verisi alınır THEN sistem her öğeyi ContentDTO'ya dönüştürmeli
3. IF JSON formatı geçersiz ise THEN sistem hata fırlatmalı
4. THE JSON Provider HTTP istemcisi kullanmalı
5. WHEN JSON alanları eksik ise THEN sistem varsayılan değerler kullanmalı

### Gereksinim 11: XML Provider Desteği

**Kullanıcı Hikayesi:** Bir sistem olarak, XML formatındaki harici kaynaklardan içerik çekebilmek istiyorum.

#### Kabul Kriterleri

1. WHEN XML Provider çağrılır THEN sistem belirtilen URL'den XML verisi çekmeli
2. WHEN XML verisi alınır THEN sistem her öğeyi ContentDTO'ya dönüştürmeli
3. IF XML formatı geçersiz ise THEN sistem hata fırlatmalı
4. THE XML Provider HTTP istemcisi kullanmalı
5. WHEN XML alanları eksik ise THEN sistem varsayılan değerler kullanmalı

### Gereksinim 12: Hata Yönetimi

**Kullanıcı Hikayesi:** Bir kullanıcı olarak, hata durumlarında anlaşılır mesajlar almak istiyorum.

#### Kabul Kriterleri

1. WHEN bir hata oluşur THEN Arama_Motoru HTTP 500 durum kodu döndürmeli
2. WHEN hata döndürülür THEN yanıt success: false içermeli
3. WHEN hata döndürülür THEN yanıt hata mesajı içermeli
4. THE hata mesajları kullanıcı dostu olmalı
5. THE sistem hataları loglara kaydetmeli

### Gereksinim 13: API Yanıt Formatı

**Kullanıcı Hikayesi:** Bir API tüketicisi olarak, tutarlı yanıt formatı almak istiyorum.

#### Kabul Kriterleri

1. WHEN başarılı arama yapılır THEN yanıt success: true içermeli
2. WHEN sonuçlar döndürülür THEN yanıt data dizisi içermeli
3. WHEN sonuçlar döndürülür THEN yanıt pagination bilgisi içermeli
4. THE her içerik id, title, type, metrics, published_at, tags ve score içermeli
5. THE pagination total, page, per_page ve total_pages içermeli
