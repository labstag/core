<?php

namespace Labstag\Email;

use Labstag\Lib\EmailLib;
use Override;

class UserSubmitEmail extends EmailLib
{
    #[Override]
    public function getCodes()
    {
        return [
            'link_approval' => 'Link to Approval User',
            'user_username' => 'Username',
            'user_email'    => 'email',
            'user_roles'    => 'Roles',
        ];
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
    protected function getReplaces()
    {
        $data                  = parent::getReplaces();
        $data['link_approval'] = 'replaceLinkApproval';

        return $data;
    }

    protected function replaceLinkApproval()
    {
        $entity = $this->data['user'];

        return $this->router->generate(
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
