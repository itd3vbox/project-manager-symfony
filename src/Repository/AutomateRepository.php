<?php

namespace App\Repository;

use App\Entity\Automate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Automate>
 *
 * @method Automate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Automate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Automate[]    findAll()
 * @method Automate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AutomateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Automate::class);
    }

//    /**
//     * @return Automate[] Returns an array of Automate objects
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

//    public function findOneBySomeField($value): ?Automate
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
