# 🚀 Load Testing Guide

Bu proje için 3 farklı yük testi aracı hazırlanmıştır: **PHP (Symfony Command)**, **Go**, ve **k6 (JavaScript)**.

---

## 📋 İçindekiler

1. [PHP (Symfony) - Önerilen](#1-php-symfony---önerilen)
2. [Go - Yüksek Performans](#2-go---yüksek-performans)
3. [k6 - Modern ve Scriptable](#3-k6---modern-ve-scriptable)
4. [Apache Bench - Basit ve Hızlı](#4-apache-bench---basit-ve-hızlı)
5. [Sonuçları Karşılaştırma](#5-sonuçları-karşılaştırma)

---

## 1. PHP (Symfony) - Önerilen

### Avantajları
✅ Projeye entegre (aynı codebase)  
✅ Symfony HTTP Client kullanır  
✅ Kolay debug ve extend edilebilir  
✅ Mülakatta göstermek için ideal  

### Kurulum
```bash
# Zaten kurulu, ekstra bir şey gerekmez
```

### Kullanım

#### Basit Test (1000 request, 10 concurrent)
```bash
docker-compose exec php php bin/console app:load-test
```

#### Özel Parametrelerle
```bash
# 5000 request, 50 concurrent user
docker-compose exec php php bin/console app:load-test -r 5000 -c 50

# Farklı URL
docker-compose exec php php bin/console app:load-test --url=http://production.com

# Sonuçları dosyaya kaydet
docker-compose exec php php bin/console app:load-test -o results.json
```

#### Test Senaryoları

**Stress Test** (Kademeli yük artışı)
```bash
docker-compose exec php php bin/console app:load-test --scenario=stress
```
- 10 users → 30s
- 50 users → 60s
- 100 users → 60s
- 200 users → 30s

**Spike Test** (Ani yük artışı)
```bash
docker-compose exec php php bin/console app:load-test --scenario=spike
```
- 20 users → 30s
- 200 users → 10s (SPIKE!)
- 20 users → 30s (recovery)

### Çıktı Örneği
```
🚀 Search Engine Load Test
========================================
Configuration
+--------------+---------------+
| Parameter    | Value         |
+--------------+---------------+
| Base URL     | http://...    |
| Scenario     | basic         |
| Total Req    | 1000          |
| Concurrent   | 10            |
+--------------+---------------+

✅ Service is healthy

Running Basic Load Test
 1000/1000 [============================] 100%

📊 Test Results
+-------------------------+-------------+
| Metric                  | Value       |
+-------------------------+-------------+
| Total Requests          | 1,000       |
| Successful              | 998         |
| Failed                  | 2           |
| Success Rate            | 99.80%      |
| Total Time              | 45.23s      |
| Requests/sec            | 22.11       |
|                         |             |
| Avg Response Time       | 452.34ms    |
| 95th Percentile         | 789.12ms    |
| 99th Percentile         | 1,234.56ms  |
|                         |             |
| Cache Hit Rate          | 78.50%      |
+-------------------------+-------------+

🎯 Performance Assessment
✅ All metrics look good!
```

---

## 2. Go - Yüksek Performans

### Avantajları
✅ Çok hızlı ve hafif  
✅ Gerçek concurrent execution  
✅ Düşük memory footprint  
✅ Production-grade tool  

### Kurulum
```bash
# Go yüklü değilse
brew install go  # macOS
# veya https://go.dev/doc/install

# Bağımlılıkları yükle (ilk çalıştırmada otomatik)
go mod init load-test
go mod tidy
```

### Kullanım

#### Basit Test
```bash
go run load-test.go
```

#### Özel Parametrelerle
```bash
# 5000 request, 100 concurrent
go run load-test.go -requests 5000 -concurrent 100

# Farklı URL
go run load-test.go -url http://production.com

# Sonuçları kaydet
go run load-test.go -output results.json
```

#### Test Senaryoları
```bash
# Stress test
go run load-test.go -scenario stress

# Spike test
go run load-test.go -scenario spike
```

#### Binary Oluşturma (Daha Hızlı)
```bash
# Compile et
go build -o load-test load-test.go

# Çalıştır
./load-test -requests 10000 -concurrent 200
```

### Çıktı Örneği
```
🚀 Search Engine Load Test
==================================================
Base URL:     http://localhost:8080
Scenario:     basic
Requests:     1000
Concurrent:   10

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
Total Time:            42.15s
Requests/sec:          23.72

Avg Response Time:     421.34ms
Min Response Time:     45.23ms
Max Response Time:     1,523.45ms
50th Percentile:       398.12ms
95th Percentile:       756.89ms
99th Percentile:       1,123.45ms

Cache Hits:            785
Cache Misses:          213
Cache Hit Rate:        78.65%

🎯 Performance Assessment
==================================================
✅ All metrics look good!
```

---

## 3. k6 - Modern ve Scriptable

### Avantajları
✅ Modern ve popüler  
✅ JavaScript ile scriptable  
✅ Grafana Cloud entegrasyonu  
✅ Detaylı raporlama  

### Kurulum
```bash
# macOS
brew install k6

# Linux
sudo apt-get install k6

# Windows
choco install k6
```

### Kullanım

#### Basit Test
```bash
k6 run load-test.js
```

#### Özel Parametrelerle
```bash
# 50 VU (Virtual Users), 30 saniye
k6 run --vus 50 --duration 30s load-test.js

# Farklı URL
BASE_URL=http://production.com k6 run load-test.js

# HTML rapor oluştur
k6 run --out json=results.json load-test.js
```

#### Grafana Cloud'a Gönder
```bash
# K6 Cloud token al: https://app.k6.io/
k6 login cloud --token YOUR_TOKEN

# Cloud'a gönder
k6 run --out cloud load-test.js
```

### Çıktı Örneği
```
          /\      |‾‾| /‾‾/   /‾‾/   
     /\  /  \     |  |/  /   /  /    
    /  \/    \    |     (   /   ‾‾\  
   /          \   |  |\  \ |  (‾)  | 
  / __________ \  |__| \__\ \_____/ .io

  execution: local
     script: load-test.js
     output: -

  scenarios: (100.00%) 1 scenario, 200 max VUs, 5m0s max duration
           * default: Up to 200 looping VUs for 4m30s over 6 stages

running (4m30.0s), 000/200 VUs, 12543 complete and 0 interrupted iterations
default ✓ [======================================] 000/200 VUs  4m30s

     ✓ search: status is 200
     ✓ search: response time < 1000ms
     ✓ search: has success field
     ✓ search: has data array

     checks.........................: 100.00% ✓ 50172      ✗ 0     
     data_received..................: 125 MB  463 kB/s
     data_sent......................: 2.5 MB  9.3 kB/s
     http_req_blocked...............: avg=1.23ms   min=1µs    med=3µs    max=234ms  p(95)=5µs    
     http_req_connecting............: avg=1.12ms   min=0s     med=0s     max=223ms  p(95)=0s     
     http_req_duration..............: avg=421.34ms min=45.2ms med=398ms  max=1.52s  p(95)=756ms  
     http_req_failed................: 0.15%   ✓ 19         ✗ 12524 
     http_req_receiving.............: avg=234µs    min=23µs   med=198µs  max=12ms   p(95)=456µs  
     http_req_sending...............: avg=45µs     min=8µs    med=34µs   max=2.3ms  p(95)=89µs   
     http_req_tls_handshaking.......: avg=0s       min=0s     med=0s     max=0s     p(95)=0s     
     http_req_waiting...............: avg=421.06ms min=45.1ms med=397.8ms max=1.52s p(95)=755.8ms
     http_reqs......................: 12543   46.47/s
     iteration_duration.............: avg=1.42s    min=1.04s  med=1.39s  max=2.52s  p(95)=1.75s  
     iterations.....................: 12543   46.47/s
     vus............................: 1       min=1        max=200 
     vus_max........................: 200     min=200      max=200 

📊 Test Summary:
================
Total Requests: 12543
Failed Requests: 0.15%
Avg Response Time: 421.34ms
95th Percentile: 756.00ms
99th Percentile: 1123.00ms
Requests/sec: 46.47
Cache Hit Rate: 78.50%
```

---

## 4. Apache Bench - Basit ve Hızlı

### Kullanım
```bash
# Basit test
./load-test.sh quick

# Full test suite
./load-test.sh full

# Stress test
./load-test.sh stress

# Rapor oluştur
./load-test.sh report
```

---

## 5. Sonuçları Karşılaştırma

### Hangi Aracı Kullanmalı?

| Senaryo | Önerilen Araç | Neden? |
|---------|---------------|--------|
| **Mülakat Demo** | PHP (Symfony) | Projeye entegre, kolay açıklanır |
| **Hızlı Test** | Apache Bench | En basit, kurulum gerektirmez |
| **Production Test** | Go veya k6 | Yüksek performans, güvenilir |
| **CI/CD Pipeline** | k6 | Scriptable, Grafana entegrasyonu |
| **Detaylı Analiz** | k6 | En detaylı metrikler |

### Örnek Karşılaştırma

**Test Koşulları:** 10,000 request, 100 concurrent

| Araç | Süre | RPS | Avg Response | Memory |
|------|------|-----|--------------|--------|
| PHP (Symfony) | 180s | 55.5 | 450ms | 128MB |
| Go | 165s | 60.6 | 420ms | 45MB |
| k6 | 170s | 58.8 | 435ms | 78MB |
| Apache Bench | 175s | 57.1 | 445ms | 12MB |

---

## 📊 Mülakatta Nasıl Gösterirsiniz?

### Senaryo 1: Basit Demo
```bash
# 1. Servisi başlat
docker-compose up -d

# 2. Yük testi çalıştır
docker-compose exec php php bin/console app:load-test -r 1000 -c 20

# 3. Sonuçları göster
# - Success rate
# - Response times
# - Cache hit rate
```

### Senaryo 2: Stress Test
```bash
# Kademeli yük artışı göster
docker-compose exec php php bin/console app:load-test --scenario=stress

# Açıklama:
# "10 kullanıcıdan başlayıp 200'e çıkıyoruz.
#  Her aşamada sistem nasıl davranıyor gözlemliyoruz.
#  Cache hit rate'in yükle birlikte nasıl değiştiğini görüyoruz."
```

### Senaryo 3: Optimizasyon Gösterimi
```bash
# 1. Cache'siz test
docker-compose exec redis redis-cli FLUSHALL
docker-compose exec php php bin/console app:load-test -r 500

# 2. Cache'li test
docker-compose exec php php bin/console app:load-test -r 500

# Karşılaştır:
# - Response time farkı
# - Cache hit rate
# - Throughput artışı
```

---

## 🎯 Mülakat Soruları ve Cevapları

**S: "Yük testi sonuçlarını nasıl yorumlarsınız?"**

**C:** "Üç ana metriğe bakarım:
1. **Success Rate**: %95+ olmalı
2. **95th Percentile**: <1000ms olmalı (kullanıcıların %95'i 1 saniyeden hızlı yanıt alıyor)
3. **Cache Hit Rate**: %70+ olmalı (cache stratejisi çalışıyor)

Eğer 95th percentile yüksekse, database query optimization veya cache TTL ayarlarına bakarım."

**S: "Büyük data geldiğinde ne yaparsınız?"**

**C:** "Yük testinde görebiliriz:
1. **Batch Processing**: Provider'dan 1000'lik chunk'larda çek
2. **Async Jobs**: Symfony Messenger ile queue'ya at
3. **Memory Management**: Generator pattern kullan
4. **Monitoring**: Response time'ı izle, threshold'ları belirle

Yük testi ile bu optimizasyonların etkisini ölçeriz."

---

## 📝 Best Practices

1. **Önce Health Check**: Servis ayakta mı kontrol et
2. **Warm-up Period**: İlk 30 saniye düşük yük
3. **Realistic Scenarios**: Gerçek kullanıcı davranışını simüle et
4. **Monitor Resources**: CPU, Memory, Database connections
5. **Repeat Tests**: Tek test yeterli değil, 3-5 kez tekrarla
6. **Document Results**: Sonuçları kaydet, karşılaştır

---

## 🔗 Kaynaklar

- [k6 Documentation](https://k6.io/docs/)
- [Apache Bench Guide](https://httpd.apache.org/docs/2.4/programs/ab.html)
- [Symfony HTTP Client](https://symfony.com/doc/current/http_client.html)
- [Go HTTP Package](https://pkg.go.dev/net/http)

---

**Başarılar! 🚀**
