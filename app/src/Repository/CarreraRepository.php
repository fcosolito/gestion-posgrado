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

    public function search(array $criteria): array
    {
        $qb = $this->createQueryBuilder('c');

        if (!empty($criteria['nombre'])) {
            $qb->andWhere('c.nombre LIKE :nombre')
                ->setParameter('nombre', '%' . $criteria['nombre'] . '%');
        }
        
        if (!empty($criteria['nroImplementacion'])) {
            $qb->andWhere('c.nroImplementacion = :nroImplementacion')
                ->setParameter('nroImplementacion', $criteria['nroImplementacion']);
        }
        
        if (!empty($criteria['nroOrdenanza'])) {
            $qb->andWhere('c.nroOrdenanza = :nroOrdenanza')
                ->setParameter('nroOrdenanza', $criteria['nroOrdenanza']);
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
