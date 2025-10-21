<?php

namespace App\Repository;

use App\Entity\Curso;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Curso>
 */
class CursoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Curso::class);
    }

    public function search($criteria): array
    {
        $qb = $this->createQueryBuilder('a');

        if (!empty($criteria['nombre'])) {
            $qb->andWhere('a.nombre LIKE :nom')
                ->setParameter('nom', "%".$criteria['nombre']."%");
        }
        if (!empty($criteria['horas'])) {
            $qb->andWhere('a.horas = :horas')
                ->setParameter('horas', $criteria['horas']);
        }
        if (!empty($criteria['implementacion'])) {
            $qb->andWhere('a.nroImplementacion = :implementacion')
                ->setParameter('implementacion', $criteria['implementacion']);
        }
        if (!empty($criteria['ordenanza'])) {
            $qb->andWhere('a.nroOrdenanza = :ordenanza')
                ->setParameter('ordenanza', $criteria['ordenanza']);
        }

        return $qb->getQuery()->getResult();
    }
    public function findNotInIds(array $ids): array
    {
        $qb = $this->createQueryBuilder('c');
        if (empty($ids)) {
            return $qb->getQuery()->getResult();
        }
        return $qb
            ->andWhere('c.id NOT IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Curso[] Returns an array of Curso objects
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

    //    public function findOneBySomeField($value): ?Curso
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
