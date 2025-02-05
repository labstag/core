<?php

namespace Labstag\Email;

use Labstag\Lib\EmailLib;
use Labstag\Replace\LinkChangePasswordReplace;
use Override;

class UserPasswordLostEmail extends EmailLib
{
    #[Override]
    public function getName(): string
    {
        return 'Password Losted';
    }

    /**
     * @return mixed[]
     */
    #[Override]
    public function getReplaces(): array
    {
        $codes = parent::getReplaces();

        return array_merge($codes, [LinkChangePasswordReplace::class]);
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
}
