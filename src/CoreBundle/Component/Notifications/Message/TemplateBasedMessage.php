<?php

namespace Runalyze\Bundle\CoreBundle\Component\Notifications\Message;

use Runalyze\Profile\Notifications\MessageTypeProfile;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Yaml\Yaml;

class TemplateBasedMessage implements MessageInterface
{
    /** @var string base path relative to root */
    const BASE_PATH = '../../../../../data/views/notifications/';

    /** @var string */
    protected $TemplateName;

    /** @var null|int $lifetime [days] */
    protected $Lifetime = null;

    /** @var null|array */
    protected $TemplateContent = null;

    /**
     * @param string $templateName relative to self::BASE_PATH
     * @param null|int $lifetime [days] (only required for insert progress)
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct($templateName, $lifetime = null)
    {
        if (!file_exists(__DIR__.'/'.self::BASE_PATH.$templateName)) {
            throw new \InvalidArgumentException(sprintf('Given template "%s" cannot be found in "%s".', $templateName, __DIR__.'/'.self::BASE_PATH));
        }

        $this->TemplateName = $templateName;
        $this->Lifetime = $lifetime;
    }

    public function getMessageType()
    {
        return MessageTypeProfile::TEMPLATE_BASED_MESSAGE;
    }

    public function getData()
    {
        return $this->TemplateName;
    }

    public function getLifetime()
    {
        return $this->Lifetime;
    }

    public function getText(TranslatorInterface $translator = null)
    {
        $this->loadTemplateIfNotDoneYet();

        return isset($this->TemplateContent['text']) ? $this->TemplateContent['text'] : '';
    }

    public function hasLink()
    {
        $this->loadTemplateIfNotDoneYet();

        return isset($this->TemplateContent['link']) && !empty($this->TemplateContent['link']);
    }

    public function getLink(RouterInterface $router = null)
    {
        $this->loadTemplateIfNotDoneYet();

        return isset($this->TemplateContent['link']) ? $this->TemplateContent['link'] : '';
    }

    protected function loadTemplateIfNotDoneYet()
    {
        if (null === $this->TemplateContent) {
            $this->TemplateContent = Yaml::parse(file_get_contents(__DIR__.'/'.self::BASE_PATH.$this->TemplateName));
        }
    }
}
