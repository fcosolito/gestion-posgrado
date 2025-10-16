<?php

namespace App\Repository;

use App\Entity\InscripcionCarrera;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InscripcionCarrera>
 */
class InscripcionCarreraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InscripcionCarrera::class);
    }
    public function findByCarrera(int $carreraId): array
    {
        return $this->createQueryBuilder('ic')
            ->andWhere('ic.carrera = :carreraId')
            ->setParameter('carreraId', $carreraId)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return InscripcionCarrera[] Returns an array of InscripcionCarrera objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?InscripcionCarrera
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
