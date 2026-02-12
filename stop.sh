#!/bin/bash

echo "🛑 Arama Motoru Servisi durduruluyor..."
echo ""

# Ana uygulamayı durdur
echo "📦 Ana uygulama durduruluyor..."
docker-compose down

# Mock API'leri durdur
echo "📡 Mock API'ler durduruluyor..."
cd mock-apis
docker-compose down
cd ..

echo ""
echo "✅ Tüm servisler durduruldu!"
echo ""
echo "🔄 Yeniden başlatmak için: ./start.sh"
echo "🗑️  Verileri silmek için: docker-compose down -v"
echo ""
