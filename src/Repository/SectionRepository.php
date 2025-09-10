<?php

namespace App\Repository;

use App\Entity\Section;
use App\Entity\SectionHasTasks;
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

    public function findHigherByPosition($val, $userId): array
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
}
