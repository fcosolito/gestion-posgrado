<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PruebaController extends AbstractController {
    #[Route('/alumno', name: 'app_alumnos')]
    public function alumnos(): Response
    {
        return $this->render('base.html.twig', [
            "currentPage" => "/alumno"
        ]);
    }
    #[Route('/carrera', name: 'app_carreras')]
    public function carreras(): Response
    {
        return $this->render('base.html.twig', [
            "currentPage" => "/carrera"
        ]);
    }
    #[Route('/curso', name: 'app_cursos')]
    public function cursos(): Response
    {
        return $this->render('base.html.twig', [
            "currentPage" => "/curso"
        ]);
    }
    #[Route('/pago', name: 'app_pagos')]
    public function pagos(): Response
    {
        return $this->render('base.html.twig', [
            "currentPage" => "/pago"
        ]);
    }
}
