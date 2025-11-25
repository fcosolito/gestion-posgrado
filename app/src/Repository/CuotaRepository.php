<?php

namespace App\Repository;

use App\Entity\Alumno;
use App\Entity\Carrera;
use App\Entity\Cuota;
use App\Entity\Curso;
use App\Entity\Edicion;
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
    
    public function findByAlumno(Alumno $alumno): array
    {
        return $this->createQueryBuilder("c")
            ->leftJoin('c.inscripcionCarrera', 'ic')
            ->leftJoin('c.inscripcionEdicion', 'ie')
            ->leftJoin('ic.alumno', 'ica')
            ->leftJoin('ie.alumno', 'iea')
            ->where('(ic IS NOT NULL AND ica.id = :alumnoId) OR (ie IS NOT NULL AND iea.id = :alumnoId)')
            ->setParameter('alumnoId', $alumno->getId())
            ->getQuery()
            ->getResult();
    }

    public function findByCurso(Curso $curso): array
    {
        return $this->createQueryBuilder("c")
            ->leftJoin("c.inscripcionEdicion", "ie")
            ->where("ie IS NOT NULL")
            ->leftJoin("ie.edicion", "ed")
            ->leftJoin("ed.curso", "cu")
            ->andWhere("cu.id = :cursoId")
            ->setParameter("cursoId", $curso->getId())
            ->getQuery()
            ->getResult();
    }

    public function findByEdicion(Edicion $edicion): array
    {
        return $this->createQueryBuilder("c")
            ->leftJoin("c.inscripcionEdicion", "ie")
            ->where("ie IS NOT NULL")
            ->leftJoin("ie.edicion", "ed")
            ->andWhere("ed.id = :edicionId")
            ->setParameter("edicionId", $edicion->getId())
            ->getQuery()
            ->getResult();
    }

    public function findByCarrera(Carrera $carrera): array
    {
        return $this->createQueryBuilder("c")
            ->leftJoin("c.inscripcionCarrera", "ic")
            ->where("ic IS NOT NULL")
            ->leftJoin("ic.carrera", "ca")
            ->andWhere("ca.id = :carreraId")
            ->setParameter("carreraId", $carrera->getId())
            ->getQuery()
            ->getResult();
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
