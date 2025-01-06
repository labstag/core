<?php

namespace Labstag\Email;

use Labstag\Lib\EmailLib;
use Override;

class UserPasswordLostEmail extends EmailLib
{
    #[Override]
    public function getName(): string
    {
        return 'Password Losted';
    }

    #[Override]
    public function getType(): string
    {
        return 'user_passwordlost';
    }

    #[Override]
    public function init(): void
    {
        $user = $this->data['user'];
        parent::init();
        $this->to($user->getEmail());
    }

    #[Override]
    protected function getReplaces()
    {
        $data                        = parent::getReplaces();
        $data['link_changepassword'] = 'replaceLinkChangePassword';

        return $data;
    }

    protected function replaceLinkChangePassword(): string
    {
        $configuration = $this->siteService->getConfiguration();
        $entity        = $this->data['user'];

        return $configuration->getUrl().$this->router->generate(
            'app_changepassword',
            [
                'uid' => $entity->getId(),
            ]
        );
    }
}
