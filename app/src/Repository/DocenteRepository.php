<?php

namespace App\Repository;

use App\Entity\Docente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Docente>
 */
class DocenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Docente::class);
    }

    public function searchXor(string $query): array {
        $qb =  $this->createQueryBuilder('d')
            ->where('d.nombre LIKE :nombre')
            ->setParameter('nombre', "%".$query."%")
            ->orWhere('d.apellido LIKE :apellido')
            ->setParameter('apellido', "%".$query."%")
            ->orWhere('d.email LIKE :email')
            ->setParameter('email', "%".$query."%");
        if (ctype_digit($query)){
            $qb->orWhere('d.dni = :dni')
                ->setParameter('dni', (int) $query);
        }

        return $qb->getQuery()->getResult();
    }
    //    /**
    //     * @return Docente[] Returns an array of Docente objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Docente
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
