# 🚀 Go Yük Testi Kullanım Kılavuzu

## Hızlı Başlangıç

### Yöntem 1: Direkt Çalıştırma (go run)

```bash
# Basit test (100 request, 10 concurrent)
go run load-test.go

# Özel parametrelerle
go run load-test.go -requests 1000 -concurrent 20

# Farklı URL
go run load-test.go -url http://production.com

# Sonuçları kaydet
go run load-test.go -requests 500 -concurrent 10 -output results.json
```

### Yöntem 2: Binary Oluştur (Daha Hızlı) ⭐ ÖNERİLEN

```bash
# 1. Binary oluştur (tek seferlik)
go build -o load-test load-test.go

# 2. Binary ile çalıştır (çok daha hızlı)
./load-test -requests 1000 -concurrent 20

# Stress test
./load-test -scenario stress

# Spike test
./load-test -scenario spike

# Sonuçları kaydet
./load-test -requests 500 -concurrent 10 -output results.json
```

## Parametreler

| Parametre | Kısa | Varsayılan | Açıklama |
|-----------|------|------------|----------|
| `-url` | | `http://localhost:8080` | Hedef URL |
| `-requests` | `-r` | `1000` | Toplam istek sayısı |
| `-concurrent` | `-c` | `10` | Eşzamanlı worker sayısı |
| `-scenario` | `-s` | `basic` | Test senaryosu (basic/stress/spike) |
| `-output` | `-o` | | Sonuçları kaydet (JSON) |
| `-version` | | | Versiyon göster |

## Test Senaryoları

### 1. Basic Test (Varsayılan)
```bash
./load-test -requests 1000 -concurrent 20
```
- Sabit sayıda request
- Sabit concurrency
- Hızlı ve basit

### 2. Stress Test
```bash
./load-test -scenario stress
```
Kademeli yük artışı:
- 10 users → 30 saniye (warm-up)
- 50 users → 60 saniye (normal load)
- 100 users → 60 saniye (high load)
- 200 users → 30 saniye (peak load)

### 3. Spike Test
```bash
./load-test -scenario spike
```
Ani yük artışı:
- 20 users → 30 saniye (normal)
- 200 users → 10 saniye (SPIKE!)
- 20 users → 30 saniye (recovery)

## Örnek Çıktılar

### Başarılı Test
```
🚀 Search Engine Load Test
==================================================
Base URL:     http://localhost:8080
Scenario:     basic
Requests:     1000
Concurrent:   20

🔍 Health Check...
✅ Service is healthy

📊 Running Basic Load Test...
Progress: 1000/1000 ✅

==================================================
📊 Test Results
==================================================
Total Requests:        1000
Successful:            998
Failed:                2
Success Rate:          99.80%
Total Time:            2.45s
Requests/sec:          408.16

Avg Response Time:     48.50ms
Min Response Time:     5.20ms
Max Response Time:     125.30ms
50th Percentile:       45.10ms
95th Percentile:       89.20ms
99th Percentile:       112.50ms

Cache Hits:            850
Cache Misses:          148
Cache Hit Rate:        85.17%

🎯 Performance Assessment
==================================================
✅ All metrics look good!

✅ Results saved to: results.json
```

### JSON Sonuç Dosyası
```json
{
  "timestamp": "2026-02-13T02:00:56+03:00",
  "summary": {
    "total_requests": 500,
    "successful": 424,
    "failed": 76,
    "success_rate": 84.8,
    "total_time": 1.24,
    "requests_per_second": 404.06
  },
  "response_times": {
    "avg": 24.51,
    "min": 0.16,
    "max": 67.6,
    "p50": 27.2,
    "p95": 38.39,
    "p99": 44.25
  },
  "cache": {
    "hits": 424,
    "misses": 0,
    "hit_rate": 100
  }
}
```

## Avantajları

✅ **Çok Hızlı**: Gerçek concurrent execution  
✅ **Hafif**: Düşük memory footprint (~8MB binary)  
✅ **Portable**: Tek binary, dependency yok  
✅ **Production-Ready**: Güvenilir ve stabil  
✅ **Cross-Platform**: macOS, Linux, Windows  

## PHP vs Go Karşılaştırma

| Özellik | PHP (Symfony) | Go |
|---------|---------------|-----|
| **Hız** | Orta | Çok Hızlı |
| **Memory** | ~128MB | ~45MB |
| **Kurulum** | Docker gerekli | Tek binary |
| **Entegrasyon** | Projeye entegre | Standalone |
| **Mülakat** | ⭐ İdeal | İyi |

## Mülakat için Kullanım

### Senaryo 1: Hızlı Demo
```bash
# Binary oluştur
go build -o load-test load-test.go

# Test çalıştır
./load-test -requests 1000 -concurrent 20

# Açıklama:
# "Go ile yazdım, çok hızlı. 1000 request 2.5 saniyede tamamlandı.
#  404 request/sec throughput aldık. Cache hit rate %85."
```

### Senaryo 2: Stress Test
```bash
./load-test -scenario stress

# Açıklama:
# "Kademeli yük artışı yapıyorum. 10 kullanıcıdan 200'e çıkıyorum.
#  Her aşamada sistem nasıl davranıyor gözlemliyoruz."
```

### Senaryo 3: Karşılaştırma
```bash
# PHP ile test
docker-compose exec php php bin/console app:load-test -r 1000 -c 20

# Go ile test
./load-test -requests 1000 -concurrent 20

# Açıklama:
# "İki farklı dilde yazdım. Go daha hızlı ama PHP projeye entegre.
#  Production'da Go kullanırım, development'ta PHP."
```

## Troubleshooting

### Problem: "command not found: go"
```bash
# Go yükle
brew install go  # macOS
```

### Problem: "connection refused"
```bash
# Servisi başlat
docker-compose up -d

# Health check
curl http://localhost:8080/api/search?keyword=test
```

### Problem: "high error rate"
```bash
# Concurrent sayısını azalt
./load-test -requests 1000 -concurrent 5

# Veya timeout artır (kod içinde)
```

## İleri Seviye

### Cross-Compile (Farklı platformlar için)
```bash
# Linux için
GOOS=linux GOARCH=amd64 go build -o load-test-linux load-test.go

# Windows için
GOOS=windows GOARCH=amd64 go build -o load-test.exe load-test.go

# macOS ARM için
GOOS=darwin GOARCH=arm64 go build -o load-test-mac load-test.go
```

### Optimize Binary
```bash
# Daha küçük binary
go build -ldflags="-s -w" -o load-test load-test.go

# Boyut karşılaştır
ls -lh load-test
```

## Kaynaklar

- [Go Documentation](https://go.dev/doc/)
- [Go HTTP Package](https://pkg.go.dev/net/http)
- [Concurrency in Go](https://go.dev/tour/concurrency/1)

---

**Başarılar! 🚀**
