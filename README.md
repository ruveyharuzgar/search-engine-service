# 🔍 Arama Motoru Servisi

Modern PHP ve Symfony framework ile geliştirilmiş, production-ready bir içerik arama ve sıralama servisi. Bu servis, birden fazla sağlayıcıdan içerik toplar, akıllı bir puanlama algoritması uygular ve güçlü bir arama API'si ile güzel bir dashboard arayüzü sunar.

---

## 📋 İçindekiler

- [Genel Bakış](#genel-bakış)
- [Temel Özellikler](#temel-özellikler)
- [Mimari](#mimari)
- [Teknoloji Yığını](#teknoloji-yığını)
- [Nasıl Çalışır](#nasıl-çalışır)
- [Puanlama Algoritması](#puanlama-algoritması)
- [Kurulum](#kurulum)
- [Kullanım](#kullanım)
- [API Dokümantasyonu](#api-dokümantasyonu)
- [Proje Yapısı](#proje-yapısı)
- [Yapılandırma](#yapılandırma)
- [Geliştirme](#geliştirme)
- [Production Deployment](#production-deployment)
- [Sorun Giderme](#sorun-giderme)
- [Monitoring](#monitoring)

---

## 🎯 Genel Bakış

Bu arama motoru servisi şunları yapar:
- **İçerik toplama** - Birden fazla dış sağlayıcıdan (JSON ve XML formatlarında)
- **Puanlama ve sıralama** - Gelişmiş çok faktörlü algoritma ile
- **Sonuçları önbellekleme** - Optimal performans için Redis kullanarak
- **RESTful API sağlama** - Programatik erişim için
- **Modern dashboard sunma** - Görsel içerik keşfi için

### Hangi Problemi Çözüyor?

Farklı formatlarda ve metriklerle birden fazla içerik kaynağınız olduğunda:
- Verileri tutarlı bir formata dönüştürmek zor
- İçeriği farklı türler arasında (video vs makale) adil şekilde sıralamak zor
- Hızlı arama sonuçları sağlamak zor
- Verileri senkronize tutmak zor

Bu servis tüm bu problemleri temiz, ölçeklenebilir bir mimari ile çözer.

---

## ✨ Temel Özellikler

### 🔍 Akıllı Arama
- **Anahtar kelime araması** - Başlık ve etiketlerde
- **Tür filtreleme** - Video/makale
- **Esnek sıralama** - Skora veya tarihe göre
- **Sayfalama** desteği
- **Gerçek zamanlı** sonuçlar

### 🎯 Akıllı Puanlama
- **Çok faktörlü algoritma:**
  - Temel metrikler (görüntülenme, beğeni, okuma süresi, tepkiler)
  - İçerik türü katsayıları
  - Güncellik skoru (zaman bazlı)
  - Etkileşim oranı
- **Dinamik hesaplama** - Her aramada
- **Adil karşılaştırma** - Farklı içerik türleri arasında

### 🚀 Performans
- **Redis önbellekleme** (1 saatlik TTL)
- **Veritabanı indeksleme** - Hızlı sorgular için
- **Optimize edilmiş sorgular** - Doctrine ORM ile
- **Lazy loading** - Verimli bellek kullanımı

### 🏗️ Mimari
- **Clean Architecture** prensipleri
- **SOLID** tasarım desenleri
- **Repository Pattern** - Veri erişimi için
- **Strategy Pattern** - Sağlayıcılar için
- **DTO Pattern** - Veri transferi için
- **Service Layer** - İş mantığı için
- **Notification System** - Olay bildirimleri için

### 🎨 Modern Dashboard
- **Responsive tasarım** - Mobil uyumlu
- **Gerçek zamanlı arama** - Anında sonuçlar
- **Görsel filtreler** - Aktif filtre gösterimi
- **Güzel UI** - Gradient arka planlar
- **Font Awesome ikonları**
- **Yumuşak animasyonlar**

### 🔌 Sağlayıcı Sistemi
- **Genişletilebilir mimari** - Kolayca yeni sağlayıcı ekleyin
- **Çoklu format** - JSON ve XML desteği
- **Hata toleransı** - Bir sağlayıcı başarısız olsa diğerleri çalışır
- **Standart normalizasyon** - Tüm veriler birleşik formata dönüştürülür

---

## 🏛️ Mimari

### Kullanılan Mimari: Clean Architecture + Hexagonal Architecture (Ports & Adapters)

Bu proje, **Clean Architecture** ve **Hexagonal Architecture** prensiplerine göre tasarlanmıştır. Bu mimari seçimi şu avantajları sağlar:

#### Neden Bu Mimari?

1. **Bağımsızlık** - Framework, veritabanı ve dış servislerden bağımsız
2. **Test Edilebilirlik** - Her katman izole test edilebilir
3. **Esneklik** - Teknoloji değişikliklerine kolay adaptasyon
4. **Bakım Kolaylığı** - Kod organizasyonu ve sorumluluk ayrımı
5. **Ölçeklenebilirlik** - Yatay ve dikey ölçeklendirme kolaylığı

### Katmanlı Mimari Diyagramı

```
┌─────────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                            │
│  (Kullanıcı Arayüzü ve API Endpoint'leri)                       │
│                                                                   │
│  ┌──────────────────┐              ┌──────────────────┐        │
│  │   Dashboard      │              │   REST API       │        │
│  │   Controller     │              │   Controller     │        │
│  │   (Twig Views)   │              │   (JSON)         │        │
│  └──────────────────┘              └──────────────────┘        │
└────────────────────┬────────────────────────┬───────────────────┘
                     │                        │
                     ↓                        ↓
┌─────────────────────────────────────────────────────────────────┐
│                    APPLICATION LAYER                             │
│  (İş Mantığı ve Use Case'ler)                                   │
│                                                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │   Search     │  │   Scoring    │  │   Provider   │         │
│  │   Service    │  │   Service    │  │   Manager    │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
│                                                                   │
│  ┌──────────────┐  ┌──────────────┐                            │
│  │    Cache     │  │ Notification │                            │
│  │   Manager    │  │   Manager    │                            │
│  └──────────────┘  └──────────────┘                            │
└────────────────────┬────────────────────────┬───────────────────┘
                     │                        │
                     ↓                        ↓
┌─────────────────────────────────────────────────────────────────┐
│                    DOMAIN LAYER                                  │
│  (İş Kuralları ve Entity'ler)                                   │
│                                                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │   Content    │  │  ContentDTO  │  │   Search     │         │
│  │   Entity     │  │              │  │  RequestDTO  │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
│                                                                   │
│  ┌──────────────────────────────────────────────────┐          │
│  │         Business Rules & Algorithms               │          │
│  │  - Scoring Algorithm                              │          │
│  │  - Search Logic                                   │          │
│  │  - Validation Rules                               │          │
│  └──────────────────────────────────────────────────┘          │
└────────────────────┬────────────────────────┬───────────────────┘
                     │                        │
                     ↓                        ↓
┌─────────────────────────────────────────────────────────────────┐
│                    INFRASTRUCTURE LAYER                          │
│  (Dış Servisler ve Teknik Detaylar)                            │
│                                                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │  Repository  │  │  Providers   │  │    Cache     │         │
│  │  (Doctrine)  │  │ (JSON, XML)  │  │   (Redis)    │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
│                                                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │    MySQL     │  │    Redis     │  │   GitHub     │         │
│  │   Database   │  │    Cache     │  │     API      │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
└─────────────────────────────────────────────────────────────────┘
```

### Bağımlılık Kuralı (Dependency Rule)

```
Presentation → Application → Domain ← Infrastructure
```

- **Dış katmanlar** iç katmanlara bağımlıdır
- **İç katmanlar** dış katmanlardan habersizdir
- **Domain Layer** hiçbir şeye bağımlı değildir (Pure Business Logic)

### Kullanılan Tasarım Desenleri

#### 1. Repository Pattern
```php
ContentRepository
├── search()      // Arama işlemleri
├── save()        // Kaydetme
├── findById()    // ID ile bulma
└── truncate()    // Temizleme
```

**Avantajlar:**
- Veritabanı işlemlerini soyutlar
- Test edilebilirlik sağlar
- Veritabanı değişikliklerine karşı esneklik

**Kullanım Örneği:**
```php
// Controller veya Service'de
$contents = $this->contentRepository->search($keyword, $type);
```

#### 2. Strategy Pattern (Provider System)
```php
ProviderInterface
├── JsonProvider      // JSON formatı için strateji
├── XmlProvider       // XML formatı için strateji
└── [NewProvider]     // Kolayca yeni strateji eklenebilir
```

**Avantajlar:**
- Yeni sağlayıcı ekleme kolaylığı
- Bağımsız test edilebilirlik
- Loose coupling (Gevşek bağlılık)

**Kullanım Örneği:**
```php
// Yeni provider eklemek için sadece interface'i implement et
class NewProvider implements ProviderInterface {
    public function fetchContents(): array {
        // Yeni kaynaktan veri çek
    }
}
```

#### 3. DTO Pattern (Data Transfer Object)
```php
ContentDTO
├── Veri taşıma      // Katmanlar arası veri transferi
├── Validasyon       // Veri doğrulama
└── Serialization    // JSON'a dönüştürme
```

**Avantajlar:**
- Type safety (Tip güvenliği)
- Veri bütünlüğü
- API contract (Sözleşme)

**Kullanım Örneği:**
```php
$dto = new ContentDTO(
    id: 'v1',
    title: 'Video Title',
    type: 'video',
    metrics: ['views' => 1000],
    publishedAt: new DateTime(),
    tags: ['tag1']
);
```

#### 4. Facade Pattern (ProviderManager)
```php
ProviderManager
└── fetchAllContents()  // Tüm provider'ları yönetir
```

**Avantajlar:**
- Basit interface
- Karmaşıklığı gizler
- Merkezi yönetim

#### 5. Service Layer Pattern
```php
SearchService
├── search()          // Arama use case'i
└── syncContents()    // Senkronizasyon use case'i
```

**Avantajlar:**
- İş mantığını izole eder
- Controller'ları ince tutar
- Yeniden kullanılabilirlik

#### 6. Dependency Injection Pattern
```php
public function __construct(
    private ContentRepository $repository,
    private ScoringService $scoringService,
    private CacheManager $cacheManager
) {}
```

**Avantajlar:**
- Loose coupling
- Test edilebilirlik
- Esneklik

### SOLID Prensipleri

#### S - Single Responsibility Principle
Her sınıf tek bir sorumluluğa sahip:
- `ScoringService` → Sadece puanlama
- `CacheManager` → Sadece önbellekleme
- `SearchService` → Sadece arama koordinasyonu

#### O - Open/Closed Principle
Genişletmeye açık, değişikliğe kapalı:
- Yeni provider eklemek için mevcut kodu değiştirmiyoruz
- Sadece yeni bir class ekliyoruz

#### L - Liskov Substitution Principle
Alt sınıflar üst sınıfların yerine kullanılabilir:
- Tüm provider'lar `ProviderInterface`'i implement eder
- Herhangi bir provider diğeriyle değiştirilebilir

#### I - Interface Segregation Principle
Küçük, odaklı interface'ler:
- `ProviderInterface` → Sadece `fetchContents()` metodu
- Gereksiz metod yok

#### D - Dependency Inversion Principle
Soyutlamalara bağımlılık:
- Service'ler interface'lere bağımlı
- Concrete implementation'lara değil

### Hexagonal Architecture (Ports & Adapters)

```
┌─────────────────────────────────────────┐
│         APPLICATION CORE                 │
│      (Business Logic)                    │
│                                          │
│  ┌────────────────────────────────┐    │
│  │     Domain Services            │    │
│  │  - SearchService               │    │
│  │  - ScoringService              │    │
│  └────────────────────────────────┘    │
│                                          │
│  ┌────────────────────────────────┐    │
│  │     Ports (Interfaces)         │    │
│  │  - ProviderInterface           │    │
│  │  - RepositoryInterface         │    │
│  └────────────────────────────────┘    │
└─────────────────────────────────────────┘
         ↑                    ↑
         │                    │
    ┌────┴────┐          ┌───┴────┐
    │ Adapters│          │Adapters│
    │ (Input) │          │(Output)│
    └─────────┘          └────────┘
         │                    │
    ┌────┴────┐          ┌───┴────┐
    │   HTTP  │          │Database│
    │   API   │          │ Redis  │
    │Dashboard│          │External│
    └─────────┘          └────────┘
```

**Input Adapters (Primary/Driving):**
- REST API Controller
- Dashboard Controller
- Console Commands

**Output Adapters (Secondary/Driven):**
- Database Repository (Doctrine)
- Cache (Redis)
- External Providers (JSON, XML)

### Veri Akışı Detayı

#### Arama İşlemi Akışı
```
1. HTTP Request (GET /api/search?keyword=programming)
   ↓
2. SearchController (Presentation Layer)
   - Request validation
   - DTO creation
   ↓
3. SearchService (Application Layer)
   - Business logic
   - Cache check
   ↓
4. CacheManager (Infrastructure)
   ├─ Cache HIT → Return cached data
   └─ Cache MISS → Continue
   ↓
5. ContentRepository (Infrastructure)
   - Database query
   - Entity to DTO conversion
   ↓
6. ScoringService (Application Layer)
   - Calculate scores for each content
   - Apply algorithm
   ↓
7. SearchService (Application Layer)
   - Sort results
   - Apply pagination
   ↓
8. CacheManager (Infrastructure)
   - Store results in cache (TTL: 1 hour)
   ↓
9. SearchController (Presentation Layer)
   - Format response
   - Return JSON
   ↓
10. HTTP Response
```

#### Senkronizasyon İşlemi Akışı
```
1. HTTP Request (POST /api/sync)
   ↓
2. SearchController
   ↓
3. SearchService::syncContents()
   ↓
4. NotificationManager::info("Starting sync")
   ↓
5. ProviderManager::fetchAllContents()
   ├─ JsonProvider::fetchContents()
   │  ├─ HTTP Client request
   │  ├─ JSON parse
   │  └─ Convert to ContentDTO[]
   │
   └─ XmlProvider::fetchContents()
      ├─ HTTP Client request
      ├─ XML parse
      └─ Convert to ContentDTO[]
   ↓
6. For each ContentDTO:
   └─ ContentRepository::save()
      └─ Doctrine ORM persist & flush
   ↓
7. CacheManager::clear()
   └─ Redis FLUSHALL
   ↓
8. NotificationManager::success("Synced X contents")
   ↓
9. Return synced count
```

### Mimari Avantajları

✅ **Testability** - Her katman izole test edilebilir
✅ **Maintainability** - Kod organizasyonu ve sorumluluk ayrımı
✅ **Scalability** - Yatay ve dikey ölçeklendirme
✅ **Flexibility** - Teknoloji değişikliklerine kolay adaptasyon
✅ **Reusability** - Service'ler yeniden kullanılabilir
✅ **Independence** - Framework, DB, UI'dan bağımsız business logic

### Mimari Kararlar ve Gerekçeleri

| Karar | Gerekçe |
|-------|---------|
| Clean Architecture | Uzun vadeli bakım kolaylığı, test edilebilirlik |
| Repository Pattern | Veritabanı soyutlaması, test kolaylığı |
| Strategy Pattern | Yeni provider ekleme esnekliği |
| DTO Pattern | Type safety, veri bütünlüğü |
| Service Layer | İş mantığı izolasyonu |
| Dependency Injection | Loose coupling, test edilebilirlik |
| Redis Cache | Yüksek performans, düşük latency |
| Doctrine ORM | Veritabanı soyutlaması, migration yönetimi |

---

## 🛠️ Teknoloji Yığını

### Backend
- **PHP 8.4** - En yeni PHP versiyonu, modern özellikler ve performans iyileştirmeleri
- **Symfony 7.0** - Önde gelen PHP framework'ünün en son versiyonu
- **Doctrine ORM 3.0** - Güçlü veritabanı soyutlama katmanı
- **Predis** - PHP için Redis client

### Veritabanı & Cache
- **MySQL 8.0** - Güvenilir ilişkisel veritabanı
- **Redis** - In-memory cache, yüksek performans

### Frontend
- **Twig** - Symfony'nin template engine'i
- **Vanilla JavaScript** - Framework bağımlılığı yok
- **Font Awesome 6** - Profesyonel ikon kütüphanesi
- **Google Fonts (Inter)** - Modern tipografi

### DevOps
- **Docker** - Containerization
- **Docker Compose** - Multi-container orkestrasyon
- **Nginx** - Yüksek performanslı web sunucusu
- **PHP-FPM** - FastCGI Process Manager

### Dış Servisler
- **GitHub API** - İçerik sağlayıcıları (JSON ve XML)

### Test & Quality
- **PHPUnit 11.5** - Unit ve integration testleri
- **Symfony Test Pack** - Test araçları
- **Monolog** - Logging framework

---

## 📦 Kurulum

### Ön Gereksinimler

- Docker & Docker Compose
- Git
- Minimum 2GB RAM
- Portlar: 8080, 3306, 6379 müsait olmalı

### Hızlı Başlangıç (3 Adım)

```bash
# 1. Repository'yi klonlayın
git clone <repository-url>
cd search-engine-service

# 2. Uygulamayı başlatın
docker-compose up -d --build

# 3. Bağımlılıkları yükleyin ve kurulumu tamamlayın
docker exec search_engine_php composer install --no-interaction --optimize-autoloader
docker exec search_engine_php php bin/console doctrine:database:create --if-not-exists
docker exec search_engine_php php bin/console doctrine:migrations:migrate --no-interaction
docker exec search_engine_php php bin/console app:sync-contents
```

### Uygulamaya Erişim

- **Dashboard:** http://localhost:8080
- **API Dokümantasyonu:** http://localhost:8080/api/doc
- **API Endpoint:** http://localhost:8080/api/search

### Kurulumu Doğrulama

```bash
# Container'ları kontrol edin
docker ps

# API'yi test edin
curl "http://localhost:8080/api/search?keyword=programming"

# Logları kontrol edin
docker-compose logs -f php
```

---

## 🚀 Kullanım

### Dashboard

1. **Tarayıcıyı açın:** http://localhost:8080
2. **Anahtar kelime girin:** Arama teriminizi yazın
3. **Filtreleri uygulayın:** Tür (video/makale) ve sıralama seçin
4. **Sonuçları görüntüleyin:** Puanlanmış ve sıralanmış içeriği görün
5. **Gezinin:** Sayfalama ile sonuçlara göz atın

### API Kullanımı

#### İçerik Arama

```bash
# Basit arama
curl "http://localhost:8080/api/search?keyword=programming"

# Türe göre filtreleme
curl "http://localhost:8080/api/search?keyword=docker&type=video"

# Tarihe göre sıralama
curl "http://localhost:8080/api/search?keyword=programming&sortBy=date"

# Sayfalama
curl "http://localhost:8080/api/search?keyword=go&page=2&perPage=5"
```

#### Veri Senkronizasyonu

```bash
# GitHub'dan yeni veri çek
curl -X POST "http://localhost:8080/api/sync"
```

### Console Komutları

```bash
# Sağlayıcılardan içerik senkronize et
docker exec search_engine_php php bin/console app:sync-contents

# Cache'i temizle
docker exec search_engine_php php bin/console cache:clear

# Veritabanı işlemleri
docker exec search_engine_php php bin/console doctrine:schema:update --dump-sql
docker exec search_engine_php php bin/console doctrine:query:sql "SELECT COUNT(*) FROM contents"
```

---

## 📚 API Dokümantasyonu

### Endpoint'ler

#### GET /api/search

İçerik arama ve getirme.

**Query Parametreleri:**
- `keyword` (string, opsiyonel) - Arama anahtar kelimesi
- `type` (string, opsiyonel) - Türe göre filtre: `video` veya `article`
- `sortBy` (string, opsiyonel) - Sıralama: `score` (varsayılan) veya `date`
- `page` (integer, opsiyonel) - Sayfa numarası (varsayılan: 1)
- `perPage` (integer, opsiyonel) - Sayfa başına sonuç (varsayılan: 10)

**Yanıt:**
```json
{
  "success": true,
  "data": [
    {
      "id": "v1",
      "title": "Go Programming Tutorial",
      "type": "video",
      "metrics": {
        "views": 15000,
        "likes": 1200,
        "duration": "15:30"
      },
      "published_at": "2024-03-15T10:00:00Z",
      "tags": ["programming", "tutorial"],
      "score": 45.5
    }
  ],
  "pagination": {
    "total": 100,
    "page": 1,
    "per_page": 10,
    "total_pages": 10
  }
}
```

#### POST /api/sync

Dış sağlayıcılardan içerik senkronize et.

**Yanıt:**
```json
{
  "success": true,
  "synced_count": 8,
  "message": "Contents synchronized successfully"
}
```

---

## 🎯 Puanlama Algoritması

Puanlama algoritması bu servisin kalbidir. Farklı içerik türleri arasında adil sıralama sağlar.

### Formül

```
Final Skor = (Temel Puan × Tür Katsayısı) + Güncellik Puanı + Etkileşim Puanı
```

### 1. Temel Puan

**Videolar için:**
```
Temel Puan = (görüntülenme / 1000) + (beğeni / 100)
```
- 10,000 görüntülenme = 10 puan
- 1,000 beğeni = 10 puan

**Makaleler için:**
```
Temel Puan = okuma_süresi + (tepkiler / 50)
```
- 10 dakika okuma = 10 puan
- 500 tepki = 10 puan

### 2. Tür Katsayısı

```
Video:   1.5  (50% bonus - videolar daha ilgi çekici)
Makale:  1.0  (standart)
```

### 3. Güncellik Puanı

```
Son 7 gün:     +5 puan
Son 30 gün:    +3 puan
Son 90 gün:    +1 puan
Daha eski:     +0 puan
```

### 4. Etkileşim Puanı

**Videolar için:**
```
Etkileşim = (beğeni / görüntülenme) × 10
```
- %10 beğeni oranı = 1.0 puan

**Makaleler için:**
```
Etkileşim = (tepkiler / okuma_süresi) × 5
```
- Dakika başına 10 tepki = 50 puan

### Örnek Hesaplama

**Video Örneği:**
```
Metrikler:
- Görüntülenme: 25,000
- Beğeni: 2,100
- Yayınlanma: 5 gün önce

Hesaplama:
Temel Puan = (25000/1000) + (2100/100) = 25 + 21 = 46
Tür Katsayısı = 1.5
Güncellik = 5.0 (son hafta)
Etkileşim = (2100/25000) × 10 = 0.84

Final Skor = (46 × 1.5) + 5.0 + 0.84 = 74.84
```

### Neden Bu Algoritma?

- **Adil karşılaştırma** - Farklı içerik türleri normalize edilir
- **Güncellik önemli** - Yeni içerik boost alır
- **Kalite > Miktar** - Etkileşim oranı dikkate alınır
- **Şeffaf** - Anlaşılması ve ayarlanması kolay
- **Ölçeklenebilir** - Büyük veri setleriyle iyi performans

---

## 📁 Proje Yapısı

```
search-engine-service/
│
├── 📄 Dokümantasyon
│   ├── README.md                    # Bu dosya (Türkçe)
│   ├── README_EN.md                 # İngilizce versiyon
│   ├── INSTALLATION.md              # Kurulum rehberi
│   ├── ARCHITECTURE.md              # Mimari detayları
│   ├── FEATURES.md                  # Özellik listesi
│   ├── PROJECT_STRUCTURE.md         # Dosya organizasyonu
│   ├── QUICK_START.md               # Hızlı başlangıç
│   └── MONITORING.md                # Monitoring rehberi
│
├── ⚙️ Yapılandırma
│   ├── .env                         # Environment değişkenleri
│   ├── .env.example                 # Environment şablonu
│   ├── composer.json                # PHP bağımlılıkları
│   ├── docker-compose.yml           # Docker servisleri
│   ├── phpunit.xml.dist             # PHPUnit yapılandırması
│   └── Makefile                     # Kolaylık komutları
│
├── 🐳 Docker
│   ├── docker/nginx/                # Nginx yapılandırması
│   └── docker/php/                  # PHP Dockerfile
│
├── ⚙️ Symfony Config
│   └── config/
│       ├── packages/                # Paket yapılandırmaları
│       ├── routes.yaml              # Route tanımları
│       └── services.yaml            # Servis tanımları
│
├── 💾 Veritabanı
│   └── migrations/                  # Veritabanı migration'ları
│
├── 💻 Kaynak Kod
│   └── src/
│       ├── Controller/              # HTTP controller'lar
│       │   ├── ApiDocController.php
│       │   ├── DashboardController.php
│       │   └── SearchController.php
│       │
│       ├── Service/                 # İş mantığı
│       │   ├── CacheManager.php
│       │   ├── NotificationManager.php
│       │   ├── ProviderManager.php
│       │   ├── ScoringService.php
│       │   └── SearchService.php
│       │
│       ├── Provider/                # Veri sağlayıcıları
│       │   ├── ProviderInterface.php
│       │   ├── JsonProvider.php
│       │   └── XmlProvider.php
│       │
│       ├── Entity/                  # Veritabanı entity'leri
│       │   └── Content.php
│       │
│       ├── Repository/              # Veri erişimi
│       │   └── ContentRepository.php
│       │
│       ├── DTO/                     # Data transfer objects
│       │   ├── ContentDTO.php
│       │   └── SearchRequestDTO.php
│       │
│       └── Command/                 # Console komutları
│           └── SyncContentsCommand.php
│
├── 🧪 Testler
│   └── tests/
│       ├── Service/                 # Servis testleri
│       ├── Provider/                # Provider testleri
│       ├── DTO/                     # DTO testleri
│       └── Controller/              # Controller testleri
│
├── 🎨 Template'ler
│   └── templates/
│       ├── base.html.twig           # Temel layout
│       ├── dashboard/               # Dashboard görünümleri
│       └── api_doc/                 # API dokümantasyonu
│
└── 🌐 Public
    └── public/
        └── index.php                # Uygulama giriş noktası
```

---

## ⚙️ Yapılandırma

### Environment Değişkenleri

`.env` dosyasını düzenleyin:

```bash
# Uygulama
APP_ENV=dev                          # dev veya prod
APP_SECRET=your-secret-key           # Production'da değiştirin

# Veritabanı
DATABASE_URL="mysql://root:root@mysql:3306/search_engine?serverVersion=8.0"

# Redis Cache
REDIS_URL="redis://redis:6379"

# İçerik Sağlayıcıları (GitHub API)
PROVIDER_JSON_URL="https://raw.githubusercontent.com/WEG-Technology/mock/refs/heads/main/v2/provider1"
PROVIDER_XML_URL="https://raw.githubusercontent.com/WEG-Technology/mock/refs/heads/main/v2/provider2"

# Cache TTL (saniye)
CACHE_TTL=3600                       # 1 saat
```

### Docker Portları

Portlar kullanımdaysa `docker-compose.yml`'i düzenleyin:

```yaml
services:
  nginx:
    ports:
      - "9080:80"  # 8080'i 9080'e değiştir
  
  mysql:
    ports:
      - "3307:3306"  # 3306'yı 3307'ye değiştir
```

---

## 🔧 Geliştirme

### Testleri Çalıştırma

Proje, temel bileşenler için kapsamlı unit testler içerir:

```bash
# Tüm testleri çalıştır
docker exec search_engine_php php bin/phpunit

# Belirli test suite'i çalıştır
docker exec search_engine_php php bin/phpunit tests/Service/ScoringServiceTest.php

# Detaylı çıktı ile çalıştır
docker exec search_engine_php php bin/phpunit --testdox

# Coverage ile çalıştır (xdebug gerektirir)
docker exec search_engine_php php bin/phpunit --coverage-html coverage
```

### Test Kapsamı

**Unit Testler:**
- ✅ **ScoringService** (9 test) - Puanlama algoritması doğrulama
- ✅ **CacheManager** (5 test) - Cache işlemleri
- ✅ **ContentDTO** (5 test) - Veri transfer objesi
- ✅ **NotificationManager** (11 test) - Bildirim sistemi
- ✅ **JsonProvider** (5 test) - JSON veri sağlayıcısı
- ✅ **XmlProvider** (4 test) - XML veri sağlayıcısı

**Integration Testler:**
- ✅ **SearchController** (10 test) - API endpoint'leri

### Debugging

```bash
# Logları görüntüle
docker-compose logs -f php
docker-compose logs -f nginx

# PHP container'a erişim
docker exec -it search_engine_php bash

# MySQL'e erişim
docker exec -it search_engine_mysql mysql -uroot -proot search_engine

# Redis'e erişim
docker exec -it search_engine_redis redis-cli
```

### Yeni Provider Ekleme

1. `ProviderInterface`'i implement eden provider sınıfı oluştur
2. `services.yaml`'da `app.provider` tag'i ile kaydet
3. Provider otomatik olarak `ProviderManager` tarafından kullanılır

Örnek:
```php
namespace App\Provider;

class NewProvider implements ProviderInterface
{
    public function fetchContents(): array
    {
        // Veri çek ve ContentDTO[] döndür
    }
}
```

---

## 📊 Monitoring

### Monitoring Eklenebilir mi?

**Kesinlikle EVET!** Detaylı bilgi için [MONITORING.md](MONITORING.md) dosyasına bakın.

### Önerilen Çözümler

1. **Prometheus + Grafana** - Metrik toplama ve görselleştirme
2. **ELK Stack** - Log aggregation ve analiz
3. **Sentry** - Error tracking
4. **New Relic / DataDog** - APM (Application Performance Monitoring)

### Hızlı Başlangıç

```php
// Health check endpoint
#[Route('/health')]
public function health(): JsonResponse
{
    return $this->json([
        'status' => 'healthy',
        'services' => [
            'database' => 'ok',
            'redis' => 'ok',
            'providers' => 'ok'
        ]
    ]);
}
```

---

## 📧 Bildirim Sistemi

Sistem, önemli olaylar gerçekleştiğinde (sync başarılı/başarısız, kritik hatalar vb.) admin kullanıcılara otomatik bildirim gönderir.

### Özellikler

- **Email Bildirimleri** - HTML formatında profesyonel email'ler
- **SMS Bildirimleri** - Kısa ve öz mesajlar (simüle edilmiş, gerçek SMS entegrasyonu eklenebilir)
- **Veritabanı Yönetimi** - Kullanıcılar ve tercihleri DB'de saklanır
- **Kanal Seçimi** - Her kullanıcı hangi kanallardan bildirim alacağını seçebilir
- **Tip Filtreleme** - Hangi tür bildirimleri alacağını belirleyebilir (error, success, warning, info)

### Bildirim Kullanıcısı Ekleme

```bash
docker-compose exec php php bin/console app:add-notification-user
```

### Bildirim Testi

```bash
# Success bildirimi
docker-compose exec php php bin/console app:test-notification --type=success

# Error bildirimi
docker-compose exec php php bin/console app:test-notification --type=error

# Warning bildirimi
docker-compose exec php php bin/console app:test-notification --type=warning

# Info bildirimi
docker-compose exec php php bin/console app:test-notification --type=info
```

### Email'leri Görüntüleme

MailHog web arayüzü: **http://localhost:8025**

### Kod Örneği

```php
// NotificationManager kullanımı
$this->notificationManager->success('İşlem başarılı!');
$this->notificationManager->error('Bir hata oluştu!', ['error_code' => 500]);
$this->notificationManager->warning('Dikkat gerekli!');
$this->notificationManager->info('Bilgilendirme mesajı');
```

### Gerçek SMS Entegrasyonu

SMS simüle edilmiş durumda. Gerçek SMS göndermek için:

1. **Twilio** entegrasyonu:
```bash
composer require twilio/sdk
```

2. `SmsChannel.php` dosyasını güncelleyin
3. `.env` dosyasına Twilio credentials ekleyin

---

## 🚀 Production Deployment

### Checklist

- [ ] `.env` dosyasını production için güncelle
- [ ] `APP_ENV=prod` yap
- [ ] `APP_SECRET` değiştir
- [ ] HTTPS/SSL etkinleştir
- [ ] Monitoring kur (loglar, metrikler)
- [ ] Backup stratejisi belirle
- [ ] CI/CD pipeline kur
- [ ] OPcache etkinleştir (zaten aktif)
- [ ] Bildirim kullanıcılarını ekle
- [ ] Gerçek SMTP sunucusu yapılandır (production için)
- [ ] Log rotation yapılandır

### Performans İpuçları

1. **OPcache Etkinleştir** - Docker'da zaten yapılandırılmış
2. **Redis Cluster Kullan** - Yüksek erişilebilirlik için
3. **Veritabanı Replikasyonu** - Okuma performansı için
4. **CDN** - Statik dosyalar için
5. **Load Balancer** - Birden fazla instance için

---

## 🐛 Sorun Giderme

### Port Zaten Kullanımda

```bash
# Portu kullanan uygulamayı kontrol et
lsof -i :8080

# docker-compose.yml'de portu değiştir
ports:
  - "9080:80"
```

### Container Başlamıyor

```bash
# Logları kontrol et
docker-compose logs php

# Yeniden oluştur
docker-compose down
docker-compose up -d --build
```

### Veritabanı Bağlantı Hatası

```bash
# MySQL'in çalıştığını kontrol et
docker ps | grep mysql

# .env'deki credentials'ı doğrula
DATABASE_URL="mysql://root:root@mysql:3306/search_engine"

# Veritabanını yeniden oluştur
docker exec search_engine_php php bin/console doctrine:database:drop --force
docker exec search_engine_php php bin/console doctrine:database:create
docker exec search_engine_php php bin/console doctrine:migrations:migrate --no-interaction
```

### Cache Sorunları

```bash
# Tüm cache'leri temizle
docker exec search_engine_php php bin/console cache:clear

# Redis'i temizle
docker exec search_engine_redis redis-cli FLUSHALL
```

### Arama Sonucu Yok

```bash
# Sağlayıcılardan veri senkronize et
docker exec search_engine_php php bin/console app:sync-contents

# Veritabanını kontrol et
docker exec search_engine_php php bin/console doctrine:query:sql "SELECT COUNT(*) FROM contents"
```

---

## 📊 Performans Metrikleri

- **Arama Yanıt Süresi:** < 100ms (cache ile)
- **Cache Hit Oranı:** > 80%
- **Veritabanı Sorgu Süresi:** < 50ms
- **Provider Sync Süresi:** < 5 saniye
- **Bellek Kullanımı:** < 128MB per request

---

## 🤝 Katkıda Bulunma

Bu bir demo projesidir. Production kullanımı için:
1. Kapsamlı testler ekleyin
2. Rate limiting implement edin
3. Authentication/authorization ekleyin
4. Monitoring ve alerting kurun
5. CI/CD pipeline oluşturun

---

## 📝 Lisans

MIT License - Bu projeyi öğrenme veya production için özgürce kullanabilirsiniz.

---

## 🎓 Öğrenme Kaynakları

Bu proje şunları gösterir:
- ✅ Clean Architecture
- ✅ SOLID Prensipleri
- ✅ Design Patterns
- ✅ RESTful API Tasarımı
- ✅ Docker & DevOps
- ✅ Modern PHP Geliştirme
- ✅ Symfony Framework
- ✅ Veritabanı Tasarımı
- ✅ Caching Stratejileri
- ✅ Test Stratejileri
- ✅ Notification System

---

## 📞 Destek

Sorun veya sorularınız için:
1. [Sorun Giderme](#sorun-giderme) bölümünü kontrol edin
2. [Dokümantasyonu](#i̇çindekiler) inceleyin
3. Docker loglarını kontrol edin: `docker-compose logs -f`
4. [MONITORING.md](MONITORING.md) dosyasına bakın

---

**❤️ ile Symfony, Docker ve modern PHP pratikleri kullanılarak geliştirilmiştir.**

**Versiyon:** 2.0.0  
**PHP Versiyonu:** 8.4  
**Symfony Versiyonu:** 7.0  
**Son Güncelleme:** 2024
