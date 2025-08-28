<?php

namespace App\Repository;

use App\Entity\Section;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Section>
 */
class SectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Section::class);
    }

    //    /**
    //     * @return Section[] Returns an array the Section objects with their tasks related to given user
    //     */
    public function findByUser($userId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAboveByPosition($val, $userId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.position > :val')
            ->andWhere('s.user = :userId')
            ->setParameter('val', $val)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneByTitle($value): ?Section
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.title = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByTaskAndUser(int $taskId, int $userId): ?Section
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.tasks', 't')
            ->andWhere('s.user = :userId')
            ->andWhere('t.id = :taskId')
            ->setParameter('taskId', $taskId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
