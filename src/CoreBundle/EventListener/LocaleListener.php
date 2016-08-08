<?php

namespace Runalyze\Bundle\CoreBundle\EventListener;

use Runalyze\Language;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class LocaleListener
 *
 * This class is used instead of symfony's original LocaleListener
 * such that our old Language class can be used.
 *
 * @package Runalyze\Bundle\CoreBundle\EventListener
 */
class LocaleListener implements EventSubscriberInterface
{
    /** @var string */
    private $defaultLocale;

    /**
     * LocaleListener constructor.
     * @param string $defaultLocale
     */
    public function __construct($defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($locale = $request->attributes->get('_locale')) {
            new Language($locale);
        } elseif ($request->hasPreviousSession() && $locale = $request->getSession()->get('_locale')) {
            new Language($locale);
        } else {
            new Language();
        }

        $locale = Language::getCurrentLanguage();

        $request->getSession()->set('_locale', $locale);
        $request->setLocale($locale);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 15)),
        );
    }
}