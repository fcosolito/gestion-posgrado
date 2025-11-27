<?php

namespace App\Controller;

use App\Entity\Alumno;
use App\Entity\Carrera;
use App\Entity\Cuota;
use App\Entity\Curso;
use App\Entity\Descuento;
use App\Entity\InscripcionCarrera;
use App\Entity\PerteneceA;
use App\Form\CarreraType;
use App\Form\CarreraSearchType;
use App\Repository\CarreraRepository;
use App\Repository\InscripcionCarreraRepository;
use App\Repository\PrecioCarreraRepository;
use App\Repository\CursoRepository;
use App\Repository\DescuentoRepository;
use App\Repository\PerteneceARepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/carrera')]
final class CarreraController extends AbstractController
{
    #[Route(name: 'app_carrera_index', methods: ['GET'])]
    public function index(Request $request,CarreraRepository $carreraRepository, InscripcionCarreraRepository $inscripcionCarreraRepository): Response
    {
        // Creamos formulario para buscar carrera por nombre, nroImpl y/o nroOrd.
        $searchForm = $this->createForm(CarreraSearchType::class);
        $searchForm->handleRequest($request);
        
        $criteria = [];

        if($searchForm->isSubmitted() && $searchForm->isValid()){
            $data = $searchForm->getData(); // Esto devuelve un objeto Carrera
            
            // Acceder a las propiedades del objeto Carrera
            if(!empty($data->getNombre())){
                $criteria['nombre'] = $data->getNombre();
            }
            if(!empty($data->getNroImplementacion())){
                $criteria['nroImplementacion'] = $data->getNroImplementacion();
            }
            if(!empty($data->getNroOrdenanza())){
                $criteria['nroOrdenanza'] = $data->getNroOrdenanza();
            }
        }
        
        $carreras = $carreraRepository->search($criteria);
        $inscriptosPorCarrera = [];
        foreach($carreras as $carrera){
            $inscriptos = $inscripcionCarreraRepository->findByCarrera($carrera->getId());
            $inscriptosPorCarrera[$carrera->getId()] = count($inscriptos); 
        }

        return $this->render('carrera/index.html.twig', [
            'carreras' => $carreras,
            'inscriptosPorCarrera'=>$inscriptosPorCarrera,
            'searchForm' => $searchForm,
        ]);
    }

    #[Route('/new', name: 'app_carrera_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $carrera = new Carrera();
        $form = $this->createForm(CarreraType::class, $carrera);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($carrera);
            $entityManager->flush();

            $this->addFlash('notice', 'Carrera creada exitosamente');

            return $this->redirectToRoute('app_carrera_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('carrera/new.html.twig', [
            'carrera' => $carrera,
            'form' => $form,
        ]);
    }

    #[Route('/search', name: 'app_carrera_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $entityManager): Response
    {
        $query =  $request->query->get("query", "");
        $carreras = $entityManager->getRepository(Carrera::class)->searchXor($query);
        $carreras_ser = array_map(
            function ($c) {
                return (
                    [
                        "id" => $c->getId(),
                        "nombre" => $c->getNombre(),
                        "nroOrdenanza" => $c->getNroOrdenanza(),
                        "nroImplementacion" => $c->getNroImplementacion(),
                    ]
                    );
            },
            $carreras
        );

        return $this->json($carreras_ser);
    }

    #[Route('/{id}', name: 'app_carrera_show', methods: ['GET'])]
    public function show(
        Carrera $carrera, 
        InscripcionCarreraRepository $inscripcionCarreraRepository,
        PerteneceARepository $perteneceARepository,
        PrecioCarreraRepository $precioCarreraRepository,
        DescuentoRepository $descuentoRepository,
        EntityManagerInterface $entityManager
    ): Response
    {   
        // Obtenemos todos los cursos de la carrera
        $cursosRelacionados = $perteneceARepository->findBy(['carrera' => $carrera]);
        $cursosObligatorios = [];
        $cursosElectivos = [];
        
        foreach ($cursosRelacionados as $relacion) {
            if ($relacion->isEsElectivo()) {
                $cursosElectivos[] = $relacion->getCurso();
            } else {
                $cursosObligatorios[] = $relacion->getCurso();
            }
        }
        
        // Obtenemos los alumnos inscriptos a la carrera
        $inscripciones = $inscripcionCarreraRepository->findByCarrera($carrera->getId());
        $alumnos = array_map(function($inscripcion) {
            $alumno = $inscripcion->getAlumno();
            return [
                "id" => $alumno->getId(),
                "nombre" => $alumno->getNombre(),
                "apellido" => $alumno->getApellido(),
                "dni" => $alumno->getDni(),
                "email" => $alumno->getEmail(),
                "inscripcion" => $inscripcion->getId(),
                "nroLegajo" => $inscripcion->getNroLegajo(),
                "fechaInscripcion" => $inscripcion->getFechaInscripcion() ? $inscripcion->getFechaInscripcion()->format("Y-m-d") : null,
                "descuento" => $inscripcion->getDescuento() ? $inscripcion->getDescuento()->getId() : null,
            ];
        }, $inscripciones);

        // Todos los descuentos, para poder modificar las inscripciones
        $descuentos = array_map(
            function (Descuento $d) {
                return [
                    "id" => $d->getId(),
                    "valor" => $d->getValor(),
                    "descripcion" => $d->getDescripcion(),
                ];
            }, $descuentoRepository->findAll()
        );

        // Carrera serializada, para el componente de react
        $carrera_ser = [
            "id" => $carrera->getId(),
        ];

        //buscamos el precio vigente de la carrera
        $precioVigente = $precioCarreraRepository->findPrecioVigentePorCarrera($carrera->getId());
        
        return $this->render('carrera/show.html.twig', [
            'carrera' => $carrera,
            'carrera_ser' => $carrera_ser,
            'inscriptosCarrera' => count($inscripciones),
            'cursosObligatorios' => $cursosObligatorios,
            'cursosElectivos' => $cursosElectivos,
            'alumnos' => $alumnos,
            'descuentos' => $descuentos,
            'precioVigente'=> $precioVigente,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_carrera_edit', methods: ['GET', 'POST'])]
    public function edit(
            Request $request,
            Carrera $carrera,
            EntityManagerInterface $entityManager,
            PerteneceARepository $perteneceRepository,
            CursoRepository $cursoRepository
        ): Response
    {
        $form = $this->createForm(CarreraType::class, $carrera);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('notice', 'Carrera guardada exitosamente');

            return $this->redirectToRoute('app_carrera_index', [], Response::HTTP_SEE_OTHER);
        }
        
        // Cursos actuales de la carrera
        $cursosCarrera = $perteneceRepository->findCursosByCarrera($carrera->getId());

        $obligatorios = array_map(
            fn($r)=> $r->getCurso(), 
            array_filter($cursosCarrera, fn($r)=> !$r->isEsElectivo())
        );
        $electivos = array_map(
            fn($r)=> $r->getCurso(),
            array_filter($cursosCarrera, fn($r) => $r->isEsElectivo())
        );

        // Todos los cursos asignados de la carrera
        $cursosAsignados = array_merge($obligatorios, $electivos);

        // Cursos restantes
        $cursosTotales = $cursoRepository->findAll();
        $cursosRestantes = array_filter($cursosTotales, fn($r)=> !in_array($r,$cursosAsignados));


        return $this->render('carrera/edit.html.twig', [
            'carrera' => $carrera,
            'form' => $form,
            'cursosObligatorios' => $obligatorios,
            'cursosElectivos' => $electivos,
            'cursosRestantes' => $cursosRestantes,
        ]);
    }

    // Método para asignar cursos ya existentes a una carrera
    #[Route('/{id}/asignar-curso', name: 'app_carrera_asignar_curso', methods: ['POST'])]
    public function asignarCurso(
            Request $request, 
            Carrera $carrera, 
            EntityManagerInterface $entityManager
        ) : Response

    {
        $cursoId = $request->request->get('curso_id');
        $tipo = $request->request->get('tipo_asignacion');

        //Recuperamos el curso de la db
        $curso = $entityManager->getRepository(Curso::class)->find($cursoId);

        // Creamos la relación en la db
        $pertenece = new PerteneceA();
        $pertenece->setCarrera($carrera);
        $pertenece->setCurso($curso);
        $pertenece->setEsElectivo($tipo === 'electivo');

        $entityManager->persist($pertenece);
        $entityManager->flush();

        $this->addFlash('notice', 'Curso asociado exitosamente');

        return $this->redirectToRoute('app_carrera_edit', ['id' => $carrera->getId()]);
    }

    // Método para asignar un curso creado nuevo a una carrera
    #[Route('/{id}/crear-asignar-curso', name: 'app_carrera_crear_asignar_curso', methods: ['POST'])]
    public function crearAsignarCurso(
            Request $request, 
            Carrera $carrera, 
            EntityManagerInterface $entityManager
        ) : Response
    {
        // Datos del formulario
        $nombre = $request->request->get('nombre');
        $nroOrdenanza = $request->request->get('nroOrdenanza');
        $nroImplementacion = $request->request->get('nroImplementacion');
        $cantidadHoras = $request->request->get('cantidadHoras');
        $tipo = $request->request->get('tipo_asignacion'); // 'obligatorio' o 'electivo'

        // Creamos el curso nuevo
        $curso = new Curso();
        $curso->setNombre($nombre);
        $curso->setNroOrdenanza($nroOrdenanza);
        $curso->setNroImplementacion($nroImplementacion);
        $curso->setHoras($cantidadHoras);

        $entityManager->persist($curso);

        // Creamos la relación entre el curso nuevo y la carrera
        $pertenece = new PerteneceA();
        $pertenece->setCarrera($carrera);
        $pertenece->setCurso($curso);
        $pertenece->setEsElectivo($tipo === 'electivo');

        $entityManager->persist($pertenece);
        $entityManager->flush();

        $this->addFlash('notice', 'Curso asociado exitosamente');

        return $this->redirectToRoute('app_carrera_edit', ['id' => $carrera->getId()]);        
    }

    #[Route('/{id}', name: 'app_carrera_delete', methods: ['POST'])]
    public function delete(Request $request, Carrera $carrera, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$carrera->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($carrera);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_carrera_index', [], Response::HTTP_SEE_OTHER);
    }

    
    #[Route('/{id}/asociar-curso/{cursoId}', name: 'app_carrera_asociar_curso', methods: ['PUT'])]
    public function asociarCurso(Request $request, Carrera $carrera, Curso $cursoId, EntityManagerInterface $entityManager): Response
    {
        $perteneceA = new PerteneceA();

        $perteneceA->setCarrera($carrera);
        $perteneceA->setCurso($cursoId);
        $perteneceA->setEsElectivo(false);

        if ($request->query->has("electivo")) {
            $perteneceA->setEsElectivo(true);
        }

        $entityManager->persist($perteneceA);
        $entityManager->flush();

        return $this->json([
            "carrera" => $carrera->getId(),
            "curso" => $cursoId->getId(),
            "esElectivo" => $perteneceA->isEsElectivo(),
        ]);
    }

    #[Route('/{id}/asociar-curso/{cursoId}', name: 'app_carrera_desasociar_curso', methods: ['POST'])]
    public function desasociarCurso(Carrera $carrera, Curso $cursoId, EntityManagerInterface $entityManager): Response
    {
        $asociaciones = $entityManager->getRepository(PerteneceA::class)->findBy(["carrera" => $carrera, "curso" => $cursoId]);

        foreach ($asociaciones as $as) {
            $entityManager->remove($as);
        }
        $entityManager->flush();

        return $this->json([

        ]);
    }

    // Método para ver el historial de precios de la carrera
    #[Route('/{id}/historial-precios', name: 'app_carrera_historial_precios', methods: ['GET'])]
    public function historialPrecios(
        Carrera $carrera,
        PrecioCarreraRepository $precioCarreraRepository
    ): Response
    {
        $precios = $precioCarreraRepository->findBy(
            ['carrera' => $carrera],
            ['fechaVigencia' => 'DESC']
        );

        return $this->render('carrera/historial_precios.html.twig', [
            'carrera' => $carrera,
            'precios' => $precios,
        ]);
    }

    #[Route('/{id}/edit-insc/{inscripcion}', name: 'api_carrera_editar_inscripcion', methods: ['PUT'])]
    public function editarInscripcion(
            Request $request,
            InscripcionCarrera $inscripcion,
            Carrera $carrera,
            EntityManagerInterface $entityManager,
        ): Response
    {
        $descuentoR = $entityManager->getRepository(Descuento::class);
        $inscripcionR = $entityManager->getRepository(InscripcionCarrera::class);

        $data = $request->toArray();

        // Obtener datos de la request
        $descuento = $data['descuento'] ? $descuentoR->find($data['descuento']) : null;
        $fechaInscripcion = isset($data['fechaInscripcion']) ? DateTime::createFromFormat("Y-m-d", $data['fechaInscripcion']) : new DateTime();
        $nroLegajo = $data['nroLegajo'] ? (int) $data['nroLegajo'] : null;
        
        $inscripcion->setDescuento($descuento);
        $inscripcion->setFechaInscripcion($fechaInscripcion);
            
        if ($nroLegajo) {
            if (!($nroLegajo == $inscripcion->getNroLegajo()) && $inscripcionR->findOneBy(["carrera" => $carrera, "nroLegajo" => $nroLegajo])) {
                return $this->json(["success" => false, "error" => "El legajo ya existe en la carrera."], 500);
            } else {
                $inscripcion->setNroLegajo($nroLegajo);
            }
        }
            
        $entityManager->persist($inscripcion);
        $entityManager->flush();

        return $this->json(["success" => true, "inscripcion" => $inscripcion->getId()]);

    }

    #[Route('/{id}/desinsc-alumno/{idAlumno}', name: 'api_carrera_desinscribir_alumno', methods: ['PUT'])]
    public function desinscribirAlumno(
            Alumno $idAlumno,
            Carrera $carrera,
            EntityManagerInterface $entityManager,
        ): Response
    {
        $inscripcionR = $entityManager->getRepository(InscripcionCarrera::class);
        $cuotaR = $entityManager->getRepository(Cuota::class);

        $inscripcion = $inscripcionR->findOneBy(["carrera" => $carrera, "alumno" => $idAlumno]) ?? new InscripcionCarrera();
        $cuotas = $inscripcion ? $cuotaR->findBy(["inscripcionCarrera" => $inscripcion]) : [];
        foreach ($cuotas as $cuota) {
            $entityManager->remove($cuota);
        }

        $entityManager->remove($inscripcion);
        $entityManager->flush();

        return $this->json(["success" => true]);
    }
}
