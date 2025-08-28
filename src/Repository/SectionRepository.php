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
        return $this->createQueryBuilder('section')
            ->andWhere('section.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult()
        ;
    }

    //    /**
    //     * @return Section[] Returns an array of Section objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

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
