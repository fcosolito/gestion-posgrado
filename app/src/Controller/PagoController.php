<?php

namespace App\Controller;

use App\Entity\Pago;
use App\Form\PagoType;
use App\Repository\PagoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pago')]
final class PagoController extends AbstractController
{
    #[Route(name: 'app_pago_index', methods: ['GET'])]
    public function index(PagoRepository $pagoRepository): Response
    {
        return $this->render('pago/index.html.twig', [
            'pagos' => $pagoRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_pago_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $pago = new Pago();
        $form = $this->createForm(PagoType::class, $pago);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($pago);
            $entityManager->flush();

            return $this->redirectToRoute('app_pago_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pago/new.html.twig', [
            'pago' => $pago,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pago_show', methods: ['GET'])]
    public function show(Pago $pago): Response
    {
        return $this->render('pago/show.html.twig', [
            'pago' => $pago,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pago_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pago $pago, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PagoType::class, $pago);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_pago_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pago/edit.html.twig', [
            'pago' => $pago,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pago_delete', methods: ['POST'])]
    public function delete(Request $request, Pago $pago, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pago->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($pago);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pago_index', [], Response::HTTP_SEE_OTHER);
    }
}
