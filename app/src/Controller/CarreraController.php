<?php

namespace App\Controller;

use App\Entity\Carrera;
use App\Entity\Curso;
use App\Entity\PerteneceA;
use App\Form\CarreraType;
use App\Form\CarreraSearchType;
use App\Repository\CarreraRepository;
use App\Repository\InscripcionCarreraRepository;
use App\Repository\CursoRepository;
use App\Repository\PerteneceARepository;
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

            return $this->redirectToRoute('app_carrera_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('carrera/new.html.twig', [
            'carrera' => $carrera,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_carrera_show', methods: ['GET'])]
    public function show(Carrera $carrera, InscripcionCarreraRepository $inscripcionCarreraRepository): Response
    {   
        
        return $this->render('carrera/show.html.twig', [
            'carrera' => $carrera,
            'inscriptosCarrera'=> count($inscripcionCarreraRepository->findByCarrera($carrera->getId())),
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
}
