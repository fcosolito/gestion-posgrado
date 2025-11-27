<?php

namespace App\Controller;

use App\Entity\Carrera;
use App\Entity\Cuota;
use App\Entity\InscripcionEdicion;
use App\Entity\PagoCuota;
use App\Form\CuotaType;
use App\Repository\AlumnoRepository;
use App\Repository\CarreraRepository;
use App\Repository\CuotaRepository;
use App\Repository\CursoRepository;
use App\Repository\EdicionRepository;
use App\Repository\InscripcionEdicionRepository;
use App\Repository\PagoCuotaRepository;
use App\Repository\PrecioCarreraRepository;
use App\Service\CalculadorCuota;
use App\Service\CalculadorEstadoCuota;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cuota')]
final class CuotaController extends AbstractController
{
    #[Route(name: 'app_cuota_index', methods: ['GET'])]
    public function index(Request $request, CuotaRepository $cuotaR, 
        CarreraRepository $carreraR, CursoRepository $cursoR,
        EdicionRepository $edicionR, AlumnoRepository $alumnoR,
        PagoCuotaRepository $pagoCuotaR, PrecioCarreraRepository $precioCarreraR,
        CalculadorCuota $calculadorCuota, InscripcionEdicionRepository $inscEdicionR
        ): Response
    {
        $carrera = null;
        $curso = null;
        $edicion = null;
        $alumno = null;
        $cuotas = null;

        if ($request->query->has("carrera")) {
            $carrera = $carreraR->find($request->query->get("carrera"));
            if ($carrera) {
                $carrera_ser = [
                    "id" => $carrera->getId(),
                    "nombre" => $carrera->getNombre(),
                    "nroOrdenanza" => $carrera->getNroOrdenanza(),
                    "nroImplementacion" => $carrera->getNroImplementacion(),
                ];
                $cuotas = $cuotaR->findByCarrera($carrera);
            }
        }
        if ($request->query->has("curso")) {
            $curso = $cursoR->find($request->query->get("curso"));
            if ($curso) {
                $curso_ser = [
                    "id" => $curso->getId(),
                    "nombre" => $curso->getNombre(),
                    "nroOrdenanza" => $curso->getNroOrdenanza(),
                    "nroImplementacion" => $curso->getNroImplementacion(),
                    "horas" => $curso->getHoras(),
                ];
                if (!$request->query->has("edicion")) {
                    $cuotas = $cuotaR->findByCurso($curso);
                }
            }
        }
        if ($request->query->has("edicion")) {
            $edicion = $edicionR->find($request->query->get("edicion"));
            if ($edicion) {
                $edicion_ser = [
                    "id" => $edicion->getId(),
                    "nombre" => $edicion->getNombre(),
                    "fechaInicio" => $edicion->getFechaInicio()->format("Y-m-d"),
                    "fechaFin" => $edicion->getFechaFin()->format("Y-m-d"),
                    "precio" => $edicion->getPrecio(),
                ];
                $cuotas = $cuotaR->findByEdicion($edicion);
            }
        }

        // Para hacer la interseccion de conjuntos de cuotas 
        function cuota_intersect ($cuotas1, $cuotas2) {
            function map_to_id ($array) {
                $mapped_array = [];
                foreach ($array as $el) {
                    $mapped_array[$el->getId()] = $el; 
                }
                return $mapped_array;
            }
            $intersection = array_intersect_key(
                map_to_id($cuotas1),
                map_to_id($cuotas2)
            );

            $result = [];
            foreach ($intersection as $key => $value) {
                $result[] = $value;
            }

            return $result;
        }

        if ($request->query->has("alumno")) {
            $alumno = $alumnoR->find($request->query->get("alumno"));
            if ($alumno) {
                $alumno_ser = [
                    "id" => $alumno->getId(),
                    "nombre" => $alumno->getNombre(),
                    "apellido" => $alumno->getApellido(),
                    "email" => $alumno->getEmail(),
                    "dni" => $alumno->getDni(),
                ];
                $cuotas_alumno = $cuotaR->findByAlumno($alumno);

                // Mostrar cuotas previamente filtradas que coinciden con el alumno
                if ($cuotas) {
                    $cuotas = cuota_intersect($cuotas, $cuotas_alumno);
                } else {
                    $cuotas = $cuotas_alumno;
                }
            }
        }
        $cuotas = $cuotas ?? $cuotaR->findAll();

        // Serializar cuotas con pagos, alumno y carrera o edicion
        $cuotas_ser = array_map(
            function (Cuota $cuota) use ($pagoCuotaR, $calculadorCuota) {
                $pagoCuotas = $pagoCuotaR->findBy(["cuota" => $cuota]);
                $pagos_ser = [];
                $montoTotal = 0;
                foreach ($pagoCuotas as $pagoCuota) {
                    $montoAsociado = $pagoCuota->getMontoCuota();
                    $montoTotal += $montoAsociado;

                    $pagos_ser[] = [
                            "id" => $pagoCuota->getPago()->getId(),
                            "fechaPago" => $pagoCuota->getPago()->getFechaPago()->format("Y-m-d"),
                            "monto" => $pagoCuota->getPago()->getMonto(),
                            "montoAsociado" => $montoAsociado,
                            "comprobanteArchivo" => $pagoCuota->getPago()->getComprobante()->getArchivo(),
                            "comprobanteId" => $pagoCuota->getPago()->getComprobante()->getId(),
                        ];
                }

                $inscCarrera = $cuota->getInscripcionCarrera();
                $inscEdicion = $cuota->getInscripcionEdicion();

                return [
                    "id" => $cuota->getId(),
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
                    "numeroCuota" => $cuota->getNumeroCuota(),
                    "pagos" => $pagos_ser,
                    "montoTotalAsociado" => $montoTotal,
                    "valor" => $calculadorCuota->calcularValor($cuota),
                    "estado" => $calculadorCuota->calcularEstado($cuota),
                    "descuento" => $inscCarrera ? ($inscCarrera->getDescuento() ? $inscCarrera->getDescuento()->getValor() : null) : (
                        $inscEdicion->getDescuento() ? $inscEdicion->getDescuento()->getValor() : null),
                ];

            }, $cuotas
        );

        return $this->render('cuota/index.html.twig', [
            'cuotas' => $cuotas_ser,
            'carrera' => $carrera_ser ?? null,
            'curso' => $curso_ser ?? null,
            'edicion' => $edicion_ser ?? null,
            'alumno' => $alumno_ser ?? null,
        ]);
    }

    #[Route('/new', name: 'app_cuota_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cuotum = new Cuota();
        $form = $this->createForm(CuotaType::class, $cuotum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cuotum);
            $entityManager->flush();

            return $this->redirectToRoute('app_cuota_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cuota/new.html.twig', [
            'cuotum' => $cuotum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cuota_show', methods: ['GET'])]
    public function show(Cuota $cuotum): Response
    {
        return $this->render('cuota/show.html.twig', [
            'cuotum' => $cuotum,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cuota_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cuota $cuotum, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CuotaType::class, $cuotum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_cuota_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cuota/edit.html.twig', [
            'cuotum' => $cuotum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cuota_delete', methods: ['POST'])]
    public function delete(Request $request, Cuota $cuotum, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cuotum->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cuotum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cuota_index', [], Response::HTTP_SEE_OTHER);
    }
}
