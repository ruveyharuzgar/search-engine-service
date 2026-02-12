<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'notification_users')]
class NotificationUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'json')]
    private array $notificationChannels = ['email']; // email, sms

    #[ORM\Column(type: 'json')]
    private array $notificationTypes = ['error', 'success']; // error, warning, info, success

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getNotificationChannels(): array
    {
        return $this->notificationChannels;
    }

    public function setNotificationChannels(array $channels): self
    {
        $this->notificationChannels = $channels;
        return $this;
    }

    public function getNotificationTypes(): array
    {
        return $this->notificationTypes;
    }

    public function setNotificationTypes(array $types): self
    {
        $this->notificationTypes = $types;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function shouldReceiveNotification(string $type, string $channel): bool
    {
        return $this->isActive 
            && in_array($type, $this->notificationTypes)
            && in_array($channel, $this->notificationChannels);
    }
}
