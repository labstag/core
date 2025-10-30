<?php

namespace Labstag\Service;

use Labstag\Email\EmailAbstract;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class EmailService
{
    public function __construct(
        /**
         * @var iterable<EmailAbstract>
         */
        #[AutowireIterator('labstag.emails')]
        private readonly iterable $emails,
    )
    {
    }

    public function all(): mixed
    {
        return $this->emails;
    }

    /**
     * @param mixed[] $data
     */
    public function get(string $code, array $data = []): ?EmailAbstract
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
