<?php

namespace App\Service;

use App\Entity\Curso;
use App\Entity\Edicion;
use App\Entity\PerteneceA;
use Doctrine\ORM\EntityManagerInterface;

class CursoService
{
    public function __construct(private EntityManagerInterface $em, private EdicionService $edicionService) {}

    public function delete(Curso $curso)
    {
        $perteneceARepository = $this->em->getRepository(PerteneceA::class);
        $edicionRepository = $this->em->getRepository(Edicion::class);

        // eliminar PerteneceA
        $perteneceAs = $perteneceARepository->findBy(["curso" => $curso]);
        foreach ($perteneceAs as $pa) {
            $this->em->remove($pa);
        }

        // eliminar ediciones
        $ediciones = $edicionRepository->findBy(["curso" => $curso]);
        foreach ($ediciones as $edicion) {
            $this->edicionService->delete($edicion);
        }

        // eliminar el curso
        $this->em->remove($curso);
        $this->em->flush();

    }
}