<?php

namespace Runalyze\Bundle\CoreBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;

class ConsoleExceptionListener
{
    /** @var LoggerInterface */
    protected $Logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->Logger = $logger;
    }

    public function onConsoleException(ConsoleExceptionEvent $event)
    {
        $command = $event->getCommand();
        $exception = $event->getException();

        $message = sprintf(
            '%s: %s (uncaught exception) at %s line %s while running console command `%s`',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $command->getName()
        );

        $this->Logger->error($message, array('exception' => $exception));
    }
}
