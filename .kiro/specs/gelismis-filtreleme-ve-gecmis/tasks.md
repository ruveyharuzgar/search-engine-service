# Uygulama Planı: Gelişmiş Filtreleme ve Arama Geçmişi

## Genel Bakış

Bu plan, mevcut arama motoruna gelişmiş filtreleme yetenekleri ve kullanıcı arama geçmişi özelliklerini eklemek için adım adım görevleri içerir. Her görev, önceki görevler üzerine inşa edilir ve artımlı ilerleme sağlar.

## Görevler

- [ ] 1. Veritabanı ve Entity yapısını oluştur
  - SearchHistory entity'sini oluştur (id, user_id, keyword, filters, searched_at)
  - Migration dosyasını oluştur
  - Repository sınıfını oluştur
  - _Gereksinimler: 4.1, 4.2, 6.1, 6.2_

- [ ]* 1.1 SearchHistory entity için birim testleri yaz
  - Entity oluşturma testleri
  - Getter/setter testleri
  - _Gereksinimler: 4.1, 4.2_

- [ ] 2. FilterDTO ve validasyon mantığını oluştur
  - FilterDTO sınıfını oluştur (dateFrom, dateTo, minViews, minLikes, maxReadingTime, tags, tagMode)
  - FilterService sınıfını oluştur
  - Tarih validasyonu ekle
  - Metrik validasyonu ekle
  - _Gereksinimler: 1.4, 1.5, 2.5, 8.1, 8.2, 8.4_

- [ ]* 2.1 FilterDTO validasyonu için özellik testi yaz
  - **Özellik 2: Geçersiz Tarih Aralığı Reddi**
  - **Doğrular: Gereksinimler 1.4**

- [ ]* 2.2 Geçersiz tarih formatı için özellik testi yaz
  - **Özellik 3: Geçersiz Tarih Formatı Reddi**
  - **Doğrular: Gereksinimler 1.5, 8.1**

- [ ]* 2.3 Negatif metrik değerleri için özellik testi yaz
  - **Özellik 5: Negatif Metrik Değeri Reddi**
  - **Doğrular: Gereksinimler 2.5, 8.2**

- [ ]* 2.4 Geçersiz sayfa numarası için özellik testi yaz
  - **Özellik 18: Geçersiz Sayfa Numarası Reddi**
  - **Doğrular: Gereksinimler 8.4**

- [ ] 3. Tarih aralığı filtreleme işlevini uygula
  - FilterService'e applyDateFilter metodunu ekle
  - Başlangıç tarihi filtresini uygula
  - Bitiş tarihi filtresini uygula
  - Her iki tarih filtresini birlikte uygula
  - _Gereksinimler: 1.1, 1.2, 1.3_

- [ ]* 3.1 Tarih filtresi için özellik testi yaz
  - **Özellik 1: Tarih Filtresi Aralık Kontrolü**
  - **Doğrular: Gereksinimler 1.1, 1.2, 1.3**

- [ ] 4. Metrik bazlı filtreleme işlevini uygula
  - FilterService'e applyMetricFilters metodunu ekle
  - Minimum görüntülenme filtresini uygula
  - Minimum beğeni filtresini uygula
  - Maksimum okuma süresi filtresini uygula
  - Birden fazla metrik filtresini birlikte uygula
  - _Gereksinimler: 2.1, 2.2, 2.3, 2.4_

- [ ]* 4.1 Metrik filtresi için özellik testi yaz
  - **Özellik 4: Metrik Filtresi Eşik Kontrolü**
  - **Doğrular: Gereksinimler 2.1, 2.2, 2.3, 2.4**

- [ ] 5. Etiket bazlı filtreleme işlevini uygula
  - FilterService'e applyTagFilter metodunu ekle
  - "any" modu (herhangi bir etiket) filtresini uygula
  - "all" modu (tüm etiketler) filtresini uygula
  - Büyük-küçük harf duyarsız eşleşme ekle
  - _Gereksinimler: 3.1, 3.2, 3.3, 3.4, 3.5_

- [ ]* 5.1 Etiket filtresi "any" modu için özellik testi yaz
  - **Özellik 6: Etiket Filtresi - Herhangi Biri Modu**
  - **Doğrular: Gereksinimler 3.1, 3.2**

- [ ]* 5.2 Etiket filtresi "all" modu için özellik testi yaz
  - **Özellik 7: Etiket Filtresi - Tümü Modu**
  - **Doğrular: Gereksinimler 3.3**

- [ ]* 5.3 Etiket büyük-küçük harf duyarsızlığı için özellik testi yaz
  - **Özellik 8: Etiket Araması Büyük-Küçük Harf Duyarsızlığı**
  - **Doğrular: Gereksinimler 3.4**

- [ ] 6. Kontrol noktası - Tüm testlerin geçtiğinden emin ol
  - Tüm testlerin geçtiğinden emin ol, sorular varsa kullanıcıya sor.

- [ ] 7. FilterService'i SearchService'e entegre et
  - SearchRequestDTO'ya filters parametresi ekle
  - SearchService.performSearch metodunu güncelle
  - Filtreleri arama sonuçlarına uygula
  - Filtre kombinasyonlarını VE mantığı ile birleştir
  - _Gereksinimler: 7.1, 7.4_

- [ ]* 7.1 Filtre kombinasyonu VE mantığı için özellik testi yaz
  - **Özellik 16: Filtre Kombinasyonu VE Mantığı**
  - **Doğrular: Gereksinimler 7.1, 7.4**

- [ ]* 7.2 Filtre sırası bağımsızlığı için özellik testi yaz
  - **Özellik 17: Filtre Sırası Bağımsızlığı (Confluence)**
  - **Doğrular: Gereksinimler 7.3**

- [ ] 8. SearchController'a filtre parametrelerini ekle
  - Controller'da filtre parametrelerini al
  - FilterDTO oluştur
  - Hata yönetimini ekle
  - OpenAPI dokümantasyonunu güncelle
  - _Gereksinimler: 8.3, 8.5_

- [ ]* 8.1 Hata mesajı açıklayıcılığı için özellik testi yaz
  - **Özellik 19: Hata Mesajı Açıklayıcılığı**
  - **Doğrular: Gereksinimler 8.5**

- [ ] 9. SearchHistoryService'i oluştur
  - SearchHistoryService sınıfını oluştur
  - saveSearch metodunu uygula
  - Arama parametrelerini kaydetme mantığını ekle
  - _Gereksinimler: 4.1, 4.2, 4.3_

- [ ]* 9.1 Arama geçmişi kaydetme için özellik testi yaz
  - **Özellik 9: Arama Geçmişi Kaydetme**
  - **Doğrular: Gereksinimler 4.1, 4.2, 4.3**

- [ ]* 9.2 Arama geçmişi veri bütünlüğü için özellik testi yaz
  - **Özellik 12: Arama Geçmişi Veri Bütünlüğü**
  - **Doğrular: Gereksinimler 5.2**

- [ ] 10. Arama geçmişi limit kontrolünü uygula
  - enforceHistoryLimit metodunu uygula
  - 100 kayıt limitini kontrol et
  - En eski kaydı silme mantığını ekle
  - _Gereksinimler: 4.4, 4.5_

- [ ]* 10.1 Arama geçmişi limit kontrolü için özellik testi yaz
  - **Özellik 10: Arama Geçmişi Limit Kontrolü**
  - **Doğrular: Gereksinimler 4.4, 4.5**

- [ ] 11. Arama geçmişi görüntüleme işlevini uygula
  - getHistory metodunu uygula
  - Sıralama mantığını ekle (en yeniden eskiye)
  - Sayfalama desteği ekle
  - _Gereksinimler: 5.1, 5.3, 5.4_

- [ ]* 11.1 Arama geçmişi sıralama için özellik testi yaz
  - **Özellik 11: Arama Geçmişi Sıralama**
  - **Doğrular: Gereksinimler 5.1**

- [ ]* 11.2 Arama geçmişi sayfalama için özellik testi yaz
  - **Özellik 13: Arama Geçmişi Sayfalama**
  - **Doğrular: Gereksinimler 5.3**

- [ ] 12. Arama geçmişi silme işlevlerini uygula
  - deleteHistory metodunu uygula (tekil silme)
  - clearAllHistory metodunu uygula (toplu silme)
  - Yetkilendirme kontrolü ekle
  - _Gereksinimler: 6.1, 6.2, 6.3, 6.4_

- [ ]* 12.1 Tekil arama geçmişi silme için özellik testi yaz
  - **Özellik 14: Tekil Arama Geçmişi Silme**
  - **Doğrular: Gereksinimler 6.1**

- [ ]* 12.2 Toplu arama geçmişi silme için özellik testi yaz
  - **Özellik 15: Toplu Arama Geçmişi Silme**
  - **Doğrular: Gereksinimler 6.2**

- [ ] 13. Kontrol noktası - Tüm testlerin geçtiğinden emin ol
  - Tüm testlerin geçtiğinden emin ol, sorular varsa kullanıcıya sor.

- [ ] 14. SearchHistoryService'i SearchService'e entegre et
  - SearchService.search metoduna geçmiş kaydetme ekle
  - Her aramadan sonra saveSearch çağır
  - _Gereksinimler: 4.1_

- [ ] 15. Arama geçmişi API endpoint'lerini oluştur
  - GET /api/search/history endpoint'i ekle
  - DELETE /api/search/history/{id} endpoint'i ekle
  - DELETE /api/search/history endpoint'i ekle (tümünü sil)
  - POST /api/search/history/{id}/replay endpoint'i ekle (geçmiş aramayı tekrarla)
  - OpenAPI dokümantasyonunu ekle
  - _Gereksinimler: 5.1, 5.5, 6.1, 6.2_

- [ ]* 15.1 Arama geçmişi API endpoint'leri için entegrasyon testleri yaz
  - GET /api/search/history testi
  - DELETE endpoint'leri testleri
  - POST replay testi
  - _Gereksinimler: 5.1, 5.5, 6.1, 6.2_

- [ ] 16. Cache yönetimini güncelle
  - Filtre parametrelerini cache anahtarına ekle
  - Cache invalidation stratejisini güncelle
  - _Gereksinimler: 8.4_

- [ ] 17. Son kontrol noktası - Tüm testlerin geçtiğinden emin ol
  - Tüm testlerin geçtiğinden emin ol, sorular varsa kullanıcıya sor.

## Notlar

- `*` ile işaretlenmiş görevler isteğe bağlıdır ve daha hızlı MVP için atlanabilir
- Her görev, izlenebilirlik için belirli gereksinimlere referans verir
- Kontrol noktaları, artımlı doğrulama sağlar
- Özellik testleri, evrensel doğruluk özelliklerini doğrular
- Birim testler, belirli örnekleri ve kenar durumları doğrular
