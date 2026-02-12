# 📂 Project Structure

```
search-engine-service/
│
├── 📄 README.md                          # Main documentation
├── 📄 INSTALLATION.md                    # Installation guide
├── 📄 ARCHITECTURE.md                    # Architecture documentation
├── 📄 PROJECT_STRUCTURE.md               # This file
│
├── 🔧 Configuration Files
│   ├── .env                              # Environment variables
│   ├── .env.example                      # Environment example
│   ├── .gitignore                        # Git ignore rules
│   ├── composer.json                     # PHP dependencies
│   ├── symfony.lock                      # Symfony lock file
│   ├── docker-compose.yml                # Main Docker Compose
│   ├── docker-compose.override.yml       # Docker override
│   ├── Makefile                          # Make commands
│   ├── start.sh                          # Startup script
│   └── stop.sh                           # Stop script
│
├── 🐳 docker/                            # Docker configurations
│   ├── nginx/
│   │   └── default.conf                  # Nginx configuration
│   └── php/
│       └── Dockerfile                    # PHP Dockerfile
│
├── ⚙️ config/                            # Symfony configurations
│   ├── bundles.php                       # Bundle registrations
│   ├── services.yaml                     # Service definitions
│   ├── routes.yaml                       # Route definitions
│   │
│   ├── packages/                         # Package configurations
│   │   ├── cache.yaml                    # Cache settings
│   │   ├── doctrine.yaml                 # Doctrine ORM
│   │   ├── framework.yaml                # Framework settings
│   │   ├── monolog.yaml                  # Logging
│   │   ├── nelmio_api_doc.yaml          # Swagger
│   │   ├── routing.yaml                  # Routing
│   │   └── twig.yaml                     # Twig template
│   │
│   └── routes/
│       └── framework.yaml                # Framework routes
│
├── 🗄️ migrations/                        # Database migrations
│   └── Version20240315000000.php         # Initial migration
│
├── 💻 src/                               # Source code
│   │
│   ├── Kernel.php                        # Symfony Kernel
│   │
│   ├── Controller/                       # Controllers
│   │   ├── ApiDocController.php          # API doc controller
│   │   ├── DashboardController.php       # Dashboard controller
│   │   └── SearchController.php          # API controller
│   │
│   ├── Service/                          # Business Logic
│   │   ├── CacheManager.php              # Cache management
│   │   ├── ProviderManager.php           # Provider management
│   │   ├── ScoringService.php            # Scoring service
│   │   └── SearchService.php             # Search service
│   │
│   ├── Provider/                         # Providers
│   │   ├── ProviderInterface.php         # Provider interface
│   │   ├── JsonProvider.php              # JSON provider
│   │   └── XmlProvider.php               # XML provider
│   │
│   ├── Entity/                           # Doctrine Entities
│   │   └── Content.php                   # Content entity
│   │
│   ├── Repository/                       # Repositories
│   │   └── ContentRepository.php         # Content repository
│   │
│   ├── DTO/                              # Data Transfer Objects
│   │   ├── ContentDTO.php                # Content DTO
│   │   └── SearchRequestDTO.php          # Search request DTO
│   │
│   └── Command/                          # Console Commands
│       └── SyncContentsCommand.php       # Sync command
│
├── 🌐 public/                            # Public files
│   └── index.php                         # Entry point
│
├── 🎨 templates/                         # Twig templates
│   ├── base.html.twig                    # Base template
│   ├── api_doc/
│   │   └── index.html.twig               # API doc template
│   └── dashboard/
│       └── index.html.twig               # Dashboard template
│
├── 🔧 bin/                               # Executables
│   └── console                           # Symfony console
│
└── 📡 mock-apis/                         # Mock APIs
    ├── docker-compose.yml                # Mock API Docker Compose
    ├── json-provider/
    │   └── index.php                     # JSON provider mock
    └── xml-provider/
        └── index.php                     # XML provider mock
```

## 📊 File Statistics

### Total File Count
- **PHP Files:** 16
- **YAML Files:** 10
- **Twig Templates:** 3
- **Docker Files:** 4
- **Documentation:** 5
- **Script Files:** 2

### Lines of Code (Approximate)
- **Backend (PHP):** ~1,500 lines
- **Frontend (HTML/CSS/JS):** ~400 lines
- **Configuration:** ~300 lines
- **Documentation:** ~2,000 lines

## 🎯 Main Components

### 1. API Layer
```
SearchController
├── GET  /api/search     → Search
└── POST /api/sync       → Synchronization

ApiDocController
└── GET  /api/doc        → API Documentation
```

### 2. Service Layer
```
SearchService          → Search operations
ScoringService         → Scoring algorithm
ProviderManager        → Provider management
CacheManager           → Cache operations
```

### 3. Data Layer
```
ContentRepository      → Database operations
JsonProvider          → JSON data source
XmlProvider           → XML data source
```

### 4. Infrastructure
```
Docker                → Containers
MySQL                 → Database
Redis                 → Cache
Nginx                 → Web server
```

## 🔄 Data Flow

```
1. HTTP Request
   ↓
2. Controller (Validation)
   ↓
3. Service (Business Logic)
   ↓
4. Cache Check
   ├─ Hit → Return
   └─ Miss → Continue
   ↓
5. Repository (Data Access)
   ↓
6. Database Query
   ↓
7. Scoring & Sorting
   ↓
8. Cache Store
   ↓
9. HTTP Response
```

## 🛠️ Development Tools

### Command Line Tools
```bash
make start          # Start
make stop           # Stop
make logs           # Show logs
make install        # Install dependencies
make migrate        # Run migrations
make sync           # Synchronize data
make test           # Run tests
make cache-clear    # Clear cache
make shell          # PHP shell
make db-shell       # MySQL shell
make redis-cli      # Redis CLI
```

### Script Files
```bash
./start.sh          # Automatic setup and start
./stop.sh           # Stop all services
```

## 📚 Documentation

- **README.md:** Overview and usage
- **INSTALLATION.md:** Step-by-step installation
- **ARCHITECTURE.md:** Detailed architecture explanation
- **PROJECT_STRUCTURE.md:** File organization
- **QUICK_START.md:** Quick start guide
- **FEATURES.md:** Features and capabilities

## 🔐 Security

### Protected Files
- `.env` → Not committed to Git
- `vendor/` → Dependencies
- `var/` → Cache and logs

### Environment Variables
```
APP_SECRET          → Application secret key
DATABASE_URL        → Database connection
REDIS_URL           → Redis connection
PROVIDER_*_URL      → Provider URLs
```

## 🧪 Test Structure

```
tests/
├── Unit/
│   ├── Service/
│   │   ├── ScoringServiceTest.php
│   │   └── CacheManagerTest.php
│   └── Provider/
│       ├── JsonProviderTest.php
│       └── XmlProviderTest.php
└── Integration/
    └── Controller/
        └── SearchControllerTest.php
```

## 📦 Dependencies

### Main Dependencies
- `symfony/framework-bundle` → Framework
- `doctrine/orm` → ORM
- `nelmio/api-doc-bundle` → Swagger
- `predis/predis` → Redis client
- `symfony/cache` → Cache
- `symfony/http-client` → HTTP client
- `symfony/twig-bundle` → Template engine
- `symfony/monolog-bundle` → Logging

## 🚀 Deployment

### Production Checklist
- [ ] Update `.env` for production
- [ ] Set `APP_ENV=prod`
- [ ] Change `APP_SECRET`
- [ ] Warm up cache
- [ ] Run migrations
- [ ] Add SSL certificate
- [ ] Setup monitoring
- [ ] Define backup strategy

## 📈 Performance

### Optimization Points
- Redis cache (3600s TTL)
- Database indexes
- Doctrine query optimization
- Nginx static file serving
- OPcache enabled

## 🔍 Monitoring

### Log Files
```
var/log/dev.log     → Development logs
var/log/prod.log    → Production logs
```

### Docker Logs
```bash
docker-compose logs -f php      # PHP logs
docker-compose logs -f nginx    # Nginx logs
docker-compose logs -f mysql    # MySQL logs
docker-compose logs -f redis    # Redis logs
```

## 🎓 Learning Resources

This project teaches:
- ✅ Clean Architecture
- ✅ SOLID Principles
- ✅ Design Patterns
- ✅ Docker & Docker Compose
- ✅ Symfony Framework
- ✅ REST API Design
- ✅ Cache Strategies
- ✅ Database Design
- ✅ Testing Strategies
