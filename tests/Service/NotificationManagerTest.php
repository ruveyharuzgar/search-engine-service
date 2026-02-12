<?php

namespace App\Tests\Service;

use App\Repository\NotificationUserRepository;
use App\Service\NotificationManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class NotificationManagerTest extends TestCase
{
    private NotificationManager $notificationManager;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $userRepository = $this->createMock(NotificationUserRepository::class);
        $userRepository->method('findAllActive')->willReturn([]);
        
        $this->notificationManager = new NotificationManager(
            $this->logger,
            $userRepository,
            [] // empty channels for unit test
        );
    }

    public function testAddSuccessNotification(): void
    {
        $this->notificationManager->success('Test success message');
        $this->assertTrue($this->notificationManager->hasNotifications());
        $this->assertEquals(1, $this->notificationManager->count());
        $this->assertTrue($this->notificationManager->hasType('success'));
    }

    public function testAddErrorNotification(): void
    {
        $this->notificationManager->error('Test error message');
        $this->assertTrue($this->notificationManager->hasType('error'));
        $this->assertEquals(1, $this->notificationManager->countByType('error'));
    }

    public function testAddWarningNotification(): void
    {
        $this->notificationManager->warning('Test warning message');
        $this->assertTrue($this->notificationManager->hasType('warning'));
    }

    public function testAddInfoNotification(): void
    {
        $this->notificationManager->info('Test info message');
        $this->assertTrue($this->notificationManager->hasType('info'));
    }

    public function testGetAllNotifications(): void
    {
        $this->notificationManager->success('Success 1');
        $this->notificationManager->error('Error 1');
        $this->notificationManager->warning('Warning 1');
        $all = $this->notificationManager->getAll();

        $this->assertCount(3, $all);
    }

    public function testGetByType(): void
    {
        $this->notificationManager->success('Success 1');
        $this->notificationManager->success('Success 2');
        $this->notificationManager->error('Error 1');
        $successNotifications = $this->notificationManager->getByType('success');
        $this->assertCount(2, $successNotifications);
    }

    public function testClear(): void
    {
        $this->notificationManager->success('Test');
        $this->notificationManager->error('Test');
        $this->notificationManager->clear();
        $this->assertFalse($this->notificationManager->hasNotifications());
        $this->assertEquals(0, $this->notificationManager->count());
    }

    public function testToArray(): void
    {
        $this->notificationManager->success('Success message');
        $this->notificationManager->error('Error message');
        $array = $this->notificationManager->toArray();
        $this->assertIsArray($array);
        $this->assertCount(2, $array);
        $this->assertArrayHasKey('type', $array[0]);
        $this->assertArrayHasKey('message', $array[0]);
        $this->assertArrayHasKey('timestamp', $array[0]);
    }

    public function testCountByType(): void
    {
        $this->notificationManager->success('Success 1');
        $this->notificationManager->success('Success 2');
        $this->notificationManager->success('Success 3');
        $this->notificationManager->error('Error 1');
        $this->assertEquals(3, $this->notificationManager->countByType('success'));
        $this->assertEquals(1, $this->notificationManager->countByType('error'));
        $this->assertEquals(0, $this->notificationManager->countByType('warning'));
    }

    public function testNotificationWithContext(): void
    {
        $context = ['user_id' => 123, 'action' => 'sync'];
        $this->notificationManager->info('Test with context', $context);
        $notifications = $this->notificationManager->getAll();
        $this->assertArrayHasKey('context', $notifications[0]);
        $this->assertEquals($context, $notifications[0]['context']);
    }
}
