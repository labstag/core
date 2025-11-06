<?php

namespace Labstag\Event\Workflow;

use Symfony\Component\Messenger\MessageBusInterface;

abstract class WorkflowAbstract
{
    public function __construct(
        protected MessageBusInterface $messageBus,
    )
    {
    }
}
