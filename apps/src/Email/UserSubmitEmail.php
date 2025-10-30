<?php

namespace Labstag\Email;

use Labstag\Replace\LinkApprovalReplace;
use Override;

class UserSubmitEmail extends EmailAbstract
{
    #[Override]
    public function getName(): string
    {
        return 'New user %user_email%';
    }

    /**
     * @return mixed[]
     */
    #[Override]
    public function getReplaces(): array
    {
        $codes = parent::getReplaces();

        return array_merge($codes, [LinkApprovalReplace::class]);
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
