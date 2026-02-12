# 🚀 Yük Testi - Hızlı Başlangıç

## Mülakat için En İyi Seçenek: PHP (Symfony Command)

### Neden PHP?
✅ Projeye entegre (aynı codebase)  
✅ Kolay açıklanır ve gösterilir  
✅ Symfony bilginizi gösterir  
✅ Ekstra kurulum gerektirmez  

### Hızlı Kullanım

```bash
# 1. Servisi başlat
docker-compose up -d

# 2. Basit test (100 request, 5 concurrent) - Hızlı test
docker-compose exec php php bin/console app:load-test -r 100 -c 5

# 3. Orta ölçekli test (1000 request, 20 concurrent)
docker-compose exec php php bin/console app:load-test -r 1000 -c 20

# 4. Stress test (kademeli yük artışı: 10→50→100→200 users)
docker-compose exec php php bin/console app:load-test --scenario=stress

# 5. Spike test (ani yük artışı: 20→200→20 users)
docker-compose exec php php bin/console app:load-test --scenario=spike

# 6. Sonuçları kaydet
docker-compose exec php php bin/console app:load-test -r 500 -c 10 -o results.json
```

### Test Sonuçları (Örnek)

**Test Koşulları:** 1000 request, 20 concurrent users

```
📊 Test Results
+-------------------------+-------------+
| Total Requests          | 1,000       |
| Successful              | 1,000       |
| Success Rate            | 100.00%     |
| Total Time              | 5.68s       |
| Requests/sec            | 176.00      |
|                         |             |
| Avg Response Time       | 5.65ms      |
| 95th Percentile         | 8.63ms      |
| 99th Percentile         | 9.95ms      |
|                         |             |
| Cache Hit Rate          | 100.00%     |
+-------------------------+-------------+

🎯 Performance Assessment
✅ All metrics look good!
```

### Örnek Çıktı
```
🚀 Search Engine Load Test
========================================
Configuration
+--------------+---------------+
| Base URL     | http://...    |
| Scenario     | basic         |
| Total Req    | 1000          |
| Concurrent   | 10            |
+--------------+---------------+

✅ Service is healthy

📊 Test Results
+-------------------------+-------------+
| Total Requests          | 1,000       |
| Successful              | 998         |
| Success Rate            | 99.80%      |
| Requests/sec            | 22.11       |
| Avg Response Time       | 452.34ms    |
| 95th Percentile         | 789.12ms    |
| Cache Hit Rate          | 78.50%      |
+-------------------------+-------------+

🎯 Performance Assessment
✅ All metrics look good!
```

## Alternatif Araçlar

### Go (Yüksek Performans)
```bash
go build -o load-test load-test.go
./load-test -requests 10000 -concurrent 200
```

### k6 (Modern)
```bash
brew install k6
k6 run load-test.js
```

### Apache Bench (Basit)
```bash
./load-test.sh quick
```

## Detaylı Dokümantasyon

Tüm detaylar için: **[LOAD_TESTING.md](LOAD_TESTING.md)**

---

## Mülakatta Nasıl Gösterirsiniz?

### Demo Senaryosu
```bash
# 1. "Şimdi yük testi yapacağım"
docker-compose exec php php bin/console app:load-test -r 1000 -c 20

# 2. Sonuçları açıkla:
# - "Success rate %99.8, çok iyi"
# - "95th percentile 789ms, kullanıcıların %95'i 1 saniyeden hızlı yanıt alıyor"
# - "Cache hit rate %78.5, cache stratejimiz çalışıyor"

# 3. Stress test göster:
docker-compose exec php php bin/console app:load-test --scenario=stress

# 4. "Görüyorsunuz ki yük arttıkça sistem nasıl davranıyor izleyebiliyoruz"
```

### Sorulara Hazır Olun

**S: "Yük testi sonuçlarını nasıl yorumlarsınız?"**

**C:** "Üç ana metriğe bakarım:
1. Success Rate: %95+ olmalı
2. 95th Percentile: <1000ms olmalı
3. Cache Hit Rate: %70+ olmalı

Eğer metrikler kötüyse, database optimization veya cache TTL ayarlarına bakarım."

**S: "Büyük data geldiğinde ne yaparsınız?"**

**C:** "Yük testinde görebiliriz. Batch processing, async jobs, memory management kullanırım. Yük testi ile optimizasyonların etkisini ölçerim."

---

**Başarılar! 🚀**
