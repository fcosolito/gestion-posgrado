<?php

namespace App\Repository;

use App\Entity\Alumno;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Alumno>
 */
class AlumnoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alumno::class);
    }

    public function searchXor(string $query): array {
        $qb =  $this->createQueryBuilder('a')
            ->where('a.nombre LIKE :nombre')
            ->setParameter('nombre', "%".$query."%")
            ->orWhere('a.apellido = :apellido')
            ->setParameter('apellido', "%".$query."%")
            ->orWhere('a.email = :email')
            ->setParameter('email', "%".$query."%");
        if (ctype_digit($query)){
            $qb->orWhere('a.dni = :dni')
                ->setParameter('dni', (int) $query);
        }

        return $qb->getQuery()->getResult();
    }
    //    /**
    //     * @return Alumno[] Returns an array of Alumno objects
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

    //    public function findOneBySomeField($value): ?Alumno
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
