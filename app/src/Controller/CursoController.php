<?php

namespace App\Controller;

use App\Entity\Curso;
use App\Entity\Docente;
use App\Entity\Edicion;
use App\Form\CursoSearchType;
use App\Form\CursoType;
use App\Repository\CarreraRepository;
use App\Repository\CursoRepository;
use App\Repository\DictaRepository;
use App\Repository\DocenteRepository;
use App\Repository\EdicionRepository;
use App\Repository\InscripcionEdicionRepository;
use App\Repository\PerteneceARepository;
use App\Service\CursoService;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/curso')]
final class CursoController extends AbstractController
{
    #[Route(name: 'app_curso_index', methods: ['GET', 'POST'])]
    public function index(Request $request, CursoRepository $cursoRepository): Response
    {
        $form = $this->createForm(CursoSearchType::class);
        $form->handleRequest($request);

        $criteria = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (!empty($data['nombre'])) {
                $criteria['nombre'] = $data['nombre'];
            }
            if (!empty($data['horas'])) {
                $criteria['horas'] = $data['horas'];
            }
            if (!empty($data['ordenanza'])) {
                $criteria['ordenanza'] = $data['ordenanza'];
            }
            if (!empty($data['implementacion'])) {
                $criteria['implementacion'] = $data['implementacion'];
            }
        }

        $cursos = $cursoRepository->search($criteria);

        return $this->render('curso/index.html.twig', [
            'cursos' => $cursos,
            'form' => $form
        ]);
    }

    #[Route('/new', name: 'app_curso_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $curso = new Curso();
        $form = $this->createForm(CursoType::class, $curso);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($curso);
            $entityManager->flush();

            return $this->redirectToRoute('app_curso_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('curso/new.html.twig', [
            'curso' => $curso,
            'form' => $form,
        ]);
    }

    #[Route('/search', name: 'app_curso_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $entityManager): Response
    {
        $query =  $request->query->get("query", "");
        $cursos = $entityManager->getRepository(Curso::class)->searchXor($query);
        $cursos_ser = array_map(
            function ($c) {
                return (
                    [
                        "id" => $c->getId(),
                        "nombre" => $c->getNombre(),
                        "nroOrdenanza" => $c->getNroOrdenanza(),
                        "nroImplementacion" => $c->getNroImplementacion(),
                        "horas" => $c->getHoras(),
                    ]
                    );
            },
            $cursos
        );

        return $this->json($cursos_ser);
    }

    #[Route('/{id}', name: 'app_curso_show', methods: ['GET', 'POST'])]
    public function show(EdicionRepository $edicionRepository, PerteneceARepository $perteneceARepository, InscripcionEdicionRepository $inscRepository, Curso $curso): Response
    {
        $ediciones_ser = array_map(
            function ($e) use ($inscRepository) {
                return [
                    "id" => $e->getId(),
                    "nombre" => $e->getNombre(),
                    "fechaInicio" => $e->getFechaInicio()->format("d-m-Y"),
                    "fechaFin" => $e->getFechaFin() ? $e->getFechaFin()->format("d-m-Y") : "",
                    "precio" => $e->getPrecio(),
                    "inscripcionesCount" => count($inscRepository->findBy(["edicion" => $e])),
                ];
            },
            $edicionRepository->findBy(["curso" => $curso], ["fechaInicio" => "ASC"])
        );
        $carreras_ser = array_map(
            function ($p) {
                $carrera = $p->getCarrera();
                return [
                    "id" => $carrera->getId(),
                    "nombre" => $carrera->getNombre(),
                    "nroOrdenanza" => $carrera->getNroOrdenanza(),
                    "nroImplementacion" => $carrera->getNroImplementacion(),
                    "esElectivo" => $p->isEsElectivo(),
                ];
            },
            $perteneceARepository->findBy(["curso" => $curso])
        );
        $curso_ser = [
            "id" => $curso->getId(),
            "nombre" => $curso->getNombre(),
            "nroOrdenanza" => $curso->getNroOrdenanza(),
            "nroImplementacion" => $curso->getNroImplementacion(),
            "horas" => $curso->getHoras(),
        ];

        return $this->render('curso/show.html.twig', [
            'curso' => $curso_ser,
            'ediciones' => $ediciones_ser,
            'carreras' => $carreras_ser,
        ]);
    }

    #[Route('/{id}', name: 'api_curso_update', methods: ['PUT', 'PATCH'])]
    public function update(Curso $curso, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['error' => 'JSON inválido'], 400);
        }

        if (isset($data['nombre'])) $curso->setNombre($data['nombre']);
        if (isset($data['nroOrdenanza'])) $curso->setNroOrdenanza($data['nroOrdenanza']);
        if (isset($data['nroImplementacion']))   $curso->setNroImplementacion($data['nroImplementacion']);
        if (isset($data['horas']))   $curso->setHoras($data['horas']);

        $em->flush();

        return $this->json([
            'success' => true,
            'curso' => [
                'id' => $curso->getId(),
                'nombre' => $curso->getNombre(),
                'nroOrdenanza' => $curso->getNroOrdenanza(),
                'nroImplementacion' => $curso->getNroImplementacion(),
                'horas' => $curso->getHoras(),
            ]
        ]);
    }
    

    #[Route('/{id}/delete', name: 'app_curso_delete', methods: ['POST'])]
    public function delete(Request $request, Curso $curso, CursoService $cursoService, LoggerInterface $logger): Response
    {
        $logger->info("Ejecucion de controler/delete");
        if ($this->isCsrfTokenValid('delete'.$curso->getId(), $request->getPayload()->getString('_token'))) {
            $logger->info("Token valido");
            $cursoService->delete($curso);
        }

        return $this->redirectToRoute('app_curso_index', [], Response::HTTP_SEE_OTHER);
    }
}
