<?php

namespace Labstag\Email;

use Labstag\Lib\EmailLib;
use Override;

class SendContactEmail extends EmailLib
{
    #[Override]
    public function getName(): string
    {
        return 'Send form contact';
    }

    #[Override]
    public function getType(): string
    {
        return 'send_contact';
    }

    #[Override]
    public function init(): void
    {
        $configuration = $this->configurationService->getConfiguration();
        parent::init();
        $this->to($configuration->getEmail());
    }
}
