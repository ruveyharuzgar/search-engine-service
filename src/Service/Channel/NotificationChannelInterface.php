<?php

namespace App\Service\Channel;

use App\Entity\NotificationUser;

interface NotificationChannelInterface
{
    public function send(NotificationUser $user, string $message, string $type, array $context = []): bool;
    
    public function supports(string $channel): bool;
}
