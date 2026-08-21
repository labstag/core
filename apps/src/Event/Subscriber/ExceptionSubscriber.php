<?php

namespace Labstag\Event\Subscriber;

use Labstag\Context\ExceptionContext;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber
{
    public function __construct(
        private ExceptionContext $exceptionContext,
    )
    {
    }

    #[AsEventListener(event: KernelEvents::EXCEPTION)]
    public function onKernelException(ExceptionEvent $exceptionEvent): void
    {
        $flattenException = FlattenException::createFromThrowable($exceptionEvent->getThrowable());
        $this->exceptionContext->setException($flattenException);
    }
}
