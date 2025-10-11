<?php

namespace Labstag\Controller;

use Labstag\Service\NotifierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/webhooks')]
final class WebhooksController extends AbstractController
{
    #[Route('/', name: 'webhooks_discord')]
    public function index(NotifierService $notifier): Response
    {
        $notifier->sendMessage('test api '.date('Y-m-d H:i:s'));
        return new JsonResponse(['ok' => true]);
    }
}
