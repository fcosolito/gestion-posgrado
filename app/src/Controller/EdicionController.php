<?php

namespace App\Controller;

use App\Entity\Alumno;
use App\Entity\Cuota;
use App\Entity\Curso;
use App\Entity\Descuento;
use App\Entity\Dicta;
use App\Entity\Docente;
use App\Entity\Edicion;
use App\Entity\Nota;
use App\Form\NotaType;
use App\Entity\InscripcionEdicion;
use App\Form\EdicionType;
use App\Repository\CuotaRepository;
use App\Repository\DescuentoRepository;
use App\Repository\DictaRepository;
use App\Repository\EdicionRepository;
use App\Repository\InscripcionEdicionRepository;
use App\Repository\NotaRepository;
use App\Service\EdicionService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Date;
use Dompdf\Dompdf;

#[Route('/edicion')]
final class EdicionController extends AbstractController
{
    #[Route('/{cursoId}/new', name: 'app_edicion_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EdicionService $edicionS, Curso $cursoId): Response
    {
        $edicion = new Edicion();
        $form = $this->createForm(EdicionType::class, $edicion);
        $form->handleRequest($request);

        // cursoId en este punto es la entidad, debe tener ese nombre para
        // que symfony asocie el argumento del metodo al parametro de la ruta
        if ($form->isSubmitted() && $form->isValid() && $cursoId) {
            $resultado = $edicionS->new($edicion, $cursoId);

            $resultado["estado"] === "exito" ? 
                $this->addFlash('notice', "Edicion guardada exitosamente") :
                $this->addFlash('notice', "Error al guardar edicion: ".$resultado["exception"]);

            return $this->redirectToRoute('app_curso_show', ["id" => $cursoId->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('edicion/new.html.twig', [
            'edicion' => $edicion,
            'curso' => $cursoId,
            'form' => $form,
        ]);
    }

    #[Route('/{cursoId}/find-by-curso', name: 'api_edicion_by_curso', methods: ['GET'])]
    public function findByCurso(Curso $cursoId, EdicionRepository $edicionRepository): Response
    {
        $ediciones = $edicionRepository->findBy(["curso" => $cursoId]);

        $ediciones_ser = array_map(
            function (Edicion $ed) {
                return [
                    "id" => $ed->getId(),
                    "nombre" => $ed->getNombre(),
                    "fechaInicio" => $ed->getFechaInicio()->format("Y-m-d"),
                    "fechaFin" => $ed->getFechaFin()->format("Y-m-d"),
                    "precio" => $ed->getPrecio(),
                ];
            }, $ediciones
        );

        return $this->json($ediciones_ser);
    }

    #[Route('/{id}', name: 'app_edicion_show', methods: ['GET'])]
    public function show(Edicion $edicion, DictaRepository $dictaRepository, InscripcionEdicionRepository $inscripcionRepository, DescuentoRepository $descuentoRepository, NotaRepository $notaRepository, CuotaRepository $cuotaRepository): Response
    {
        $docentes_ser = array_map(
            function ($dicta) {
                $docente = $dicta->getDocente();

                return [
                    "id" => $docente->getId(),
                    "nombre" => $docente->getNombre(),
                    "apellido" => $docente->getApellido(),
                    "dni" => $docente->getDni(),
                    "esFirmante" => $dicta->isEsFirmante(),
                ];
            },
            $dictaRepository->findBy(["edicion" => $edicion])
        );

        $alumnos_ser = array_map(
            function (InscripcionEdicion $i) use ($notaRepository, $cuotaRepository) {
                $alumno = $i->getAlumno();

                return [
                    "id" => $alumno->getId(),
                    "nombre" => $alumno->getNombre(),
                    "apellido" => $alumno->getApellido(),
                    "dni" => $alumno->getDni(),
                    "descuento" => $i->getDescuento() ? $i->getDescuento()->getId() : null,
                    "nroLegajo" => $i->getNroLegajo() ?? null,
                    "fechaInscripcion" => $i->getFechaInscripcion() ? $i->getFechaInscripcion()->format("Y-m-d") : null,
                    "inscripcion" => $i->getId(),
                    "notasCount" => count($notaRepository->findBy(["inscripcionEdicion" => $i])),
                    "cuotas" => array_map(
                        function (Cuota $cuota) {
                            return [
                                "estado" => $cuota->getEstado(),
                                "numero" => $cuota->getNumeroCuota(),
                            ];
                        },
                        $cuotaRepository->findBy(["inscripcionEdicion" => $i])
                    ),
                ];
            },
            $inscripcionRepository->findBy(["edicion" => $edicion])
        );

        $edicion_ser = [
            "id" => $edicion->getId(),
            "nombre" => $edicion->getNombre(),
            "fechaInicio" => $edicion->getFechaInicio()->format("Y-m-d"),
            "fechaFin" => $edicion->getFechaFin()->format("Y-m-d"),
            "precio" => $edicion->getPrecio(),
        ];

        $descuentos_ser = array_map(
            function (Descuento $d) {
                return [
                    "id" => $d->getId(),
                    "descripcion" => $d->getDescripcion(),
                    "valor" => $d->getValor(),
                ];
            },
            $descuentoRepository->findAll()
        );

        return $this->render('edicion/show.html.twig', [
            'edicion' => $edicion_ser,
            'curso' => $edicion->getCurso(),
            'docentes' => $docentes_ser,
            'alumnos' => $alumnos_ser,
            'descuentos' => $descuentos_ser,
        ]);
    }


    #[Route('/{id}', name: 'api_edicion_update', methods: ['PUT'])]
    public function update(Request $request, Edicion $edicion, EdicionService $edicionS): Response
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['error' => 'JSON inválido'], 400);
        }

        $resultado = $edicionS->update($edicion, $data);

        $resultado["estado"] === "exito" ? 
            $this->addFlash('notice', "Edicion guardada exitosamente") :
            $this->addFlash('notice', "Error al guardar la edicion");


        return $this->json([
            'success' => true,
            'edicion' => [
                'id' => $edicion->getId(),
                'nombre' => $edicion->getNombre(),
                'fechaInicio' => $edicion->getFechaInicio(),
                'fechaFin' => $edicion->getFechaFin(),
                'precio' => $edicion->getPrecio(),
            ]
        ]);
    }

    #[Route('/{id}', name: 'app_edicion_delete', methods: ['POST'])]
    public function delete(Request $request, Edicion $edicion, EdicionService $edicionS): Response
    {
        if ($this->isCsrfTokenValid('delete'.$edicion->getId(), $request->getPayload()->getString('_token'))) {
            $resultado = $edicionS->delete($edicion);
        }

        $resultado["estado"] === "exito" ? 
            $this->addFlash('notice', "Edicion eliminada exitosamente") :
            $this->addFlash('notice', "Error al eliminar edicion :".$resultado["exception"]);

        return $this->redirectToRoute('app_curso_show', ["id" => $edicion->getCurso()->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/notas', name: 'app_edicion_notas', methods: ['GET', 'POST'])]
    public function notas(Request $request, Edicion $edicion, EntityManagerInterface $entityManager): Response
    {

        $notum = new Nota();
        $form = $this->createForm(NotaType::class, $notum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($notum);
            $entityManager->flush();

            return $this->redirectToRoute('app_edicion_notas', ['id' => $edicion->getId()], Response::HTTP_SEE_OTHER);
        }

        // Obtener inscripciones y notas en una sola consulta optimizada
        $inscripciones = $entityManager->getRepository(\App\Entity\InscripcionEdicion::class)
            ->createQueryBuilder('ie')
            ->select('ie', 'a')
            ->innerJoin('ie.alumno', 'a')
            ->where('ie.edicion = :edicion')
            ->setParameter('edicion', $edicion)
            ->getQuery()
            ->getResult();

        // Obtener las notas de esta edición
        $notas = $entityManager->createQueryBuilder()
            ->select('n', 'ie', 'a')
            ->from(\App\Entity\Nota::class, 'n')
            ->innerJoin('n.inscripcionEdicion', 'ie')
            ->innerJoin('ie.alumno', 'a')
            ->where('ie.edicion = :edicion')
            ->setParameter('edicion', $edicion)
            ->getQuery()
            ->getResult();

        // Preparar datos para el template de notas
        $notasData = [];
        
        foreach ($notas as $nota) {
            $inscripcion = $nota->getInscripcionEdicion();
            $alumno = $inscripcion->getAlumno();
            
             // Generar enlace para el archivo si existe
            $documentacionHtml = 'Sin documentación';
            if ($nota->getDocumentacionNota()) {
                $archivo = $nota->getDocumentacionNota()->getArchivo();
                $documentacionHtml = sprintf(
                    '<a href="/uploads/documentos_notas/%s" target="_blank" class="btn-documento">Ver archivo</a>',
                    $archivo
                );
            }

            $notasData[] = [
                'id' => $nota->getId(),
                'alumno' => $alumno->getNombre() . ' ' . $alumno->getApellido(),
                'nota' => $nota->getValor(),
                'descripcion' => $nota->getDescripcion(),
                'fecha_carga' => $nota->getFechaCarga() ? $nota->getFechaCarga()->format('d/m/Y') : 'N/A',
                'documentacion' => $documentacionHtml,
            ];
        }

        return $this->render('edicion/notas_edicion.html.twig', [
            'edicion' => $edicion,
            'curso' => $edicion->getCurso(),
            'notas' => $notasData,
            'inscripciones' => $inscripciones,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/notas/pdf', name: 'app_edicion_notas_pdf', methods: ['GET'])]
    public function notasPdf(Edicion $edicion, EntityManagerInterface $entityManager): Response
    {
        // Obtener las notas de esta edición
        $notas = $entityManager->createQueryBuilder()
            ->select('n', 'ie', 'a')
            ->from(\App\Entity\Nota::class, 'n')
            ->innerJoin('n.inscripcionEdicion', 'ie')
            ->innerJoin('ie.alumno', 'a')
            ->where('ie.edicion = :edicion')
            ->setParameter('edicion', $edicion)
            ->getQuery()
            ->getResult();

        // Preparar datos para el template de notas
        $notasData = [];
        
        foreach ($notas as $nota) {
            $inscripcion = $nota->getInscripcionEdicion();
            $alumno = $inscripcion->getAlumno();
            
            $notasData[] = [
                'id' => $nota->getId(),
                'alumno' => $alumno->getNombre() . ' ' . $alumno->getApellido(),
                'nota' => $nota->getValor(),
                'descripcion' => $nota->getDescripcion(),
                'fecha_carga' => $nota->getFechaCarga() ? $nota->getFechaCarga()->format('d/m/Y') : 'N/A',
            ];
        }

        // Renderizar HTML para el PDF
        $fechaEmision = new \DateTime('now', new \DateTimeZone('America/Argentina/Buenos_Aires'));
        $html = $this->renderView('edicion/pdf_notas.html.twig', [
            'edicion' => $edicion,
            'notas' => $notasData,
            'fecha_emision' => $fechaEmision
        ]);

        // Generar PDF con Dompdf
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Retornar PDF como descarga
        $fecha = $fechaEmision->format('d-m-Y');
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="notas_' . $edicion->getNombre() . '_' . $fecha . '.pdf"'
        ]);
    }

    #[Route('/{id}/asoc-docente/{idDocente}', name: 'api_edicion_asociar_docente', methods: ['PUT'])]
    public function asociarDocente(Request $request, Edicion $edicion, Docente $idDocente, EdicionService $edicionS): Response
    {
        // Validacion:
        // Comprobar que la edicion y el docente existen
        // lo hace symfony al usar variables de path

        // En el body de la request puede pasarse "esFirmante: true"
        // para modificar la condicion o setearla por primera vez.
        // Se asume falso si no se indica.
        $data = $request->toArray();
        $esFirmante = $data['esFirmante'];

        $resultado = $edicionS->asociarDocente($edicion, $idDocente, $esFirmante ? true : false);

        $resultado["estado"] === "exito" ? $this->addFlash('notice', "Asociacion guardada exitosamente") :
            $this->addFlash('notice', "Error al asociar docente a edicion");

        $dicta = $resultado["dicta"];
        return $this->json([
            "dicta" => [
                "docente" => $dicta->getDocente()->getNombre(),
                "edicion" => $dicta->getEdicion()->getNombre(),
            ],
        ]);
    }

    #[Route('/{id}/desasoc-docente/{idDocente}', name: 'api_edicion_desasociar_docente', methods: ['PUT'])]
    public function desasociarDocente(Request $request, Edicion $edicion, Docente $idDocente, EntityManagerInterface $entityManager): Response
    {
        // Validacion:
        // Comprobar que la edicion y el docente existen
        // lo hace symfony al usar variables de path

        $dictaRepository = $entityManager->getRepository(Dicta::class);
        $dicta = $dictaRepository->findOneBy(["docente" => $idDocente, "edicion" => $edicion]) ?? new Dicta();

        $entityManager->remove($dicta);
        $entityManager->flush();

        $this->addFlash('notice', "Asociacion eliminada exitosamente");

        return $this->json([
            "success" => true,
        ]);
    }

    #[Route('/{id}/insc-alumno/{idAlumno}', name: 'api_edicion_inscribir_alumno', methods: ['PUT'])]
    public function inscribirAlumno(
            Request $request,
            Alumno $idAlumno,
            Edicion $edicion,
            EdicionService $edicionS
        ): Response
    {
        $data = $request->toArray();
        $resultado = $edicionS->inscribirAlumno($idAlumno, $edicion, $data);

        $resultado["estado"] === "exito" ? 
            $this->addFlash('notice', "Inscripcion guardada exitosamente") :
            $this->addFlash('notice', "Error al inscribir alumno");


        return $this->json(["success" => true]);

    }

    #[Route('/{id}/desinsc-alumno/{idAlumno}', name: 'api_edicion_desinscribir_alumno', methods: ['PUT'])]
    public function desinscribirEdicionApi(
            Alumno $idAlumno,
            Edicion $edicion,
            EdicionService $edicionS
        ): Response
    {
        $resultado =  $edicionS->desinscribirAlumno($idAlumno, $edicion);

        $resultado["estado"] === "exito" ? 
            $this->addFlash('notice', "Inscripcion eliminada exitosamente") :
            $this->addFlash('notice', "Error al eliminar inscripción");

        return $this->json(["success" => true]);
    }

    #[Route('/{id}/edit-insc/{inscripcion}', name: 'api_edicion_editar_inscripcion', methods: ['PUT'])]
    public function editarInscripcion(
            Request $request,
            InscripcionEdicion $inscripcion,
            Edicion $edicion,
            EdicionService $edicionS
        ): Response
    {
        $data = $request->toArray();

        $resultado = $edicionS->editarInscripcion($inscripcion, $edicion, $data);
            
        $resultado["estado"] === "exito" ?
            $this->addFlash('notice', "Inscripción guardada exitosamente") :
            $this->addFlash('notice', "Error al guardar inscripción");

        return $this->json(["success" => true]);

    }
}
