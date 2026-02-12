# 📊 Monitoring Rehberi

## Evet, Monitoring Eklenebilir!

Bu projeye monitoring eklemek için birçok seçenek var. İşte öneriler:

---

## 🎯 Monitoring Seçenekleri

### 1. **Prometheus + Grafana** (Önerilen)

**Neden?**
- Açık kaynak ve ücretsiz
- Güçlü metrik toplama
- Güzel dashboard'lar
- Alerting desteği

**Nasıl Ekleriz?**

```yaml
# docker-compose.yml'e ekle
services:
  prometheus:
    image: prom/prometheus
    ports:
      - "9090:9090"
    volumes:
      - ./monitoring/prometheus.yml:/etc/prometheus/prometheus.yml
    
  grafana:
    image: grafana/grafana
    ports:
      - "3000:3000"
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=admin
```

**Toplanacak Metrikler:**
- Request count
- Response time
- Error rate
- Cache hit ratio
- Database query time
- Memory usage
- CPU usage

---

### 2. **Symfony Profiler** (Zaten Var!)

**Avantajlar:**
- Symfony ile built-in
- Development için mükemmel
- Detaylı request profiling

**Kullanım:**
```
http://localhost:8080/_profiler
```

**Gösterir:**
- Request/Response details
- Database queries
- Cache operations
- Memory usage
- Timeline

---

### 3. **ELK Stack** (Elasticsearch, Logstash, Kibana)

**Neden?**
- Log aggregation
- Full-text search
- Visualization
- Real-time analysis

**Nasıl Ekleriz?**

```yaml
# docker-compose.yml
services:
  elasticsearch:
    image: elasticsearch:8.11.0
    ports:
      - "9200:9200"
    
  kibana:
    image: kibana:8.11.0
    ports:
      - "5601:5601"
```

**Monolog ile Entegrasyon:**
```yaml
# config/packages/monolog.yaml
monolog:
    handlers:
        elasticsearch:
            type: elasticsearch
            index: search-engine-logs
            elasticsearch:
                host: elasticsearch
                port: 9200
```

---

### 4. **New Relic / DataDog** (SaaS)

**Avantajlar:**
- Hazır çözüm
- Kolay kurulum
- Güçlü özellikler
- APM (Application Performance Monitoring)

**Dezavantajlar:**
- Ücretli
- Dış servise bağımlılık

---

### 5. **Sentry** (Error Tracking)

**Neden?**
- Hata takibi
- Stack trace
- User context
- Email alerts

**Kurulum:**
```bash
composer require sentry/sentry-symfony
```

```yaml
# config/packages/sentry.yaml
sentry:
    dsn: '%env(SENTRY_DSN)%'
    options:
        environment: '%kernel.environment%'
```

---

## 🚀 Hızlı Başlangıç: Basit Monitoring

### 1. Health Check Endpoint

```php
// src/Controller/HealthController.php
#[Route('/health', name: 'health_check')]
public function check(): JsonResponse
{
    return $this->json([
        'status' => 'healthy',
        'timestamp' => time(),
        'services' => [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'providers' => $this->checkProviders(),
        ]
    ]);
}
```

### 2. Metrics Endpoint

```php
#[Route('/metrics', name: 'metrics')]
public function metrics(): JsonResponse
{
    return $this->json([
        'requests_total' => $this->getRequestCount(),
        'cache_hit_ratio' => $this->getCacheHitRatio(),
        'avg_response_time' => $this->getAvgResponseTime(),
        'error_rate' => $this->getErrorRate(),
    ]);
}
```

### 3. Custom Monolog Handler

```php
// src/Monolog/MetricsHandler.php
class MetricsHandler extends AbstractHandler
{
    public function handle(array $record): bool
    {
        // Metrikleri Redis'e kaydet
        // Prometheus'a gönder
        // veya dosyaya yaz
        return false;
    }
}
```

---

## 📈 Önerilen Metrikler

### Application Metrics
- **Request Rate:** İstek sayısı/saniye
- **Response Time:** Ortalama yanıt süresi
- **Error Rate:** Hata oranı
- **Success Rate:** Başarı oranı

### Business Metrics
- **Search Count:** Arama sayısı
- **Sync Count:** Senkronizasyon sayısı
- **Content Count:** Toplam içerik sayısı
- **Popular Keywords:** Popüler arama kelimeleri

### Infrastructure Metrics
- **CPU Usage:** CPU kullanımı
- **Memory Usage:** Bellek kullanımı
- **Disk I/O:** Disk okuma/yazma
- **Network I/O:** Ağ trafiği

### Database Metrics
- **Query Count:** Sorgu sayısı
- **Query Time:** Sorgu süresi
- **Connection Pool:** Bağlantı havuzu
- **Slow Queries:** Yavaş sorgular

### Cache Metrics
- **Hit Rate:** Cache hit oranı
- **Miss Rate:** Cache miss oranı
- **Eviction Rate:** Cache temizleme oranı
- **Memory Usage:** Cache bellek kullanımı

---

## 🔔 Alerting

### Önerilen Alert'ler

1. **High Error Rate**
   - Condition: Error rate > 5%
   - Action: Email + Slack notification

2. **Slow Response Time**
   - Condition: Avg response time > 1s
   - Action: Email notification

3. **Low Cache Hit Rate**
   - Condition: Cache hit rate < 70%
   - Action: Warning notification

4. **Database Connection Issues**
   - Condition: Connection errors
   - Action: Critical alert

5. **Disk Space Low**
   - Condition: Disk usage > 80%
   - Action: Warning notification

---

## 🛠️ Pratik Uygulama

### Adım 1: Prometheus Exporter Ekle

```bash
composer require promphp/prometheus_client_php
```

### Adım 2: Metrics Service Oluştur

```php
namespace App\Service;

class MetricsService
{
    private CollectorRegistry $registry;
    
    public function incrementRequestCount(string $endpoint): void
    {
        $counter = $this->registry->getOrRegisterCounter(
            'app',
            'requests_total',
            'Total requests',
            ['endpoint']
        );
        $counter->inc(['endpoint' => $endpoint]);
    }
    
    public function recordResponseTime(float $duration): void
    {
        $histogram = $this->registry->getOrRegisterHistogram(
            'app',
            'response_time_seconds',
            'Response time in seconds'
        );
        $histogram->observe($duration);
    }
}
```

### Adım 3: Middleware Ekle

```php
class MetricsMiddleware
{
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $start = microtime(true);
        
        $response = $handler->handle($request);
        
        $duration = microtime(true) - $start;
        $this->metricsService->recordResponseTime($duration);
        $this->metricsService->incrementRequestCount($request->getPathInfo());
        
        return $response;
    }
}
```

---

## 📊 Dashboard Örnekleri

### Grafana Dashboard Panelleri

1. **Request Rate**
   - Line chart
   - Last 1 hour
   - Requests per second

2. **Response Time**
   - Line chart
   - P50, P95, P99 percentiles

3. **Error Rate**
   - Gauge
   - Current error percentage

4. **Cache Hit Ratio**
   - Gauge
   - Current hit ratio

5. **Top Endpoints**
   - Table
   - Most requested endpoints

6. **Database Queries**
   - Line chart
   - Queries per second

---

## 🎯 Sonuç

**Monitoring eklenebilir mi?** → **Kesinlikle EVET!**

**En iyi seçenek:** Prometheus + Grafana
- Ücretsiz
- Güçlü
- Kolay kurulum
- Symfony ile uyumlu

**Hızlı başlangıç için:**
1. Symfony Profiler'ı kullan (zaten var)
2. Health check endpoint ekle
3. Monolog ile log'ları topla
4. İhtiyaç oldukça Prometheus ekle

**Production için:**
- Prometheus + Grafana
- Sentry (error tracking)
- ELK Stack (log analysis)
- Custom metrics endpoint

---

**Monitoring, production ortamında kritik öneme sahiptir!** 🚀
