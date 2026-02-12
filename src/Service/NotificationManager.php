<?php

namespace App\Service;

use App\Entity\NotificationUser;
use App\Repository\NotificationUserRepository;
use App\Service\Channel\NotificationChannelInterface;
use Psr\Log\LoggerInterface;

class NotificationManager
{
    private array $notifications = [];
    private array $channels = [];

    public function __construct(
        private LoggerInterface $logger,
        private NotificationUserRepository $userRepository,
        iterable $channels
    ) {
        foreach ($channels as $channel) {
            if ($channel instanceof NotificationChannelInterface) {
                $this->channels[] = $channel;
            }
        }
    }

    /**
     * Add a success notification and send to users
     */
    public function success(string $message, array $context = []): void
    {
        $this->add('success', $message, $context);
        $this->logger->info($message, $context);
        $this->sendToUsers('success', $message, $context);
    }

    /**
     * Add an error notification and send to users
     */
    public function error(string $message, array $context = []): void
    {
        $this->add('error', $message, $context);
        $this->logger->error($message, $context);
        $this->sendToUsers('error', $message, $context);
    }

    /**
     * Add a warning notification and send to users
     */
    public function warning(string $message, array $context = []): void
    {
        $this->add('warning', $message, $context);
        $this->logger->warning($message, $context);
        $this->sendToUsers('warning', $message, $context);
    }

    /**
     * Add an info notification and send to users
     */
    public function info(string $message, array $context = []): void
    {
        $this->add('info', $message, $context);
        $this->logger->info($message, $context);
        $this->sendToUsers('info', $message, $context);
    }

    /**
     * Send notification to all eligible users
     */
    private function sendToUsers(string $type, string $message, array $context = []): void
    {
        try {
            $users = $this->userRepository->findAllActive();
            
            foreach ($users as $user) {
                $this->sendToUser($user, $type, $message, $context);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to send notifications to users', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notification to a specific user through their preferred channels
     */
    private function sendToUser(NotificationUser $user, string $type, string $message, array $context = []): void
    {
        foreach ($user->getNotificationChannels() as $channelName) {
            if (!$user->shouldReceiveNotification($type, $channelName)) {
                continue;
            }

            foreach ($this->channels as $channel) {
                if ($channel->supports($channelName)) {
                    $channel->send($user, $message, $type, $context);
                }
            }
        }
    }

    /**
     * Add a notification
     */
    private function add(string $type, string $message, array $context = []): void
    {
        $this->notifications[] = [
            'type' => $type,
            'message' => $message,
            'context' => $context,
            'timestamp' => new \DateTime(),
        ];
    }

    /**
     * Get all notifications
     */
    public function getAll(): array
    {
        return $this->notifications;
    }

    /**
     * Get notifications by type
     */
    public function getByType(string $type): array
    {
        return array_filter(
            $this->notifications,
            fn($notification) => $notification['type'] === $type
        );
    }

    /**
     * Check if there are any notifications
     */
    public function hasNotifications(): bool
    {
        return !empty($this->notifications);
    }

    /**
     * Check if there are notifications of a specific type
     */
    public function hasType(string $type): bool
    {
        return !empty($this->getByType($type));
    }

    /**
     * Clear all notifications
     */
    public function clear(): void
    {
        $this->notifications = [];
    }

    /**
     * Get notification count
     */
    public function count(): int
    {
        return count($this->notifications);
    }

    /**
     * Get notification count by type
     */
    public function countByType(string $type): int
    {
        return count($this->getByType($type));
    }

    /**
     * Convert notifications to array format for API responses
     */
    public function toArray(): array
    {
        return array_map(function ($notification) {
            return [
                'type' => $notification['type'],
                'message' => $notification['message'],
                'timestamp' => $notification['timestamp']->format('Y-m-d H:i:s'),
            ];
        }, $this->notifications);
    }
}
