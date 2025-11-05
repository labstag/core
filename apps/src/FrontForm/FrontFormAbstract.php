<?php

namespace Labstag\FrontForm;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Labstag\Entity\Submission;
use Labstag\Repository\RepositoryAbstract;
use Labstag\Repository\SubmissionRepository;
use Labstag\Service\EmailService;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;

#[AutoconfigureTag('labstag.frontforms')]
abstract class FrontFormAbstract implements FrontFormInterface
{
    public function __construct(
        protected MailerInterface $mailer,
        protected FormFactoryInterface $formFactory,
        protected SubmissionRepository $submissionRepository,
        protected EmailService $emailService,
        protected RequestStack $requestStack,
        protected EntityManagerInterface $entityManager,
    )
    {
    }

    /**
     * @param FormInterface<mixed> $form
     */
    public function execute(FormInterface $form, bool $disable = false, bool $save = true): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        $form->handleRequest($request);

        $state = (false === $disable) && $form->isSubmitted() && $form->isValid() && $request->isMethod('POST');

        if ($state && $save) {
            $this->saveForm($form);
        }

        return $state;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function getFields(array $data): mixed
    {
        unset($data);

        return [];
    }

    /**
     * @return RepositoryAbstract<object>
     */
    protected function getRepository(string $entity): object
    {
        $entityRepository = $this->entityManager->getRepository($entity);
        if (is_null($entityRepository)) {
            throw new Exception('Repository not found');
        }

        return $entityRepository;
    }

    /**
     * @param FormInterface<mixed> $form
     */
    protected function saveForm(FormInterface $form): void
    {
        $raw  = $form->all();
        $data = [];
        foreach ($raw as $key => $row) {
            $data[$key] = $row->getData();
        }

        $submission = new Submission();
        $submission->setType($this->getCode());
        $submission->setData($data);

        $this->submissionRepository->save($submission);
    }
}
