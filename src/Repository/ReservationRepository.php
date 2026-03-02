<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findNextSession($client)
    {
        return $this->createQueryBuilder('r')
            ->where('r.client = :client')
            ->andWhere('r.date > :now')
            ->setParameter('client', $client)
            ->setParameter('now', new \DateTime())
            ->orderBy('r.date', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countByClient($client): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.client = :client')
            ->setParameter('client', $client)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findNextSeanceForClient(int $clientId): ?Seance
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.client = :client')
            ->andWhere('s.date >= :now')
            ->setParameter('client', $clientId)
            ->setParameter('now', new \DateTime())
            ->orderBy('s.date', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countByClientAndDateRange($client, $start, $end): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.client = :client')
            ->andWhere('r.date BETWEEN :start AND :end')
            ->setParameter('client', $client)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

        public function save(Reservation $entity, bool $flush = false): void
        {
            $this->getEntityManager()->persist($entity);
    
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        }
    
        public function remove(Reservation $entity, bool $flush = false): void
        {
            $this->getEntityManager()->remove($entity);
    
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        }

//    /**
//     * @return Reservation[] Returns an array of Reservation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Reservation
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
