<?php

declare(strict_types=1);

namespace Labstag\Context;

use Symfony\Component\ErrorHandler\Exception\FlattenException;

class ExceptionContext
{

    private ?FlattenException $flattenException = null;

    public function getException(): ?FlattenException
    {
        return $this->flattenException;
    }

    public function setException(FlattenException $flattenException): void
    {
        $this->flattenException = $flattenException;
    }
}
