# 📧 Bildirim Sistemi Dokümantasyonu

## Genel Bakış

Search Engine Service, önemli sistem olayları için otomatik bildirim gönderen kapsamlı bir bildirim sistemi içerir. Sistem, email ve SMS kanallarını destekler ve veritabanında kullanıcı tercihlerini saklar.

## Özellikler

### ✅ Desteklenen Kanallar
- **Email** - HTML formatında profesyonel email'ler (MailHog ile test edilebilir)
- **SMS** - Kısa mesajlar (şu anda simüle edilmiş, gerçek entegrasyon eklenebilir)

### ✅ Bildirim Tipleri
- **Success** (✅) - Başarılı işlemler (örn: sync tamamlandı)
- **Error** (🔴) - Kritik hatalar (örn: sync başarısız)
- **Warning** (⚠️) - Uyarılar (örn: yüksek bellek kullanımı)
- **Info** (ℹ️) - Bilgilendirme (örn: bakım planlandı)

### ✅ Kullanıcı Yönetimi
- Veritabanında kullanıcı bilgileri
- Kanal tercihleri (email, sms)
- Tip filtreleme (hangi bildirimleri alacak)
- Aktif/pasif durumu

## Mimari

```
NotificationManager
    ├── NotificationUserRepository (DB'den kullanıcıları çeker)
    ├── EmailChannel (Email gönderir)
    └── SmsChannel (SMS gönderir)
```

### Bileşenler

1. **NotificationUser Entity** - Kullanıcı bilgileri ve tercihleri
2. **NotificationManager Service** - Ana bildirim yöneticisi
3. **NotificationChannelInterface** - Kanal interface'i
4. **EmailChannel** - Email gönderme implementasyonu
5. **SmsChannel** - SMS gönderme implementasyonu (simüle)

## Kurulum

### 1. Migration Çalıştırma

```bash
docker-compose exec php php bin/console doctrine:migrations:migrate
```

### 2. Admin Kullanıcı Ekleme

```bash
docker-compose exec php php bin/console app:add-notification-user
```

Bu komut şu kullanıcıyı ekler:
- **İsim**: Rüveyha Rüzgar
- **Email**: ruveyharuzgar.108@gmail.com
- **Telefon**: +905523650801
- **Kanallar**: email, sms
- **Tipler**: error, success, warning, info

### 3. MailHog Kontrolü

Email'leri görmek için: **http://localhost:8025**

## Kullanım

### Kod İçinde

```php
use App\Service\NotificationManager;

class YourService
{
    public function __construct(
        private NotificationManager $notificationManager
    ) {}

    public function someMethod(): void
    {
        // Success bildirimi
        $this->notificationManager->success(
            'İşlem başarıyla tamamlandı!',
            ['count' => 10, 'duration' => '2.5s']
        );

        // Error bildirimi
        $this->notificationManager->error(
            'Kritik hata oluştu!',
            ['error_code' => 500, 'message' => 'Database connection failed']
        );

        // Warning bildirimi
        $this->notificationManager->warning(
            'Yüksek bellek kullanımı tespit edildi',
            ['memory_usage' => '85%']
        );

        // Info bildirimi
        $this->notificationManager->info(
            'Sistem bakımı planlandı',
            ['scheduled_time' => '2024-03-20 02:00:00']
        );
    }
}
```

### Test Komutları

```bash
# Success bildirimi test
docker-compose exec php php bin/console app:test-notification --type=success

# Error bildirimi test
docker-compose exec php php bin/console app:test-notification --type=error

# Warning bildirimi test
docker-compose exec php php bin/console app:test-notification --type=warning

# Info bildirimi test
docker-compose exec php php bin/console app:test-notification --type=info
```

## Email Şablonu

Email'ler HTML formatında gönderilir ve şunları içerir:
- Renkli header (tip bazlı)
- Ana mesaj
- Ek detaylar (context)
- Timestamp
- Profesyonel görünüm

### Email Renkleri
- **Success**: Yeşil (#28a745)
- **Error**: Kırmızı (#dc3545)
- **Warning**: Sarı (#ffc107)
- **Info**: Mavi (#17a2b8)

## SMS Formatı

SMS mesajları kısa ve öz tutulur (max 160 karakter):

```
[SUCCESS] Successfully synchronized 8 contents from providers
[ERROR] Critical error in sync process
[WARNING] High memory usage detected
[INFO] System maintenance scheduled
```

## Veritabanı Şeması

```sql
CREATE TABLE notification_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    notification_channels JSON NOT NULL,  -- ["email", "sms"]
    notification_types JSON NOT NULL,     -- ["error", "success", "warning", "info"]
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL
);
```

## Yapılandırma

### .env Dosyası

```env
# Mail Configuration
MAILER_DSN=smtp://mailhog:1025
MAIL_FROM=noreply@searchengine.com

# SMS Configuration (optional)
SMS_API_URL=
SMS_API_KEY=
```

### Production için

```env
# Gmail SMTP
MAILER_DSN=smtp://username:password@smtp.gmail.com:587

# SendGrid
MAILER_DSN=smtp://apikey:YOUR_API_KEY@smtp.sendgrid.net:587

# AWS SES
MAILER_DSN=ses+smtp://ACCESS_KEY:SECRET_KEY@default?region=eu-west-1
```

## Gerçek SMS Entegrasyonu

### Twilio ile

1. Paketi yükle:
```bash
composer require twilio/sdk
```

2. `src/Service/Channel/SmsChannel.php` güncelle:
```php
use Twilio\Rest\Client;

class SmsChannel implements NotificationChannelInterface
{
    private Client $twilioClient;

    public function __construct(
        private LoggerInterface $logger,
        private string $twilioSid,
        private string $twilioToken,
        private string $fromPhone
    ) {
        $this->twilioClient = new Client($twilioSid, $twilioToken);
    }

    public function send(NotificationUser $user, string $message, string $type, array $context = []): bool
    {
        try {
            $smsMessage = $this->formatSmsMessage($message, $type);
            
            $this->twilioClient->messages->create(
                $user->getPhone(),
                [
                    'from' => $this->fromPhone,
                    'body' => $smsMessage
                ]
            );
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('SMS send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
```

3. `.env` güncelle:
```env
TWILIO_SID=your_account_sid
TWILIO_TOKEN=your_auth_token
TWILIO_FROM_PHONE=+1234567890
```

## Yeni Kullanıcı Ekleme

### Manuel (Database)

```sql
INSERT INTO notification_users (name, email, phone, notification_channels, notification_types, is_active, created_at)
VALUES (
    'John Doe',
    'john@example.com',
    '+1234567890',
    '["email", "sms"]',
    '["error", "warning"]',
    1,
    NOW()
);
```

### Programatik

```php
$user = new NotificationUser();
$user->setName('John Doe')
    ->setEmail('john@example.com')
    ->setPhone('+1234567890')
    ->setNotificationChannels(['email', 'sms'])
    ->setNotificationTypes(['error', 'warning'])
    ->setIsActive(true);

$this->notificationUserRepository->save($user);
```

## Mevcut Entegrasyonlar

Sistem şu anda şu yerlerde kullanılıyor:

### 1. Content Sync
```php
// SearchService::syncContents()
$this->notificationManager->info('Starting content synchronization');
// ... sync işlemi ...
$this->notificationManager->success("Successfully synchronized {$count} contents");
```

### 2. Error Handling
```php
try {
    // risky operation
} catch (\Exception $e) {
    $this->notificationManager->error(
        'Operation failed: ' . $e->getMessage(),
        ['exception' => $e]
    );
}
```

## Best Practices

1. **Context Kullanın** - Ek bilgi için context parametresini kullanın
2. **Anlamlı Mesajlar** - Açık ve anlaşılır mesajlar yazın
3. **Tip Seçimi** - Doğru bildirim tipini kullanın
4. **Spam Önleme** - Çok sık bildirim göndermekten kaçının
5. **Test Edin** - Production'a geçmeden önce test komutlarını kullanın

## Troubleshooting

### Email Gönderilmiyor

1. MailHog çalışıyor mu kontrol edin:
```bash
docker-compose ps mailhog
```

2. MAILER_DSN doğru mu kontrol edin:
```bash
docker-compose exec php php bin/console debug:config framework mailer
```

3. Log'lara bakın:
```bash
docker-compose exec php tail -f var/log/dev.log
```

### Kullanıcı Bulunamıyor

```bash
# Kullanıcıları listele
docker-compose exec php php bin/console doctrine:query:sql "SELECT * FROM notification_users"

# Yeni kullanıcı ekle
docker-compose exec php php bin/console app:add-notification-user
```

## Gelecek Geliştirmeler

- [ ] Slack entegrasyonu
- [ ] Webhook desteği
- [ ] Push notification (mobile)
- [ ] Bildirim geçmişi (log table)
- [ ] Kullanıcı yönetim paneli
- [ ] Bildirim şablonları
- [ ] Rate limiting
- [ ] Batch notifications

## Lisans

Bu bildirim sistemi Search Engine Service'in bir parçasıdır.
