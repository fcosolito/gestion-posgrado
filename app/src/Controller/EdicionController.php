<?php

namespace App\Controller;

use App\Entity\Curso;
use App\Entity\Descuento;
use App\Entity\Dicta;
use App\Entity\Docente;
use App\Entity\Edicion;
use App\Entity\Nota;
use App\Form\NotaType;
use App\Entity\InscripcionEdicion;
use App\Form\EdicionType;
use App\Repository\DescuentoRepository;
use App\Repository\DictaRepository;
use App\Repository\DocenteRepository;
use App\Repository\EdicionRepository;
use App\Repository\InscripcionEdicionRepository;
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
    public function new(Request $request, EntityManagerInterface $entityManager, Curso $cursoId): Response
    {
        $edicion = new Edicion();
        $form = $this->createForm(EdicionType::class, $edicion);
        $form->handleRequest($request);

        // cursoId en este punto es la entidad, debe tener ese nombre para
        // que symfony asocie el argumento del metodo al parametro de la ruta
        if ($form->isSubmitted() && $form->isValid() && $cursoId) {
            $edicion->setCurso($cursoId);
            $entityManager->persist($edicion);
            $entityManager->flush();

            return $this->redirectToRoute('app_curso_show', ["id" => $cursoId->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('edicion/new.html.twig', [
            'edicion' => $edicion,
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
    public function show(Edicion $edicion, DictaRepository $dictaRepository, InscripcionEdicionRepository $inscripcionRepository, DescuentoRepository $descuentoRepository): Response
    {
        $docentes_ser = array_map(
            function ($dicta) {
                $docente = $dicta->getDocente();

                return [
                    "id" => $docente->getId(),
                    "nombre" => $docente->getNombre(),
                    "apellido" => $docente->getApellido(),
                    "esFirmante" => $dicta->isEsFirmante(),
                ];
            },
            $dictaRepository->findBy(["edicion" => $edicion])
        );

        $alumnos_ser = array_map(
            function ($i) {
                $alumno = $i->getAlumno();

                return [
                    "id" => $alumno->getId(),
                    "nombre" => $alumno->getNombre(),
                    "apellido" => $alumno->getApellido(),
                    "dni" => $alumno->getDni(),
                    "descuento" => $i->getDescuento()->getId(),
                    "nota" => $i->getNota() ? $i->getNota()->getValor() : "",
                    "inscripcion" => $i->getId(),
                ];
            },
            $inscripcionRepository->findByEdicionConNota($edicion)
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
    public function update(Request $request, Edicion $edicion, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['error' => 'JSON inválido'], 400);
        }

        if (isset($data['nombre'])) $edicion->setNombre($data['nombre']);
        if (isset($data['fechaInicio'])) $edicion->setFechaInicio(new DateTime($data['fechaInicio']));
        if (isset($data['fechaFin']))   $edicion->setFechaFin(new DateTime($data['fechaFin']));
        if (isset($data['precio']))   $edicion->setPrecio($data['precio']);

        $entityManager->flush();

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
    public function delete(Request $request, Edicion $edicion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$edicion->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($edicion);
            $entityManager->flush();
        }

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
            
            $notasData[] = [
                'id' => $nota->getId(),
                'alumno' => $alumno->getNombre() . ' ' . $alumno->getApellido(),
                'nota' => $nota->getValor(),
                'descripcion' => $nota->getDescripcion(),
                'fecha_carga' => $nota->getFechaCarga() ? $nota->getFechaCarga()->format('d/m/Y') : 'N/A',
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
    public function asociarDocente(Request $request, Edicion $edicion, Docente $idDocente, EntityManagerInterface $entityManager): Response
    {
        // Validacion:
        // Comprobar que la edicion y el docente existen
        // lo hace symfony al usar variables de path

        $dictaRepository = $entityManager->getRepository(Dicta::class);
        $dicta = $dictaRepository->findOneBy(["docente" => $idDocente, "edicion" => $edicion]) ?? new Dicta();

        $dicta->setDocente($idDocente);
        $dicta->setEdicion($edicion);

        // En el body de la request puede pasarse "esFirmante: true"
        // para modificar la condicion o setearla por primera vez.
        // Se asume falso si no se indica.
        $data = $request->toArray();
        $esFirmante = $data['esFirmante'];

        $dicta->setEsFirmante($esFirmante ? true : false);

        $entityManager->persist($dicta);
        $entityManager->flush();

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

        return $this->json([
            "success" => true,
        ]);
    }
}
