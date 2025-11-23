<?php

namespace App\Repository;

use App\Entity\Cuota;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cuota>
 */
class CuotaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cuota::class);
    }
    public function findCuotasPendientesByAlumno(int $alumnoId): array
        {
            try {
                // Consulta que NO usa pagoCuotas
                $qb = $this->createQueryBuilder('c');
                
                // Subquery para encontrar cuotas que SÍ tienen pagos
                $subQuery = $this->getEntityManager()->createQueryBuilder()
                    ->select('IDENTITY(pc.cuota)')
                    ->from('App\Entity\PagoCuota', 'pc')
                    ->getQuery()
                    ->getDQL();
                
                // Cuotas del alumno que NO están en la subquery de cuotas pagadas
                $result = $qb
                    ->leftJoin('c.inscripcionCarrera', 'ic')
                    ->leftJoin('c.inscripcionEdicion', 'ie')
                    ->leftJoin('ic.alumno', 'ica')
                    ->leftJoin('ie.alumno', 'iea')
                    ->where('(ic IS NOT NULL AND ica.id = :alumnoId) OR (ie IS NOT NULL AND iea.id = :alumnoId)')
                    ->andWhere($qb->expr()->notIn('c.id', $subQuery)) // Cuotas que NO tienen pago
                    ->setParameter('alumnoId', $alumnoId)
                    ->orderBy('c.numeroCuota', 'ASC')
                    ->getQuery()
                    ->getResult();

                return $result;

            } catch (\Exception $e) {
                error_log("ERROR en Repository: " . $e->getMessage());
                return [];
            }
        }


    //    /**
    //     * @return Cuota[] Returns an array of Cuota objects
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

    //    public function findOneBySomeField($value): ?Cuota
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
