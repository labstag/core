<?php

namespace Labstag\Controller;

use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(
        #[Autowire(service: 'public.storage')]
        FilesystemOperator $filesystemOperator
    ): Response
    {
        dump($filesystemOperator);

        return $this->render(
            'home/index.html.twig',
            ['controller_name' => 'HomeController']
        );
    }
}
