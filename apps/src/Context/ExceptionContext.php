<?php

namespace Labstag\Context;

use Symfony\Component\ErrorHandler\Exception\FlattenException;

class ExceptionContext
{
    private ?FlattenException $exception = null;

    public function setException(FlattenException $exception): void
    {
        $this->exception = $exception;
    }

    public function getException(): ?FlattenException
    {
        return $this->exception;
    }
}
