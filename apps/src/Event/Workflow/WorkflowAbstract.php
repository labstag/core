<?php

namespace Labstag\Event\Workflow;

use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class WorkflowAbstract
{
    public function __construct(
        protected MessageDispatcherService $messageBus,
    )
    {
    }
}
