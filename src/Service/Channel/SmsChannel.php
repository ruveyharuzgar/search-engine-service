<?php

namespace App\Service\Channel;

use App\Entity\NotificationUser;
use Psr\Log\LoggerInterface;

class SmsChannel implements NotificationChannelInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private ?string $smsApiUrl = null,
        private ?string $smsApiKey = null
    ) {}

    public function send(NotificationUser $user, string $message, string $type, array $context = []): bool
    {
        if (!$user->getPhone()) {
            $this->logger->warning('Cannot send SMS: user has no phone number', [
                'user' => $user->getEmail()
            ]);
            return false;
        }

        try {
            // For now, just log the SMS (you can integrate with Twilio, Vonage, etc.)
            $smsMessage = $this->formatSmsMessage($message, $type);
            
            $this->logger->info('SMS notification (simulated)', [
                'phone' => $user->getPhone(),
                'type' => $type,
                'message' => $smsMessage
            ]);

            // TODO: Integrate with real SMS provider
            // Example with Twilio:
            // $this->twilioClient->messages->create($user->getPhone(), [
            //     'from' => $this->fromPhone,
            //     'body' => $smsMessage
            // ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send SMS notification', [
                'phone' => $user->getPhone(),
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    public function supports(string $channel): bool
    {
        return $channel === 'sms';
    }

    private function formatSmsMessage(string $message, string $type): string
    {
        $prefix = match($type) {
            'error' => '[ERROR]',
            'warning' => '[WARNING]',
            'success' => '[SUCCESS]',
            'info' => '[INFO]',
            default => ''
        };

        // SMS messages should be short (160 chars)
        $fullMessage = trim("$prefix $message");
        
        if (strlen($fullMessage) > 160) {
            return substr($fullMessage, 0, 157) . '...';
        }

        return $fullMessage;
    }
}
