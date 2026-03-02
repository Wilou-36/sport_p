<?php

namespace App\Repository;

use App\Entity\Objectif;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Objectif>
 */
class ObjectifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Objectif::class);
    }

    public function findObjectifActif($user): ?Objectif
    {
        $today = new \DateTime();

        return $this->createQueryBuilder('o')
            ->andWhere('o.user = :user')
            ->andWhere('o.dateDebut <= :today')
            ->andWhere('o.dateFin >= :today')
            ->setParameter('user', $user)
            ->setParameter('today', $today)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Objectif[] Returns an array of Objectif objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Objectif
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
