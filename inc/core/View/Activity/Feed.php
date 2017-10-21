<?php
namespace Runalyze\View\Activity;

use PicoFeed\Syndication\Rss20FeedBuilder;
use PicoFeed\Syndication\Rss20ItemBuilder;
use Runalyze\Bundle\CoreBundle\Component\Activity\ActivityContext;
use Runalyze\Bundle\CoreBundle\Services\Activity\ActivityContextFactory;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Runalyze\Data\Cadence\Unit;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Runalyze\Util\LocalTime;
use Symfony\Component\Translation\TranslatorInterface;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Activity\PaceUnit;
use Runalyze\Bundle\CoreBundle\Twig\ValueExtension;

class Feed {

    /** @var array $Activities */
    protected $Activities;

    /** @var Rss20FeedBuilder */
    protected $FeedBuilder;

    /** @var TranslatorInterface */
    protected $Translator;

    /** @var ActivityContextFactory */
    protected $ActivityContextFactory;

    /** @var UrlGeneratorInterface */
    protected $UrlGenerator;

    /** @var ConfigurationManager */
    protected $ConfigurationManager;

    /**
     * Feed constructor.
     * @param TranslatorInterface $translator
     * @param ActivityContextFactory $activityContextFactory
     * @param UrlGeneratorInterface $urlGenerator
     * @param ConfigurationManager $configurationManager
     */
    public function __construct(TranslatorInterface $translator, ActivityContextFactory $activityContextFactory, UrlGeneratorInterface $urlGenerator, ConfigurationManager $configurationManager)
    {
        $this->FeedBuilder = new Rss20FeedBuilder();
        $this->ActivityContextFactory = $activityContextFactory;
        $this->Translator = $translator;
        $this->UrlGenerator = $urlGenerator;
        $this->ConfigurationManager = $configurationManager;
        $this->FeedBuilder->withDate(new \DateTime());
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator) {
        $this->Translator = $translator;
    }

    /**
     * @return string
     */
    public function buildFeed()
    {
        $this->createItems();
        return $this->FeedBuilder->build();
    }

    /**
     * @param $activities
     * @return $this
     */
    public function setActivities($activities)
    {
        $this->Activities = $activities;
        return $this;
    }

    /**
     * @param $author
     * @return $this
     */
    public function setFeedAuthor($author)
    {
        $this->FeedBuilder->withAuthor($author);
        return $this;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setFeedTitle($title)
    {
        $this->FeedBuilder->withTitle($title);
        return $this;
    }

    /**
     * @param $feed
     * @return $this
     */
    public function setFeedUrl($feed)
    {
        $this->FeedBuilder->withFeedUrl($feed);
        return $this;
    }

    /**
     * @param $site
     * @return $this
     */
    public function setSiteUrl($site)
    {
        $this->FeedBuilder->withSiteUrl($site);
        return $this;
    }

    /**
     * @param \DateTime $date
     * @return $this
     */
    public function setFeedDate(\DateTime $date)
    {
        $this->FeedBuilder->withDate($date);
        return $this;
    }

    /**
     * @param ActivityContext $activityContext
     * @param ValueExtension $valueDecorator
     * @return string
     */
    private function createItemContent(ActivityContext $activityContext, ValueExtension $valueDecorator)
    {
        $activity = $activityContext->getActivity();
        $content = '<b>'.$this->Translator->trans('Sport') . '</b>: ' . $activity->getSport()->getName();
        if ($activity->getType() !== null) {
            $content .= '<br><b>'.$this->Translator->trans('Activity type') . '</b>: ' . $activity->getType()->getName();
        }
        $content .= '<br><b>'.$this->Translator->trans('Duration') . '</b>: '.(new \DateTime())->setTimezone(new \DateTimeZone("UTC"))->setTimestamp($activityContext->getActivity()->getS())->format('H:i:s');

        if ($activity->getDistance()) {
            $content .= '<br><b>' . $this->Translator->trans('Distance') . '</b>: ' . $valueDecorator->distance($activity->getDistance());
            $content .= '<br><b>' . $this->Translator->trans('Pace') . '</b>: ' . $valueDecorator->pace($activity->getElapsedTime() / $activity->getDistance(), $activity->getSport()->getSpeedUnit());
        }

        if ($activityContext->getActivity()->getNotes()) {
            $content .= '<br><b>'.$this->Translator->trans('Notes:') . '</b><br>'.$activityContext->getActivity()->getNotes();
        }

        if ($activityContext->getActivity()->isPublic()) {
            $content .= '<br><a href="'.$this->UrlGenerator->generate('shared-activity', array('activityHash' => base_convert((int)$activityContext->getActivity()->getId(), 10, 35)), UrlGeneratorInterface::ABSOLUTE_URL);
            $content .= '">'.$this->Translator->trans('View full activity').'</a>';
        }

        return $content;
    }

    /**
     * @param Training $activity
     */
    private function createItem(Training $activity)
    {

        $item = new Rss20ItemBuilder($this->FeedBuilder);
        $activityContext = $this->ActivityContextFactory->getContext($activity);
        $time = (new LocalTime($activityContext->getActivity()->getTime()))->format('d.m.Y');
        $account = $activity->getAccount();
        $valueDecorator = new ValueExtension($this->ConfigurationManager);
        $item->withTitle($time.' - '.$activityContext->getSport()->getName().' - '.$valueDecorator->distance($activity->getDistance()));


        $item->withPublishedDate(new LocalTime($activityContext->getActivity()->getTime()));
        $item->withContent($this->createItemContent($activityContext, $valueDecorator));
        $item->withAuthor($account->getUsername());
        if ($activityContext->getActivity()->isPublic()) {
            $item->withUrl($this->UrlGenerator->generate('shared-activity', array('activityHash' => base_convert((int)$activityContext->getActivity()->getId(), 10, 35)), UrlGeneratorInterface::ABSOLUTE_URL));
        }
        $this->FeedBuilder->withItem($item);
    }

    private function createItems()
    {
        if ($this->Activities) {
            foreach ($this->Activities as $activity) {
                $this->createItem($activity);
            }
        }
    }

}