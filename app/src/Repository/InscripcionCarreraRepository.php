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
    public function findCarrerasByAlumnoId(int $alumnoId): array
    {
        // Devuelve entidades Carrera (no arrays)
        return $this->getEntityManager()
            ->createQuery(
                'SELECT c
                 FROM App\Entity\Carrera c
                 JOIN App\Entity\InscripcionCarrera ic WITH ic.carrera = c
                 WHERE ic.alumno = :alumnoId'
            )
            ->setParameter('alumnoId', $alumnoId)
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
