<?php

namespace App\Repository;

use App\Entity\SectionHasTasks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SectionHasTasks>
 */
class SectionHasTasksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SectionHasTasks::class);
    }

    //    /**
    //     * @return SectionHasTasks[] Returns an array of SectionHasTasks objects
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

    //    public function findOneBySomeField($value): ?SectionHasTasks
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findMaxPositionInSection($value): ?int
       {
           return $this->createQueryBuilder('sectionHasTasks')
           ->select('MAX(sectionHasTasks.position) as max_position')
               ->andWhere('sectionHasTasks.section = :val')
               ->setParameter('val', $value)
            //    ->orderBy('sectionHasTasks.position', 'DESC')
            //    ->setMaxResults(1)
               ->getQuery()
               ->getSingleScalarResult()
           ;
       }
    
    public function findOneByTaskAndUser(int $taskId, int $userId): ?SectionHasTasks
    {
        return $this->createQueryBuilder('sectionHasTasks')
            ->innerJoin('sectionHasTasks.section', 's')
            ->andWhere('s.user = :userId')
            ->andWhere('sectionHasTasks.task = :taskId')
            ->setParameter('taskId', $taskId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findHigherByPositionInSection($val, $sectionId): array
    {
        return $this->createQueryBuilder('sectionHasTasks')
            ->andWhere('sectionHasTasks.position > :val')
            ->andWhere('sectionHasTasks.section = :sectionId')
            ->setParameter('sectionId', $sectionId)
            ->setParameter('val', $val)
            ->getQuery()
            ->getResult()
        ;
    }
}
