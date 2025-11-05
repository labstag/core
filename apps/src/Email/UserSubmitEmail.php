<?php

namespace Labstag\Email;

use Override;

class UserSubmitEmail extends EmailAbstract
{
    #[Override]
    public function getName(): string
    {
        return 'New user %user_email%';
    }

    #[Override]
    public function getType(): string
    {
        return 'user_submit';
    }

    #[Override]
    public function init(): void
    {
        $configuration = $this->configurationService->getConfiguration();
        parent::init();
        $this->to($configuration->getEmail());
    }
}
