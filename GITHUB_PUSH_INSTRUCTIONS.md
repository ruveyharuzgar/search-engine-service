# GitHub'a Push Etme Talimatları

## 1. GitHub'da Yeni Repository Oluşturun

1. https://github.com adresine gidin
2. Sağ üstteki "+" butonuna tıklayın
3. "New repository" seçin
4. Repository adı: `search-engine-service` (veya istediğiniz bir isim)
5. Description: "Modern PHP/Symfony search engine with multi-provider aggregation and notification system"
6. Public veya Private seçin
7. **README, .gitignore veya license EKLEMEYIN** (zaten var)
8. "Create repository" butonuna tıklayın

## 2. Local Repository'yi GitHub'a Bağlayın

GitHub'da repository oluşturduktan sonra, aşağıdaki komutları terminalinizde çalıştırın:

```bash
# GitHub repository URL'inizi buraya yazın (örnek aşağıda)
git remote add origin https://github.com/KULLANICI_ADINIZ/search-engine-service.git

# Ana branch'i main olarak ayarlayın (zaten main)
git branch -M main

# İlk push
git push -u origin main
```

## 3. Alternatif: SSH ile Push (Önerilen)

Eğer SSH key'iniz varsa:

```bash
git remote add origin git@github.com:KULLANICI_ADINIZ/search-engine-service.git
git branch -M main
git push -u origin main
```

## 4. SSH Key Yoksa Oluşturun

```bash
# SSH key oluştur
ssh-keygen -t ed25519 -C "ruveyharuzgar.108@gmail.com"

# Public key'i kopyala
cat ~/.ssh/id_ed25519.pub

# GitHub'a ekle:
# 1. GitHub Settings > SSH and GPG keys
# 2. New SSH key
# 3. Kopyaladığınız key'i yapıştırın
```

## 5. Sonraki Push'lar

İlk push'tan sonra, değişikliklerinizi şöyle push edebilirsiniz:

```bash
git add .
git commit -m "feat: yeni özellik açıklaması"
git push
```

## 6. Commit Message Formatı

Conventional Commits kullanıyoruz:

- `feat:` - Yeni özellik
- `fix:` - Bug düzeltme
- `docs:` - Dokümantasyon değişikliği
- `style:` - Kod formatı (işlevsellik değişmez)
- `refactor:` - Kod yeniden yapılandırma
- `test:` - Test ekleme/düzeltme
- `chore:` - Build, dependency güncellemeleri

Örnekler:
```bash
git commit -m "feat: add email notification system"
git commit -m "fix: resolve cache clear issue"
git commit -m "docs: update README with notification guide"
```

## 7. .env Dosyası Uyarısı

⚠️ `.env` dosyası `.gitignore`'da olduğu için GitHub'a gitmeyecek.
Production'da `.env` dosyasını manuel oluşturmanız gerekecek.

`.env.example` dosyası GitHub'da olacak, oradan kopyalayıp kullanabilirsiniz:

```bash
cp .env.example .env
# Sonra .env dosyasını düzenleyin
```

## 8. GitHub Repository Özellikleri

Repository oluşturduktan sonra şunları ekleyin:

### Topics (Etiketler)
- php
- symfony
- search-engine
- docker
- redis
- mysql
- notification-system
- rest-api
- clean-architecture

### About Section
```
Modern PHP/Symfony search engine with multi-provider content aggregation, intelligent scoring, Redis caching, and comprehensive notification system. Features Clean Architecture, Docker containerization, and production-ready monitoring.
```

### README Badges (Opsiyonel)

README.md'nin başına ekleyebilirsiniz:

```markdown
![PHP Version](https://img.shields.io/badge/PHP-8.4-blue)
![Symfony Version](https://img.shields.io/badge/Symfony-7.0-black)
![Tests](https://img.shields.io/badge/tests-55%20passed-success)
![License](https://img.shields.io/badge/license-MIT-green)
```

## 9. GitHub Actions (CI/CD) - Opsiyonel

Otomatik test çalıştırmak için `.github/workflows/tests.yml` oluşturabilirsiniz.

## 10. Sorun Giderme

### "Permission denied" hatası
```bash
# SSH key'inizi kontrol edin
ssh -T git@github.com
```

### "Repository not found" hatası
```bash
# Remote URL'i kontrol edin
git remote -v

# Yanlışsa düzeltin
git remote set-url origin DOGRU_URL
```

### "Authentication failed" hatası
```bash
# Personal Access Token kullanın
# GitHub Settings > Developer settings > Personal access tokens
# Token oluşturun ve şifre yerine kullanın
```

## 11. İlk Push Sonrası

Push başarılı olduktan sonra:

1. GitHub repository sayfanızı yenileyin
2. README.md'nin düzgün göründüğünden emin olun
3. Tüm dosyaların yüklendiğini kontrol edin
4. Repository'yi Public yaptıysanız, başkalarıyla paylaşabilirsiniz!

## 12. Hızlı Komutlar

```bash
# Durum kontrolü
git status

# Değişiklikleri görüntüle
git diff

# Commit geçmişi
git log --oneline

# Son commit'i düzelt
git commit --amend

# Branch oluştur
git checkout -b feature/yeni-ozellik

# Branch'leri listele
git branch -a
```

---

**Başarılar! 🚀**

Sorularınız olursa GitHub documentation'a bakabilirsiniz:
https://docs.github.com/en/get-started
