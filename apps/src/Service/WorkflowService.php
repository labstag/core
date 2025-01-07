<?php

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\WorkflowInterface;

class WorkflowService
{
    public function __construct(
        #[Autowire(service: 'workflow.registry')]
        protected Registry $workflowRegistry,
        protected RequestStack $requestStack,
        protected EntityManagerInterface $entityManager
    )
    {
    }

    public function change(string $entity, string $transition, mixed $uid): void
    {
        $entityRepository = $this->entityManager->getRepository($entity);

        $object = $entityRepository->find($uid);
        if (!$this->workflowRegistry->has($object)) {
            return;
        }

        $workflow = $this->workflowRegistry->get($object);
        if (!$workflow->can($object, $transition)) {
            return;
        }

        $workflow->apply($object, $transition);
        $this->entityManager->flush();

        $session = $this->requestStack->getSession();
        if (!$session instanceof FlashBagAwareSessionInterface) {
            return;
        }

        $session->getFlashBag()->add('success', new TranslatableMessage('The status has been successfully changed'));
    }

    public function get(object $entity): ?WorkflowInterface
    {
        if (!$this->workflowRegistry->has($entity)) {
            return null;
        }

        return $this->workflowRegistry->get($entity);
    }

    public function init(object $entity): void
    {
        $workflow = $this->get($entity);
        if (!$workflow instanceof WorkflowInterface) {
            return;
        }

        $initialMarking = new Marking();
        $definition     = $workflow->getDefinition();
        $initialPlaces  = $definition->getInitialPlaces();
        foreach ($initialPlaces as $initialPlace) {
            $initialMarking->mark($initialPlace);
        }

        $markingStore = $workflow->getMarkingStore();
        $markingStore->setMarking($entity, $initialMarking);
    }
}
