<?php

namespace Labstag\Event\Workflow;

use Labstag\Service\MessageDispatcherService;

abstract class WorkflowAbstract
{
    public function __construct(
        protected MessageDispatcherService $messageBus,
    )
    {
    }
}
