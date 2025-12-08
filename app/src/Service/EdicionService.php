<?php 

namespace App\Service;

use App\Entity\Alumno;
use App\Entity\Cuota;
use App\Entity\Curso;
use App\Entity\Descuento;
use App\Entity\Dicta;
use App\Entity\Docente;
use App\Entity\Edicion;
use App\Entity\InscripcionEdicion;
use App\Entity\Nota;
use App\Entity\PagoCuota;
use App\Repository\EdicionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class EdicionService 
{
    public function __construct(private EntityManagerInterface $em, private CuotaService $cuotaService) {}

    public function new(Edicion $edicion, Curso $curso): array
    {
        try {
            $edicion->setCurso($curso);
            $this->em->persist($edicion);
            $this->em->flush();

            return [ "estado" => "exito", "edicionId" => $edicion->getId()];
        } catch (Exception $e) {
            return [ "estado" => "error", "exception" => $e->getMessage()];
        }
    }

    public function update(Edicion $edicion, $data): array
    {
        try {
            if (isset($data['nombre'])) $edicion->setNombre($data['nombre']);
            if (isset($data['fechaInicio'])) $edicion->setFechaInicio(new DateTime($data['fechaInicio']));
            if (isset($data['fechaFin']))   $edicion->setFechaFin(new DateTime($data['fechaFin']));
            if (isset($data['precio']))   $edicion->setPrecio($data['precio']);

            $this->em->flush();
            return ["estado" => "exito"];
        } catch (Exception $e) {
            return ["estado" => "error", "exception" => $e->getMessage()];
        }
    }

    public function delete(Edicion $edicion): array
    {
        try {
            // borrar la edicion requiere borrar todas las inscripciones y 'Dicta' asociados
            $inscripciones = $this->em->getRepository(InscripcionEdicion::class)->findBy(["edicion" => $edicion]);
            $dictas = $this->em->getRepository(Dicta::class)->findBy(["edicion" => $edicion]);

            // borrar inscripciones
            foreach ($inscripciones as $insc) {
                $cuotas = $this->em->getRepository(Cuota::class)->findBy(["inscripcionEdicion" => $insc]);
                // borrar cuotas
                foreach ($cuotas as $cuota) {
                    $pagoCuotas = $this->em->getRepository(PagoCuota::class)->findBy(["cuota" => $cuota]);
                    // borrar PagoCuotas
                    foreach ($pagoCuotas as $pc) {
                        $this->em->remove($pc);
                    }
                    $this->em->remove($cuota);
                }
                // borrar notas
                $notas = $this->em->getRepository(Nota::class)->findBy(["inscripcionEdicion" => $insc]);
                foreach ($notas as $nota) {
                    $this->em->remove($nota);
                }
                $this->em->remove($insc);
            }
            foreach ($dictas as $dicta) {
                $this->em->remove($dicta);
            }
            $this->em->remove($edicion);
            $this->em->flush();

            return ["estado" => "exito"];

        } catch (Exception $e) {
            return ["estado" => "error", "exception" => $e->getMessage()];
        }
    }

    public function asociarDocente(Edicion $edicion, Docente $docente, bool $firmante): array
    {
        try {
            $dictaRepository = $this->em->getRepository(Dicta::class);
            $dicta = $dictaRepository->findOneBy(["docente" => $docente, "edicion" => $edicion]) ?? new Dicta();
            $dicta->setDocente($docente);
            $dicta->setEdicion($edicion);
            $dicta->setEsFirmante($firmante);

            $this->em->persist($dicta);
            $this->em->flush();

            return ["estado" => "exito", "dicta" => $dicta];
        } catch (Exception $e) {
            return ["estado" => "error", "exception" => $e->getMessage()];
        }
    }

    public function desasociarDocente(Edicion $edicion, Docente $docente): array
    {
        try {
            $dictaRepository = $this->em->getRepository(Dicta::class);
            $dicta = $dictaRepository->findOneBy(["docente" => $docente, "edicion" => $edicion]) ?? new Dicta();

            $this->em->remove($dicta);
            $this->em->flush();

            return ["estado" => "exito", "dicta" => $dicta];
        } catch (Exception $e) {
            return ["estado" => "error", "exception" => $e->getMessage()];
        }
    }
    
    public function inscribirAlumno(
            Alumno $alumno,
            Edicion $edicion,
            $data
        ): array
    {
        $descuentoR = $this->em->getRepository(Descuento::class);
        $inscripcionR = $this->em->getRepository(InscripcionEdicion::class);

        $inscripcion = $inscripcionR->findOneBy(["edicion" => $edicion, "alumno" => $alumno]) ?? new InscripcionEdicion();

        // Obtener datos de la request
        $descuento = $data['descuento'] ? $descuentoR->find($data['descuento']) : null;
        $fechaInscripcion = $data['fechaInscripcion'] ? DateTime::createFromFormat("Y-m-d", $data['fechaInscripcion']) : new DateTime();
        $nroLegajo = $data['nroLegajo'] ? (int) $data['nroLegajo'] : null;
        
        $inscripcion->setAlumno($alumno);
        $inscripcion->setEdicion($edicion);
        $inscripcion->setDescuento($descuento);
        $inscripcion->setFechaInscripcion($fechaInscripcion);
            
        if ($nroLegajo) {
            if ($inscripcionR->findOneBy(["edicion" => $edicion, "nroLegajo" => $nroLegajo])) {
                return ["estado" => "error", "exception" => "El legajo ya existe en la edicion."];
            } else {
                $inscripcion->setNroLegajo($nroLegajo);
            }
        }
            
        // Crear una cuota asociada al alumno
        $cuotaInscripcion = new Cuota();
        $cuotaInscripcion->setInscripcionEdicion($inscripcion);
        $cuotaInscripcion->setNumeroCuota(1);
        $cuotaInscripcion->setEstado($this->cuotaService->calcularEstado($cuotaInscripcion));

        $this->em->persist($cuotaInscripcion);
        $this->em->persist($inscripcion);
        $this->em->flush();

        return ["estado" => "exito"];
    }

    public function desinscribirAlumno(
            Alumno $idAlumno,
            Edicion $edicion,
        ): array
    {
        $inscripcionR = $this->em->getRepository(InscripcionEdicion::class);
        $cuotaR = $this->em->getRepository(Cuota::class);

        $inscripcion = $inscripcionR->findOneBy(["edicion" => $edicion, "alumno" => $idAlumno]) ?? new InscripcionEdicion();
        $cuota = $inscripcion && $cuotaR->findOneBy(["inscripcionEdicion" => $inscripcion]) ?
            $cuotaR->findOneBy(["inscripcionEdicion" => $inscripcion]) : new Cuota();

        $this->em->remove($cuota);
        $this->em->remove($inscripcion);
        $this->em->flush();

        return ["estado" => "exito"];
    }

    public function editarInscripcion(
            InscripcionEdicion $inscripcion,
            Edicion $edicion,
            $data
        ): array
    {
        $descuentoR = $this->em->getRepository(Descuento::class);
        $inscripcionR = $this->em->getRepository(InscripcionEdicion::class);


        // Obtener datos de la request
        $descuento = $data['descuento'] ? $descuentoR->find($data['descuento']) : null;
        $fechaInscripcion = isset($data['fechaInscripcion']) ? DateTime::createFromFormat("Y-m-d", $data['fechaInscripcion']) : new DateTime();
        $nroLegajo = $data['nroLegajo'] ? (int) $data['nroLegajo'] : null;
        
        $inscripcion->setDescuento($descuento);
        $inscripcion->setFechaInscripcion($fechaInscripcion);
            
        if ($nroLegajo) {
            if ($inscripcionR->findOneBy(["edicion" => $edicion, "nroLegajo" => $nroLegajo])) {
                return ["estado" => "error", "exception" => "El legajo ya existe en la edicion."];
            } else {
                $inscripcion->setNroLegajo($nroLegajo);
            }
        }
            
        $this->em->persist($inscripcion);
        $this->em->flush();

        return ["estado" => "exito"];

    }
}