# 🏗️ Architecture Documentation

## General Architecture

The project is designed following **Clean Architecture** and **SOLID** principles.

```
┌─────────────────────────────────────────────────────────┐
│                    Presentation Layer                    │
│  ┌──────────────┐              ┌──────────────┐        │
│  │  Dashboard   │              │  API (REST)  │        │
│  │ Controller   │              │  Controller  │        │
│  └──────────────┘              └──────────────┘        │
└────────────────────┬────────────────────┬───────────────┘
                     │                    │
┌────────────────────┴────────────────────┴───────────────┐
│                    Service Layer                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │   Search     │  │   Scoring    │  │   Provider   │ │
│  │   Service    │  │   Service    │  │   Manager    │ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
│                                                          │
│  ┌──────────────┐                                       │
│  │    Cache     │                                       │
│  │   Manager    │                                       │
│  └──────────────┘                                       │
└────────────────────┬────────────────────┬───────────────┘
                     │                    │
┌────────────────────┴────────────────────┴───────────────┐
│                  Data Access Layer                       │
│  ┌──────────────┐              ┌──────────────┐        │
│  │  Repository  │              │  Providers   │        │
│  │   (Doctrine) │              │ (JSON, XML)  │        │
│  └──────────────┘              └──────────────┘        │
└────────────────────┬────────────────────┬───────────────┘
                     │                    │
┌────────────────────┴────────────────────┴───────────────┐
│                  Infrastructure                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │    MySQL     │  │    Redis     │  │  External    │ │
│  │   Database   │  │    Cache     │  │  Providers   │ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
└─────────────────────────────────────────────────────────┘
```

## Layers

### 1. Presentation Layer

**Responsibilities:**
- Accept HTTP requests
- Validation
- Response formatting
- Error handling

**Components:**
- `SearchController`: API endpoints
- `DashboardController`: Web interface

### 2. Service Layer (Business Logic)

**Responsibilities:**
- Business logic
- Data processing
- Coordination

**Components:**

#### SearchService
- Manages search operations
- Implements cache strategy
- Sorting and pagination

#### ScoringService
- Content scoring algorithm
- Base score calculation
- Freshness and engagement scores

#### ProviderManager
- Manages providers
- Parallel data fetching
- Error tolerance

#### CacheManager
- Redis cache operations
- Cache key management
- TTL control

### 3. Data Access Layer

**Responsibilities:**
- Database operations
- Fetching data from external sources
- Data transformations

**Components:**

#### ContentRepository
- CRUD operations
- Search queries
- Entity-DTO conversion

#### Providers
- `JsonProvider`: Fetch data in JSON format
- `XmlProvider`: Fetch data in XML format
- `ProviderInterface`: Contract for new providers

## Design Patterns

### 1. Repository Pattern
```php
ContentRepository
├── search()      // Search operations
├── save()        // Save
└── truncate()    // Clean
```

**Advantages:**
- Abstracts database operations
- Testability
- Changeability

### 2. Strategy Pattern (Provider)
```php
ProviderInterface
├── JsonProvider
├── XmlProvider
└── [NewProvider]  // Easily extensible
```

**Advantages:**
- Easy to add new providers
- Independent testability
- Loose coupling

### 3. DTO Pattern
```php
ContentDTO
├── Data transfer
├── Validation
└── Serialization
```

**Advantages:**
- Type safety
- Data integrity
- API contract

### 4. Facade Pattern (ProviderManager)
```php
ProviderManager
└── fetchAllContents()  // Manages all providers
```

**Advantages:**
- Simple interface
- Hides complexity
- Centralized management

## Data Flow

### Search Operation
```
1. HTTP Request
   ↓
2. SearchController::search()
   ↓
3. Create SearchRequestDTO
   ↓
4. SearchService::search()
   ↓
5. Cache check (CacheManager)
   ├─ Hit → Return cached data
   └─ Miss → Continue
   ↓
6. ContentRepository::search()
   ↓
7. ScoringService::calculateScore()
   ↓
8. Sorting and pagination
   ↓
9. Store in cache
   ↓
10. JSON Response
```

### Synchronization Operation
```
1. HTTP POST /api/sync
   ↓
2. SearchController::sync()
   ↓
3. SearchService::syncContents()
   ↓
4. ProviderManager::fetchAllContents()
   ├─ JsonProvider::fetchContents()
   └─ XmlProvider::fetchContents()
   ↓
5. For each content:
   └─ ContentRepository::save()
   ↓
6. CacheManager::clear()
   ↓
7. JSON Response (synced_count)
```

## Scoring Algorithm Details

### Formula Components

```php
final_score = (base_score × type_coefficient) + freshness_score + engagement_score
```

### 1. Base Score

**Video:**
```php
base_score = (views / 1000) + (likes / 100)
```
- 10,000 views = 10 points
- 1,000 likes = 10 points

**Article:**
```php
base_score = reading_time + (reactions / 50)
```
- 10 minutes reading = 10 points
- 500 reactions = 10 points

### 2. Type Coefficient

```php
video: 1.5    // Videos are 50% more valuable
article: 1.0  // Articles are standard
```

### 3. Freshness Score

```php
if (days <= 7)   return 5.0;  // Last week
if (days <= 30)  return 3.0;  // Last month
if (days <= 90)  return 1.0;  // Last 3 months
return 0.0;                    // Older
```

### 4. Engagement Score

**Video:**
```php
engagement = (likes / views) × 10
```
- 10% like ratio = 1.0 point

**Article:**
```php
engagement = (reactions / reading_time) × 5
```
- 10 reactions per minute = 50 points

### Example Calculation

**Video Example:**
```
views: 25,000
likes: 2,100
published: 5 days ago

base_score = (25000/1000) + (2100/100) = 25 + 21 = 46
type_coefficient = 1.5
freshness_score = 5.0 (last week)
engagement_score = (2100/25000) × 10 = 0.84

final_score = (46 × 1.5) + 5.0 + 0.84 = 74.84
```

## Cache Strategy

### Cache Key Structure
```php
search_{md5(keyword_type_sortBy_page_perPage)}
```

### Cache Flow
```
Request → Cache Check
           ├─ HIT → Return cached data
           └─ MISS → Database query
                     ↓
                     Calculate scores
                     ↓
                     Store in cache (TTL: 3600s)
                     ↓
                     Return data
```

### Cache Invalidation
- All cache cleared on sync operation
- Auto expire after TTL
- Manual clear: `cache:clear` command

## Database Schema

```sql
CREATE TABLE contents (
    id VARCHAR(50) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    type VARCHAR(20) NOT NULL,
    metrics JSON NOT NULL,
    published_at DATETIME NOT NULL,
    tags JSON NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_type (type),
    INDEX idx_published_at (published_at)
);
```

### Index Strategy
- `idx_type`: For type filtering
- `idx_published_at`: For date sorting

## Scalability

### Horizontal Scaling
```
Load Balancer
    ├─ App Instance 1
    ├─ App Instance 2
    └─ App Instance 3
         ↓
    Redis Cluster
         ↓
    MySQL Master-Slave
```

### Optimization Points
1. **Database:**
   - Read replicas
   - Query optimization
   - Connection pooling

2. **Cache:**
   - Redis cluster
   - Cache warming
   - Distributed cache

3. **Application:**
   - Async processing
   - Queue system
   - CDN for static files

## Security

### API Security
- Rate limiting (optional)
- Input validation
- SQL injection protection (Doctrine)
- XSS protection (Twig)

### Infrastructure Security
- Docker network isolation
- Environment variables
- Secrets management

## Monitoring and Logging

### Log Levels
```
ERROR: Provider error, DB error
WARNING: Cache miss, Slow query
INFO: Sync successful, Request log
DEBUG: Detailed debug info
```

### Metrics
- Request count
- Response time
- Cache hit ratio
- Provider success rate
- Database query time

## Test Strategy

### Unit Tests
```
Service Layer
├─ ScoringService
│  ├─ testCalculateVideoScore()
│  ├─ testCalculateArticleScore()
│  └─ testFreshnessScore()
├─ CacheManager
└─ ProviderManager
```

### Integration Tests
```
API Tests
├─ testSearchEndpoint()
├─ testSyncEndpoint()
└─ testPagination()
```

### Performance Tests
- Load testing
- Stress testing
- Cache performance

## Future Improvements

### Recommended Enhancements
1. **Elasticsearch integration** - Advanced search
2. **GraphQL API** - Flexible data fetching
3. **WebSocket** - Real-time updates
4. **Machine Learning** - Smart ranking
5. **Analytics Dashboard** - Usage metrics
6. **Rate Limiting** - API protection
7. **API Versioning** - Backward compatibility
8. **Async Processing** - Queue system

## Conclusion

This architecture is:
- ✅ Clean and understandable
- ✅ Testable
- ✅ Scalable
- ✅ Easy to maintain
- ✅ Extensible
- ✅ SOLID compliant
