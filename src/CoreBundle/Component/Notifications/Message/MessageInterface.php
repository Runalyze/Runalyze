<?php

namespace Runalyze\Bundle\CoreBundle\Component\Notifications\Message;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

interface MessageInterface
{
    /**
     * @return int
     * 
     * @see \Runalyze\Profile\Notifications\MessageTypeProfile
     */
    public function getMessageType();

    /**
     * @return string
     */
    public function getData();

    /**
     * @return null|int [days]
     */
    public function getLifetime();

    /**
     * @param TranslatorInterface $translator
     * @return string
     */
    public function getText(TranslatorInterface $translator);

    /**
     * @return bool
     */
    public function hasLink();

    /**
     * @param RouterInterface $router
     * @return string
     */
    public function getLink(RouterInterface $router);
}
