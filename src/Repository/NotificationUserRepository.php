<?php

namespace App\Repository;

use App\Entity\NotificationUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NotificationUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationUser::class);
    }

    public function save(NotificationUser $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Get active users who should receive notifications for given type and channel
     */
    public function findActiveForNotification(string $type, string $channel): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    public function findAllActive(): array
    {
        return $this->findBy(['isActive' => true]);
    }
}
