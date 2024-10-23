<?php
namespace App\Controller\Backoffice;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_back_main_index', methods:['GET'])]
    public function index(): Response
    {
        return $this->render('main.html.twig');
    }
}