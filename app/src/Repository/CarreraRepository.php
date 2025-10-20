<?php

namespace App\Repository;

use App\Entity\Carrera;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Carrera>
 */
class CarreraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Carrera::class);
    }

    public function search(string $query): array {
        $qb =  $this->createQueryBuilder('c')
            ->where('c.nombre LIKE :nombre')
            ->setParameter('nombre', "%".$query."%");
        if (ctype_digit($query)){
            $qb->orWhere('c.nroOrdenanza = :ord')
                ->setParameter('ord', (int) $query)
                ->orWhere('c.nroImplementacion = :imp')
                ->setParameter('imp', (int) $query);
        }

        return $qb->getQuery()->getResult();
    }
    //    /**
    //     * @return Carrera[] Returns an array of Carrera objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Carrera
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
