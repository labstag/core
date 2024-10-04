<?php

namespace Labstag\Controller;

use Labstag\Service\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(
        FileService $fileService
    ): Response
    {
        $fileService->all();

        return $this->render(
            'home/index.html.twig',
            ['controller_name' => 'HomeController']
        );
    }
}
