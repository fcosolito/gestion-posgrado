<?php

namespace App\Controller;

use App\Entity\Carrera;
use App\Entity\Curso;
use App\Entity\PerteneceA;
use App\Form\CarreraType;
use App\Repository\CarreraRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/carrera')]
final class CarreraController extends AbstractController
{
    #[Route(name: 'app_carrera_index', methods: ['GET'])]
    public function index(CarreraRepository $carreraRepository): Response
    {
        return $this->render('carrera/index.html.twig', [
            'carreras' => $carreraRepository->findAll(),
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

    #[Route('/search', name: 'app_carrera_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $entityManager): Response
    {
        $query =  $request->query->get("query", "");
        $carreras = $entityManager->getRepository(Carrera::class)->search($query);
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
    public function show(Carrera $carrera): Response
    {
        return $this->render('carrera/show.html.twig', [
            'carrera' => $carrera,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_carrera_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Carrera $carrera, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CarreraType::class, $carrera);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_carrera_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('carrera/edit.html.twig', [
            'carrera' => $carrera,
            'form' => $form,
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
}
