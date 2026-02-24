# Gereksinimler Dokümanı

## Giriş

Bu doküman, arama motoru servisine gelişmiş filtreleme özellikleri ve kullanıcı arama geçmişi eklenmesi için gereksinimleri tanımlar. Kullanıcılar daha detaylı filtrelerle arama yapabilecek ve geçmiş aramalarını görüntüleyebilecekler.

## Sözlük

- **Arama_Motoru**: İçerik arama ve sonuç döndürme sistemi
- **Filtre**: Arama sonuçlarını daraltmak için kullanılan kriter
- **Arama_Geçmişi**: Kullanıcının daha önce yaptığı aramaların kaydı
- **Tarih_Aralığı**: Başlangıç ve bitiş tarihleri ile tanımlanan zaman dilimi
- **Metrik_Filtresi**: İçerik metriklerine (görüntülenme, beğeni vb.) dayalı filtre
- **Kullanıcı**: Sistemi kullanan kişi veya uygulama

## Gereksinimler

### Gereksinim 1: Tarih Aralığı Filtreleme

**Kullanıcı Hikayesi:** Bir kullanıcı olarak, belirli bir tarih aralığındaki içerikleri bulmak için tarih filtresi kullanmak istiyorum.

#### Kabul Kriterleri

1. WHEN bir kullanıcı başlangıç tarihi sağlar THEN Arama_Motoru yalnızca bu tarihten sonra yayınlanan içerikleri döndürmeli
2. WHEN bir kullanıcı bitiş tarihi sağlar THEN Arama_Motoru yalnızca bu tarihten önce yayınlanan içerikleri döndürmeli
3. WHEN bir kullanıcı hem başlangıç hem bitiş tarihi sağlar THEN Arama_Motoru yalnızca bu Tarih_Aralığı içindeki içerikleri döndürmeli
4. IF başlangıç tarihi bitiş tarihinden sonra ise THEN Arama_Motoru hata mesajı döndürmeli
5. WHEN tarih formatı geçersiz ise THEN Arama_Motoru açıklayıcı hata mesajı döndürmeli

### Gereksinim 2: Metrik Bazlı Filtreleme

**Kullanıcı Hikayesi:** Bir kullanıcı olarak, popüler içerikleri bulmak için görüntülenme, beğeni gibi metriklere göre filtreleme yapmak istiyorum.

#### Kabul Kriterleri

1. WHEN bir kullanıcı minimum görüntülenme sayısı belirtir THEN Arama_Motoru yalnızca bu değerin üzerinde görüntülenmeye sahip içerikleri döndürmeli
2. WHEN bir kullanıcı minimum beğeni sayısı belirtir THEN Arama_Motoru yalnızca bu değerin üzerinde beğeniye sahip içerikleri döndürmeli
3. WHEN bir kullanıcı maksimum okuma süresi belirtir THEN Arama_Motoru yalnızca bu sürenin altında okuma süresine sahip içerikleri döndürmeli
4. WHEN birden fazla Metrik_Filtresi uygulanır THEN Arama_Motoru tüm filtreleri karşılayan içerikleri döndürmeli
5. IF metrik değeri negatif ise THEN Arama_Motoru hata mesajı döndürmeli

### Gereksinim 3: Etiket Bazlı Filtreleme

**Kullanıcı Hikayesi:** Bir kullanıcı olarak, ilgilendiğim konulardaki içerikleri bulmak için etiketlere göre filtreleme yapmak istiyorum.

#### Kabul Kriterleri

1. WHEN bir kullanıcı tek bir etiket belirtir THEN Arama_Motoru bu etikete sahip tüm içerikleri döndürmeli
2. WHEN bir kullanıcı birden fazla etiket belirtir THEN Arama_Motoru en az bir etiketi içeren içerikleri döndürmeli
3. WHEN bir kullanıcı "tümü" modu ile birden fazla etiket belirtir THEN Arama_Motoru tüm etiketleri içeren içerikleri döndürmeli
4. THE Arama_Motoru etiket aramasında büyük-küçük harf duyarlı olmamalı
5. WHEN etiket listesi boş ise THEN Arama_Motoru tüm içerikleri döndürmeli

### Gereksinim 4: Arama Geçmişi Kaydetme

**Kullanıcı Hikayesi:** Bir kullanıcı olarak, daha önce yaptığım aramaları tekrar kullanabilmek için arama geçmişimin kaydedilmesini istiyorum.

#### Kabul Kriterleri

1. WHEN bir kullanıcı arama yapar THEN Arama_Motoru arama parametrelerini Arama_Geçmişi tablosuna kaydetmeli
2. WHEN arama kaydedilir THEN sistem arama zamanını, anahtar kelimeyi ve tüm filtreleri kaydetmeli
3. WHEN aynı arama tekrar yapılır THEN sistem yeni bir kayıt oluşturmalı
4. THE Arama_Motoru her kullanıcı için en fazla 100 arama kaydı tutmalı
5. WHEN kullanıcının 100'den fazla arama kaydı olur THEN sistem en eski kaydı silmeli

### Gereksinim 5: Arama Geçmişi Görüntüleme

**Kullanıcı Hikayesi:** Bir kullanıcı olarak, geçmiş aramalarımı görmek ve tekrar kullanmak istiyorum.

#### Kabul Kriterleri

1. WHEN bir kullanıcı arama geçmişini talep eder THEN Arama_Motoru kullanıcının son aramalarını en yeniden eskiye sıralı döndürmeli
2. WHEN arama geçmişi görüntülenir THEN her kayıt arama zamanını, anahtar kelimeyi ve uygulanan filtreleri içermeli
3. THE Arama_Motoru sayfalama desteği sağlamalı
4. WHEN kullanıcının hiç arama geçmişi yoksa THEN sistem boş liste döndürmeli
5. WHEN geçmiş arama seçilir THEN sistem aynı parametrelerle yeni arama yapmalı

### Gereksinim 6: Arama Geçmişi Silme

**Kullanıcı Hikayesi:** Bir kullanıcı olarak, arama geçmişimi temizleyebilmek istiyorum.

#### Kabul Kriterleri

1. WHEN bir kullanıcı tek bir arama kaydını siler THEN Arama_Motoru yalnızca o kaydı silmeli
2. WHEN bir kullanıcı tüm geçmişi temizler THEN Arama_Motoru kullanıcının tüm arama kayıtlarını silmeli
3. WHEN silme işlemi başarılı olur THEN sistem başarı mesajı döndürmeli
4. IF silinecek kayıt bulunamazsa THEN sistem hata mesajı döndürmeli
5. THE silme işlemi geri alınamaz olmalı

### Gereksinim 7: Filtre Kombinasyonları

**Kullanıcı Hikayesi:** Bir kullanıcı olarak, çok spesifik sonuçlar elde etmek için birden fazla filtreyi aynı anda kullanmak istiyorum.

#### Kabul Kriterleri

1. WHEN birden fazla filtre türü uygulanır THEN Arama_Motoru tüm filtreleri VE mantığı ile birleştirmeli
2. WHEN filtre kombinasyonu hiç sonuç döndürmez THEN sistem boş liste ve bilgilendirici mesaj döndürmeli
3. THE Arama_Motoru filtre sırasından bağımsız çalışmalı
4. WHEN mevcut filtreler ile yeni filtreler eklenir THEN sistem tüm filtreleri birlikte uygulamalı
5. THE Arama_Motoru filtre performansını optimize etmeli

### Gereksinim 8: Filtre Validasyonu

**Kullanıcı Hikayesi:** Bir sistem yöneticisi olarak, geçersiz filtre değerlerinin sistemi bozmamasını istiyorum.

#### Kabul Kriterleri

1. WHEN geçersiz tarih formatı sağlanır THEN Arama_Motoru açıklayıcı hata mesajı döndürmeli
2. WHEN negatif metrik değeri sağlanır THEN Arama_Motoru hata mesajı döndürmeli
3. WHEN desteklenmeyen filtre parametresi sağlanır THEN Arama_Motoru parametreyi görmezden gelmeli veya hata döndürmeli
4. WHEN sayfa numarası sıfır veya negatif ise THEN Arama_Motoru hata mesajı döndürmeli
5. THE tüm hata mesajları hangi parametrenin hatalı olduğunu belirtmeli
