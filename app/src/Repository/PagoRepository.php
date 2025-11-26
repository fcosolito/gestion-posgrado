<?php

namespace App\Repository;

use App\Entity\Pago;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pago>
 */
class PagoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pago::class);
    }

    public function findWithRelations(int $id): ?Pago
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.comprobante', 'c')
            ->leftJoin('p.pagoCuotas', 'pc')
            ->leftJoin('pc.cuota', 'cu')
            ->leftJoin('cu.inscripcionCarrera', 'ic')
            ->leftJoin('cu.inscripcionEdicion', 'ie')
            ->leftJoin('ic.alumno', 'ica')
            ->leftJoin('ie.alumno', 'iea')
            ->leftJoin('ic.carrera', 'icarr')
            ->leftJoin('ie.edicion', 'ied')
            ->leftJoin('ied.curso', 'iedc')
            ->addSelect('c', 'pc', 'cu', 'ic', 'ie', 'ica', 'iea', 'icarr', 'ied', 'iedc')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
        public function findAllWithRelations()
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.comprobante', 'c')
            ->leftJoin('p.pagoCuotas', 'pc')
            ->leftJoin('pc.cuota', 'cu')
            ->addSelect('c', 'pc', 'cu')
            ->orderBy('p.fechaPago', 'DESC')   // <-- agregar ordenamiento por fecha descendente
            ->getQuery()
            ->getResult();
    }

    public function search(array $data): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.comprobante', 'comprobante')
            ->leftJoin('p.pagoCuotas', 'pagoCuotas')
            ->leftJoin('pagoCuotas.cuota', 'cuota')
            ->leftJoin('cuota.inscripcionCarrera', 'inscripcionCarrera')
            ->leftJoin('cuota.inscripcionEdicion', 'inscripcionEdicion')
            ->leftJoin('inscripcionCarrera.alumno', 'alumnoCarrera')
            ->leftJoin('inscripcionEdicion.alumno', 'alumnoEdicion')
            ->leftJoin('inscripcionCarrera.carrera', 'carrera')
            ->leftJoin('inscripcionEdicion.edicion', 'edicion')
            ->leftJoin('edicion.curso', 'curso')
            ->addSelect('comprobante', 'pagoCuotas', 'cuota', 'inscripcionCarrera', 
                    'inscripcionEdicion', 'alumnoCarrera', 'alumnoEdicion', 
                    'carrera', 'edicion', 'curso');

        if (!empty($data['alumno'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('alumnoCarrera.nombre', ':alumno'),
                $qb->expr()->like('alumnoCarrera.apellido', ':alumno'),
                $qb->expr()->like('alumnoEdicion.nombre', ':alumno'),
                $qb->expr()->like('alumnoEdicion.apellido', ':alumno')
            ))
            ->setParameter('alumno', '%' . $data['alumno'] . '%');
        }

        if (!empty($data['monto_min'])) {
            $qb->andWhere('p.monto >= :monto_min')
            ->setParameter('monto_min', $data['monto_min']);
        }

        if (!empty($data['monto_max'])) {
            $qb->andWhere('p.monto <= :monto_max')
            ->setParameter('monto_max', $data['monto_max']);
        }

        if (!empty($data['fecha_desde'])) {
            $qb->andWhere('p.fechaPago >= :fecha_desde')
            ->setParameter('fecha_desde', $data['fecha_desde']);
        }

        if (!empty($data['fecha_hasta'])) {
            $qb->andWhere('p.fechaPago <= :fecha_hasta')
            ->setParameter('fecha_hasta', $data['fecha_hasta']);
        }

        return $qb
            ->orderBy('p.fechaPago', 'DESC')
            ->getQuery()
            ->getResult();
    }
        


    //    /**
    //     * @return Pago[] Returns an array of Pago objects
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

    //    public function findOneBySomeField($value): ?Pago
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
