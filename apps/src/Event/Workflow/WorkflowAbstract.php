<?php

namespace Labstag\Event\Workflow;

use Labstag\Service\ConfigurationService;
use Labstag\Service\EmailService;

abstract class WorkflowAbstract
{
    public function __construct(
        protected EmailService $emailService,
        protected ConfigurationService $configurationService,
    )
    {
    }
}
