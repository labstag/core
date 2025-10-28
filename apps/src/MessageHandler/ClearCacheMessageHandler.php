<?php

namespace Labstag\MessageHandler;

use Labstag\Message\ClearCacheMessage;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ClearCacheMessageHandler
{
    public function __construct(
        private KernelInterface $kernel,
    )
    {
    }

    public function __invoke(ClearCacheMessage $clearCacheMessage): void
    {
        unset($clearCacheMessage);
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $arrayInput = new ArrayInput(['cache:clear']);

        $bufferedOutput = new BufferedOutput();
        $application->run($arrayInput, $bufferedOutput);
        // do something with your message
    }
}
