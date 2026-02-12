# 🚀 k6 (JavaScript) Yük Testi Kullanım Kılavuzu

## Hızlı Başlangıç

### Kurulum
```bash
# macOS
brew install k6

# Linux
sudo apt-get install k6

# Windows
choco install k6
```

### Temel Kullanım

```bash
# Varsayılan test (script içindeki stages)
k6 run load-test.js

# Özel VU (Virtual Users) ve süre
k6 run load-test.js --vus 10 --duration 30s

# Belirli sayıda iteration
k6 run load-test.js --vus 10 --iterations 100

# Sonuçları JSON'a kaydet
k6 run load-test.js --out json=results.json

# HTML rapor oluştur (summary.json'dan)
k6 run load-test.js --summary-export=summary.json
```

## Test Senaryoları

### 1. Hızlı Test (10 VU, 30 saniye)
```bash
k6 run load-test.js --vus 10 --duration 30s
```

### 2. Orta Ölçekli Test (50 VU, 2 dakika)
```bash
k6 run load-test.js --vus 50 --duration 2m
```

### 3. Stress Test (Kademeli artış - script içinde tanımlı)
```bash
k6 run load-test.js
```
Script içindeki stages:
- 30s → 20 users (warm-up)
- 1m → 50 users (normal load)
- 30s → 100 users (peak load)
- 1m → 100 users (sustained peak)
- 30s → 200 users (spike)
- 30s → 0 users (ramp-down)

### 4. Iteration-Based Test
```bash
# 100 iteration, 10 concurrent user
k6 run load-test.js --vus 10 --iterations 100
```

## Parametreler

| Parametre | Açıklama | Örnek |
|-----------|----------|-------|
| `--vus` | Virtual Users (eşzamanlı kullanıcı) | `--vus 50` |
| `--duration` | Test süresi | `--duration 5m` |
| `--iterations` | Toplam iteration sayısı | `--iterations 1000` |
| `--out` | Çıktı formatı | `--out json=results.json` |
| `--summary-export` | Summary JSON export | `--summary-export=summary.json` |
| `--quiet` | Sessiz mod | `--quiet` |
| `--no-color` | Renksiz çıktı | `--no-color` |

## Örnek Çıktılar

### Terminal Çıktısı
```
         /\      Grafana   /‾‾/  
    /\  /  \     |\  __   /  /   
   /  \/    \    | |/ /  /   ‾‾\ 
  /          \   |   (  |  (‾)  |
 / __________ \  |_|\_\  \_____/ 

     execution: local
        script: load-test.js
        output: -

     scenarios: (100.00%) 1 scenario, 10 max VUs, 1m0s max duration
              * default: 10 looping VUs for 30s

  █ THRESHOLDS 

    ✓ http_req_duration
      ✓ 'p(95)<1000' p(95)=40.7ms
      ✓ 'p(99)<2000' p(99)=60.78ms

    ✓ search_duration
      ✓ 'p(95)<800' p(95)=37ms

  █ TOTAL RESULTS 

    checks_total.......: 1268   41.25/s
    checks_succeeded...: 88.01% ✓ 1116 / ✗ 152

    ✓ search: status is 200
      ↳  84% — ✓ 204 / ✗ 38
    ✓ search: response time < 1000ms
    ✓ search: has success field
    ✓ search: has data array
    ✓ search: has pagination

    CUSTOM
    cache_hits.....................: 204    6.64/s
    search_duration................: avg=19.38ms min=0 med=20ms max=61ms

    HTTP
    http_req_duration..............: avg=19.98ms min=287µs med=19.59ms max=135.69ms
    http_req_failed................: 13.62% ✓ 38 / ✗ 241
    http_reqs......................: 279    9.08/s

    EXECUTION
    iterations.....................: 242    7.87/s
    vus............................: 10     min=10 max=10

📊 Test Summary:
================
Total Requests: 279
Failed Requests: 13.62%
Avg Response Time: 19.98ms
95th Percentile: 40.70ms
99th Percentile: 60.78ms
Requests/sec: 9.08
Cache Hit Rate: 100.00%
```

## Gelişmiş Özellikler

### 1. Thresholds (Eşik Değerler)
Script içinde tanımlı:
```javascript
thresholds: {
  'http_req_duration': ['p(95)<1000', 'p(99)<2000'],
  'http_req_failed': ['rate<0.05'],
  'errors': ['rate<0.05'],
  'search_duration': ['p(95)<800'],
}
```

### 2. Custom Metrics
```javascript
const searchDuration = new Trend('search_duration');
const cacheHits = new Counter('cache_hits');
const cacheMisses = new Counter('cache_misses');
```

### 3. Checks (Doğrulamalar)
```javascript
check(res, {
  'search: status is 200': (r) => r.status === 200,
  'search: response time < 1000ms': (r) => r.timings.duration < 1000,
  'search: has data array': (r) => Array.isArray(body.data),
});
```

## Grafana Cloud Entegrasyonu

### 1. Hesap Oluştur
```bash
# k6 Cloud'a kayıt ol
https://app.k6.io/
```

### 2. Token Al ve Login
```bash
# Token ile login
k6 login cloud --token YOUR_TOKEN
```

### 3. Cloud'a Gönder
```bash
# Test sonuçlarını cloud'a gönder
k6 run --out cloud load-test.js

# Veya sadece sonuçları stream et
k6 cloud load-test.js
```

### 4. Dashboard'da Görüntüle
- Real-time metrics
- Grafik ve tablolar
- Karşılaştırma ve trend analizi
- Team collaboration

## Çıktı Formatları

### JSON Output
```bash
k6 run load-test.js --out json=results.json
```

### CSV Output (InfluxDB format)
```bash
k6 run load-test.js --out influxdb=http://localhost:8086/k6
```

### Prometheus Remote Write
```bash
k6 run load-test.js --out experimental-prometheus-rw
```

## Avantajları

✅ **Modern**: JavaScript ile scriptable  
✅ **Detaylı**: Comprehensive metrics ve checks  
✅ **Görsel**: Grafana Cloud entegrasyonu  
✅ **Flexible**: Custom metrics ve thresholds  
✅ **CI/CD Ready**: Exit codes ve JSON output  
✅ **Open Source**: Ücretsiz ve açık kaynak  

## PHP vs Go vs k6 Karşılaştırma

| Özellik | PHP (Symfony) | Go | k6 (JavaScript) |
|---------|---------------|-----|-----------------|
| **Hız** | Orta | Çok Hızlı | Hızlı |
| **Scriptable** | Hayır | Hayır | ✅ Evet |
| **Metrics** | Temel | Temel | ⭐ Detaylı |
| **Görselleştirme** | Hayır | Hayır | ✅ Grafana |
| **CI/CD** | İyi | İyi | ⭐ Mükemmel |
| **Mülakat** | ⭐ İdeal | İyi | İyi |

## Mülakat için Kullanım

### Senaryo 1: Hızlı Demo
```bash
# Test çalıştır
k6 run load-test.js --vus 10 --duration 30s

# Açıklama:
# "k6 ile JavaScript kullanarak yük testi yaptım.
#  Custom metrics tanımladım: cache hit rate, search duration.
#  Thresholds ile otomatik pass/fail kontrolü yapıyorum."
```

### Senaryo 2: CI/CD Entegrasyonu
```bash
# Exit code kontrolü
k6 run load-test.js --vus 50 --duration 1m
echo $?  # 0 = success, 99 = thresholds failed

# Açıklama:
# "CI/CD pipeline'da kullanabilirim.
#  Thresholds fail olursa exit code 99 döner, deployment durur."
```

### Senaryo 3: Grafana Cloud
```bash
# Cloud'a gönder
k6 run --out cloud load-test.js

# Açıklama:
# "Grafana Cloud'da real-time dashboard var.
#  Team ile paylaşabilir, trend analizi yapabilirim."
```

## Troubleshooting

### Problem: "command not found: k6"
```bash
# k6 yükle
brew install k6  # macOS
```

### Problem: "thresholds have been crossed"
```bash
# Normal, threshold'lar fail oldu
# Exit code 99 döner
# Script içindeki threshold'ları ayarla
```

### Problem: "connection refused"
```bash
# Servisi başlat
docker-compose up -d

# URL'i kontrol et
curl http://localhost:8080/api/search?keyword=test
```

## İleri Seviye

### Custom Script Yazma
```javascript
import http from 'k6/http';
import { check } from 'k6';

export const options = {
  vus: 10,
  duration: '30s',
};

export default function () {
  const res = http.get('http://localhost:8080/api/search?keyword=test');
  check(res, {
    'status is 200': (r) => r.status === 200,
  });
}
```

### Modüler Test
```javascript
// scenarios.js
export const scenarios = {
  smoke: { vus: 1, duration: '1m' },
  load: { vus: 50, duration: '5m' },
  stress: { vus: 100, duration: '10m' },
};

// load-test.js
import { scenarios } from './scenarios.js';
export const options = { scenarios: scenarios.load };
```

## Kaynaklar

- [k6 Documentation](https://k6.io/docs/)
- [k6 Examples](https://k6.io/docs/examples/)
- [Grafana Cloud k6](https://grafana.com/products/cloud/k6/)
- [k6 GitHub](https://github.com/grafana/k6)

---

**Başarılar! 🚀**
