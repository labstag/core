<?php

namespace Labstag\Controller\Admin;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Factory\AdminContextFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatableMessage;
use Throwable;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/messenger')]
class MessengerController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected AdminContextFactory $adminContextFactory,
        protected DashboardController $dashboardController,
    )
    {
    }

    #[Route(
        '/delete/{id}',
        name: 'admin_messenger_delete',
        requirements: ['id' => '\d+']
    )]
    public function delete(int $id): Response
    {
        $this->connection()->delete(
            'messenger_messages',
            ['id' => $id]
        );
        $this->addFlash('success', new TranslatableMessage('Message deleted.'));

        return $this->redirectToRoute('admin_messenger_index');
    }

    #[Route('', name: 'admin_messenger_index')]
    public function index(Request $request): Response
    {
        if ('/admin/messenger' === $request->getPathInfo()) {
            return $this->redirect('/admin?routeName=admin_messenger_index');
        }

        $context = $this->adminContextFactory->create($request, $this->dashboardController, null);
        $request->attributes->set('ea', $context);

        try {
            $rows = $this->connection()->fetchAllAssociative(
                'SELECT id, queue_name, body, created_at, available_at, delivered_at FROM messenger_messages ORDER BY created_at DESC'
            );
        } catch (Throwable) {
            $rows = [];
        }

        return $this->render(
            'admin/messenger/index.html.twig',
            ['rows' => $rows]
        );
    }

    protected function connection(): Connection
    {
        return $this->entityManager->getConnection();
    }
}
