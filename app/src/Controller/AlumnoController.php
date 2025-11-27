<?php

namespace App\Controller;

use App\Entity\Alumno;
use App\Entity\InscripcionEdicion;
use App\Entity\InscripcionCarrera;
use App\Entity\Carrera;
use App\Entity\Edicion;
use App\Entity\Cuota;
use App\Entity\DocumentacionNota;
use App\Entity\Descuento;
use App\Entity\PerteneceA;
use App\Form\AlumnoType;
use App\Entity\Nota;
use App\Form\NotaType;
use App\Repository\AlumnoRepository;
use App\Repository\CarreraRepository;
use App\Repository\CursoRepository;
use App\Repository\EdicionRepository;
use App\Repository\InscripcionCarreraRepository;
use App\Repository\InscripcionEdicionRepository;
use App\Service\CalculadorCuota;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;

#[Route('/alumno')]
final class AlumnoController extends AbstractController
{
    #[Route(name: 'app_alumno_index', methods: ['GET', 'POST'])]
    public function index(Request $request, AlumnoRepository $alumnoRepository, EntityManagerInterface $entityManager): Response
    {
        // Crear un formulario sin entidad (solo para búsqueda)
        $formBuscar = $this->createFormBuilder(null, ['method' => 'GET'])
            ->add('nombre', null, ['required' => false])
            ->add('apellido', null, ['required' => false])
            ->add('dni', null, ['required' => false])
            ->add('email', null, ['required' => false])
            ->getForm();
        
        $formBuscar->handleRequest($request);

        // Obtener alumnos según los filtros
        $alumnos = $alumnoRepository->findAll(); // Por defecto, todos
        
        if ($formBuscar->isSubmitted() && $formBuscar->isValid()) {
            $data = $formBuscar->getData();
            
            // Crear query builder para búsqueda dinámica
            $qb = $alumnoRepository->createQueryBuilder('a');
            
            if (!empty($data['nombre'])) {
                $qb->andWhere('a.nombre LIKE :nombre')
                   ->setParameter('nombre', '%' . $data['nombre'] . '%');
            }
            if (!empty($data['apellido'])) {
                $qb->andWhere('a.apellido LIKE :apellido')
                   ->setParameter('apellido', '%' . $data['apellido'] . '%');
            }
            if (!empty($data['dni'])) {
                $qb->andWhere('a.dni LIKE :dni')
                   ->setParameter('dni', '%' . $data['dni'] . '%');
            }
            if (!empty($data['email'])) {
                $qb->andWhere('a.email LIKE :email')
                   ->setParameter('email', '%' . $data['email'] . '%');
            }
            
            $alumnos = $qb->getQuery()->getResult();
        }

        // Serializar los alumnos a un array simple
        $alumnosData = [];
        foreach ($alumnos as $alumno) {
            $alumnosData[] = [
                'id' => $alumno->getId(),
                'nombre' => $alumno->getNombre(),
                'apellido' => $alumno->getApellido(),
                'dni' => $alumno->getDni(),
                'email' => $alumno->getEmail(),
            ];
        }

        $alumno = new Alumno();
        $formCrear = $this->createForm(AlumnoType::class, $alumno);
        $formCrear->handleRequest($request);

        if ($formCrear->isSubmitted() && $formCrear->isValid()) {
            $entityManager->persist($alumno);
            $entityManager->flush();

            $this->addFlash('notice', 'Alumno creado exitosamente');

            return $this->redirectToRoute('app_alumno_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alumno/index.html.twig', [
            'alumnos' => $alumnosData,
            'formBuscar' => $formBuscar,
            'alumno' => $alumno,
            'formCrear' => $formCrear,
        ]);
    }

    #[Route('/new', name: 'app_alumno_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $alumno = new Alumno();
        $form = $this->createForm(AlumnoType::class, $alumno);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($alumno);
            $entityManager->flush();

            $this->addFlash('notice', "Alumno guardado exitosamente");

            return $this->redirectToRoute('app_alumno_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alumno/new.html.twig', [
            'alumno' => $alumno,
            'form' => $form,
        ]);
    }

    #[Route('/search', name: 'app_alumno_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $entityManager): Response
    {
        $query =  $request->query->get("query", "");
        $alumnos = $entityManager->getRepository(Alumno::class)->searchXor($query);
        $alumnos_ser = array_map(
            function (Alumno $a) {
                return (
                    [
                        "id" => $a->getId(),
                        "nombre" => $a->getNombre(),
                        "apellido" => $a->getApellido(),
                        "dni" => $a->getDni(),
                        "email" => $a->getEmail(),
                    ]
                    );
            },
            $alumnos
        );

        return $this->json($alumnos_ser);
    }

    #[Route('/{id}', name: 'app_alumno_show', methods: ['GET'])]
    public function show(Alumno $alumno): Response
    {
        return $this->render('alumno/show.html.twig', [
            'alumno' => $alumno,
        ]);
    }

    
    #[Route('/{id}/edit', name: 'app_alumno_edit', methods: ['GET', 'POST'])]
    public function edit(
            Request $request,
            Alumno $alumno,
            EntityManagerInterface $entityManager,
            InscripcionCarreraRepository $inscripcionCarreraRepository,
            InscripcionEdicionRepository $inscripcionEdicionRepository,
            CarreraRepository $carreraRepository,
            CursoRepository $cursoRepository,
        ): Response
    {
        $form = $this->createForm(AlumnoType::class, $alumno);
        $form->handleRequest($request);

        $carreras = $inscripcionCarreraRepository -> findCarrerasByAlumnoId($alumno->getId());
        $cursos = $inscripcionEdicionRepository -> findCursosByAlumnoId($alumno->getId());
        $cuotasNoPagas = []; // esto debería rellenarlo
        $cuotasPagas = []; // esto debería rellenarlo

        // Buscamos las carreras y los cursos a los que el alumno no está inscripto para mostrar en las secciones de inscripciones.
        $carrerasIds = !empty($carreras) ? array_map(fn($c) => $c->getId(),$carreras) : []; 
        $cursosIds = !empty($cursos) ? array_map(fn($c) => $c->getId(),$cursos) : [];
        $carrerasNoInscripto = $carreraRepository->findNotInIds($carrerasIds);
        $cursosNoInscripto = $cursoRepository->findNotInIds($cursosIds);


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('notice', "Alumno guardado exitosamente");

            return $this->redirectToRoute('app_alumno_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alumno/edit.html.twig', [
            'alumno' => $alumno,
            'form' => $form,
            'carreras' => $carreras,
            'cursos' => $cursos,
            'cuotasNoPagas' => $cuotasNoPagas,
            'cuotasPagas' => $cuotasPagas,
            'carrerasNoInscripto' => $carrerasNoInscripto,
            'cursosNoInscripto' => $cursosNoInscripto,
        ]);
    }

    #[Route('/{id}', name: 'app_alumno_delete', methods: ['POST'])]
    public function delete(Request $request, Alumno $alumno, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$alumno->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($alumno);
            $entityManager->flush();
            
            $this->addFlash('notice', 'Alumno eliminado exitosamente');
        }

        return $this->redirectToRoute('app_alumno_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/inscribir-alumno-curso', name:'app_alumno_inscribir_curso', methods : ['POST'])]
    public function inscribirAlumnoACurso(
        Request $request,
        Alumno $alumno,
        EdicionRepository $edicionRepository,
        EntityManagerInterface $entityManager 
    ): Response
    {   
        $cursoId = $request->request->get('curso_id');
        
        // Buscar la última edición disponible
        $ultimaEdicion = $edicionRepository->findUltimaEdicionByCursoId($cursoId);
        
        // Verificar que existe una edición antes de crear la inscripción
        if (!$ultimaEdicion) {
            $this->addFlash(
                'error',
                '❌ No se pudo completar la inscripción. No hay ediciones disponibles para este curso.'
            );
            return $this->redirectToRoute('app_alumno_edit', ['id' => $alumno->getId()]);
        }
        
        // Crear la inscripción
        $inscripcionEdicion = new InscripcionEdicion();
        $inscripcionEdicion->setEdicion($ultimaEdicion);
        $inscripcionEdicion->setAlumno($alumno);
        $inscripcionEdicion->setDescuento(new Descuento());

        $entityManager->persist($inscripcionEdicion);
        $entityManager->flush();

        // Mostrar mensaje de confirmación
        if ($ultimaEdicion) {
            $this->addFlash(
                'success',
                sprintf(
                    '¡Inscripción exitosa!<br><br>
                    <strong>Curso:</strong> %s<br>
                    <strong>Edición:</strong> %s<br>
                    <strong>Fecha de inicio:</strong> %s<br>
                    <strong>Precio:</strong> $%s',
                    $ultimaEdicion->getCurso()->getNombre(),
                    $ultimaEdicion->getNombre(),
                    $ultimaEdicion->getFechaInicio()->format('d/m/Y'),
                    number_format($ultimaEdicion->getPrecio(), 2)
                )
            );
        } else {
            $this->addFlash(
                'warning',
                '¡Inscripción fallida!<br>No se encontró información detallada de la edición.'
            );
        }

        return $this->redirectToRoute('app_alumno_edit', [
            'id' => $alumno->getId(),
        ]);
    }

    #[Route('/{id}/visualizar', name: 'app_alumno_visualizar', methods: ['GET', 'POST'])]
    public function visualizar(Request $request, int $id, AlumnoRepository $alumnoRepository, EntityManagerInterface $entityManager, CalculadorCuota $calculadorCuota): Response
    {
        // Buscar manualmente el alumno
        $alumno = $alumnoRepository->find($id);
        
        // Verificar si existe
        if (!$alumno) {
            throw $this->createNotFoundException('Alumno no encontrado');
        }
        
        // Obtener las inscripciones a carreras del alumno
        $inscripcionesCarrera = $entityManager->createQueryBuilder()
            ->select('ic', 'c')
            ->from(\App\Entity\InscripcionCarrera::class, 'ic')
            ->innerJoin('ic.carrera', 'c')
            ->where('ic.alumno = :alumno')
            ->setParameter('alumno', $alumno)
            ->getQuery()
            ->getResult();

        // Obtener las inscripciones a ediciones del alumno
        $inscripcionesEdicion = $entityManager->createQueryBuilder()
            ->select('ie', 'e', 'c')
            ->from(\App\Entity\InscripcionEdicion::class, 'ie')
            ->innerJoin('ie.edicion', 'e')
            ->innerJoin('e.curso', 'c')
            ->where('ie.alumno = :alumno')
            ->setParameter('alumno', $alumno)
            ->getQuery()
            ->getResult();

        // Preparar datos de carreras para el template
        $carrerasData = [];
        foreach ($inscripcionesCarrera as $inscripcion) {
            $carrera = $inscripcion->getCarrera();
            $carrerasData[] = [
                'nombre' => $carrera->getNombre(),
                'nro_ordenanza' => $carrera->getNroOrdenanza(),
                'nro_implementacion' => $carrera->getNroImplementacion()
            ];
        }

        // Preparar datos de ediciones (cursos) para el template
        $cursosData = [];
        $fechaActual = new \DateTime();
        
        foreach ($inscripcionesEdicion as $inscripcion) {
            $edicion = $inscripcion->getEdicion();
            $curso = $edicion->getCurso();
            
            // Determinar el estado basado en las fechas
            $fechaInicio = $edicion->getFechaInicio();
            $fechaFin = $edicion->getFechaFin();
            
            if ($fechaActual < $fechaInicio) {
                $estado = 'Próximo';
            } elseif ($fechaFin && $fechaActual > $fechaFin) {
                $estado = 'Finalizado';
            } else {
                $estado = 'En curso';
            }
            
            $cursosData[] = [
                'curso' => $curso->getNombre(),
                'edicion' => $edicion->getNombre(),
                'estado' => $estado,
                'horas' => $curso->getHoras()
            ];
        }

        // Obtener todas las cuotas asociadas a las inscripciones del alumno
        $cuotasPagos = $entityManager->createQueryBuilder()
            ->select('cu', 'ic', 'ie', 'pc', 'p')
            ->from(\App\Entity\Cuota::class, 'cu')
            ->leftJoin('cu.inscripcionCarrera', 'ic')
            ->leftJoin('cu.inscripcionEdicion', 'ie')
            ->leftJoin(\App\Entity\PagoCuota::class, 'pc', 'WITH', 'pc.cuota = cu')
            ->leftJoin('pc.pago', 'p')
            ->where('ic.alumno = :alumno OR ie.alumno = :alumno')
            ->setParameter('alumno', $alumno)
            ->getQuery()
            ->getResult();

        $pagoCuotas = [];
        $cuotas = [];
        foreach ($cuotasPagos as $cuota) {
            if ($cuota instanceof \App\Entity\PagoCuota) {
                $pagoCuotas[] = $cuota;
            }
            if ($cuota instanceof \App\Entity\Cuota) {
                $cuotas[] = $cuota;
            }
        }

        // Preparar datos de cuotas para el template
        $cuotasData = [];
        foreach ($cuotas as $cuota) {
            // Factorice lo anterior en este metodo, comprobar si funciona igual
            $estadoPago = $calculadorCuota->calcularEstado($cuota);


            if ($cuota->getInscripcionCarrera() === null) {
                $carreraCurso = $cuota->getInscripcionEdicion()->getEdicion()->getNombre();
            } else {
                $carreraCurso = $cuota->getInscripcionCarrera()->getCarrera()->getNombre();
            }


            $cuotasData[] = [
                'carreraCurso' => $carreraCurso,
                'estado' => $estadoPago
            ];
        }
        
        $formEditar = $this->createForm(AlumnoType::class, $alumno);
        $formEditar->handleRequest($request);

        if ($formEditar->isSubmitted() && $formEditar->isValid()) {
            $entityManager->flush();

            $this->addFlash('notice', 'Alumno modificado exitosamente');

            return $this->redirectToRoute('app_alumno_visualizar', ['id' => $alumno->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alumno/_alumno_visualizar.html.twig', [
            'alumno' => $alumno,
            'formEditar' => $formEditar,
            'carreras' => $carrerasData,
            'cursos' => $cursosData,
            'cuotas' => $cuotasData,
        ]);
    }

    #[Route('/{id}/inscribir-carrera', name: 'app_alumno_inscribir_carrera_view', methods: ['GET', 'POST'])]
    public function inscribirCarreraView(Request $request, Alumno $alumno, EntityManagerInterface $entityManager): Response
    {
        // Traer todas las carreras de la base de datos
        $carreras = $entityManager->getRepository(Carrera::class)->findAll();

        // Se obtienen las inscripciones del alumno
        $inscripcionesCarrera = $entityManager->createQueryBuilder()
            ->select('ic', 'c', 'd')
            ->from(\App\Entity\InscripcionCarrera::class, 'ic')
            ->leftJoin('ic.carrera', 'c')
            ->leftJoin('ic.descuento', 'd')
            ->where('ic.alumno = :alumno')
            ->setParameter('alumno', $alumno)
            ->getQuery()
            ->getResult();
        
        // Obtener todas las cuotas del alumno (tengan o no pagos)
        $cuotas = $entityManager->createQueryBuilder()
            ->select('cu', 'ic', 'c')
            ->from(\App\Entity\Cuota::class, 'cu')
            ->leftJoin('cu.inscripcionCarrera', 'ic')
            ->leftJoin('ic.carrera', 'c')
            ->where('ic.alumno = :alumno')
            ->setParameter('alumno', $alumno)
            ->getQuery()
            ->getResult();

        // Obtener todos los pagos de cuotas del alumno
        $pagosCuotas = $entityManager->createQueryBuilder()
            ->select('pc', 'p', 'cu')
            ->from(\App\Entity\PagoCuota::class, 'pc')
            ->leftJoin('pc.pago', 'p')
            ->leftJoin('pc.cuota', 'cu')
            ->getQuery()
            ->getResult();

        // Crear mapa de pagos por cuota
        $pagosPorCuota = [];
        foreach ($pagosCuotas as $pagoCuota) {
            $cuotaId = $pagoCuota->getCuota()->getId();
            if (!isset($pagosPorCuota[$cuotaId])) {
                $pagosPorCuota[$cuotaId] = [];
            }
            $pagosPorCuota[$cuotaId][] = $pagoCuota;
        }

        // Crear mapas de inscripciones y cuotas por carrera
        $inscripcionesPorCarrera = [];
        $carrerasInscriptas = [];

        foreach ($inscripcionesCarrera as $inscripcion) {
            $carreraId = $inscripcion->getCarrera()->getId();
            $carrerasInscriptas[] = $carreraId;
            $inscripcionesPorCarrera[$carreraId] = $inscripcion;
        }

        // Agrupar cuotas por carrera y calcular estados
        $cuotasPorCarrera = [];
        
        foreach ($cuotas as $cuota) {
            $inscripcion = $cuota->getInscripcionCarrera();
            
            if ($inscripcion) {
                $carreraId = $inscripcion->getCarrera()->getId();
                $cuotaId = $cuota->getId();
                
                if (!isset($cuotasPorCarrera[$carreraId])) {
                    $cuotasPorCarrera[$carreraId] = [];
                }
                
                // Calcular montos de la cuota
                $montoPagado = 0;
                $montoCuota = 0;
                $contadorPagos = 0;
                
                if (isset($pagosPorCuota[$cuotaId])) {
                    foreach ($pagosPorCuota[$cuotaId] as $pagoCuota) {
                        $montoCuota = $pagoCuota->getMontoCuota();
                        $montoPagado += $pagoCuota->getPago()->getMonto();
                        $contadorPagos++;
                    }
                }
                
                // Determinar estado
                if ($contadorPagos === 0) {
                    $estado = 'Pendiente';
                } elseif ($montoPagado >= $montoCuota) {
                    $estado = 'Paga';
                } else {
                    $estado = 'Parcial';
                }
                
                $cuotasPorCarrera[$carreraId][] = [
                    'numero' => $cuota->getNumeroCuota(),
                    'monto' => $montoCuota,
                    'pagado' => $montoPagado,
                    'estado' => $estado
                ];
            }
        }

        // Preparar datos para todas las carreras
        $carrerasData = [];
        foreach ($carreras as $carrera) {
            $carreraId = $carrera->getId();

            // Verificar si el alumno está inscripto en esta carrera
            if (in_array($carreraId, $carrerasInscriptas)) {
                $estado = 'Inscripto';
                $accion = 'Borrar';
                
                // Obtener datos de la inscripción
                $inscripcion = $inscripcionesPorCarrera[$carreraId];
                $descuento = $inscripcion->getDescuento();
                
                // Extraer valores del descuento si existe
                $valorDescuento = $descuento ? $descuento->getValor() : 0;
                $descripcionDescuento = $descuento ? $descuento->getDescripcion() : '';
                
                // Extraer valores de la inscripción
                $legajo = $inscripcion->getNroLegajo() ?? 'Sin legajo';
                $fechaInscripcion = $inscripcion->getFechaInscripcion() ? $inscripcion->getFechaInscripcion()->format('Y-m-d') : null;
                
                // Obtener cuotas de esta carrera
                $cuotasInfo = $cuotasPorCarrera[$carreraId] ?? [];
            } else {
                $estado = 'No inscripto';
                $accion = 'Inscribir';
                $valorDescuento = 0;
                $descripcionDescuento = '';
                $legajo = null;
                $fechaInscripcion = null;
                $cuotasInfo = [];
            }

            $carrerasData[] = [
                'id' => $carreraId,
                'nombre' => $carrera->getNombre(),
                'ordenanza' => $carrera->getNroOrdenanza(),
                'implementacion' => $carrera->getNroImplementacion(),
                'legajo' => $legajo,
                'descuento' => $valorDescuento,
                'descripcion' => $descripcionDescuento,
                'fechaInscripcion' => $fechaInscripcion,
                'estado' => $estado,
                'accion' => $accion,
                'cuotas' => $cuotasInfo
            ];
        }

        // Ordenar carreras por estado (inscriptas primero)
        usort($carrerasData, function($a, $b) {
            if ($a['estado'] === 'Inscripto' && $b['estado'] !== 'Inscripto') {
                return -1;
            }
            if ($a['estado'] !== 'Inscripto' && $b['estado'] === 'Inscripto') {
                return 1;
            }
            return 0;
        });

        // Obtener descuentos para seleccionar a la hora de inscribir
        $descuentos = $entityManager->getRepository(Descuento::class)->findAll();

        return $this->render('alumno/inscribirCarrera.html.twig', [
            'alumno' => $alumno,
            'carrerasData' => $carrerasData,
            'descuentos' => $descuentos,
        ]);
    }

    #[Route('/{id}/inscribir-carrera/{carrera}', name: 'app_alumno_inscribir_carrera', methods: ['POST'])]
    public function inscribirCarrera(
            Request $request,
            Alumno $alumno,
            Carrera $carrera,
            EntityManagerInterface $entityManager
        ): Response
    {
        // Obtener datos del POST
        $valorDescuento = (int) $request->request->get('valorDescuento', 0);
        $fechaInscripcion = $request->request->get('fecha_inscripcion');
        $descripcionDescuento = $request->request->get('descripcionDescuento', '');
        $nroLegajo = $request->request->get('nroLegajo') ? (int) $request->request->get('nroLegajo') : null;
        
        // Verificar si ya existe la inscripción
        $inscripcionExistente = $entityManager->getRepository(InscripcionCarrera::class)
            ->findOneBy(['alumno' => $alumno, 'carrera' => $carrera]);

        if ($inscripcionExistente) {
            $this->addFlash('warning', 'El alumno ya está inscripto en esta carrera');
        } else {
            // Crear la nueva inscripción
            $inscripcion = new InscripcionCarrera();
            $inscripcion->setAlumno($alumno);
            $inscripcion->setCarrera($carrera);
            
            // Crear y asociar descuento
            $descuento = new Descuento();
            $descuento->setValor($valorDescuento);
            $descuento->setDescripcion($descripcionDescuento);
            $inscripcion->setDescuento($descuento);
            
            // Establecer número de legajo si existe
            if ($nroLegajo !== null) {
                $inscripcion->setNroLegajo($nroLegajo);
            }
            
            // Establecer fecha de inscripción
            if ($fechaInscripcion) {
                $inscripcion->setFechaInscripcion(new \DateTime($fechaInscripcion));
            }

            $entityManager->persist($descuento);
            $entityManager->persist($inscripcion);
            $entityManager->flush();

            // Creamos las cuotas correspondientes a la carrera
            $this->crearCuotasParaCarrera($inscripcion, $entityManager);

            $this->addFlash('notice', 'Alumno inscripto exitosamente en ' . $carrera->getNombre());
        }

        return $this->redirectToRoute('app_alumno_inscribir_carrera_view', ['id' => $alumno->getId()], Response::HTTP_SEE_OTHER);
    }

    private function crearCuotasParaCarrera(
        InscripcionCarrera $inscripcion, 
        EntityManagerInterface $entityManager
    ): void
    {
        $carrera = $inscripcion->getCarrera();
        $cantidadCuotas = $carrera->getCantidadCuotas();

        // Si la carrera no tiene cantidad de cuotas definida, no crear cuotas
        if (!$cantidadCuotas || $cantidadCuotas <= 0) {
            return;
        }

        // Crear cada cuota
        for ($i = 1; $i <= $cantidadCuotas; $i++) {
            $cuota = new Cuota();
            $cuota->setInscripcionCarrera($inscripcion);
            $cuota->setNumeroCuota($i);
            $entityManager->persist($cuota);
        }

        $entityManager->flush();
    }

    #[Route('/{id}/inscribir-edicion', name: 'app_alumno_inscribir_edicion_view', methods: ['GET', 'POST'])]
    public function inscribirEdicionView(Request $request, Alumno $alumno, EntityManagerInterface $entityManager): Response
    {
        // Traer todas las edicion de la base de datos
        $ediciones = $entityManager->getRepository(Edicion::class)->findAll();

        // Se obtienen las inscripciondes del alumno
        $inscripcionesEdicion = $entityManager->createQueryBuilder()
            ->select('ie', 'e', 'd', 'c')
            ->from(\App\Entity\InscripcionEdicion::class, 'ie')
            ->leftJoin('ie.edicion', 'e')
            ->leftJoin('ie.descuento', 'd')
            ->leftJoin('e.curso', 'c')
            ->where('ie.alumno = :alumno')
            ->setParameter('alumno', $alumno)
            ->getQuery()
            ->getResult();

        // Obtener todas las cuotas del alumno
        $cuotas = $entityManager->createQueryBuilder()
            ->select('cu', 'ie', 'e')
            ->from(\App\Entity\Cuota::class, 'cu')
            ->leftJoin('cu.inscripcionEdicion', 'ie')
            ->leftJoin('ie.edicion', 'e')
            ->where('ie.alumno = :alumno')
            ->setParameter('alumno', $alumno)
            ->getQuery()
            ->getResult();

        // Obtener todos los pagos de cuotas del alumno
        $pagosCuotas = $entityManager->createQueryBuilder()
            ->select('pc', 'p', 'cu')
            ->from(\App\Entity\PagoCuota::class, 'pc')
            ->leftJoin('pc.pago', 'p')
            ->leftJoin('pc.cuota', 'cu')
            ->getQuery()
            ->getResult();

        // Crear mapa de pagos por cuota
        $pagosPorCuota = [];
        foreach ($pagosCuotas as $pagoCuota) {
            $cuotaId = $pagoCuota->getCuota()->getId();
            if (!isset($pagosPorCuota[$cuotaId])) {
                $pagosPorCuota[$cuotaId] = [];
            }
            $pagosPorCuota[$cuotaId][] = $pagoCuota;
        }

        // Crear mapa de inscripciones y cuotas por edicion
        $inscripcionesPorEdicion = [];
        $edicionesInscriptas = [];

        foreach ($inscripcionesEdicion as $inscripcion) {
            $edicionId = $inscripcion->getEdicion()->getId();
            $edicionesInscriptas[] = $edicionId;
            $inscripcionesPorEdicion[$edicionId] = $inscripcion;
        }

        // Agrupar cuotas por edicion y calcular estados
        $cuotasPorEdicion = [];
        
        foreach ($cuotas as $cuota) {
            $inscripcion = $cuota->getInscripcionEdicion();
            
            if ($inscripcion) {
                $edicionId = $inscripcion->getEdicion()->getId();
                $cuotaId = $cuota->getId();
                
                if (!isset($cuotasPorEdicion[$edicionId])) {
                    $cuotasPorEdicion[$edicionId] = [];
                }
                
                // Calcular montos de la cuota (misma lógica que visualizar)
                $montoPagado = 0;
                $montoCuota = 0;
                $contadorPagos = 0;
                
                if (isset($pagosPorCuota[$cuotaId])) {
                    foreach ($pagosPorCuota[$cuotaId] as $pagoCuota) {
                        $montoCuota = $pagoCuota->getMontoCuota();
                        $montoPagado += $pagoCuota->getPago()->getMonto();
                        $contadorPagos++;
                    }
                }
                
                // Determinar estado
                if ($contadorPagos === 0) {
                    $estado = 'Pendiente';
                } elseif ($montoPagado >= $montoCuota) {
                    $estado = 'Paga';
                } else {
                    $estado = 'Parcial';
                }
                
                $cuotasPorEdicion[$edicionId][] = [
                    'numero' => $cuota->getNumeroCuota(),
                    'monto' => $montoCuota,
                    'pagado' => $montoPagado,
                    'estado' => $estado
                ];
            }
        }

        // Preparar datos para todas las ediciones
        $edicionesData = [];
        foreach ($ediciones as $edicion) {
            $edicionId = $edicion->getId();

            // Verificar si el alumno está inscripto en esta edicion
            if (in_array($edicionId, $edicionesInscriptas)) {
                $estado = 'Inscripto';
                $accion = 'Borrar';
                
                // Obtener datos de la inscripción
                $inscripcion = $inscripcionesPorEdicion[$edicionId];
                $descuento = $inscripcion->getDescuento();
                
                // Extraer valores del descuento si existe
                $valorDescuento = $descuento ? $descuento->getValor() : 0;
                $descripcionDescuento = $descuento ? $descuento->getDescripcion() : '';
                
                // Extraer valores de la inscripción
                $legajo = $inscripcion->getNroLegajo() ?? 'Sin legajo';
                $fechaInscripcion = $inscripcion->getFechaInscripcion() ? $inscripcion->getFechaInscripcion()->format('Y-m-d') : null;
                
                // Obtener cuotas de esta edicion
                $cuotasInfo = $cuotasPorEdicion[$edicionId] ?? [];
            } else {
                $estado = 'No inscripto';
                $accion = 'Inscribir';
                $valorDescuento = 0;
                $descripcionDescuento = '';
                $legajo = null;
                $fechaInscripcion = null;
                $cuotasInfo = [];
            }

            $edicionesData[] = [
                'id' => $edicionId,
                'nombre' => $edicion->getNombre(),
                'curso' => $edicion->getCurso()->getNombre(),
                'ordenanza' => $edicion->getCurso()->getNroOrdenanza(),
                'implementacion' => $edicion->getCurso()->getNroImplementacion(),
                'legajo' => $legajo,
                'descuento' => $valorDescuento,
                'descripcion' => $descripcionDescuento,
                'fechaInscripcion' => $fechaInscripcion,
                'estado' => $estado,
                'accion' => $accion,
                'cuotas' => $cuotasInfo
            ];
        }

        // Ordenar ediciones por estado (inscriptas primero)
        usort($edicionesData, function($a, $b) {
            if ($a['estado'] === 'Inscripto' && $b['estado'] !== 'Inscripto') {
                return -1;
            }
            if ($a['estado'] !== 'Inscripto' && $b['estado'] === 'Inscripto') {
                return 1;
            }
            return 0;
        });

        // Obtener descuentos para seleccionar a la hora de inscribir
        $descuentos = $entityManager->getRepository(Descuento::class)->findAll();

        return $this->render('alumno/inscribirEdicion.html.twig', [
            'alumno' => $alumno,
            'edicionesData' => $edicionesData,
            'descuentos' => $descuentos,
        ]);
    }

    #[Route('/{id}/inscribir-edicion/{edicion}', name: 'app_alumno_inscribir_edicion', methods: ['POST'])]
    public function inscribirEdicion(
            Request $request,
            Alumno $alumno,
            Edicion $edicion,
            EntityManagerInterface $entityManager
        ): Response
    { 
        // Obtener datos del POST
        $valorDescuento = (int) $request->request->get('valorDescuento', 0);
        $fechaInscripcion = $request->request->get('fecha_inscripcion');
        $descripcionDescuento = $request->request->get('descripcionDescuento', '');
        $nroLegajo = $request->request->get('nroLegajo') ? (int) $request->request->get('nroLegajo') : null;
        
        // Verificar si ya existe la inscripción
        $inscripcionExistente = $entityManager->getRepository(InscripcionEdicion::class)
            ->findOneBy(['alumno' => $alumno, 'edicion' => $edicion]);

        if ($inscripcionExistente) {
            $this->addFlash('warning', 'El alumno ya está inscripto en esta edición');
        } else {
            // Obtener el curso de la edición
            $curso = $edicion->getCurso();
            
            // Verificar si el alumno está inscripto en alguna carrera a la que pertenece el curso
            $carreraInscripta = $entityManager->createQueryBuilder()
                ->select('ic')
                ->from(InscripcionCarrera::class, 'ic')
                ->join('ic.carrera', 'c')
                ->join(PerteneceA::class, 'pa', 'WITH', 'pa.carrera = c AND pa.curso = :curso')
                ->where('ic.alumno = :alumno')
                ->setParameter('alumno', $alumno)
                ->setParameter('curso', $curso)
                ->getQuery()
                ->getOneOrNullResult();
            
            // Crear la nueva inscripción
            $inscripcion = new InscripcionEdicion();
            $inscripcion->setAlumno($alumno);
            $inscripcion->setEdicion($edicion);
            
            // Crear y asociar descuento
            $descuento = new Descuento();
            $descuento->setValor($valorDescuento);
            $descuento->setDescripcion($descripcionDescuento);
            $inscripcion->setDescuento($descuento);
            
            // Establecer número de legajo si existe
            if ($nroLegajo !== null) {
                $inscripcion->setNroLegajo($nroLegajo);
            }
            
            // Establecer fecha de inscripción
            if ($fechaInscripcion) {
                $inscripcion->setFechaInscripcion(new \DateTime($fechaInscripcion));
            }

            $entityManager->persist($descuento);
            $entityManager->persist($inscripcion);
            $entityManager->flush();

            // Crear cuotas solo si no está inscripto en la carrera correspondiente
            if ($carreraInscripta) {
                $this->addFlash('notice', 'Alumno inscripto exitosamente en ' . $edicion->getNombre() . '. No se crearon cuotas ya que está inscripto en la carrera correspondiente.');
            } else {
                // Creamos las cuotas correspondientes a la edición
                $this->crearCuotasParaEdicion($inscripcion, $entityManager);
                $this->addFlash('notice', 'Alumno inscripto exitosamente en ' . $edicion->getNombre() . '. Se creó una cuota correspondiente.');
            }
        }

        return $this->redirectToRoute('app_alumno_inscribir_edicion_view', ['id' => $alumno->getId()], Response::HTTP_SEE_OTHER);
    }

    private function crearCuotasParaEdicion(
        InscripcionEdicion $inscripcion, 
        EntityManagerInterface $entityManager
    ): void
    {
        // esta funcion crea una sola cuota, en este momento los cursos o ediciones no tienen un atributo de numero de cuotas
        $cuota = new Cuota();
        $cuota->setInscripcionEdicion($inscripcion);
        $cuota->setNumeroCuota(1);
       
        $entityManager->persist($cuota);
        $entityManager->flush();
    }

    #[Route('/{id}/desinscribir-carrera/{carrera}', name: 'app_alumno_desinscribir_carrera', methods: ['POST'])]
    public function desinscribirCarrera(Alumno $alumno, \App\Entity\Carrera $carrera, EntityManagerInterface $entityManager): Response
    {
        // Buscar la inscripción
        $inscripcion = $entityManager->getRepository(\App\Entity\InscripcionCarrera::class)
            ->findOneBy(['alumno' => $alumno, 'carrera' => $carrera]);

        if (!$inscripcion) {
            $this->addFlash('warning', 'El alumno no está inscripto en esta carrera');
        } else {
            // Eliminamos las cuotas asociadas a la inscripción 
            $this->eliminarCuotasDeInscripcion($inscripcion, $entityManager, 'carrera');

            // Eliminar la inscripción
            $entityManager->remove($inscripcion);
            $entityManager->flush();

            $this->addFlash('notice', 'Inscripción eliminada exitosamente de ' . $carrera->getNombre());
        }

        return $this->redirectToRoute('app_alumno_inscribir_carrera_view', ['id' => $alumno->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/desinscribir-edicion/{edicion}', name: 'app_alumno_desinscribir_edicion', methods: ['POST'])]
    public function desinscribirEdicion(Alumno $alumno, \App\Entity\Edicion $edicion, EntityManagerInterface $entityManager): Response
    {
        // Buscar la inscripción
        $inscripcion = $entityManager->getRepository(\App\Entity\InscripcionEdicion::class)
            ->findOneBy(['alumno' => $alumno, 'edicion' => $edicion]);

        if (!$inscripcion) {
            $this->addFlash('warning', 'El alumno no está inscripto en esta edicion');
        } else {
            // Eliminar las notas asociadas a la inscripción
            $notas = $entityManager->getRepository(\App\Entity\Nota::class)
                ->findBy(['inscripcionEdicion' => $inscripcion]);
            
            foreach ($notas as $nota) {
                // Eliminar documentación asociada a la nota
                $documentacion = $nota->getDocumentacionNota();
                if ($documentacion) {
                    $uploadDir = $this->getParameter('documentos_notas_directory');
                    $archivoPath = $uploadDir . '/' . $documentacion->getArchivo();
                    
                    // Eliminar archivo físico si existe
                    if (file_exists($archivoPath)) {
                        unlink($archivoPath);
                    }
                    
                    // Desvincular la documentación de la nota
                    $nota->setDocumentacionNota(null);
                    $entityManager->persist($nota);
                    $entityManager->flush(); // Flush to update FK before deleting
                    
                    // Eliminar entidad DocumentacionNota
                    $entityManager->remove($documentacion);
                }
                
                $entityManager->remove($nota);
            }
            
            // Eliminamos las cuotas asociadas a la inscripción 
            $this->eliminarCuotasDeInscripcion($inscripcion, $entityManager, 'edicion');

            // Eliminar la inscripción
            $entityManager->remove($inscripcion);
            $entityManager->flush();

            $this->addFlash('notice', 'Inscripción eliminada exitosamente de ' . $edicion->getNombre());
        }

        return $this->redirectToRoute('app_alumno_inscribir_edicion_view', ['id' => $alumno->getId()], Response::HTTP_SEE_OTHER);
    }

    private function eliminarCuotasDeInscripcion(
            $inscripcion, 
            EntityManagerInterface $entityManager,
            string $tipo = 'carrera'
        ): void
    {
        // Se buscan todas las cuotas asociadas a esta inscripción según el tipo
        if ($tipo === 'carrera') {
            $cuotas = $entityManager->getRepository(Cuota::class)
                ->findBy(['inscripcionCarrera' => $inscripcion]);
        } else if ($tipo === 'edicion') {
            $cuotas = $entityManager->getRepository(Cuota::class)
                ->findBy(['inscripcionEdicion' => $inscripcion]);
        } else{
            throw new \InvalidArgumentException("Tipo de inscripción inválido: $tipo. Debe ser 'carrera' o 'edicion'.");
        }

        // Eliminar cada cuota y sus pagos asociados
        foreach ($cuotas as $cuota) {
            // Primero obtener los pagos de cuota asociados
            $pagosCuotas = $entityManager->getRepository(\App\Entity\PagoCuota::class)
                ->findBy(['cuota' => $cuota]);
            
            foreach ($pagosCuotas as $pagoCuota) {
                // Obtener el pago antes de eliminar la relación
                $pago = $pagoCuota->getPago();
                
                // Eliminar la relación PagoCuota
                $entityManager->remove($pagoCuota);
                
                // Eliminar el Pago
                if ($pago) {
                    $entityManager->remove($pago);
                }
            }
            
            // Luego eliminar la cuota
            $entityManager->remove($cuota);
        }
        
        // No hacemos flush aquí, se hará junto con la eliminación de la inscripción
    }

    #[Route('/{id}/notas', name: 'app_alumno_notas', methods: ['GET', 'POST'])]
    public function notas(Request $request, Alumno $alumno, EntityManagerInterface $entityManager): Response
    {
        $notum = new Nota();
        $form = $this->createForm(NotaType::class, $notum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Manejar la subida del archivo
            $archivoFile = $form->get('archivo')->getData();
            
            if ($archivoFile) {
                $documentacionNota = new DocumentacionNota();
                
                // Generar nombre único para el archivo
                $originalFilename = pathinfo($archivoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$archivoFile->guessExtension();
                
                // Mover el archivo al directorio de uploads
                try {
                    $archivoFile->move(
                        $this->getParameter('documentos_notas_directory'),
                        $newFilename
                    );
                    
                    $documentacionNota->setArchivo($newFilename);
                    $entityManager->persist($documentacionNota);
                    
                    $notum->setDocumentacionNota($documentacionNota);
                    
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error al subir el archivo: '.$e->getMessage());
                }
            }

            $entityManager->persist($notum);
            $entityManager->flush();

            $this->addFlash('success', 'Nota guardada correctamente.');
            return $this->redirectToRoute('app_alumno_notas', ['id' => $alumno->getId()], Response::HTTP_SEE_OTHER);
        }

        // Obtener notas del alumno
        $notas = $entityManager->createQueryBuilder()
            ->select('n', 'ie', 'e', 'c', 'dn')
            ->from(\App\Entity\Nota::class, 'n')
            ->innerJoin('n.inscripcionEdicion', 'ie')
            ->innerJoin('ie.edicion', 'e')
            ->innerJoin('e.curso', 'c')
            ->leftJoin('n.documentacionNota', 'dn')
            ->where('ie.alumno = :alumno')
            ->setParameter('alumno', $alumno)
            ->getQuery()
            ->getResult();

        // Obtener todas las inscripciones del alumno (para el dropdown)
        $inscripciones = $entityManager->createQueryBuilder()
            ->select('ie', 'e', 'c')
            ->from(\App\Entity\InscripcionEdicion::class, 'ie')
            ->innerJoin('ie.edicion', 'e')
            ->innerJoin('e.curso', 'c')
            ->where('ie.alumno = :alumno')
            ->setParameter('alumno', $alumno)
            ->getQuery()
            ->getResult();

        // Preparar datos para el template
        $notasData = [];

        foreach ($notas as $nota) {
            $inscripcion = $nota->getInscripcionEdicion();
            $edicion = $inscripcion->getEdicion();
            $curso = $edicion->getCurso();
            
            // Generar enlace para el archivo si existe
            $documentacionHtml = 'Sin documentación';
            if ($nota->getDocumentacionNota()) {
                $archivo = $nota->getDocumentacionNota()->getArchivo();
                $documentacionHtml = sprintf(
                    '<a href="/uploads/documentos_notas/%s" target="_blank" class="btn-documento">Ver archivo</a>',
                    $archivo
                );
            }
            
            $notasData[] = [
                'id' => $nota->getId(),
                'curso' => $curso->getNombre(),
                'edicion' => $edicion->getNombre(),
                'nota' => $nota->getValor(),
                'descripcion' => $nota->getDescripcion(),
                'documentacion' => $documentacionHtml,
                'fecha_carga' => $nota->getFechaCarga() ? $nota->getFechaCarga()->format('d/m/Y') : 'N/A',
            ];
        }

        return $this->render('alumno/notas_alumno.html.twig', [
            'alumno' => $alumno,
            'notas' => $notasData,
            'inscripciones' => $inscripciones,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/notas/pdf', name: 'app_alumno_notas_pdf', methods: ['GET'])]
    public function notasPdf(Alumno $alumno, EntityManagerInterface $entityManager): Response
    {
        // Obtener notas del alumno (misma lógica que notas())
        $notas = $entityManager->createQueryBuilder()
            ->select('n', 'ie', 'e', 'c')
            ->from(\App\Entity\Nota::class, 'n')
            ->innerJoin('n.inscripcionEdicion', 'ie')
            ->innerJoin('ie.edicion', 'e')
            ->innerJoin('e.curso', 'c')
            ->where('ie.alumno = :alumno')
            ->setParameter('alumno', $alumno)
            ->getQuery()
            ->getResult();

        // Preparar datos para el template
        $notasData = [];
        foreach ($notas as $nota) {
            $inscripcion = $nota->getInscripcionEdicion();
            $edicion = $inscripcion->getEdicion();
            $curso = $edicion->getCurso();
            
            $notasData[] = [
                'curso' => $curso->getNombre(),
                'edicion' => $edicion->getNombre(),
                'nota' => $nota->getValor(),
                'descripcion' => $nota->getDescripcion(),
                'fecha_carga' => $nota->getFechaCarga() ? $nota->getFechaCarga()->format('d/m/Y') : 'N/A',
            ];
        }

        // Renderizar HTML para el PDF
        $fechaEmision = new \DateTime('now', new \DateTimeZone('America/Argentina/Buenos_Aires'));
        $html = $this->renderView('alumno/pdf_notas.html.twig', [
            'alumno' => $alumno,
            'notas' => $notasData,
            'fecha_emision' => $fechaEmision
        ]);

        // Generar PDF con Dompdf
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Retornar PDF como descarga
        $fecha = $fechaEmision->format('d-m-Y');
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="notas_' . $alumno->getNombre() . '_' . $alumno->getApellido() . '_' . $fecha . '.pdf"'
        ]);
    }

}

  
