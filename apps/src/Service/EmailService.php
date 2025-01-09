<?php

namespace Labstag\Service;

use Labstag\Lib\EmailLib;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class EmailService
{
    public function __construct(
        #[AutowireIterator('labstag.emails')]
        private readonly iterable $emails
    )
    {
    }

    public function all(): iterable
    {
        return $this->emails;
    }

    public function get(string $code, array $data = []): ?EmailLib
    {
        $template = null;
        foreach ($this->emails as $email) {
            if ($email->getType() != $code) {
                continue;
            }

            $template = $email;
            $template->setData($data);

            break;
        }

        return $template;
    }
}
