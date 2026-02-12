# 🚀 Installation Guide

## Step-by-Step Installation

### 1. Start Main Application
```bash
docker-compose up -d --build
```

### 2. Install Composer Dependencies
```bash
docker exec search_engine_php composer install --no-interaction --optimize-autoloader
```

### 3. Prepare Database
```bash
# Create database
docker exec search_engine_php php bin/console doctrine:database:create --if-not-exists

# Run migrations
docker exec search_engine_php php bin/console doctrine:migrations:migrate --no-interaction
```

### 4. Load Initial Data
```bash
# Synchronize via console command (fetches from GitHub API)
docker exec search_engine_php php bin/console app:sync-contents

# Or via API
curl -X POST http://localhost:8080/api/sync
```

### 5. Test the Application

**Dashboard:**
```
http://localhost:8080
```

**Swagger API Documentation:**
```
http://localhost:8080/api/doc
```

**API Test:**
```bash
curl "http://localhost:8080/api/search?query=programming"
```

## 🎯 Quick Test Commands

```bash
# List all contents
curl "http://localhost:8080/api/search?query=programming"

# Get only videos
curl "http://localhost:8080/api/search?query=docker&type=video"

# Sort by date
curl "http://localhost:8080/api/search?query=programming&sortBy=date"

# Resynchronize data
docker exec search_engine_php php bin/console app:sync-contents
# Or via API
curl -X POST "http://localhost:8080/api/sync"
```

## 🔧 Troubleshooting

### Port Conflict
If ports 8080, 8081, 8082 are in use:

Change ports in **docker-compose.yml**:
```yaml
ports:
  - "9080:80"  # Use 9080 instead of 8080
```

### View Container Logs
```bash
# All logs
docker-compose logs -f

# PHP logs only
docker-compose logs -f php

# Nginx logs only
docker-compose logs -f nginx
```

### Clear Cache
```bash
docker exec search_engine_php php bin/console cache:clear
```

### Reset Database
```bash
docker exec search_engine_php php bin/console doctrine:schema:drop --force
docker exec search_engine_php php bin/console doctrine:migrations:migrate --no-interaction
docker exec search_engine_php php bin/console app:sync-contents
```

### Restart All Containers
```bash
docker-compose down
docker-compose up -d --build
```

## ✅ Successful Installation Check

The following URLs should be working:

- ✅ Dashboard: http://localhost:8080
- ✅ API Swagger: http://localhost:8080/api/doc
- ✅ API Search: http://localhost:8080/api/search

## 🌐 Data Sources

The application fetches data from real GitHub API endpoints:
- JSON Provider: https://raw.githubusercontent.com/WEG-Technology/mock/refs/heads/main/v2/provider1
- XML Provider: https://raw.githubusercontent.com/WEG-Technology/mock/refs/heads/main/v2/provider2

## 🎉 Done!

Your search engine service is now ready. You can search from the dashboard or use the API.
