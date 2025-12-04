<?php

namespace App\Service;

use App\Entity\Alumno;
use App\Entity\Carrera;
use App\Entity\Curso;
use App\Entity\Edicion;
use App\Entity\Cuota;
use App\Repository\AlumnoRepository;
use App\Repository\CarreraRepository;
use App\Repository\CursoRepository;
use App\Repository\EdicionRepository;
use App\Repository\InscripcionEdicionRepository;
use App\Repository\CuotaRepository;
use App\Repository\PagoCuotaRepository;
use App\Repository\PrecioCarreraRepository;
use Doctrine\ORM\EntityManagerInterface;

final class CuotaService
{
    public function __construct(
        private CarreraRepository $carreraR,
        private CursoRepository $cursoR,
        private EdicionRepository $edicionR,
        private AlumnoRepository $alumnoR,
        private CuotaRepository $cuotaR,
        private PagoCuotaRepository $pagoCuotaR,
        private PrecioCarreraRepository $precioCarreraR,
        private CalculadorCuota $calculadorCuota,
        private InscripcionEdicionRepository $inscEdicionR,
        private EntityManagerInterface $em,
    ) {}

    /**
     * Punto principal que encapsula todo el procesado del index
     */
    public function obtenerDatosIndex(array $query): array
    {
        $carrera = null;
        $curso = null;
        $edicion = null;
        $alumno = null;
        $cuotas = null;

        // Parte en donde se aplican los filtros
        if (isset($query["carrera"])) {
            $carrera = $this->carreraR->find($query["carrera"]);
            if ($carrera) {
                $cuotas = $this->cuotaR->findByCarrera($carrera);
            }
        }

        if (isset($query["curso"])) {
            $curso = $this->cursoR->find($query["curso"]);
            if ($curso) {
                if (!$edicion) {
                    $cuotas = $this->cuotaR->findByCurso($curso);
                }
            }
        }

        if (isset($query["edicion"])) {
            $edicion = $this->edicionR->find($query["edicion"]);
            if ($edicion) {
                $cuotas = $this->cuotaR->findByEdicion($edicion);
            }
        }

        if (isset($query["alumno"])) {
            $alumno = $this->alumnoR->find($query["alumno"]);
            if ($alumno) {
                $cuotasAlumno = $this->cuotaR->findByAlumno($alumno);

                if ($cuotas) {
                    $cuotas = $this->intersectCuotas($cuotas, $cuotasAlumno);
                } else {
                    $cuotas = $cuotasAlumno;
                }
            }
        }

        // esto es para traer todas en caso de que no se ingrese ningún filtro
        $cuotas = $cuotas ?? $this->cuotaR->findAll();

        // Serialización final
        return [
            "cuotas"  => array_map(fn($c) => $this->serializarCuota($c), $cuotas),
            "carrera" => $carrera ? $this->serializarCarrera($carrera) : null,
            "curso"   => $curso   ? $this->serializarCurso($curso) : null,
            "edicion" => $edicion ? $this->serializarEdicion($edicion) : null,
            "alumno"  => $alumno  ? $this->serializarAlumno($alumno) : null,
        ];
    }

    // Se hace la intersección
    private function intersectCuotas(array $c1, array $c2): array
    {
        $mapId = fn($arr) => array_reduce($arr, function ($acc, Cuota $el) {
            $acc[$el->getId()] = $el;
            return $acc;
        }, []);

        return array_values(
            array_intersect_key($mapId($c1), $mapId($c2))
        );
    }

    // Métodos de serializar para el obtener datos de cuotas. 
    private function serializarCarrera(Carrera $carrera): array
    {
        return [
            "id" => $carrera->getId(),
            "nombre" => $carrera->getNombre(),
            "nroOrdenanza" => $carrera->getNroOrdenanza(),
            "nroImplementacion" => $carrera->getNroImplementacion(),
        ];
    }

    private function serializarCurso(Curso $curso): array
    {
        return [
            "id" => $curso->getId(),
            "nombre" => $curso->getNombre(),
            "nroOrdenanza" => $curso->getNroOrdenanza(),
            "nroImplementacion" => $curso->getNroImplementacion(),
            "horas" => $curso->getHoras(),
        ];
    }

    private function serializarEdicion(Edicion $edicion): array
    {
        return [
            "id" => $edicion->getId(),
            "nombre" => $edicion->getNombre(),
            "fechaInicio" => $edicion->getFechaInicio()->format("Y-m-d"),
            "fechaFin" => $edicion->getFechaFin()->format("Y-m-d"),
            "precio" => $edicion->getPrecio(),
        ];
    }

    private function serializarAlumno(Alumno $alumno): array
    {
        return [
            "id" => $alumno->getId(),
            "nombre" => $alumno->getNombre(),
            "apellido" => $alumno->getApellido(),
            "email" => $alumno->getEmail(),
            "dni" => $alumno->getDni(),
        ];
    }

    private function serializarCuota(Cuota $cuota): array
    {
        $pagoCuotas = $this->pagoCuotaR->findBy(["cuota" => $cuota]);

        $pagos = [];
        $montoTotal = 0;

        foreach ($pagoCuotas as $pc) {
            $montoAsociado = $pc->getMontoCuota();
            $montoTotal += $montoAsociado;

            $pagos[] = [
                "id" => $pc->getPago()->getId(),
                "fechaPago" => $pc->getPago()->getFechaPago()->format("Y-m-d"),
                "monto" => $pc->getPago()->getMonto(),
                "montoAsociado" => $montoAsociado,
                "comprobanteArchivo" => $pc->getPago()->getComprobante()?->getArchivo(),
                "comprobanteId" => $pc->getPago()->getComprobante()?->getId(),
            ];
        }

        $inscCarrera = $cuota->getInscripcionCarrera();
        $inscEdicion = $cuota->getInscripcionEdicion();

        return [
            "id" => $cuota->getId(),
            "numeroCuota" => $cuota->getNumeroCuota(),
            "pagos" => $pagos,
            "montoTotalAsociado" => $montoTotal,
            "valor" => $this->calculadorCuota->calcularValor($cuota),
            "estado" => $this->calculadorCuota->calcularEstado($cuota),
            "inscripcionCarrera" => $inscCarrera ? [
                "carreraNombre" => $inscCarrera->getCarrera()->getNombre(),
                "carreraId" => $inscCarrera->getCarrera()->getId(),
                "nroLegajo" => $inscCarrera->getNroLegajo(),
                "carreraNroImplementacion" => $inscCarrera->getCarrera()->getNroImplementacion(),
                "carreraNroOrdenanza" => $inscCarrera->getCarrera()->getNroOrdenanza(),
                "alumnoNombre" => $inscCarrera->getAlumno()->getNombre(),
                "alumnoApellido" => $inscCarrera->getAlumno()->getApellido(),
                "alumnoId" => $inscCarrera->getAlumno()->getId(),
                "alumnoDni" => $inscCarrera->getAlumno()->getDni(),
            ] : null,
            "inscripcionEdicion" => $inscEdicion ? [
                "edicionNombre" => $inscEdicion->getEdicion()->getNombre(),
                "edicionId" => $inscEdicion->getEdicion()->getId(),
                "nroLegajo" => $inscEdicion->getNroLegajo(),
                "edicionFechaInicio" => $inscEdicion->getEdicion()->getFechaInicio()->format("Y-m-d"),
                "edicionFechaFin" => $inscEdicion->getEdicion()->getFechaFin()->format("Y-m-d"),
                "alumnoNombre" => $inscEdicion->getAlumno()->getNombre(),
                "alumnoApellido" => $inscEdicion->getAlumno()->getApellido(),
                "alumnoId" => $inscEdicion->getAlumno()->getId(),
                "alumnoDni" => $inscEdicion->getAlumno()->getDni(),
            ] : null,
            "descuento" => $inscCarrera
                ? ($inscCarrera->getDescuento()?->getValor())
                : ($inscEdicion->getDescuento()?->getValor()),
        ];
    }


    // Método que se usa en el new de CuotaController
    public function create(Cuota $cuota): void
    {
        try {
            $this->em->persist($cuota);
            $this->em->flush();
        } catch (\Throwable $e) {
            throw new \RuntimeException("No se pudo crear la cuota.");
        }
    }

    public function update(Cuota $cuota): void
    {
        try {
            $this->em->flush();
        } catch (\Throwable $e) {
            throw new \RuntimeException("No se pudo actualizar la cuota.");
        }
    }

    public function delete(Cuota $cuota): void
    {
        try {
            $this->em->remove($cuota);
            $this->em->flush();
        } catch (\Throwable $e) {
            throw new \RuntimeException("No se pudo eliminar la cuota.");
        }
    }
}
