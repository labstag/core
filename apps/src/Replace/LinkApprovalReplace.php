<?php

namespace Labstag\Replace;

use Labstag\Lib\ReplaceLib;

class LinkApprovalReplace extends ReplaceLib
{
    public function exec(): string
    {
        if (!isset($this->data['user'])) {
            return '';
        }

        $configuration = $this->configurationService->getConfiguration();
        $entity        = $this->data['user'];

        return $configuration->getUrl() . $this->router->generate(
            'admin_workflow',
            [
                '_locale'    => 'fr',
                'uid'        => $entity->getId(),
                'transition' => 'approval',
                'entity'     => $entity::class,
            ]
        );
    }

    public function getCode(): string
    {
        return 'link_approval';
    }

    public function getTitle(): string
    {
        return 'Link to Approval User';
    }
}
