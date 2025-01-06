<?php

namespace Labstag\Email;

use Labstag\Lib\EmailLib;
use Override;

class SendFormContactEmail extends EmailLib
{
    #[Override]
    public function getName(): string
    {
        return 'Send form contact %user_username%';
    }

    #[Override]
    public function getType(): string
    {
        return 'send_contact';
    }

    #[Override]
    public function init(): void
    {
        $configuration = $this->siteService->getConfiguration();
        parent::init();
        $this->to($configuration->getEmail());
    }
}
