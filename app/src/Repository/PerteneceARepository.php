<?php

namespace App\Repository;

use App\Entity\PerteneceA;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PerteneceA>
 */
class PerteneceARepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PerteneceA::class);
    }
    public function findCursosByCarrera(int $carreraId): array
    {
        return $this->createQueryBuilder('pa')
            ->innerJoin('pa.curso', 'c')
            ->innerJoin('pa.carrera', 'ca')
            ->addSelect('c') // Cargar el curso completo
            ->addSelect('ca') // Cargar la carrera completa
            ->where('ca.id = :carreraId')
            ->setParameter('carreraId', $carreraId)
            ->orderBy('pa.esElectivo', 'ASC') // Primero obligatorios, luego electivos
            ->addOrderBy('c.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return PerteneceA[] Returns an array of PerteneceA objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PerteneceA
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
