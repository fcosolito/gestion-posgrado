<?php

namespace App\Controller;

use App\Entity\Curso;
use App\Form\EjemploCursoType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EjemploCursoController extends AbstractController
{
    #[Route('/curso/nuevo', name: 'curso_nuevo')]
    public function nuevo(Request $request): Response
    {
        $cursos = [];

        for ($i = 0; $i < 10; $i++) {
            $curso = new Curso();
            $curso->setNombre("Curso ".$i);

            $cursos[] = $curso;
        }

        $form = $this->createForm(EjemploCursoType::class, null, [
            "cursos" => $cursos,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            dd($form->getData());
        }

        return $this->render('curso/nuevo.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/api/dictados_por_curso/{id}', name: 'api_dictados_por_curso')]
    public function dictadosPorCurso(int $id): Response
    {
        $dictados = [];

        for ($i = 0; $i < 10; $i++) {
            $dict = [
                "id" => $i,
                "nombre" => "Dictado ".$i
            ];

            $dictados[] = $dict;
        }

        return $this->json($dictados);
    }
}