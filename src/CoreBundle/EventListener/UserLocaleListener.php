<?php

namespace Runalyze\Bundle\CoreBundle\EventListener;

use Runalyze\Language;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Stores the locale of the user in the session after the
 * login. This can be used by the LocaleListener afterwards.
 */
class UserLocaleListener
{
    /** @var \Symfony\Component\HttpFoundation\Session\Session */
    private $session;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\Session $sessuib
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (null !== $user->getLanguage()) {
            $this->session->set('_locale', $user->getLanguage());
            new Language($user->getLanguage());
        }
    }
}