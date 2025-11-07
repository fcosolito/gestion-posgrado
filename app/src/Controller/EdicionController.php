<?php

namespace App\Controller;

use App\Entity\Curso;
use App\Entity\Descuento;
use App\Entity\Edicion;
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
                    "esFirmante" => $dicta->getEsFirmante(),
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
                    "descuento" => $i->getDescuento()->getValor(),
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
}
