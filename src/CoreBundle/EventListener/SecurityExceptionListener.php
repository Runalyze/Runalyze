<?php
namespace Runalyze\Bundle\CoreBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;

class SecurityExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof DisabledException) {
            // Customize your response object to display the exception details
            $response = new Response();
            $response->setContent('<html><body><h1>Custom disabled page!</h1></body></html>');

            // Send the modified response object to the event
            $event->setResponse($response);
        }
        elseif ($exception instanceof LockedException) {
            // Or render a custom template as a subrequest instead...
            $kernel = $event->getKernel();
            $response  = $kernel->forward('AcmeDemoBundle:AccountStatus:locked', array(
                'exception' => $exception,
            ));

            $event->setResponse($response);
        }
        // ... and so on
    }
}