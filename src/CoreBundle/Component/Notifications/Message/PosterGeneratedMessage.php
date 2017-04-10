<?php

namespace Runalyze\Bundle\CoreBundle\Component\Notifications\Message;

use Runalyze\Profile\Notifications\MessageTypeProfile;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class PosterGeneratedMessage implements MessageInterface
{
    /** @var int all posters succeeded */
    const STATE_SUCCESS = 0;

    /** @var int only some posters have been generated */
    const STATE_PARTIAL = 1;

    /** @var int no poster generated */
    const STATE_FAILED = 2;

    /** @var int see self::STATE_... */
    protected $State = 0;

    public function __construct($state = self::STATE_SUCCESS)
    {
        $this->State = (int)$state;
    }

    public function getMessageType()
    {
        return MessageTypeProfile::POSTER_GENERATED_MESSAGE;
    }

    public function getData()
    {
        return (string)$this->State;
    }

    public function getLifetime()
    {
        return 5;
    }

    public function getText(TranslatorInterface $translator)
    {
        switch ($this->State) {
            case self::STATE_PARTIAL:
                return $translator->trans('Not all of your posters could be generated.');
            case self::STATE_FAILED:
                return $translator->trans('You requested posters could not be generated.');
        }

        return $translator->trans('Your posters have been generated and are now available for download.');
    }

    public function hasLink()
    {
        return self::STATE_FAILED != $this->State;
    }

    public function getLink(RouterInterface $router)
    {
        return $router->generate('poster');
    }
}
