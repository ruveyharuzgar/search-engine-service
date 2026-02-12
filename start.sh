#!/bin/bash

echo "🚀 Arama Motoru Servisi Başlatılıyor..."
echo ""

# Renk kodları
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Mock API'leri başlat
echo -e "${BLUE}📡 Mock API'ler başlatılıyor...${NC}"
cd mock-apis
docker-compose up -d
cd ..
sleep 3
echo -e "${GREEN}✅ Mock API'ler hazır${NC}"
echo ""

# Ana uygulamayı başlat
echo -e "${BLUE}🐳 Docker container'ları başlatılıyor...${NC}"
docker-compose up -d --build
sleep 5
echo -e "${GREEN}✅ Container'lar hazır${NC}"
echo ""

# Composer bağımlılıklarını yükle
echo -e "${BLUE}📦 Composer bağımlılıkları yükleniyor...${NC}"
docker-compose exec -T php composer install --no-interaction
echo -e "${GREEN}✅ Bağımlılıklar yüklendi${NC}"
echo ""

# Veritabanını oluştur
echo -e "${BLUE}🗄️  Veritabanı oluşturuluyor...${NC}"
docker-compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction
echo -e "${GREEN}✅ Veritabanı hazır${NC}"
echo ""

# İlk verileri yükle
echo -e "${BLUE}🔄 İlk veriler yükleniyor...${NC}"
sleep 2
curl -s -X POST http://localhost:8080/api/sync > /dev/null 2>&1
echo -e "${GREEN}✅ Veriler yüklendi${NC}"
echo ""

echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}🎉 Kurulum tamamlandı!${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${YELLOW}📍 Erişim Adresleri:${NC}"
echo ""
echo -e "  🌐 Dashboard:        ${BLUE}http://localhost:8080${NC}"
echo -e "  📚 Swagger API:      ${BLUE}http://localhost:8080/api/doc${NC}"
echo -e "  🔍 API Search:       ${BLUE}http://localhost:8080/api/search${NC}"
echo -e "  📡 JSON Provider:    ${BLUE}http://localhost:8081/index.php${NC}"
echo -e "  📡 XML Provider:     ${BLUE}http://localhost:8082/index.php${NC}"
echo ""
echo -e "${YELLOW}🧪 Test Komutları:${NC}"
echo ""
echo -e "  curl \"http://localhost:8080/api/search\""
echo -e "  curl \"http://localhost:8080/api/search?keyword=programming\""
echo -e "  curl \"http://localhost:8080/api/search?type=video\""
echo ""
echo -e "${YELLOW}🛑 Durdurmak için:${NC}"
echo ""
echo -e "  ./stop.sh"
echo ""
