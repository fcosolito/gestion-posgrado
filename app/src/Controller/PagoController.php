<?php

namespace App\Controller;

use App\Entity\Comprobante;
use App\Entity\Pago;
use App\Entity\PagoCuota;
use App\Entity\Alumno;
use App\Entity\Cuota;
use App\Form\PagoType;
use App\Form\PagoSearchType;
use App\Repository\PagoRepository;
use App\Repository\CuotaRepository;
use App\Repository\AlumnoRepository;
use App\Service\PagoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/pago')]
final class PagoController extends AbstractController
{
    #[Route(name: 'app_pago_index', methods: ['GET'])]
    public function index(Request $request, PagoRepository $pagoRepository): Response
    {
        $searchForm = $this->createForm(PagoSearchType::class);
        $searchForm->handleRequest($request);

        $pagos = [];
        
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = $searchForm->getData();
            $pagos = $pagoRepository->search($data);
        } else {
            $pagos = $pagoRepository->findAllWithRelations();
        }

        return $this->render('pago/index.html.twig', [
            'pagos' => $pagos,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/new', name: 'app_pago_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PagoService $pagoS, EntityManagerInterface $entityManager, AlumnoRepository $alumnoRepository): Response
    {
        $pago = new Pago();
        $form = $this->createForm(PagoType::class, $pago);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // 2. Obtener cuotas seleccionadas del campo del formulario
                $cuotasSeleccionadasJson = $form->get('cuotasSeleccionadas')->getData();
                
                // Si está vacío, intentar del request directo
                if (empty($cuotasSeleccionadasJson)) {
                    // Usar all() para obtener arrays, ya que get() solo funciona con valores escalares
                    $allRequestData = $request->request->all();
                    $pagoData = $allRequestData['pago'] ?? [];
                    if (isset($pagoData['cuotasSeleccionadas']) && $pagoData['cuotasSeleccionadas'] !== '') {
                        $cuotasSeleccionadasJson = $pagoData['cuotasSeleccionadas'];
                    } else {
                        $cuotasSeleccionadasJson = '[]';
                    }
                }
                
                $cuotasSeleccionadasIds = json_decode($cuotasSeleccionadasJson, true) ?? [];
                
                if (empty($cuotasSeleccionadasIds)) {
                    $this->addFlash('error', 'Debe seleccionar al menos una cuota para pagar.');
                    return $this->render('pago/new.html.twig', [
                        'pago' => $pago,
                        'form' => $form,
                        'alumnos' => $alumnoRepository->findAll(),
                    ]);
                }

                // 3. Buscar las cuotas en la base de datos
                $cuotasSeleccionadas = [];
                foreach ($cuotasSeleccionadasIds as $cuotaId) {
                    $cuota = $entityManager->getRepository(Cuota::class)->find($cuotaId);
                    if ($cuota) {
                        $cuotasSeleccionadas[] = $cuota;
                    }
                }

                // 4. Procesar comprobante
                $archivoComprobante = $form->get('archivoComprobante')->getData();
                if ($archivoComprobante) {
                    $resultado = $pagoS->newComprobante(new Comprobante(), $archivoComprobante, $this->getParameter('comprobantes_directory'));
                    $resultado["estado"] === "exito" ? 
                        $pago->setComprobante($resultado["comprobante"]) :
                        $this->addFlash('error', 'Error al registrar el comprobante: ' . $resultado["exception"]);
                }
                $resultado = $pagoS->new($pago, $cuotasSeleccionadas);

                $resultado["estado"] === "exito" ? 
                    $this->addFlash('success', 'Pago registrado correctamente para ' . count($cuotasSeleccionadas) . ' cuotas.') :
                    $this->addFlash('error', 'Error al registrar el pago: ' . $resultado["exception"]);
                
                return $this->redirectToRoute('app_pago_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('pago/new.html.twig', [
            'pago' => $pago,
            'form' => $form,
            'alumnos' => $alumnoRepository->findAll(),
        ]);
    }

    #[Route('/search', name: 'app_pago_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $entityManager): Response
    {
        $query =  $request->query->get("query", "");
        $pagos = $entityManager->getRepository(Pago::class)->searchXor($query);
        $pagos_ser = array_map(
            function (Pago $p) {
                return (
                    [
                        "id" => $p->getId(),
                        "monto" => $p->getMonto(),
                        "fechaPago" => $p->getFechaPago()->format("Y-m-d"),
                    ]
                    );
            },
            $pagos
        );

        return $this->json($pagos_ser);
    }

    #[Route('/api/cuotas-pendientes/{alumnoId}', name: 'app_pago_cuotas_pendientes', methods: ['GET'])]
    public function getCuotasPendientes(int $alumnoId, CuotaRepository $cuotaRepository): JsonResponse
    {
        try {
            // Verificar que el alumnoId sea válido
            if ($alumnoId <= 0) {
                return $this->json([
                    'success' => false,
                    'error' => 'ID de alumno inválido',
                    'cuotas' => []
                ], 400);
            }

            $cuotasPendientes = $cuotaRepository->findCuotasPendientesByAlumno($alumnoId);

            $data = [];
            foreach ($cuotasPendientes as $cuota) {
                $concepto = '';
                $tipo = '';
                
                if ($cuota->getInscripcionCarrera()) {
                    $tipo = 'Carrera';
                    $concepto = $cuota->getInscripcionCarrera()->getCarrera()->getNombre();
                } elseif ($cuota->getInscripcionEdicion()) {
                    $tipo = 'Edición';
                    $concepto = $cuota->getInscripcionEdicion()->getEdicion()->getNombre();
                } else {
                    // Si no tiene ninguna relación, saltar esta cuota
                    continue;
                }
                
                $data[] = [
                    'id' => $cuota->getId(),
                    'numero_cuota' => $cuota->getNumeroCuota(),
                    'tipo' => $tipo,
                    'concepto' => $concepto,
                    'descripcion' => sprintf('Cuota %d - %s', $cuota->getNumeroCuota(), $concepto)
                ];
            }
            
            return $this->json([
                'success' => true,
                'cuotas' => $data
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error interno del servidor: ' . $e->getMessage(),
                'cuotas' => []
            ], 500);
        }
    }
    #[Route('/{id}', name: 'app_pago_show', methods: ['GET'])]
    public function show(Pago $pago): Response
    {
        return $this->render('pago/show.html.twig', [
            'pago' => $pago,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pago_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pago $pago, PagoService $pagoS, EntityManagerInterface $entityManager): Response
    {
        // Guardar el nombre del archivo antiguo antes de procesar el formulario
        $archivoAntiguo = null;
        $comprobanteAntiguo = $pago->getComprobante();
        if ($comprobanteAntiguo) {
            $archivoAntiguo = $comprobanteAntiguo->getArchivo();
        }

        $form = $this->createForm(PagoType::class, $pago);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Manejar el archivo del comprobante
            $archivoComprobante = $form->get('archivoComprobante')->getData();
            $comprobante = $pago->getComprobante() ?? new Comprobante();
            
            if ($archivoComprobante) {
                $resultado = $pagoS->updateComprobante($comprobante, $archivoComprobante, $archivoAntiguo, $this->getParameter('comprobantes_directory'), new Filesystem());

                if (!($resultado["estado"] === "exito")) {
                    $this->addFlash('error', 'Error al actualizar comprobante.');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Pago actualizado correctamente.');
            return $this->redirectToRoute('app_pago_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pago/edit.html.twig', [
            'pago' => $pago,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/asoc-cuota/{idCuota}', name: 'app_pago_asociar_cuota', methods: ['PUT'])]
    public function asociarCuota(Request $request, Pago $pago, Cuota $idCuota, PagoService $pagoS): Response
    {
        $data = $request->toArray();
        $montoCuota = $data['montoCuota'] ?? 0;

        $resultado = $pagoS->asociarCuota($pago, $idCuota, $montoCuota);
        $resultado["estado"] === "exito" ?
            $this->addFlash('notice', 'Se asocio el pago a la cuota exitosamente.') :
            $this->addFlash('error', 'Error al asociar el pago: ' . $resultado["exception"]);

        return $this->json([
            "pagoCuota" => [
                "pago" => $pago->getId(),
                "cuota" => $idCuota->getId(),
            ],
        ]);       
    }

    #[Route('/{id}/desasoc-cuota/{idCuota}', name: 'app_pago_desasociar_cuota', methods: ['PUT'])]
    public function desasociarCuota(Request $request, Pago $pago, Cuota $idCuota, PagoService $pagoS): Response
    {
        $resultado = $pagoS->desasociarCuota($pago, $idCuota);

        $resultado["estado"] === "exito" ?
            $this->addFlash('notice', 'Se desasocio el pago de la cuota exitosamente.') :
            $this->addFlash('error', 'Error al desasociar el pago: ' . $resultado["exception"]);

        return $this->json([
            "success" => true,
        ]);
    }

    
    #[Route('/{id}', name: 'app_pago_delete', methods: ['POST'])]
    public function delete(Request $request, Pago $pago, PagoService $pagoS): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pago->getId(), $request->getPayload()->getString('_token'))) {
            
            $resultado = $pagoS->delete($pago, $this->getParameter('comprobantes_directory'));
            
            $resultado["estado"] === "exito" ?
                $this->addFlash('notice', 'Pago eliminado exitosamente.') :
                $this->addFlash('error', 'Error al eliminar el pago: ' . $resultado["exception"]);
        }

        return $this->redirectToRoute('app_pago_index', [], Response::HTTP_SEE_OTHER);
    }
}
