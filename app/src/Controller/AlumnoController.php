<?php

namespace App\Controller;

use App\Entity\Alumno;
use App\Entity\InscripcionEdicion;
use App\Form\AlumnoType;
use App\Entity\Nota;
use App\Form\NotaType;
use App\Repository\AlumnoRepository;
use App\Repository\CarreraRepository;
use App\Repository\CursoRepository;
use App\Repository\EdicionRepository;
use App\Repository\InscripcionCarreraRepository;
use App\Repository\InscripcionEdicionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

            return $this->redirectToRoute('app_alumno_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alumno/new.html.twig', [
            'alumno' => $alumno,
            'form' => $form,
        ]);
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
        $inscripcionEdicion->setDescuento(0);

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
    public function visualizar(Request $request, int $id, AlumnoRepository $alumnoRepository, EntityManagerInterface $entityManager): Response
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

            $pagosCuota = [];
            $montoPagar = 0;
            $montoPagado = 0;
            $contadorPagos = 0;
            foreach ($pagoCuotas as $pago) {
                if ($pago->getCuota()->getId() === $cuota->getId()) {
                    $pagosCuota[] = $pago;
                    $montoPagar = $pago->getPago()->getMontoCuota();
                    $montoPagado += $pago->getPago()->getMontoPagado();
                    $contadorPagos += 1;
                }
            }
            
            if($contadorPagos === 0){
                $estadoPago = 'Pendiente';
            } elseif ($montoPagado >= $montoPagar) {
                $estadoPago = 'Paga'; 
            } elseif ($montoPagado === 0) {
                $estadoPago = 'Pendiente';
            } else {
                $estadoPago = 'Faltante';
            }


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

    #[Route('/{id}/inscribir-carrera', name: 'app_alumno_inscribir', methods: ['GET', 'POST'])]
    public function inscribir(Request $request, Alumno $alumno, EntityManagerInterface $entityManager): Response
    {
        // Traer todas las carreras de la base de datos
        $carreras = $entityManager->createQueryBuilder()
            ->select('c')
            ->from(\App\Entity\Carrera::class, 'c')
            ->getQuery()
            ->getResult();

        // Obtener las inscripciones del alumno para verificar en cuáles ya está inscripto
        $inscripciones = $entityManager->createQueryBuilder()
            ->select('ic', 'c')
            ->from(\App\Entity\InscripcionCarrera::class, 'ic')
            ->innerJoin('ic.carrera', 'c')
            ->where('ic.alumno = :alumno')
            ->setParameter('alumno', $alumno)
            ->getQuery()
            ->getResult();

        // Crear un array con los IDs de las carreras en las que el alumno está inscripto
        $carrerasInscriptas = [];
        foreach ($inscripciones as $inscripcion) {
            $carrerasInscriptas[] = $inscripcion->getCarrera()->getId();
        }

        $carrerasData = [];
        foreach ($carreras as $carrera) {

            // Verificar si el alumno está inscripto en esta carrera
            if (in_array($carrera->getId(), $carrerasInscriptas)) {
                $estado = 'Inscripto';
                $accion = 'Borrar';
            } else {
                $estado = 'No inscripto';
                $accion = 'Inscribir';
            }

            $carrerasData[] = [
                'id' => $carrera->getId(),
                'nombre' => $carrera->getNombre(),
                'ordenanza' => $carrera->getNroOrdenanza(),
                'implementacion' => $carrera->getNroImplementacion(),
                'estado' => $estado,
                'accion' => $accion,
            ];
        }

        // Ordenar carreras por estado
        usort($carrerasData, function($a, $b) {
            if ($a['estado'] === 'Inscripto' && $b['estado'] !== 'Inscripto') {
                return -1; // $a va primero
            }
            if ($a['estado'] !== 'Inscripto' && $b['estado'] === 'Inscripto') {
                return 1; // $b va primero
            }
            return 0; // mantener orden original
        });

        return $this->render('alumno/inscribir.html.twig', [
            'alumno' => $alumno,
            'carrerasData' => $carrerasData,
        ]);
    }

    #[Route('/{id}/inscribir-carrera/{carrera}', name: 'app_alumno_inscribir_carrera', methods: ['POST'])]
    public function inscribirCarrera(Alumno $alumno, \App\Entity\Carrera $carrera, EntityManagerInterface $entityManager): Response
    {
        // Verificar si ya existe la inscripción
        $inscripcionExistente = $entityManager->getRepository(\App\Entity\InscripcionCarrera::class)
            ->findOneBy(['alumno' => $alumno, 'carrera' => $carrera]);

        if ($inscripcionExistente) {
            $this->addFlash('warning', 'El alumno ya está inscripto en esta carrera');
        } else {
            // Crear la nueva inscripción
            $inscripcion = new \App\Entity\InscripcionCarrera();
            $inscripcion->setAlumno($alumno);
            $inscripcion->setCarrera($carrera);
            $inscripcion->setDescuento(0); // Descuento por defecto 0

            $entityManager->persist($inscripcion);
            $entityManager->flush();

            $this->addFlash('notice', 'Alumno inscripto exitosamente en ' . $carrera->getNombre());
        }

        return $this->redirectToRoute('app_alumno_inscribir', ['id' => $alumno->getId()], Response::HTTP_SEE_OTHER);
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
            // Eliminar la inscripción
            $entityManager->remove($inscripcion);
            $entityManager->flush();

            $this->addFlash('notice', 'Inscripción eliminada exitosamente de ' . $carrera->getNombre());
        }

        return $this->redirectToRoute('app_alumno_inscribir', ['id' => $alumno->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/notas', name: 'app_alumno_notas', methods: ['GET', 'POST'])]
    public function notas(Request $request, Alumno $alumno, EntityManagerInterface $entityManager): Response
    {

        $notum = new Nota();
        $form = $this->createForm(NotaType::class, $notum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($notum);
            $entityManager->flush();

            return $this->redirectToRoute('app_alumno_notas', ['id' => $alumno->getId()], Response::HTTP_SEE_OTHER);
        }

        // Obtener notas del alumno
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
            
            $notasData[] = [
                'id' => $nota->getId(),
                'curso' => $curso->getNombre(),
                'edicion' => $edicion->getNombre(),
                'nota' => $nota->getValor(),
                'descripcion' => $nota->getDescripcion(),
                'fecha_carga' => $nota->getFechaCarga() ? $nota->getFechaCarga()->format('d/m/Y') : 'N/A',
            ];
        }

        return $this->render('alumno/notas_alumno.html.twig', [
            'alumno' => $alumno,
            'notas' => $notasData,
            'inscripciones' =>  $inscripciones,
            'form' => $form,
        ]);

    }

}
