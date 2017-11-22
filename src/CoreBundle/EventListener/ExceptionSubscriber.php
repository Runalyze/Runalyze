<?php
namespace Runalyze\Bundle\CoreBundle\EventListener;

use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\PDOException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onException(GetResponseForExceptionEvent $event) {
        $exception = $event->getException();
            $response = new Response('Custom error message', 500);
            echo "test";
            $event->setResponse($response);

    }

    public static function getSubscribedEvents() {
        $events[KernelEvents::EXCEPTION][] = ['onException', 60];
        return $events;
    }

}