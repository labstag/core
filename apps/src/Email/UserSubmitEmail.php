<?php

namespace Labstag\Email;

use Labstag\Lib\EmailLib;
use Labstag\Replace\LinkApprovalReplace;
use Override;

class UserSubmitEmail extends EmailLib
{
    #[Override]
    public function getName(): string
    {
        return 'New user %user_email%';
    }

    #[Override]
    public function getReplaces(): array
    {
        $codes = parent::getReplaces();

        return array_merge(
            $codes,
            [
                LinkApprovalReplace::class,
            ]
        );
    }

    #[Override]
    public function getType(): string
    {
        return 'user_submit';
    }

    #[Override]
    public function init(): void
    {
        $configuration = $this->siteService->getConfiguration();
        parent::init();
        $this->to($configuration->getEmail());
    }
}
