# 🎯 Mülakat Hazırlık Rehberi - Search Engine Service

## 📊 Proje Özeti (Elevator Pitch)

"Modern bir içerik arama ve sıralama servisi geliştirdim. Birden fazla sağlayıcıdan (JSON ve XML formatlarında) içerik toplayıp, akıllı bir puanlama algoritması ile sıralıyor ve RESTful API üzerinden sunuyorum. Clean Architecture ve Hexagonal Architecture prensiplerini kullandım. Redis ile önbellekleme, bildirim sistemi, Docker containerization ve kapsamlı unit testler ekledim. PHP 8.4 ve Symfony 7.0 ile production-ready bir sistem."

---

## 🔍 1. ARAMA ALGORİTMALARI

### Kullanılan Algoritmalar

#### A) Basit String Matching (LIKE Query)
```sql
WHERE title LIKE '%keyword%' OR tags LIKE '%keyword%'
```

**Neden bu yaklaşım?**
- Basit ve hızlı implementasyon
- MySQL'in native LIKE operatörü optimize edilmiş
- Index kullanımı ile performans artışı
- Küçük-orta ölçekli veri setleri için yeterli

**Alternatifler (İyileştirme önerileri):**
- **Full-Text Search**: MySQL FULLTEXT index
- **Elasticsearch**: Büyük veri setleri için
- **Fuzzy Search**: Typo tolerance için Levenshtein distance
- **Trigram Search**: PostgreSQL pg_trgm extension

#### B) Weighted Scoring Algorithm (Ağırlıklı Puanlama)

**Formül:**
```
Final Score = (Base Score × Content Type Coefficient) + Recency Score + Engagement Score
```

**Bileşenler:**

1. **Base Score (Temel Puan)**
   - Video: `views / 1000 + likes / 100`
   - Text: `reading_time + reactions / 50`
   
2. **Content Type Coefficient (İçerik Türü Katsayısı)**
   - Video: 1.5 (daha yüksek engagement)
   - Text: 1.0 (baseline)

3. **Recency Score (Güncellik Puanı)**
   - 1 hafta içinde: +5
   - 1 ay içinde: +3
   - 3 ay içinde: +1
   - Daha eski: +0

4. **Engagement Score (Etkileşim Puanı)**
   - Video: `(likes / views) × 10`
   - Text: `(reactions / reading_time) × 5`

**Neden bu algoritma?**
- Farklı içerik türlerini adil karşılaştırma
- Güncellik faktörü (fresh content boost)
- Engagement quality (sadece view değil, etkileşim oranı)
- Normalize edilmiş metrikler (büyük sayıları kontrol altına alma)

**Alternatif Algoritmalar:**
- **TF-IDF**: Term frequency - inverse document frequency
- **BM25**: Best Matching 25 (Elasticsearch default)
- **Learning to Rank**: Machine learning based
- **Collaborative Filtering**: User behavior based

---

## 🏗️ 2. MİMARİ DETAYLARI

### A) Clean Architecture + Hexagonal Architecture (Ports & Adapters)

```
┌─────────────────────────────────────────────────────────┐
│                    Presentation Layer                    │
│  (Controllers, Commands, Templates)                      │
│  - SearchController                                      │
│  - DashboardController                                   │
│  - CLI Commands                                          │
└────────────────┬────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────┐
│                   Application Layer                      │
│  (Use Cases, Services, DTOs)                            │
│  - SearchService (orchestration)                        │
│  - NotificationManager                                   │
│  - ProviderManager                                       │
│  - DTOs (SearchRequestDTO, ContentDTO)                  │
└────────────────┬────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────┐
│                    Domain Layer                          │
│  (Business Logic, Entities)                             │
│  - ScoringService (core algorithm)                      │
│  - Content Entity                                        │
│  - NotificationUser Entity                              │
└────────────────┬────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────┐
│                 Infrastructure Layer                     │
│  (External Services, Adapters)                          │
│  - Repositories (ContentRepository)                     │
│  - Providers (JsonProvider, XmlProvider)                │
│  - Channels (EmailChannel, SmsChannel)                  │
│  - CacheManager (Redis adapter)                         │
└─────────────────────────────────────────────────────────┘
```

### B) Kullanılan Design Patterns

#### 1. **Repository Pattern**
```php
interface ContentRepository {
    public function search(string $keyword, ?string $type): array;
    public function save(Content $content): void;
}
```
**Amaç**: Data access logic'i business logic'ten ayırma

#### 2. **Strategy Pattern**
```php
interface ProviderInterface {
    public function fetchContents(): array;
}

class JsonProvider implements ProviderInterface { }
class XmlProvider implements ProviderInterface { }
```
**Amaç**: Farklı provider'ları runtime'da değiştirilebilir yapma

#### 3. **DTO Pattern (Data Transfer Object)**
```php
class ContentDTO {
    public function __construct(
        public string $id,
        public string $title,
        public string $type,
        // ...
    ) {}
}
```
**Amaç**: Layer'lar arası veri transferi, validation

#### 4. **Facade Pattern**
```php
class SearchService {
    // Karmaşık operasyonları basit interface'e indirgeme
    public function search(SearchRequestDTO $request): array
}
```

#### 5. **Service Layer Pattern**
- Business logic'i controller'dan ayırma
- Reusable, testable services

#### 6. **Dependency Injection**
```php
public function __construct(
    private ContentRepository $repository,
    private ScoringService $scoringService,
    private CacheManager $cacheManager
) {}
```
**Amaç**: Loose coupling, testability

#### 7. **Factory Pattern** (implicit)
- Symfony service container
- Provider'ların tagged iterator ile oluşturulması

### C) SOLID Principles Uygulaması

#### S - Single Responsibility Principle
- Her class tek bir sorumluluğa sahip
- `ScoringService`: Sadece puanlama
- `CacheManager`: Sadece cache yönetimi
- `ProviderManager`: Sadece provider orchestration

#### O - Open/Closed Principle
- Yeni provider eklemek için mevcut kodu değiştirmiyoruz
- Sadece yeni `ProviderInterface` implementasyonu ekliyoruz

#### L - Liskov Substitution Principle
- `JsonProvider` ve `XmlProvider` birbirinin yerine kullanılabilir
- Interface contract'ı bozmuyor

#### I - Interface Segregation Principle
- `ProviderInterface`: Sadece gerekli metotlar
- `NotificationChannelInterface`: Minimal interface

#### D - Dependency Inversion Principle
- High-level modules (SearchService) low-level modules'e (Repository) bağımlı değil
- Her ikisi de abstraction'a (interface) bağımlı

### D) Veri Akışı (Data Flow)

```
1. HTTP Request
   ↓
2. Controller (validation, DTO creation)
   ↓
3. SearchService (orchestration)
   ↓
4. CacheManager (cache check)
   ↓ (cache miss)
5. ContentRepository (database query)
   ↓
6. ScoringService (score calculation)
   ↓
7. Sorting & Pagination
   ↓
8. CacheManager (cache store)
   ↓
9. Response (JSON)
```

### E) Caching Strategy

**Cache Key Generation:**
```php
$key = 'search_' . md5(serialize([
    'keyword' => $keyword,
    'type' => $type,
    'sortBy' => $sortBy,
    'page' => $page,
    'perPage' => $perPage
]));
```

**Cache Invalidation:**
- Time-based: 1 saat TTL
- Event-based: Sync işleminde `clear()`

**Cache Layers:**
1. Redis (distributed cache)
2. OPcache (PHP bytecode cache)

---

## 🎤 3. MÜLAKATTA SORABİLECEKLERİ SORULAR

### A) Teknik Sorular

#### 1. Mimari ve Tasarım

**S: Neden Clean Architecture kullandınız?**
**C:** 
- Business logic'i framework'ten bağımsız tutmak
- Test edilebilirlik artırmak
- Değişime açık, bakımı kolay kod
- Layer'lar arası bağımlılıkları kontrol etmek
- Örnek: Provider değiştirmek istediğimde sadece infrastructure layer'ı değiştiriyorum

**S: Hexagonal Architecture'ın avantajları nedir?**
**C:**
- Ports (interfaces) ve Adapters (implementations) ayrımı
- External dependencies'i kolayca mock'layabilme
- Farklı delivery mechanisms (HTTP, CLI, Queue) kullanabilme
- Domain logic'i izole etme

**S: Repository Pattern neden kullandınız?**
**C:**
- Data access logic'i business logic'ten ayırmak
- Database değişikliklerini kolaylaştırmak
- Test'lerde mock repository kullanabilmek
- Query logic'i merkezi yönetmek

#### 2. Performans ve Ölçeklenebilirlik

**S: Sistem yavaşlarsa ne yaparsınız?**
**C:**
1. **Profiling**: Xdebug, Blackfire ile bottleneck tespiti
2. **Database Optimization**:
   - Index ekleme (title, tags, published_at)
   - Query optimization (EXPLAIN kullanımı)
   - Connection pooling
3. **Cache Strategy**:
   - Cache hit rate artırma
   - Cache warming
   - Multi-level caching (Redis + CDN)
4. **Horizontal Scaling**:
   - Load balancer ekleme
   - Read replicas (master-slave)
   - Sharding
5. **Async Processing**:
   - Queue system (RabbitMQ, Redis Queue)
   - Background jobs için Symfony Messenger

**S: 1 milyon içerik olsa ne değişir?**
**C:**
1. **Database**:
   - Partitioning (tarih bazlı)
   - Full-text search index
   - Elasticsearch'e geçiş
2. **Caching**:
   - Aggressive caching
   - Cache preloading
   - CDN kullanımı
3. **Search**:
   - Elasticsearch/Algolia entegrasyonu
   - Faceted search
   - Auto-complete için Trie data structure
4. **Architecture**:
   - Microservices'e geçiş
   - CQRS pattern (Command Query Responsibility Segregation)
   - Event sourcing

**S: Redis çökerse ne olur?**
**C:**
- Graceful degradation: Cache miss olarak davranır
- Database'den direkt çeker (yavaş ama çalışır)
- Redis Sentinel ile high availability
- Redis Cluster ile sharding
- Fallback cache (APCu, Memcached)

#### 3. Güvenlik

**S: SQL Injection'dan nasıl korunuyorsunuz?**
**C:**
- Doctrine ORM kullanıyorum (prepared statements)
- User input'u hiçbir zaman direkt query'de kullanmıyorum
- Parameterized queries
- Input validation ve sanitization

**S: Rate limiting var mı?**
**C:**
- Şu an yok ama eklenebilir:
```php
// Symfony Rate Limiter component
use Symfony\Component\RateLimiter\RateLimiterFactory;

$limiter = $factory->create($request->getClientIp());
if (!$limiter->consume(1)->isAccepted()) {
    throw new TooManyRequestsHttpException();
}
```

**S: API authentication nasıl?**
**C:**
- Şu an public API
- Production için:
  - JWT tokens (LexikJWTAuthenticationBundle)
  - API keys
  - OAuth2 (league/oauth2-server-bundle)
  - Rate limiting per user

#### 4. Testing

**S: Test coverage nedir?**
**C:**
- 55 test, 174 assertion
- Unit tests: Services, DTOs, Providers
- Integration tests: NotificationManager
- Controller tests: API endpoints
- Coverage artırmak için:
  - Feature tests eklemek
  - Edge cases test etmek
  - Mutation testing (Infection PHP)

**S: Nasıl test ediyorsunuz?**
**C:**
```php
// Unit test - dependencies mock'lanır
$scoringService = new ScoringService();
$score = $scoringService->calculateScore($content);
$this->assertGreaterThan(0, $score);

// Integration test - gerçek dependencies
$client = static::createClient();
$client->request('GET', '/api/search?keyword=test');
$this->assertResponseIsSuccessful();
```

#### 5. Bildirim Sistemi

**S: Email gönderimi başarısız olursa?**
**C:**
1. **Retry Mechanism**:
```php
try {
    $this->mailer->send($email);
} catch (\Exception $e) {
    // Queue'ya at, sonra tekrar dene
    $this->messageBus->dispatch(new SendEmailMessage($email));
}
```
2. **Dead Letter Queue**: Başarısız mesajları ayrı queue'da sakla
3. **Monitoring**: Failed email count metric'i
4. **Fallback**: SMS gönder veya admin'e bildir

**S: 1000 kullanıcıya aynı anda email gönderirseniz?**
**C:**
- Async processing (Symfony Messenger)
- Batch processing (100'er 100'er)
- Rate limiting (SMTP provider limits)
- Queue system (RabbitMQ)
```php
foreach ($users as $user) {
    $this->messageBus->dispatch(
        new SendNotificationMessage($user, $message)
    );
}
```

### B) Davranışsal Sorular

**S: Projeyi geliştirirken en zorlandığınız şey?**
**C:**
- Farklı provider formatlarını normalize etmek
- Adil puanlama algoritması tasarlamak
- Cache invalidation stratejisi belirlemek
- Email sistemi debug'ı (MailHog entegrasyonu)

**S: Projeyi nasıl iyileştirebilirsiniz?**
**C:**
1. **Search Improvements**:
   - Elasticsearch entegrasyonu
   - Fuzzy search
   - Auto-complete
   - Faceted search (filters)
   - Search suggestions

2. **Performance**:
   - Database query optimization
   - CDN kullanımı
   - Lazy loading
   - Image optimization

3. **Features**:
   - User authentication
   - Favorite/bookmark system
   - Search history
   - Analytics dashboard
   - A/B testing for scoring algorithm

4. **DevOps**:
   - CI/CD pipeline (GitHub Actions)
   - Automated deployment
   - Blue-green deployment
   - Monitoring (Prometheus, Grafana)

**S: Takım çalışmasında nasıl katkı sağlarsınız?**
**C:**
- Code review yaparak
- Dokümantasyon yazarak
- Knowledge sharing (tech talks)
- Pair programming
- Mentoring junior developers

### C) Sistem Tasarımı Soruları

**S: Bu sistemi microservices'e nasıl dönüştürürsünüz?**
**C:**
```
┌─────────────────┐
│   API Gateway   │
└────────┬────────┘
         │
    ┌────┴────┬────────┬──────────┐
    │         │        │          │
┌───▼───┐ ┌──▼──┐ ┌───▼────┐ ┌──▼────────┐
│Search │ │Score│ │Provider│ │Notification│
│Service│ │Svc  │ │Service │ │Service    │
└───┬───┘ └──┬──┘ └───┬────┘ └──┬────────┘
    │        │        │          │
    └────────┴────────┴──────────┘
              │
         ┌────▼────┐
         │Event Bus│
         │(Kafka)  │
         └─────────┘
```

**S: Monitoring nasıl yaparsınız?**
**C:**
1. **Metrics** (Prometheus):
   - Request rate, latency, error rate
   - Cache hit rate
   - Database query time
   - Queue length

2. **Logging** (ELK Stack):
   - Structured logging (JSON)
   - Log levels (DEBUG, INFO, ERROR)
   - Correlation IDs

3. **Tracing** (Jaeger):
   - Distributed tracing
   - Request flow visualization

4. **Alerting** (Grafana):
   - High error rate
   - Slow response time
   - Cache miss rate spike

---

## 💡 4. GÜÇLÜ YANLARINIZ

### Teknik Yetkinlikler
- ✅ Modern PHP (8.4) ve Symfony (7.0)
- ✅ Clean Architecture ve Design Patterns
- ✅ Docker ve containerization
- ✅ Testing (Unit, Integration)
- ✅ Caching strategies (Redis)
- ✅ RESTful API design
- ✅ Database design ve optimization
- ✅ Git ve version control

### Soft Skills
- ✅ Problem solving (algoritma tasarımı)
- ✅ Dokümantasyon (comprehensive README)
- ✅ Code organization (clean, maintainable)
- ✅ Best practices (SOLID, DRY, KISS)

---

## 🚀 5. DEMO SENARYOSU

Mülakatta canlı demo yapmanız istenirse:

### Senaryo 1: Basit Arama
```bash
# 1. Docker'ı başlat
docker-compose up -d

# 2. Verileri sync et
docker-compose exec php php bin/console app:sync-contents

# 3. Dashboard'u göster
open http://localhost:8080

# 4. API'yi test et
curl "http://localhost:8080/api/search?keyword=docker&type=video&sortBy=score"
```

### Senaryo 2: Bildirim Sistemi
```bash
# 1. Test bildirimi gönder
docker-compose exec php php bin/console app:test-notification --type=error

# 2. MailHog'da göster
open http://localhost:8025

# 3. Log'ları göster
docker-compose exec php tail -f var/log/dev.log
```

### Senaryo 3: Test Coverage
```bash
# Tüm testleri çalıştır
docker-compose exec php php bin/phpunit

# Specific test
docker-compose exec php php bin/phpunit tests/Service/ScoringServiceTest.php
```

---

## 📚 6. HAZIRLIK ÖNERİLERİ

### Teknik Hazırlık
1. **Kodu ezberden açıklayabilin**:
   - ScoringService algoritması
   - Provider pattern implementasyonu
   - Cache strategy

2. **Alternatifleri bilin**:
   - Neden Symfony? (Laravel, Slim, Lumen)
   - Neden MySQL? (PostgreSQL, MongoDB)
   - Neden Redis? (Memcached, APCu)

3. **Trade-off'ları anlayın**:
   - Consistency vs Availability (CAP theorem)
   - Normalization vs Denormalization
   - Sync vs Async processing

### Davranışsal Hazırlık
1. **STAR method** kullanın:
   - Situation: Proje gereksinimi
   - Task: Çözmem gereken problem
   - Action: Yaptığım aksiyonlar
   - Result: Sonuç ve öğrendiklerim

2. **Hikaye anlatın**:
   - "Bu projeyi geliştirirken..."
   - "En zorlandığım kısım..."
   - "En gurur duyduğum özellik..."

---

## 🎯 7. SON TAVSİYELER

### Mülakat Öncesi
- [ ] Projeyi baştan sona çalıştırın
- [ ] README'yi okuyun
- [ ] Testleri çalıştırın
- [ ] Demo senaryolarını prova edin
- [ ] Sorulara cevaplarınızı not alın

### Mülakat Sırasında
- ✅ Özgüvenli ama mütevazı olun
- ✅ Bilmediğiniz şeyi "bilmiyorum ama öğrenmeye açığım" deyin
- ✅ Soru sorun (meraklı olun)
- ✅ Whiteboard kullanın (mimari çizin)
- ✅ Trade-off'ları açıklayın

### Mülakat Sonrası
- ✅ Teşekkür emaili gönderin
- ✅ Sorulan soruları not alın
- ✅ Eksik gördüğünüz yerleri geliştirin

---

## 📖 8. EK KAYNAKLAR

### Kitaplar
- Clean Architecture (Robert C. Martin)
- Design Patterns (Gang of Four)
- Domain-Driven Design (Eric Evans)

### Online Kaynaklar
- Symfony Documentation
- PHP The Right Way
- Martin Fowler's Blog

### Pratik
- LeetCode (algorithm practice)
- System Design Primer (GitHub)
- Refactoring Guru (design patterns)

---

**Başarılar! 🚀**

Bu proje, modern PHP development, clean code principles ve system design konularında güçlü bir portfolio piece. Özgüvenle anlatın!
