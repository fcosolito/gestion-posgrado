<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PruebaController extends AbstractController {
    #[Route('/base', name: 'app_base')]
    public function base(): Response
    {
        return $this->render('base.html.twig');
    }
}
