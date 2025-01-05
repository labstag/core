<?php

namespace Labstag\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Service\EmailService;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;

abstract class FrontFormLib
{
    public function __construct(
        protected MailerInterface $mailer,
        protected FormFactoryInterface $formFactory,
        protected EmailService $emailService,
        protected RequestStack $requestStack,
        protected EntityManagerInterface $entityManager
    )
    {
    }

    public function execute(Form $form, bool $disable)
    {
        $request = $this->requestStack->getCurrentRequest();
        $form->handleRequest($request);

        return (true != $disable) && $form->isSubmitted() && $form->isValid() && $request->isMethod('POST');
    }

    protected function getRepository(string $entity)
    {
        return $this->entityManager->getRepository($entity);
    }
}
