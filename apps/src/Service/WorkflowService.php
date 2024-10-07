<?php

namespace Labstag\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Registry;

class WorkflowService
{
    public function __construct(
        #[Autowire(service: 'workflow.registry')]
        protected Registry $workflowRegistry
    )
    {
    }

    public function init($entity)
    {
        if (!$this->workflowRegistry->has($entity)) {
            return;
        }

        $workflow       = $this->workflowRegistry->get($entity);
        $initialMarking = new Marking();
        $definition     = $workflow->getDefinition();
        $initialPlaces  = $definition->getInitialPlaces();
        foreach ($initialPlaces as $initialPlace) {
            $initialMarking->mark($initialPlace);
        }

        $markingStore = $workflow->getMarkingStore();
        $markingStore->setMarking($entity, $initialMarking);

        dump(get_class_methods($workflow));
    }
}
