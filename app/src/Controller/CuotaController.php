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
use App\Service\CuotaService;
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
