<?php

namespace App\Controller;

use App\Entity\Descuento;
use App\Entity\InscripcionEdicion;
use App\Form\DescuentoType;
use App\Repository\DescuentoRepository;
use App\Repository\InscripcionEdicionRepository;
use App\Service\DescuentoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/descuento')]
final class DescuentoController extends AbstractController
{
    #[Route(name: 'app_descuento_index', methods: ['GET'])]
    public function index(DescuentoRepository $descuentoRepository): Response
    {
        return $this->render('descuento/index.html.twig', [
            'descuentos' => $descuentoRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_descuento_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $descuento = new Descuento();
        $form = $this->createForm(DescuentoType::class, $descuento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($descuento);
            $entityManager->flush();

            return $this->redirectToRoute('app_descuento_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('descuento/new.html.twig', [
            'descuento' => $descuento,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/asoc-insc-e', name: 'app_descuento_asociar_inscripcion_edicion', methods: ['POST'])]
    public function asociarInscEdicion(Descuento $descuento, Request $request, InscripcionEdicionRepository $ieR, DescuentoRepository $dR, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['success' => false]);
        }

        if (isset($data["inscripcion"])) {
            $insc = $ieR->find($data["inscripcion"]);
            $insc->setDescuento($descuento);

            $em->persist($insc);
            $em->flush();
        } else {
            return $this->json(['success' => false]);
        }

        return $this->json([
            'success' => true,
            'inscripcion' => [
                'id' => $insc->getId(),
                'fechaInscripcion' => $insc->getFechaInscripcion() ? $insc->getFechaInscripcion()->format("Y-m-d") : null,
                'nroLegajo' => $insc->getNroLegajo(),
                'descuento' => [
                    'id' => $descuento->getId(),
                    'descripcion' => $descuento->getDescripcion(),
                    'valor' => $descuento->getValor(),
                ],
            ]
        ]);
    }

    #[Route('/{id}', name: 'app_descuento_show', methods: ['GET'])]
    public function show(Descuento $descuento): Response
    {
        return $this->render('descuento/show.html.twig', [
            'descuento' => $descuento,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_descuento_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Descuento $descuento, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DescuentoType::class, $descuento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_descuento_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('descuento/edit.html.twig', [
            'descuento' => $descuento,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_descuento_delete', methods: ['POST'])]
    public function delete(Request $request, Descuento $descuento, DescuentoService $descuentoService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$descuento->getId(), $request->getPayload()->getString('_token'))) {
            $descuentoService->delete($descuento);
        }

        return $this->redirectToRoute('app_descuento_index', [], Response::HTTP_SEE_OTHER);
    }
}
