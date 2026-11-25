<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatableMessage;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/messenger')]
class MessengerController extends AbstractController
{
    public function __construct(protected EntityManagerInterface $entityManager)
    {
    }

    protected function connection(): \Doctrine\DBAL\Connection
    {
        return $this->entityManager->getConnection();
    }

    #[Route('', name: 'admin_messenger_index')]
    public function index(): Response
    {
        $rows = $this->connection()->fetchAllAssociative(
            'SELECT id, queue_name, body, created_at, available_at, delivered_at FROM messenger_messages ORDER BY created_at DESC'
        );

        return $this->render('admin/messenger/index.html.twig', ['rows' => $rows]);
    }

    #[Route('/delete/{id}', name: 'admin_messenger_delete', requirements: ['id' => '\d+'])]
    public function delete(int $id, Request $request): Response
    {
        $this->connection()->delete('messenger_messages', ['id' => $id]);
        $this->addFlash('success', new TranslatableMessage('Message deleted.'));

        return $this->redirectToRoute('admin_messenger_index');
    }
}
