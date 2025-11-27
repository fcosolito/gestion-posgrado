<?php

namespace App\Controller;

use App\Entity\Nota;
use App\Entity\InscripcionEdicion;
use App\Entity\DocumentacionNota;
use App\Form\NotaType;
use App\Repository\NotaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nota')]
final class NotaController extends AbstractController
{
    #[Route(name: 'app_nota_index', methods: ['GET'])]
    public function index(NotaRepository $notaRepository): Response
    {
        return $this->render('nota/index.html.twig', [
            'notas' => $notaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_nota_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $notum = new Nota();
        $form = $this->createForm(NotaType::class, $notum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($notum);
            $entityManager->flush();

            return $this->redirectToRoute('app_nota_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('nota/new.html.twig', [
            'notum' => $notum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_nota_show', methods: ['GET'])]
    public function show(Nota $notum): Response
    {
        return $this->render('nota/show.html.twig', [
            'notum' => $notum,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_nota_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Nota $notum, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NotaType::class, $notum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_nota_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('nota/edit.html.twig', [
            'notum' => $notum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_nota_delete', methods: ['POST'])]
    public function delete(Request $request, Nota $notum, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$notum->getId(), $request->getPayload()->getString('_token'))) {
            try {
                
                // 2. Eliminar el archivo físico y la entidad DocumentacionNota
                $documentacion = $notum->getDocumentacionNota();
                if ($documentacion) {
                    $uploadDir = $this->getParameter('documentos_notas_directory');
                    $archivoPath = $uploadDir . '/' . $documentacion->getArchivo();
                    
                    // Eliminar archivo físico si existe
                    if (file_exists($archivoPath)) {
                        unlink($archivoPath);
                    }
                    
                    // Desvincular y eliminar entidad DocumentacionNota
                    $notum->setDocumentacionNota(null);
                    $entityManager->persist($notum);
                    $entityManager->flush(); // Flush to update FK before deleting
                    $entityManager->remove($documentacion);
                }
                
                // 3. Eliminar la nota
                $entityManager->remove($notum);
                $entityManager->flush();
                
                $this->addFlash('notice', 'Nota y documentación eliminadas correctamente.');
                
            } catch (\Exception $e) {
                $this->addFlash('error', `Error al eliminar la nota.`);
                // En producción se puede loguear el error real internamente
                //$this->logger->error('Error eliminando nota: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token de seguridad inválido.');
        }

        // Redirección según el contexto
        $redirectTo = $request->request->get('redirect_to');
        
        if ($redirectTo === 'alumno') {
            $alumnoId = $request->request->get('alumno_id');
            if ($alumnoId) {
                return $this->redirectToRoute('app_alumno_notas', ['id' => $alumnoId], Response::HTTP_SEE_OTHER);
            }
        } elseif ($redirectTo === 'edicion') {
            $edicionId = $request->request->get('edicion_id');
            if ($edicionId) {
                return $this->redirectToRoute('app_edicion_notas', ['id' => $edicionId], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->redirectToRoute('app_nota_index', [], Response::HTTP_SEE_OTHER);
    }
}
