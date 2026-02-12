<?php

namespace App\Repository;

use App\Entity\Content;
use App\DTO\ContentDTO;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Content::class);
    }

    /**
     * İçerik arama
     */
    public function search(?string $keyword = null, ?string $type = null): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($keyword) {
            $qb->andWhere('c.title LIKE :keyword OR c.tags LIKE :keyword')
               ->setParameter('keyword', '%' . $keyword . '%');
        }

        if ($type) {
            $qb->andWhere('c.type = :type')
               ->setParameter('type', $type);
        }

        $entities = $qb->getQuery()->getResult();

        // Entity'leri DTO'ya dönüştür
        return array_map(function (Content $entity) {
            return new ContentDTO(
                id: $entity->getId(),
                title: $entity->getTitle(),
                type: $entity->getType(),
                metrics: $entity->getMetrics(),
                publishedAt: $entity->getPublishedAt(),
                tags: $entity->getTags()
            );
        }, $entities);
    }

    /**
     * İçerik kaydet veya güncelle
     */
    public function save(ContentDTO $dto): void
    {
        $entity = $this->find($dto->id);

        if (!$entity) {
            $entity = new Content();
            $entity->setId($dto->id);
        }

        $entity->setTitle($dto->title)
               ->setType($dto->type)
               ->setMetrics($dto->metrics)
               ->setPublishedAt($dto->publishedAt)
               ->setTags($dto->tags)
               ->setUpdatedAt(new \DateTime());

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Tüm içerikleri sil
     */
    public function truncate(): void
    {
        $this->createQueryBuilder('c')
             ->delete()
             ->getQuery()
             ->execute();
    }
}
