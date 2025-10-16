<?php

namespace App\Controller;

use App\Entity\Carrera;
use App\Form\CarreraType;
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
    public function index(CarreraRepository $carreraRepository, InscripcionCarreraRepository $inscripcionCarreraRepository): Response
    {
        $carreras = $carreraRepository->findAll();
        $inscriptosPorCarrera = [];
        foreach($carreras as $carrera){
            $inscriptos = $inscripcionCarreraRepository->findByCarrera($carrera->getId());
            $inscriptosPorCarrera[$carrera->getId()] = count($inscriptos); 
        }

        return $this->render('carrera/index.html.twig', [
            'carreras' => $carreras,
            'inscriptosPorCarrera'=>$inscriptosPorCarrera,
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
