<?php
// src/CoreBundle/EventListener/LocaleListener.php
namespace Runalyze\Bundle\CoreBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
include_once '../inc/system/class.Language.php';

class LocaleListener implements EventSubscriberInterface
{
    private $defaultLocale;

    public function __construct($defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        //define('FRONTEND_PATH', '../inc/');
        new \Language();
        $request = $event->getRequest();
	$request->setLocale(\Language::getCurrentLanguage());
        if (!$request->hasPreviousSession()) {
            return;
        }
        // try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        } else {
            // if no explicit locale has been set on this request, use one from the session
           
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 15)),
        );
    }
}

/**
 * Returns the translation for a textstring
 * @param string $text
 * @param string $domain [optional]
 * @return string
 */
function __($text, $domain = 'runalyze') {
    return Language::__($text, $domain);
}

/**
 * Echo the translation for a textstring
 * @param string $text
 * @param string $domain [optional]
 */
function _e($text, $domain = 'runalyze') {
    echo Language::_e($text, $domain);
}

/**
 * Return singular/plural translation for a textstring
 * @param string $msg1
 * @param string $msg2
 * @param int $n
 * @param string $domain [optional]
 * @return string
 */
function _n($msg1, $msg2, $n, $domain = 'runalyze') {
    return Language::_n($msg1, $msg2, $n, $domain);
}

/**
 * Echo singular/plural translation for a textstring
 * @param string $msg1
 * @param string $msg2
 * @param int $n
 * @param string $domain [optional]
 */
function _ne($msg1, $msg2, $n, $domain = 'runalyze') {
    echo Language::_ne($msg1, $msg2, $n, $domain);
}