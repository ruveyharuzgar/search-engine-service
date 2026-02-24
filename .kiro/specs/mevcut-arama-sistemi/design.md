# Tasarım Dokümanı - Mevcut Arama Sistemi

## Genel Bakış

Bu doküman, mevcut arama motoru servisinin mimari tasarımını ve işleyişini belgeler. Sistem, çeşitli provider'lardan içerik çeker, skorlama algoritması ile sıralar ve kullanıcılara cache destekli arama sonuçları sunar.

## Mimari

### Yüksek Seviye Mimari

```
┌──────────────┐
│   Client     │
└──────┬───────┘
       │ HTTP GET /api/search
       ▼
┌──────────────────┐
│SearchController  │
└──────┬───────────┘
       │
       ▼
┌──────────────────┐
│ SearchService    │◄────────┐
└──────┬───────────┘         │
       │                     │
       ├─────────────────────┤
       │                     │
       ▼                     ▼
┌──────────────┐    ┌────────────────┐
│CacheManager  │    │ProviderManager │
└──────┬───────┘    └────────┬───────┘
       │                     │
       ▼                     ▼
┌──────────────┐    ┌────────────────┐
│ContentRepo   │    │JSON/XML Provider│
└──────────────┘    └────────────────┘
       │                     │
       ▼                     ▼
┌──────────────┐    ┌────────────────┐
│  Database    │    │External Sources│
└──────────────┘    └────────────────┘
```

### Veri Akışı

1. **Arama İsteği**: Client → Controller → Service
2. **Cache Kontrolü**: Service → CacheManager
3. **Veritabanı Sorgusu**: Service → Repository → Database
4. **Skorlama**: Service → ScoringService
5. **Sıralama ve Sayfalama**: Service
6. **Yanıt**: Service → Controller → Client

### Senkronizasyon Akışı

1. **Sync Tetikleme**: Client → Controller → Service
2. **Provider Çağrısı**: Service → ProviderManager → Providers
3. **Veri Kaydetme**: Service → Repository → Database
4. **Cache Temizleme**: Service → CacheManager
5. **Bildirim**: Service → NotificationManager

## Bileşenler ve Arayüzler

### 1. SearchController

HTTP isteklerini yöneten controller.

```php
class SearchController extends AbstractController
{
    public function __construct(
        private SearchService $searchService
    ) {}
    
    #[Route('/api/search', methods: ['GET'])]
    public function search(Request $request): JsonResponse;
    
    #[Route('/api/sync', methods: ['POST'])]
    public function sync(): JsonResponse;
}
```

**Sorumluluklar**:
- HTTP parametrelerini alma
- SearchRequestDTO oluşturma
- Hata yakalama ve yanıt formatı
- Geriye uyumluluk (query/keyword, sort_by/sortBy)

### 2. SearchService

Arama iş mantığını yöneten servis.

```php
class SearchService
{
    public function __construct(
        private ContentRepository $contentRepository,
        private ScoringService $scoringService,
        private CacheManager $cacheManager,
        private ProviderManager $providerManager,
        private NotificationManager $notificationManager
    ) {}
    
    public function search(SearchRequestDTO $request): array;
    private function performSearch(SearchRequestDTO $request): array;
    private function sortContents(array $contents, string $sortBy): array;
    public function syncContents(): int;
}
```

**Sorumluluklar**:
- Cache yönetimi
- Arama koordinasyonu
- Skorlama ve sıralama
- Sayfalama
- Provider senkronizasyonu

### 3. ScoringService

İçerik skorlama algoritması.

```php
class ScoringService
{
    private const TYPE_COEFFICIENTS = [
        'video' => 1.5,
        'article' => 1.0,
    ];
    
    public function calculateScore(ContentDTO $content): float;
    private function calculateBaseScore(ContentDTO $content): float;
    private function getTypeCoefficient(string $type): float;
    private function calculateFreshnessScore(\DateTime $publishedAt): float;
    private function calculateEngagementScore(ContentDTO $content): float;
}
```

**Skorlama Formülü**:
```
Final Skor = (Temel Puan * Tür Katsayısı) + Güncellik Puanı + Etkileşim Puanı
```

**Temel Puan**:
- Video: `(views / 1000) + (likes / 100)`
- Makale: `reading_time + (reactions / 50)`

**Tür Katsayısı**:
- Video: 1.5
- Makale: 1.0

**Güncellik Puanı**:
- ≤ 7 gün: +5
- ≤ 30 gün: +3
- ≤ 90 gün: +1
- > 90 gün: +0

**Etkileşim Puanı**:
- Video: `(likes / views) * 10`
- Makale: `(reactions / reading_time) * 5`

### 4. CacheManager

Cache işlemlerini yöneten servis.

```php
class CacheManager
{
    public function __construct(
        private CacheInterface $cache
    ) {}
    
    public function generateKey(string $prefix, array $params): string;
    public function get(string $key, callable $callback): mixed;
    public function clear(): void;
}
```

**Sorumluluklar**:
- Cache anahtarı oluşturma
- Cache okuma/yazma
- Cache temizleme

### 5. ContentRepository

Veritabanı işlemleri.

```php
class ContentRepository extends ServiceEntityRepository
{
    public function search(?string $keyword, ?string $type): array;
    public function save(ContentDTO $dto): void;
    public function truncate(): void;
}
```

**Sorumluluklar**:
- İçerik arama (LIKE sorguları)
- İçerik kaydetme/güncelleme
- Entity ↔ DTO dönüşümü

### 6. ProviderManager

Provider'ları yöneten servis.

```php
class ProviderManager
{
    public function __construct(
        private iterable $providers
    ) {}
    
    public function fetchAllContents(): array;
}
```

**Sorumluluklar**:
- Tüm provider'ları çağırma
- İçerikleri birleştirme

### 7. Provider Interface

Provider'lar için arayüz.

```php
interface ProviderInterface
{
    public function fetchContents(): array;
}
```

**Implementasyonlar**:
- JsonProvider: JSON formatındaki kaynaklardan çeker
- XmlProvider: XML formatındaki kaynaklardan çeker

### 8. ContentDTO

İçerik veri transfer nesnesi.

```php
class ContentDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $type,
        public readonly array $metrics,
        public readonly \DateTime $publishedAt,
        public readonly array $tags,
        public float $score = 0.0
    ) {}
    
    public function toArray(): array;
}
```

### 9. SearchRequestDTO

Arama parametreleri DTO'su.

```php
class SearchRequestDTO
{
    public function __construct(
        public readonly ?string $keyword = null,
        public readonly ?string $type = null,
        public readonly string $sortBy = 'score',
        public readonly int $page = 1,
        public readonly int $perPage = 10
    ) {}
}
```

## Veri Modelleri

### Content Entity

```php
#[ORM\Entity]
#[ORM\Table(name: 'contents')]
#[ORM\Index(columns: ['type'])]
#[ORM\Index(columns: ['published_at'])]
class Content
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 50)]
    private string $id;
    
    #[ORM\Column(type: 'string', length: 255)]
    private string $title;
    
    #[ORM\Column(type: 'string', length: 20)]
    private string $type;
    
    #[ORM\Column(type: 'json')]
    private array $metrics;
    
    #[ORM\Column(type: 'datetime')]
    private \DateTime $publishedAt;
    
    #[ORM\Column(type: 'json')]
    private array $tags;
    
    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;
    
    #[ORM\Column(type: 'datetime')]
    private \DateTime $updatedAt;
}
```

### Metrics JSON Yapısı

**Video**:
```json
{
    "views": 15000,
    "likes": 450
}
```

**Makale**:
```json
{
    "reading_time": 8,
    "reactions": 120
}
```

## Doğruluk Özellikleri

*Bir özellik (property), sistemin tüm geçerli çalıştırmalarında doğru olması gereken bir davranış veya karakteristiktir. Özellikler, insan tarafından okunabilir spesifikasyonlar ile makine tarafından doğrulanabilir doğruluk garantileri arasında köprü görevi görür.*


### Özellik 1: Anahtar Kelime Eşleşmesi

*Herhangi bir* anahtar kelime ve içerik listesi için, arama sonuçlarındaki tüm içeriklerin başlık veya etiketlerinde bu anahtar kelime bulunmalıdır.

**Doğrular: Gereksinimler 1.1**

### Özellik 2: Büyük-Küçük Harf Duyarsızlığı

*Herhangi bir* anahtar kelime için, farklı büyük-küçük harf kombinasyonları aynı arama sonucunu vermelidir.

**Doğrular: Gereksinimler 1.3**

### Özellik 3: Kısmi Eşleşme Desteği

*Herhangi bir* kısmi kelime için, tam kelimeyi içeren içerikler arama sonuçlarında bulunmalıdır.

**Doğrular: Gereksinimler 1.5**

### Özellik 4: Tür Filtresi Doğruluğu

*Herhangi bir* geçerli tür (video/article) için, filtreleme sonrası tüm içerikler belirtilen türde olmalıdır.

**Doğrular: Gereksinimler 2.1, 2.2**

### Özellik 5: Geçersiz Tür Reddi

*Herhangi bir* geçersiz tür değeri için, sistem hata mesajı döndürmelidir.

**Doğrular: Gereksinimler 2.4**

### Özellik 6: Video Temel Skor Formülü

*Herhangi bir* video içeriği için, temel skor `(views / 1000) + (likes / 100)` formülüne göre hesaplanmalıdır.

**Doğrular: Gereksinimler 3.1**

### Özellik 7: Makale Temel Skor Formülü

*Herhangi bir* makale içeriği için, temel skor `reading_time + (reactions / 50)` formülüne göre hesaplanmalıdır.

**Doğrular: Gereksinimler 3.2**

### Özellik 8: Video Tür Katsayısı

*Herhangi bir* video içeriği için, temel skor 1.5 katsayısı ile çarpılmalıdır.

**Doğrular: Gereksinimler 3.3**

### Özellik 9: Makale Tür Katsayısı

*Herhangi bir* makale içeriği için, temel skor 1.0 katsayısı ile çarpılmalıdır.

**Doğrular: Gereksinimler 3.4**

### Özellik 10: Güncellik Puanı Hesaplama

*Herhangi bir* içerik için, güncellik puanı yayın tarihine göre şu şekilde hesaplanmalıdır:
- ≤7 gün: +5
- ≤30 gün: +3
- ≤90 gün: +1
- >90 gün: +0

**Doğrular: Gereksinimler 4.1, 4.2, 4.3, 4.4, 4.5**

### Özellik 11: Video Etkileşim Puanı Formülü

*Herhangi bir* video içeriği için, etkileşim puanı `(likes / views) * 10` formülüne göre hesaplanmalıdır (views > 0 ise).

**Doğrular: Gereksinimler 5.1**

### Özellik 12: Makale Etkileşim Puanı Formülü

*Herhangi bir* makale içeriği için, etkileşim puanı `(reactions / reading_time) * 5` formülüne göre hesaplanmalıdır (reading_time > 0 ise).

**Doğrular: Gereksinimler 5.2**

### Özellik 13: Etkileşim Puanı Negatif Olmamalı (Invariant)

*Herhangi bir* içerik için, hesaplanan etkileşim puanı negatif olmamalıdır (>= 0).

**Doğrular: Gereksinimler 5.5**

### Özellik 14: Final Skor Formülü

*Herhangi bir* içerik için, final skor `(temel_skor * tür_katsayısı) + güncellik_puanı + etkileşim_puanı` formülüne göre hesaplanmalıdır.

**Doğrular: Gereksinimler 3.5**

### Özellik 15: Skor Sıralaması

*Herhangi bir* içerik listesi için, "score" sıralaması seçildiğinde sonuçlar skordan yükseğe doğru sıralanmalıdır.

**Doğrular: Gereksinimler 6.1**

### Özellik 16: Tarih Sıralaması

*Herhangi bir* içerik listesi için, "date" sıralaması seçildiğinde sonuçlar yayın tarihine göre yeniden eskiye sıralanmalıdır.

**Doğrular: Gereksinimler 6.2**

### Özellik 17: Geçersiz Sıralama Varsayılanı

*Herhangi bir* geçersiz sıralama kriteri için, sistem varsayılan olarak "score" sıralamasını kullanmalıdır.

**Doğrular: Gereksinimler 6.4**

### Özellik 18: Sayfalama Doğruluğu

*Herhangi bir* sayfa numarası ve sayfa boyutu için, döndürülen sonuçlar doğru offset ve limit ile hesaplanmalıdır.

**Doğrular: Gereksinimler 7.1, 7.2**

### Özellik 19: Sayfalama Metadata Doğruluğu

*Herhangi bir* arama sonucu için, sayfalama bilgisi (total, page, per_page, total_pages) doğru hesaplanmalıdır.

**Doğrular: Gereksinimler 7.4**

### Özellik 20: Cache Anahtarı Tutarlılığı

*Herhangi bir* arama parametresi seti için, aynı parametreler her zaman aynı cache anahtarını üretmelidir.

**Doğrular: Gereksinimler 8.4**

### Özellik 21: JSON DTO Dönüşümü

*Herhangi bir* geçerli JSON içerik verisi için, sistem bunu ContentDTO'ya dönüştürebilmelidir.

**Doğrular: Gereksinimler 10.2**

### Özellik 22: JSON Eksik Alan Yönetimi

*Herhangi bir* eksik alana sahip JSON verisi için, sistem varsayılan değerler kullanarak ContentDTO oluşturmalıdır.

**Doğrular: Gereksinimler 10.5**

### Özellik 23: XML DTO Dönüşümü

*Herhangi bir* geçerli XML içerik verisi için, sistem bunu ContentDTO'ya dönüştürebilmelidir.

**Doğrular: Gereksinimler 11.2**

### Özellik 24: XML Eksik Alan Yönetimi

*Herhangi bir* eksik alana sahip XML verisi için, sistem varsayılan değerler kullanarak ContentDTO oluşturmalıdır.

**Doğrular: Gereksinimler 11.5**

### Özellik 25: İçerik DTO Yapısı

*Herhangi bir* ContentDTO için, id, title, type, metrics, published_at, tags ve score alanları bulunmalıdır.

**Doğrular: Gereksinimler 13.4**

### Özellik 26: Sayfalama Yapısı

*Herhangi bir* sayfalama bilgisi için, total, page, per_page ve total_pages alanları bulunmalıdır.

**Doğrular: Gereksinimler 13.5**

## Hata Yönetimi

### Validasyon Hataları

- **Geçersiz tür**: "Invalid content type. Supported types: video, article"
- **Geçersiz sıralama**: Varsayılan sıralama kullanılır (hata fırlatılmaz)
- **Geçersiz sayfa**: "Page number must be positive"

### İş Mantığı Hataları

- **İçerik bulunamadı**: Boş liste döndürülür (hata değil)
- **Sıfıra bölme**: Etkileşim puanı 0 olarak hesaplanır

### Sistem Hataları

- **Veritabanı hatası**: "Database error occurred"
- **Cache hatası**: "Cache operation failed"
- **Provider hatası**: "Failed to fetch contents from provider"
- **JSON/XML parse hatası**: "Invalid data format"

## Test Stratejisi

### İkili Test Yaklaşımı

Sistem hem birim testleri hem de özellik tabanlı testler kullanır:

- **Birim testler**: Belirli örnekler, kenar durumlar, entegrasyon noktaları
- **Özellik testleri**: Tüm girdiler üzerinde evrensel özellikler

### Özellik Tabanlı Test Konfigürasyonu

- **Test kütüphanesi**: PHPUnit ile Eris (PHP için property-based testing)
- **Minimum iterasyon**: Her özellik testi için 100 iterasyon
- **Test etiketleme**: `Feature: mevcut-arama-sistemi, Property {numara}: {özellik_metni}`

### Test Kapsamı

**Birim Testler**:
- SearchController HTTP işlemleri
- SearchService arama koordinasyonu
- ScoringService skor hesaplamaları
- CacheManager cache işlemleri
- ContentRepository CRUD işlemleri
- Provider'lar (JSON/XML parsing)
- Kenar durumlar (boş sonuçlar, null değerler, sıfıra bölme)

**Özellik Testleri**:
- Arama özellikleri (1-5)
- Skorlama özellikleri (6-14)
- Sıralama özellikleri (15-17)
- Sayfalama özellikleri (18-19)
- Cache özellikleri (20)
- DTO dönüşüm özellikleri (21-26)

### Test Veri Üretimi

**Rastgele İçerik Üretimi**:
- Rastgele ID'ler (UUID)
- Rastgele başlıklar (10-50 karakter)
- Rastgele türler (video/article)
- Rastgele metrikler:
  - Video: views (0-1000000), likes (0-50000)
  - Makale: reading_time (1-30), reactions (0-1000)
- Rastgele yayın tarihleri (son 2 yıl)
- Rastgele etiketler (1-5 etiket, 10 etiket havuzundan)

**Rastgele Arama Parametreleri**:
- Rastgele anahtar kelimeler
- Rastgele türler (video/article/null)
- Rastgele sıralama (score/date)
- Rastgele sayfa numaraları (1-10)
- Rastgele sayfa boyutları (5-50)

### Entegrasyon Testleri

- Controller → Service → Repository akışı
- Cache hit/miss senaryoları
- Provider senkronizasyonu
- Hata yönetimi ve loglama
- API yanıt formatları

### Mevcut Testler

Sistemde zaten mevcut testler var:
- `tests/Controller/SearchControllerTest.php`
- `tests/Service/ScoringServiceTest.php`
- `tests/Service/CacheManagerTest.php`
- `tests/Provider/JsonProviderTest.php`
- `tests/Provider/XmlProviderTest.php`

Bu testler gözden geçirilmeli ve özellik testleri ile tamamlanmalıdır.
