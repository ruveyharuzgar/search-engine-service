<?php

namespace App\Tests\Service;

use App\Entity\NotificationUser;
use App\Repository\NotificationUserRepository;
use App\Service\NotificationManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NotificationManagerIntegrationTest extends KernelTestCase
{
    private NotificationManager $notificationManager;
    private NotificationUserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $this->notificationManager = $container->get(NotificationManager::class);
        $this->userRepository = $container->get(NotificationUserRepository::class);
    }

    public function testNotificationUserExists(): void
    {
        $users = $this->userRepository->findAllActive();
        
        $this->assertNotEmpty($users, 'Should have at least one active notification user');
        $this->assertInstanceOf(NotificationUser::class, $users[0]);
    }

    public function testSuccessNotification(): void
    {
        $this->notificationManager->success('Test success message', ['test' => true]);
        
        $notifications = $this->notificationManager->getAll();
        $this->assertNotEmpty($notifications);
        $this->assertEquals('success', $notifications[0]['type']);
        $this->assertEquals('Test success message', $notifications[0]['message']);
    }

    public function testErrorNotification(): void
    {
        $this->notificationManager->error('Test error message', ['error_code' => 500]);
        
        $notifications = $this->notificationManager->getAll();
        $this->assertNotEmpty($notifications);
        $this->assertEquals('error', $notifications[0]['type']);
    }

    public function testWarningNotification(): void
    {
        $this->notificationManager->warning('Test warning message');
        
        $notifications = $this->notificationManager->getAll();
        $this->assertNotEmpty($notifications);
        $this->assertEquals('warning', $notifications[0]['type']);
    }

    public function testInfoNotification(): void
    {
        $this->notificationManager->info('Test info message');
        
        $notifications = $this->notificationManager->getAll();
        $this->assertNotEmpty($notifications);
        $this->assertEquals('info', $notifications[0]['type']);
    }

    public function testNotificationToArray(): void
    {
        $this->notificationManager->success('Test message');
        
        $array = $this->notificationManager->toArray();
        $this->assertIsArray($array);
        $this->assertArrayHasKey('type', $array[0]);
        $this->assertArrayHasKey('message', $array[0]);
        $this->assertArrayHasKey('timestamp', $array[0]);
    }

    public function testMultipleNotifications(): void
    {
        $this->notificationManager->success('Success 1');
        $this->notificationManager->error('Error 1');
        $this->notificationManager->warning('Warning 1');
        
        $this->assertEquals(3, $this->notificationManager->count());
        $this->assertEquals(1, $this->notificationManager->countByType('success'));
        $this->assertEquals(1, $this->notificationManager->countByType('error'));
        $this->assertEquals(1, $this->notificationManager->countByType('warning'));
    }

    public function testClearNotifications(): void
    {
        $this->notificationManager->success('Test');
        $this->notificationManager->error('Test');
        
        $this->assertEquals(2, $this->notificationManager->count());
        
        $this->notificationManager->clear();
        
        $this->assertEquals(0, $this->notificationManager->count());
        $this->assertFalse($this->notificationManager->hasNotifications());
    }
}
