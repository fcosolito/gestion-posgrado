<?php

namespace App\Repository;

use App\Entity\Edicion;
use App\Entity\InscripcionEdicion;
use App\Entity\Nota;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InscripcionEdicion>
 */
class InscripcionEdicionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InscripcionEdicion::class);
    }
    public function findCursosByAlumnoId(int $alumnoId): array
    {
        // Traigo las inscripciones con sus ediciones y cursos asociados
        $inscripciones = $this->createQueryBuilder('ie')
            ->leftJoin('ie.edicion', 'e')
            ->addSelect('e')
            ->leftJoin('e.curso', 'c')
            ->addSelect('c')
            ->andWhere('ie.alumno = :alumnoId')
            ->setParameter('alumnoId', $alumnoId)
            ->getQuery()
            ->getResult();

        // Mapeo a un array de entidades Curso
        $cursos = [];
        foreach ($inscripciones as $inscripcion) {
            $edicion = $inscripcion->getEdicion();
            if ($edicion && $edicion->getCurso()) {
                $cursos[] = $edicion->getCurso();
            }
        }

        return $cursos;
    }
    //    /**
    //     * @return InscripcionEdicion[] Returns an array of InscripcionEdicion objects
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

    //    public function findOneBySomeField($value): ?InscripcionEdicion
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
