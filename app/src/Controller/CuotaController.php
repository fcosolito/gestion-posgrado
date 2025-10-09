<?php

namespace App\Controller;

use App\Entity\Cuota;
use App\Form\CuotaType;
use App\Repository\CuotaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cuota')]
final class CuotaController extends AbstractController
{
    #[Route(name: 'app_cuota_index', methods: ['GET'])]
    public function index(CuotaRepository $cuotaRepository): Response
    {
        return $this->render('cuota/index.html.twig', [
            'cuotas' => $cuotaRepository->findAll(),
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
