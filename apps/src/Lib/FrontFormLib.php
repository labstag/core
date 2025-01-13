<?php

namespace Labstag\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Labstag\Interface\FrontFormInterface;
use Labstag\Service\EmailService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;

abstract class FrontFormLib implements FrontFormInterface
{
    public function __construct(
        protected MailerInterface $mailer,
        protected FormFactoryInterface $formFactory,
        protected EmailService $emailService,
        protected RequestStack $requestStack,
        protected EntityManagerInterface $entityManager,
    )
    {
    }

    public function execute(FormInterface $form, bool $disable): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        $form->handleRequest($request);

        return ($disable != true) && $form->isSubmitted() && $form->isValid() && $request->isMethod('POST');
    }

    protected function getRepository(string $entity): ServiceEntityRepositoryLib
    {
        $entityRepository = $this->entityManager->getRepository($entity);
        if (!$entityRepository instanceof ServiceEntityRepositoryLib) {
            throw new Exception('Repository not found');
        }

        return $entityRepository;
    }
}
