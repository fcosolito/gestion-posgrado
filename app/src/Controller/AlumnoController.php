<?php

namespace App\Controller;

use App\Entity\Alumno;
use App\Entity\InscripcionEdicion;
use App\Form\AlumnoType;
use App\Repository\AlumnoRepository;
use App\Repository\CarreraRepository;
use App\Repository\CursoRepository;
use App\Repository\EdicionRepository;
use App\Repository\InscripcionCarreraRepository;
use App\Repository\InscripcionEdicionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/alumno')]
final class AlumnoController extends AbstractController
{
    #[Route(name: 'app_alumno_index', methods: ['GET'])]
    public function index(AlumnoRepository $alumnoRepository): Response
    {
        return $this->render('alumno/index.html.twig', [
            'alumnos' => $alumnoRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_alumno_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $alumno = new Alumno();
        $form = $this->createForm(AlumnoType::class, $alumno);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($alumno);
            $entityManager->flush();

            return $this->redirectToRoute('app_alumno_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alumno/new.html.twig', [
            'alumno' => $alumno,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_alumno_show', methods: ['GET'])]
    public function show(Alumno $alumno): Response
    {
        return $this->render('alumno/show.html.twig', [
            'alumno' => $alumno,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_alumno_edit', methods: ['GET', 'POST'])]
    public function edit(
            Request $request,
            Alumno $alumno,
            EntityManagerInterface $entityManager,
            InscripcionCarreraRepository $inscripcionCarreraRepository,
            InscripcionEdicionRepository $inscripcionEdicionRepository,
            CarreraRepository $carreraRepository,
            CursoRepository $cursoRepository,
        ): Response
    {
        $form = $this->createForm(AlumnoType::class, $alumno);
        $form->handleRequest($request);

        $carreras = $inscripcionCarreraRepository -> findCarrerasByAlumnoId($alumno->getId());
        $cursos = $inscripcionEdicionRepository -> findCursosByAlumnoId($alumno->getId());
        $cuotasNoPagas = []; // esto debería rellenarlo
        $cuotasPagas = []; // esto debería rellenarlo

        // Buscamos las carreras y los cursos a los que el alumno no está inscripto para mostrar en las secciones de inscripciones.
        $carrerasIds = !empty($carreras) ? array_map(fn($c) => $c->getId(),$carreras) : []; 
        $cursosIds = !empty($cursos) ? array_map(fn($c) => $c->getId(),$cursos) : [];
        $carrerasNoInscripto = $carreraRepository->findNotInIds($carrerasIds);
        $cursosNoInscripto = $cursoRepository->findNotInIds($cursosIds);


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_alumno_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alumno/edit.html.twig', [
            'alumno' => $alumno,
            'form' => $form,
            'carreras' => $carreras,
            'cursos' => $cursos,
            'cuotasNoPagas' => $cuotasNoPagas,
            'cuotasPagas' => $cuotasPagas,
            'carrerasNoInscripto' => $carrerasNoInscripto,
            'cursosNoInscripto' => $cursosNoInscripto,
        ]);
    }

    #[Route('/{id}', name: 'app_alumno_delete', methods: ['POST'])]
    public function delete(Request $request, Alumno $alumno, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$alumno->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($alumno);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_alumno_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/inscribir-alumno-curso', name:'app_alumno_inscribir_curso', methods : ['POST'])]
    public function inscribirAlumnoACurso(
        Request $request,
        Alumno $alumno,
        EdicionRepository $edicionRepository,
        EntityManagerInterface $entityManager 
    ): Response
    {   
        $cursoId = $request->request->get('curso_id');
        
        // Buscar la última edición disponible
        $ultimaEdicion = $edicionRepository->findUltimaEdicionByCursoId($cursoId);
        
        // Verificar que existe una edición antes de crear la inscripción
        if (!$ultimaEdicion) {
            $this->addFlash(
                'error',
                '❌ No se pudo completar la inscripción. No hay ediciones disponibles para este curso.'
            );
            return $this->redirectToRoute('app_alumno_edit', ['id' => $alumno->getId()]);
        }
        
        // Crear la inscripción
        $inscripcionEdicion = new InscripcionEdicion();
        $inscripcionEdicion->setEdicion($ultimaEdicion);
        $inscripcionEdicion->setAlumno($alumno);
        $inscripcionEdicion->setDescuento(0);

        $entityManager->persist($inscripcionEdicion);
        $entityManager->flush();

        // Mostrar mensaje de confirmación
        if ($ultimaEdicion) {
            $this->addFlash(
                'success',
                sprintf(
                    '¡Inscripción exitosa!<br><br>
                    <strong>Curso:</strong> %s<br>
                    <strong>Edición:</strong> %s<br>
                    <strong>Fecha de inicio:</strong> %s<br>
                    <strong>Precio:</strong> $%s',
                    $ultimaEdicion->getCurso()->getNombre(),
                    $ultimaEdicion->getNombre(),
                    $ultimaEdicion->getFechaInicio()->format('d/m/Y'),
                    number_format($ultimaEdicion->getPrecio(), 2)
                )
            );
        } else {
            $this->addFlash(
                'warning',
                '¡Inscripción fallida!<br>No se encontró información detallada de la edición.'
            );
        }

        return $this->redirectToRoute('app_alumno_edit', [
            'id' => $alumno->getId(),
        ]);
    }
}
