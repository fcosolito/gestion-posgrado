<?php

namespace App\Service;

use App\Entity\Descuento;
use App\Entity\InscripcionCarrera;
use App\Entity\InscripcionEdicion;
use Doctrine\ORM\EntityManagerInterface;

class DescuentoService
{
    public function __construct(private EntityManagerInterface $em){}

    public function delete(Descuento $descuento) 
    {
        $inscripcionesCarrera = $this->em->getRepository(InscripcionCarrera::class)->findBy(["descuento" => $descuento]);
        $inscripcionesEdicion = $this->em->getRepository(InscripcionEdicion::class)->findBy(["descuento" => $descuento]);

        // eliminar referencias de inscripciones al descuento
        foreach ($inscripcionesCarrera as $insc) {
            $insc->setDescuento(null);
        }
        foreach ($inscripcionesEdicion as $insc) {
            $insc->setDescuento(null);
        }

        $this->em->remove($descuento);
        $this->em->flush();
    }
}