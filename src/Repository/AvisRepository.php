<?php

namespace App\Repository;

use App\Entity\Avis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Avis>
 */
class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    public function getAverageForCoach($coach)
    {
        return $this->createQueryBuilder('a')
            ->select('AVG(a.note)')
            ->join('a.seance', 's')
            ->where('s.coach = :coach')
            ->setParameter('coach', $coach)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getStatsForCoach($coach): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('AVG(a.note) as moyenne')
            ->addSelect('COUNT(a.id) as total')
            ->join('a.seance', 's')
            ->where('s.coach = :coach')
            ->setParameter('coach', $coach);

        $global = $qb->getQuery()->getSingleResult();

        // Répartition des notes
        $distribution = $this->createQueryBuilder('a')
            ->select('a.note, COUNT(a.id) as count')
            ->join('a.seance', 's')
            ->where('s.coach = :coach')
            ->setParameter('coach', $coach)
            ->groupBy('a.note')
            ->getQuery()
            ->getResult();

        return [
            'moyenne' => round((float) $global['moyenne'], 1),
            'total' => (int) $global['total'],
            'distribution' => $distribution
        ];
    }

//    /**
//     * @return Avis[] Returns an array of Avis objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Avis
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
