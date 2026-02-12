# ⚡ Quick Start

## 🚀 Start in 3 Steps

### 1️⃣ Run the Startup Script
```bash
chmod +x start.sh
./start.sh
```

This command:
- ✅ Starts mock APIs
- ✅ Creates Docker containers
- ✅ Installs dependencies
- ✅ Prepares the database
- ✅ Loads initial data

### 2️⃣ Open the Dashboard
```
http://localhost:8080
```

### 3️⃣ Start Searching!
You can search from the dashboard or via API.

---

## 🎯 Quick Test Commands

### API Tests
```bash
# List all contents
curl "http://localhost:8080/api/search?query=programming"

# Search for "programming"
curl "http://localhost:8080/api/search?query=programming"

# Get only videos
curl "http://localhost:8080/api/search?query=docker&type=video"

# Sort by date
curl "http://localhost:8080/api/search?query=programming&sortBy=date"

# Resynchronize data
curl -X POST "http://localhost:8080/api/sync"
```

### Swagger API Documentation
```
http://localhost:8080/api/doc
```

---

## 🛠️ Makefile Commands

```bash
make start          # Start
make stop           # Stop
make restart        # Restart
make logs           # Show all logs
make logs-php       # PHP logs
make sync           # Synchronize data
make cache-clear    # Clear cache
make shell          # PHP shell
make db-shell       # MySQL shell
make redis-cli      # Redis CLI
make clean          # Clean everything
```

---

## 📊 Access URLs

| Service | URL | Description |
|---------|-----|-------------|
| 🌐 Dashboard | http://localhost:8080 | Web interface |
| 📚 Swagger | http://localhost:8080/api/doc | API documentation |
| 🔍 API Search | http://localhost:8080/api/search | Search endpoint |

**Data Sources:**
- 📡 JSON Provider: GitHub API (WEG-Technology/mock)
- 📡 XML Provider: GitHub API (WEG-Technology/mock)

---

## 🎨 Dashboard Features

1. **Search Box:** Search by keyword
2. **Type Filter:** Select Video or Article
3. **Sorting:** By Score or Date
4. **Pagination:** Forward/Back navigation
5. **Synchronization:** Fetch data from providers

---

## 🔧 Troubleshooting

### Port Conflict
If port 8080 is in use, change it in `docker-compose.yml`:
```yaml
ports:
  - "9080:80"  # Use 9080 instead of 8080
```

### Containers Not Starting
```bash
docker-compose down
docker-compose up -d --build
```

### Database Error
```bash
docker exec search_engine_php php bin/console doctrine:schema:drop --force
docker exec search_engine_php php bin/console doctrine:migrations:migrate --no-interaction
docker exec search_engine_php php bin/console app:sync-contents
```

### Cache Issue
```bash
make cache-clear
```

---

## 📖 Detailed Documentation

- **README.md** → Overview and features
- **INSTALLATION.md** → Step-by-step installation
- **ARCHITECTURE.md** → Architecture and design decisions
- **PROJECT_STRUCTURE.md** → File organization

---

## 🎓 Example Usage Scenarios

### Scenario 1: Search Video Content
```bash
curl "http://localhost:8080/api/search?type=video&sortBy=score"
```

### Scenario 2: Recently Added Content
```bash
curl "http://localhost:8080/api/search?sortBy=date"
```

### Scenario 3: Search Specific Keyword
```bash
curl "http://localhost:8080/api/search?query=docker"
```

### Scenario 4: Pagination
```bash
curl "http://localhost:8080/api/search?page=1&perPage=5"
```

---

## 🛑 Stopping

```bash
./stop.sh
```

or

```bash
make stop
```

---

## 💡 Tips

1. **Cache:** Search results are cached for 1 hour
2. **Synchronization:** Use `/api/sync` endpoint for new data
3. **Swagger:** Use Swagger UI to test the API
4. **Logs:** Monitor all logs with `make logs`
5. **Shell:** Enter container with `make shell`

---

## 🎉 Successful Installation Check

The following commands should work:

```bash
# Dashboard access
curl -I http://localhost:8080

# API access
curl http://localhost:8080/api/search?query=programming
```

If all return 200 OK, installation is successful! 🎊

The application fetches data from real GitHub API endpoints.

---

## 📞 Help

If you encounter issues:
1. Check logs: `make logs`
2. Restart containers: `make restart`
3. Clean installation: `make clean && make start`

---

**Happy coding! 🚀**
