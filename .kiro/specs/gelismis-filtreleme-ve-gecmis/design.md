# Tasarım Dokümanı - Gelişmiş Filtreleme ve Arama Geçmişi

## Genel Bakış

Bu tasarım, mevcut arama motoruna gelişmiş filtreleme yetenekleri ve kullanıcı arama geçmişi özelliklerini ekler. Sistem, tarih aralığı, metrik ve etiket bazlı filtreler ile kullanıcıların daha spesifik aramalar yapmasını sağlar. Ayrıca, kullanıcıların geçmiş aramalarını kaydeder ve yeniden kullanmalarına olanak tanır.

## Mimari

### Yüksek Seviye Mimari

```
┌─────────────┐
│   Client    │
└──────┬──────┘
       │ HTTP Request (filters + params)
       ▼
┌─────────────────────┐
│ SearchController    │
└──────┬──────────────┘
       │
       ▼
┌─────────────────────┐
│  SearchService      │◄──────┐
└──────┬──────────────┘       │
       │                      │
       ├──────────────────────┤
       │                      │
       ▼                      ▼
┌──────────────┐    ┌──────────────────┐
│FilterService │    │SearchHistoryServ.│
└──────┬───────┘    └────────┬─────────┘
       │                     │
       ▼                     ▼
┌──────────────┐    ┌──────────────────┐
│ContentRepo   │    │SearchHistoryRepo │
└──────────────┘    └──────────────────┘
```

### Katman Yapısı

1. **Controller Katmanı**: HTTP isteklerini alır, validasyon yapar
2. **Service Katmanı**: İş mantığını yönetir, filtreleme ve geçmiş işlemlerini koordine eder
3. **Repository Katmanı**: Veritabanı işlemlerini gerçekleştiir
4. **DTO Katmanı**: Veri transfer nesneleri

## Bileşenler ve Arayüzler

### 1. FilterDTO

Filtre parametrelerini taşıyan veri transfer nesnesi.

```php
class FilterDTO
{
    public function __construct(
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
        public readonly ?int $minViews = null,
        public readonly ?int $minLikes = null,
        public readonly ?int $maxReadingTime = null,
        public readonly ?array $tags = null,
        public readonly string $tagMode = 'any' // 'any' veya 'all'
    ) {}
    
    public function hasFilters(): bool;
    public function toArray(): array;
}
```

### 2. SearchHistoryEntity

Arama geçmişi veritabanı entity'si.

```php
#[ORM\Entity]
#[ORM\Table(name: 'search_history')]
class SearchHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private int $id;
    
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $keyword;
    
    #[ORM\Column(type: 'json')]
    private array $filters;
    
    #[ORM\Column(type: 'datetime')]
    private \DateTime $searchedAt;
    
    #[ORM\Column(type: 'string', length: 50)]
    private string $userId; // Gelecekte kullanıcı sistemi için
    
    // Getters ve Setters
}
```

### 3. FilterService

Filtreleme mantığını yöneten servis.

```php
class FilterService
{
    public function applyFilters(
        array $contents,
        FilterDTO $filters
    ): array;
    
    private function applyDateFilter(
        array $contents,
        ?string $dateFrom,
        ?string $dateTo
    ): array;
    
    private function applyMetricFilters(
        array $contents,
        FilterDTO $filters
    ): array;
    
    private function applyTagFilter(
        array $contents,
        ?array $tags,
        string $mode
    ): array;
    
    public function validateFilters(FilterDTO $filters): void;
}
```

### 4. SearchHistoryService

Arama geçmişi işlemlerini yöneten servis.

```php
class SearchHistoryService
{
    public function __construct(
        private SearchHistoryRepository $repository
    ) {}
    
    public function saveSearch(
        ?string $keyword,
        FilterDTO $filters,
        string $userId = 'anonymous'
    ): void;
    
    public function getHistory(
        string $userId = 'anonymous',
        int $page = 1,
        int $perPage = 20
    ): array;
    
    public function deleteHistory(
        int $historyId,
        string $userId = 'anonymous'
    ): void;
    
    public function clearAllHistory(
        string $userId = 'anonymous'
    ): void;
    
    private function enforceHistoryLimit(string $userId): void;
}
```

### 5. SearchHistoryRepository

Arama geçmişi veritabanı işlemleri.

```php
class SearchHistoryRepository extends ServiceEntityRepository
{
    public function save(SearchHistory $history): void;
    
    public function findByUserId(
        string $userId,
        int $limit = 100,
        int $offset = 0
    ): array;
    
    public function deleteById(int $id, string $userId): bool;
    
    public function deleteAllByUserId(string $userId): int;
    
    public function countByUserId(string $userId): int;
    
    public function deleteOldestByUserId(string $userId, int $keepCount): void;
}
```

### 6. Güncellenmiş SearchRequestDTO

Mevcut DTO'ya filtre desteği eklenir.

```php
class SearchRequestDTO
{
    public function __construct(
        public readonly ?string $keyword = null,
        public readonly ?string $type = null,
        public readonly string $sortBy = 'score',
        public readonly int $page = 1,
        public readonly int $perPage = 10,
        public readonly ?FilterDTO $filters = null
    ) {}
}
```

## Veri Modelleri

### SearchHistory Tablosu

```sql
CREATE TABLE search_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    keyword VARCHAR(255),
    filters JSON NOT NULL,
    searched_at DATETIME NOT NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_searched_at (searched_at)
);
```

### Filters JSON Yapısı

```json
{
    "dateFrom": "2024-01-01",
    "dateTo": "2024-12-31",
    "minViews": 1000,
    "minLikes": 50,
    "maxReadingTime": 10,
    "tags": ["teknoloji", "yazılım"],
    "tagMode": "any"
}
```

## Doğruluk Özellikleri

*Bir özellik (property), sistemin tüm geçerli çalıştırmalarında doğru olması gereken bir davranış veya karakteristiktir. Özellikler, insan tarafından okunabilir spesifikasyonlar ile makine tarafından doğrulanabilir doğruluk garantileri arasında köprü görevi görür.*


### Özellik 1: Tarih Filtresi Aralık Kontrolü

*Herhangi bir* içerik listesi ve geçerli tarih aralığı (başlangıç ve/veya bitiş) için, filtreleme sonrası tüm içeriklerin yayın tarihleri belirtilen aralık içinde olmalıdır.

**Doğrular: Gereksinimler 1.1, 1.2, 1.3**

### Özellik 2: Geçersiz Tarih Aralığı Reddi

*Herhangi bir* başlangıç ve bitiş tarihi çifti için, eğer başlangıç tarihi bitiş tarihinden sonra ise, sistem hata döndürmelidir.

**Doğrular: Gereksinimler 1.4**

### Özellik 3: Geçersiz Tarih Formatı Reddi

*Herhangi bir* geçersiz tarih formatı için, sistem açıklayıcı hata mesajı döndürmelidir.

**Doğrular: Gereksinimler 1.5, 8.1**

### Özellik 4: Metrik Filtresi Eşik Kontrolü

*Herhangi bir* içerik listesi ve metrik eşik değerleri (minViews, minLikes, maxReadingTime) için, filtreleme sonrası tüm içeriklerin metrikleri belirtilen eşikleri karşılamalıdır.

**Doğrular: Gereksinimler 2.1, 2.2, 2.3, 2.4**

### Özellik 5: Negatif Metrik Değeri Reddi

*Herhangi bir* negatif metrik değeri için, sistem hata mesajı döndürmelidir.

**Doğrular: Gereksinimler 2.5, 8.2**

### Özellik 6: Etiket Filtresi - Herhangi Biri Modu

*Herhangi bir* içerik listesi ve etiket listesi için, "any" modunda filtreleme sonrası tüm içerikler en az bir belirtilen etiketi içermelidir.

**Doğrular: Gereksinimler 3.1, 3.2**

### Özellik 7: Etiket Filtresi - Tümü Modu

*Herhangi bir* içerik listesi ve etiket listesi için, "all" modunda filtreleme sonrası tüm içerikler belirtilen tüm etiketleri içermelidir.

**Doğrular: Gereksinimler 3.3**

### Özellik 8: Etiket Araması Büyük-Küçük Harf Duyarsızlığı

*Herhangi bir* etiket için, farklı büyük-küçük harf kombinasyonları aynı filtreleme sonucunu vermelidir.

**Doğrular: Gereksinimler 3.4**

### Özellik 9: Arama Geçmişi Kaydetme

*Herhangi bir* arama parametresi seti için, arama yapıldığında sistem bu parametreleri arama zamanı ile birlikte veritabanına kaydetmelidir.

**Doğrular: Gereksinimler 4.1, 4.2, 4.3**

### Özellik 10: Arama Geçmişi Limit Kontrolü

*Herhangi bir* kullanıcı için, arama geçmişi kayıt sayısı 100'ü geçtiğinde, sistem en eski kaydı otomatik olarak silmelidir.

**Doğrular: Gereksinimler 4.4, 4.5**

### Özellik 11: Arama Geçmişi Sıralama

*Herhangi bir* kullanıcı için, arama geçmişi sorgulandığında sonuçlar en yeniden eskiye doğru sıralanmış olmalıdır.

**Doğrular: Gereksinimler 5.1**

### Özellik 12: Arama Geçmişi Veri Bütünlüğü

*Herhangi bir* arama geçmişi kaydı için, kayıt arama zamanını, anahtar kelimeyi ve tüm uygulanan filtreleri içermelidir.

**Doğrular: Gereksinimler 5.2**

### Özellik 13: Arama Geçmişi Sayfalama

*Herhangi bir* sayfa numarası ve sayfa başına kayıt sayısı için, arama geçmişi doğru sayfalama bilgisi ile döndürülmelidir.

**Doğrular: Gereksinimler 5.3**

### Özellik 14: Tekil Arama Geçmişi Silme

*Herhangi bir* geçerli arama geçmişi ID'si için, silme işlemi yalnızca o kaydı silmeli ve diğer kayıtları etkilemememelidir.

**Doğrular: Gereksinimler 6.1**

### Özellik 15: Toplu Arama Geçmişi Silme

*Herhangi bir* kullanıcı için, tüm geçmişi temizleme işlemi o kullanıcının tüm arama kayıtlarını silmelidir.

**Doğrular: Gereksinimler 6.2**

### Özellik 16: Filtre Kombinasyonu VE Mantığı

*Herhangi bir* içerik listesi ve birden fazla filtre türü için, tüm filtreler VE mantığı ile uygulanmalı ve sonuçlar tüm filtreleri karşılamalıdır.

**Doğrular: Gereksinimler 7.1, 7.4**

### Özellik 17: Filtre Sırası Bağımsızlığı (Confluence)

*Herhangi bir* içerik listesi ve filtre seti için, filtrelerin uygulanma sırası sonucu değiştirmemelidir.

**Doğrular: Gereksinimler 7.3**

### Özellik 18: Geçersiz Sayfa Numarası Reddi

*Herhangi bir* sıfır veya negatif sayfa numarası için, sistem hata mesajı döndürmelidir.

**Doğrular: Gereksinimler 8.4**

### Özellik 19: Hata Mesajı Açıklayıcılığı

*Herhangi bir* validasyon hatası için, döndürülen hata mesajı hangi parametrenin hatalı olduğunu belirtmelidir.

**Doğrular: Gereksinimler 8.5**

## Hata Yönetimi

### Validasyon Hataları

- **Geçersiz tarih formatı**: "Invalid date format for {parameter}. Expected: YYYY-MM-DD"
- **Geçersiz tarih aralığı**: "Start date cannot be after end date"
- **Negatif metrik**: "Metric value for {parameter} cannot be negative"
- **Geçersiz sayfa**: "Page number must be positive"
- **Geçersiz etiket modu**: "Tag mode must be 'any' or 'all'"

### İş Mantığı Hataları

- **Geçmiş kaydı bulunamadı**: "Search history record not found"
- **Yetkisiz silme**: "Cannot delete history record of another user"

### Sistem Hataları

- **Veritabanı hatası**: "Database error occurred"
- **Cache hatası**: "Cache operation failed"

## Test Stratejisi

### İkili Test Yaklaşımı

Sistem hem birim testleri hem de özellik tabanlı testler (property-based tests) kullanacaktır:

- **Birim testler**: Belirli örnekler, kenar durumlar ve hata koşulları
- **Özellik testleri**: Tüm girdiler üzerinde evrensel özellikler

### Özellik Tabanlı Test Konfigürasyonu

- **Test kütüphanesi**: PHPUnit ile Eris (PHP için property-based testing)
- **Minimum iterasyon**: Her özellik testi için 100 iterasyon
- **Test etiketleme**: Her test, tasarım dokümanındaki özellik numarasını referans almalı
- **Format**: `Feature: gelismis-filtreleme-ve-gecmis, Property {numara}: {özellik_metni}`

### Test Kapsamı

**Birim Testler**:
- FilterService validasyon mantığı
- SearchHistoryService limit kontrolü
- Repository CRUD işlemleri
- DTO dönüşümleri
- Kenar durumlar (boş listeler, null değerler)

**Özellik Testleri**:
- Tüm filtre özellikleri (1-8, 16-17)
- Arama geçmişi özellikleri (9-15)
- Validasyon özellikleri (2, 3, 5, 18, 19)

### Test Veri Üretimi

**Rastgele İçerik Üretimi**:
- Rastgele tarihler (son 2 yıl içinde)
- Rastgele metrikler (0-1000000 arası)
- Rastgele etiketler (10 farklı etiket havuzundan)
- Rastgele türler (video/article)

**Rastgele Filtre Üretimi**:
- Rastgele tarih aralıkları
- Rastgele metrik eşikleri
- Rastgele etiket kombinasyonları
- Rastgele etiket modları

### Entegrasyon Testleri

- Controller → Service → Repository akışı
- Cache entegrasyonu
- Veritabanı işlemleri
- API yanıt formatları
