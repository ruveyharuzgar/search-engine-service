# Uygulama Planı: Mevcut Arama Sistemi Dokümantasyonu ve Test Kapsamı

## Genel Bakış

Bu plan, mevcut arama sisteminin test kapsamını artırmak ve eksik testleri eklemek için görevleri içerir. Sistem zaten çalışıyor durumda, bu nedenle odak noktası test ve dokümantasyon iyileştirmeleridir.

## Görevler

- [ ] 1. Mevcut testleri gözden geçir ve eksiklikleri belirle
  - tests/Controller/SearchControllerTest.php'yi incele
  - tests/Service/ScoringServiceTest.php'yi incele
  - tests/Service/CacheManagerTest.php'yi incele
  - tests/Provider/JsonProviderTest.php'yi incele
  - tests/Provider/XmlProviderTest.php'yi incele
  - Eksik test senaryolarını listele
  - _Gereksinimler: Tüm gereksinimler_

- [ ] 2. SearchService için eksik birim testlerini ekle
  - performSearch metodu testleri
  - sortContents metodu testleri
  - syncContents metodu testleri
  - Kenar durumlar (boş sonuçlar, null değerler)
  - _Gereksinimler: 1.1, 1.2, 6.1, 6.2, 9.1, 9.2_

- [ ]* 2.1 Anahtar kelime eşleşmesi için özellik testi yaz
  - **Özellik 1: Anahtar Kelime Eşleşmesi**
  - **Doğrular: Gereksinimler 1.1**

- [ ]* 2.2 Büyük-küçük harf duyarsızlığı için özellik testi yaz
  - **Özellik 2: Büyük-Küçük Harf Duyarsızlığı**
  - **Doğrular: Gereksinimler 1.3**

- [ ]* 2.3 Kısmi eşleşme desteği için özellik testi yaz
  - **Özellik 3: Kısmi Eşleşme Desteği**
  - **Doğrular: Gereksinimler 1.5**

- [ ] 3. Tür filtresi testlerini ekle veya güncelle
  - Video türü filtresi testi
  - Makale türü filtresi testi
  - Geçersiz tür testi
  - _Gereksinimler: 2.1, 2.2, 2.4_

- [ ]* 3.1 Tür filtresi doğruluğu için özellik testi yaz
  - **Özellik 4: Tür Filtresi Doğruluğu**
  - **Doğrular: Gereksinimler 2.1, 2.2**

- [ ]* 3.2 Geçersiz tür reddi için özellik testi yaz
  - **Özellik 5: Geçersiz Tür Reddi**
  - **Doğrular: Gereksinimler 2.4**

- [ ] 4. ScoringService testlerini genişlet
  - Mevcut testleri gözden geçir
  - Video temel skor formülü testleri ekle
  - Makale temel skor formülü testleri ekle
  - Tür katsayısı testleri ekle
  - Güncellik puanı testleri ekle
  - Etkileşim puanı testleri ekle
  - Sıfıra bölme kenar durumu testleri ekle
  - _Gereksinimler: 3.1, 3.2, 3.3, 3.4, 3.5, 4.1-4.5, 5.1-5.5_

- [ ]* 4.1 Video temel skor formülü için özellik testi yaz
  - **Özellik 6: Video Temel Skor Formülü**
  - **Doğrular: Gereksinimler 3.1**

- [ ]* 4.2 Makale temel skor formülü için özellik testi yaz
  - **Özellik 7: Makale Temel Skor Formülü**
  - **Doğrular: Gereksinimler 3.2**

- [ ]* 4.3 Video tür katsayısı için özellik testi yaz
  - **Özellik 8: Video Tür Katsayısı**
  - **Doğrular: Gereksinimler 3.3**

- [ ]* 4.4 Makale tür katsayısı için özellik testi yaz
  - **Özellik 9: Makale Tür Katsayısı**
  - **Doğrular: Gereksinimler 3.4**

- [ ]* 4.5 Güncellik puanı hesaplama için özellik testi yaz
  - **Özellik 10: Güncellik Puanı Hesaplama**
  - **Doğrular: Gereksinimler 4.1, 4.2, 4.3, 4.4, 4.5**

- [ ]* 4.6 Video etkileşim puanı formülü için özellik testi yaz
  - **Özellik 11: Video Etkileşim Puanı Formülü**
  - **Doğrular: Gereksinimler 5.1**

- [ ]* 4.7 Makale etkileşim puanı formülü için özellik testi yaz
  - **Özellik 12: Makale Etkileşim Puanı Formülü**
  - **Doğrular: Gereksinimler 5.2**

- [ ]* 4.8 Etkileşim puanı negatif olmama invariant'ı için özellik testi yaz
  - **Özellik 13: Etkileşim Puanı Negatif Olmamalı (Invariant)**
  - **Doğrular: Gereksinimler 5.5**

- [ ]* 4.9 Final skor formülü için özellik testi yaz
  - **Özellik 14: Final Skor Formülü**
  - **Doğrular: Gereksinimler 3.5**

- [ ] 5. Kontrol noktası - Skorlama testlerinin geçtiğinden emin ol
  - Tüm testlerin geçtiğinden emin ol, sorular varsa kullanıcıya sor.

- [ ] 6. Sıralama testlerini ekle
  - Skor sıralaması testi
  - Tarih sıralaması testi
  - Geçersiz sıralama varsayılan davranış testi
  - _Gereksinimler: 6.1, 6.2, 6.3, 6.4_

- [ ]* 6.1 Skor sıralaması için özellik testi yaz
  - **Özellik 15: Skor Sıralaması**
  - **Doğrular: Gereksinimler 6.1**

- [ ]* 6.2 Tarih sıralaması için özellik testi yaz
  - **Özellik 16: Tarih Sıralaması**
  - **Doğrular: Gereksinimler 6.2**

- [ ]* 6.3 Geçersiz sıralama varsayılanı için özellik testi yaz
  - **Özellik 17: Geçersiz Sıralama Varsayılanı**
  - **Doğrular: Gereksinimler 6.4**

- [ ] 7. Sayfalama testlerini ekle veya güncelle
  - Sayfa numarası testi
  - Sayfa boyutu testi
  - Varsayılan sayfalama testi
  - Sayfalama metadata testi
  - Geçersiz sayfa testi
  - _Gereksinimler: 7.1, 7.2, 7.3, 7.4, 7.5_

- [ ]* 7.1 Sayfalama doğruluğu için özellik testi yaz
  - **Özellik 18: Sayfalama Doğruluğu**
  - **Doğrular: Gereksinimler 7.1, 7.2**

- [ ]* 7.2 Sayfalama metadata doğruluğu için özellik testi yaz
  - **Özellik 19: Sayfalama Metadata Doğruluğu**
  - **Doğrular: Gereksinimler 7.4**

- [ ] 8. CacheManager testlerini genişlet
  - Mevcut testleri gözden geçir
  - Cache anahtarı oluşturma testi ekle
  - Cache hit/miss senaryoları ekle
  - Cache temizleme testi ekle
  - _Gereksinimler: 8.1, 8.2, 8.3, 8.4, 8.5_

- [ ]* 8.1 Cache anahtarı tutarlılığı için özellik testi yaz
  - **Özellik 20: Cache Anahtarı Tutarlılığı**
  - **Doğrular: Gereksinimler 8.4**

- [ ] 9. Provider testlerini genişlet
  - JsonProvider testlerini gözden geçir
  - XmlProvider testlerini gözden geçir
  - DTO dönüşüm testleri ekle
  - Eksik alan yönetimi testleri ekle
  - Geçersiz format testleri ekle
  - _Gereksinimler: 10.1-10.5, 11.1-11.5_

- [ ]* 9.1 JSON DTO dönüşümü için özellik testi yaz
  - **Özellik 21: JSON DTO Dönüşümü**
  - **Doğrular: Gereksinimler 10.2**

- [ ]* 9.2 JSON eksik alan yönetimi için özellik testi yaz
  - **Özellik 22: JSON Eksik Alan Yönetimi**
  - **Doğrular: Gereksinimler 10.5**

- [ ]* 9.3 XML DTO dönüşümü için özellik testi yaz
  - **Özellik 23: XML DTO Dönüşümü**
  - **Doğrular: Gereksinimler 11.2**

- [ ]* 9.4 XML eksik alan yönetimi için özellik testi yaz
  - **Özellik 24: XML Eksik Alan Yönetimi**
  - **Doğrular: Gereksinimler 11.5**

- [ ] 10. Kontrol noktası - Tüm testlerin geçtiğinden emin ol
  - Tüm testlerin geçtiğinden emin ol, sorular varsa kullanıcıya sor.

- [ ] 11. ContentRepository testlerini ekle
  - search metodu testleri
  - save metodu testleri
  - Entity ↔ DTO dönüşüm testleri
  - _Gereksinimler: 1.1, 1.2, 2.1, 2.2_

- [ ] 12. SearchController entegrasyon testlerini genişlet
  - Mevcut testleri gözden geçir
  - Başarılı arama senaryoları ekle
  - Hata senaryoları ekle
  - Geriye uyumluluk testleri ekle (query/keyword, sort_by/sortBy)
  - _Gereksinimler: 12.1-12.5, 13.1-13.5_

- [ ]* 12.1 İçerik DTO yapısı için özellik testi yaz
  - **Özellik 25: İçerik DTO Yapısı**
  - **Doğrular: Gereksinimler 13.4**

- [ ]* 12.2 Sayfalama yapısı için özellik testi yaz
  - **Özellik 26: Sayfalama Yapısı**
  - **Doğrular: Gereksinimler 13.5**

- [ ] 13. Senkronizasyon testlerini ekle
  - syncContents metodu testi
  - Provider çağrısı testi
  - Veri kaydetme testi
  - Cache temizleme testi
  - Bildirim testi
  - _Gereksinimler: 9.1-9.5_

- [ ]* 13.1 Senkronizasyon entegrasyon testleri yaz
  - Başarılı senkronizasyon testi
  - Başarısız senkronizasyon testi
  - Bildirim gönderimi testi
  - _Gereksinimler: 9.1-9.5_

- [ ] 14. Hata yönetimi testlerini ekle
  - HTTP 500 yanıt testi
  - Hata yanıt formatı testi
  - Hata mesajı içeriği testi
  - Loglama testi
  - _Gereksinimler: 12.1-12.5_

- [ ] 15. Son kontrol noktası - Tüm testlerin geçtiğinden emin ol
  - Tüm testlerin geçtiğinden emin ol, sorular varsa kullanıcıya sor.

- [ ] 16. Test kapsamı raporunu oluştur
  - PHPUnit coverage raporu çalıştır
  - Kapsam yüzdesini kontrol et
  - Eksik kapsam alanlarını belirle
  - _Gereksinimler: Tüm gereksinimler_

- [ ] 17. README ve dokümantasyonu güncelle
  - API endpoint'lerini dokümante et
  - Skorlama algoritmasını açıkla
  - Kullanım örnekleri ekle
  - Test çalıştırma talimatları ekle
  - _Gereksinimler: Tüm gereksinimler_

## Notlar

- `*` ile işaretlenmiş görevler isteğe bağlıdır ve daha hızlı tamamlama için atlanabilir
- Her görev, izlenebilirlik için belirli gereksinimlere referans verir
- Kontrol noktaları, artımlı doğrulama sağlar
- Özellik testleri, evrensel doğruluk özelliklerini doğrular
- Birim testler, belirli örnekleri ve kenar durumları doğrular
- Bu plan mevcut sistemi değiştirmez, sadece test kapsamını artırır
