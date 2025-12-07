<?php

namespace App\Service;

use App\Entity\Dicta;
use App\Entity\Docente;
use Doctrine\ORM\EntityManagerInterface;

class DocenteService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function delete(Docente $docente)
    {
        // eliminar Dictas
        $dictas = $this->em->getRepository(Dicta::class)->findBy(["docente" => $docente]);
        foreach ($dictas as $dicta) {
            $this->em->remove($dicta);
        }

        $this->em->remove($docente);
        $this->em->flush();
    }
}