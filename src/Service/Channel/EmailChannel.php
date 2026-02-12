<?php

namespace App\Service\Channel;

use App\Entity\NotificationUser;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailChannel implements NotificationChannelInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private string $fromEmail = 'noreply@searchengine.com'
    ) {}

    public function send(NotificationUser $user, string $message, string $type, array $context = []): bool
    {
        try {
            $subject = $this->getSubject($type);
            
            $email = (new Email())
                ->from($this->fromEmail)
                ->to($user->getEmail())
                ->subject($subject)
                ->html($this->buildHtmlBody($message, $type, $context));

            $this->mailer->send($email);
            
            $this->logger->info('Email notification sent', [
                'user' => $user->getEmail(),
                'type' => $type,
                'message' => $message
            ]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email notification', [
                'user' => $user->getEmail(),
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    public function supports(string $channel): bool
    {
        return $channel === 'email';
    }

    private function getSubject(string $type): string
    {
        return match($type) {
            'error' => '🔴 Search Engine - Error Alert',
            'warning' => '⚠️ Search Engine - Warning',
            'success' => '✅ Search Engine - Success',
            'info' => 'ℹ️ Search Engine - Information',
            default => 'Search Engine - Notification'
        };
    }

    private function buildHtmlBody(string $message, string $type, array $context): string
    {
        $color = match($type) {
            'error' => '#dc3545',
            'warning' => '#ffc107',
            'success' => '#28a745',
            'info' => '#17a2b8',
            default => '#6c757d'
        };

        $contextHtml = '';
        if (!empty($context)) {
            $contextHtml = '<div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">';
            $contextHtml .= '<strong>Additional Details:</strong><br>';
            foreach ($context as $key => $value) {
                if ($key !== 'exception') {
                    $contextHtml .= sprintf('<div>%s: %s</div>', htmlspecialchars($key), htmlspecialchars(print_r($value, true)));
                }
            }
            $contextHtml .= '</div>';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: {$color}; color: white; padding: 20px; border-radius: 5px 5px 0 0;">
            <h2 style="margin: 0;">Search Engine Notification</h2>
        </div>
        <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px;">
            <div style="padding: 15px; background: #f8f9fa; border-left: 4px solid {$color}; margin-bottom: 20px;">
                {$message}
            </div>
            {$contextHtml}
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666;">
                <p>This is an automated notification from Search Engine Service.</p>
                <p>Time: {$this->getCurrentTime()}</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getCurrentTime(): string
    {
        return (new \DateTime())->format('Y-m-d H:i:s');
    }
}
