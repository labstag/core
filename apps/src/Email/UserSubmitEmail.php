<?php

namespace Labstag\Email;

use Labstag\Lib\EmailLib;
use Override;

class UserSubmitEmail extends EmailLib
{
    #[Override]
    public function getCodes(): array
    {
        $codes = parent::getCodes();

        return array_merge(
            $codes,
            [
                'link_approval' => [
                    'title'    => 'Link to Approval User',
                    'function' => 'replaceLinkApproval',
                ],
            ]
        );
    }

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
        $configuration = $this->siteService->getConfiguration();
        parent::init();
        $this->to($configuration->getEmail());
    }

    protected function replaceLinkApproval(): string
    {
        $configuration = $this->siteService->getConfiguration();
        $entity        = $this->data['user'];

        return $configuration->getUrl().$this->router->generate(
            'admin_workflow',
            [
                '_locale'    => 'fr',
                'uid'        => $entity->getId(),
                'transition' => 'approval',
                'entity'     => $entity::class,
            ]
        );
    }
}
