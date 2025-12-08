<?php

namespace App\Controller;

use App\Entity\Cuota;
use App\Form\CuotaType;
use App\Service\CuotaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cuota')]
final class CuotaController extends AbstractController
{

    #[Route(name: 'app_cuota_index', methods: ['GET'])]
    public function index(Request $request, CuotaService $cuotaService): Response
    {
        $parametros = $request->query->all();
        $result = $cuotaService->obtenerDatosIndex($parametros);

        return $this->render('cuota/index.html.twig', $result);
    }

    #[Route('/new', name: 'app_cuota_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CuotaService $cuotaService): Response
    {
        $cuota = new Cuota();
        $form = $this->createForm(CuotaType::class, $cuota);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $cuotaService->create($cuota);
                $this->addFlash('success', 'Cuota creada correctamente.');
                return $this->redirectToRoute('app_cuota_index');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('cuota/new.html.twig', [
            'form' => $form,
            'cuotum' => $cuota,
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
    public function edit(Request $request, Cuota $cuota, CuotaService $service): Response
    {
        $form = $this->createForm(CuotaType::class, $cuota);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $service->update($cuota);
                $this->addFlash('success', 'Cuota actualizada correctamente.');
                return $this->redirectToRoute('app_cuota_index');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('cuota/edit.html.twig', [
            'form' => $form,
            'cuotum' => $cuota,
        ]);
    }

    #[Route('/{id}', name: 'app_cuota_delete', methods: ['POST'])]
    public function delete(Request $request, Cuota $cuota, CuotaService $service): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cuota->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $service->delete($cuota);
                $this->addFlash('success', 'Cuota eliminada correctamente.');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_cuota_index');
    }
}
