<?php
namespace Runalyze\View\Activity;

use PicoFeed\Syndication\Rss20FeedBuilder;
use PicoFeed\Syndication\Rss20ItemBuilder;
use Runalyze\Bundle\CoreBundle\Component\Activity\ActivityContext;
use Runalyze\Bundle\CoreBundle\Services\Activity\ActivityContextFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Runalyze\Util\LocalTime;
use Symfony\Component\Translation\TranslatorInterface;
use Runalyze\Bundle\CoreBundle\Entity\Training;

class Feed {

    /** @var array $Activities */
    protected $Activities;

    /** @var Rss20FeedBuilder */
    protected $FeedBuilder;

    /** @var TranslatorInterface */
    protected $Translator;

    /** @var ActivityContextFactory */
    protected $ActivityContextFactory;

    /**
     * Feed constructor.
     * @param TranslatorInterface $translator
     * @param ActivityContextFactory $activityContextFactory
     */
    public function __construct(TranslatorInterface $translator, ActivityContextFactory $activityContextFactory)
    {
        $this->FeedBuilder = new Rss20FeedBuilder();
        $this->ActivityContextFactory = $activityContextFactory;
        $this->Translator = $translator;
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
     * @return string
     */
    private function createItemContent(ActivityContext $activityContext)
    {
        $content = '<h1>'.$this->Translator->trans('Sport') . ': ' . $activityContext->getActivity()->getSport()->getName().'</h1>';
        $content .= '<br><b>'.$this->Translator->trans('Duration') . '</b>: ' . $activityContext->getActivity()->getElapsedTime();

        if ($activityContext->getActivity()->getType() !== null) {
            $content .= '<br><b>'.$this->Translator->trans('Activity type') . '</b>: ' . $activityContext->getActivity()->getType()->getName();
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
        $time = (new LocalTime($activityContext->getActivity()->getTime()))->format('d.m.Y H:i');

        $item->withTitle($time.' '.$activityContext->getActivity()->getDistance(). ' ' .$activityContext->getDecorator()->getTitle());
        $item->withPublishedDate(new LocalTime($activityContext->getActivity()->getTime()));
        $item->withContent($this->createItemContent($activityContext));
        $item->withAuthor($activity->getAccount()->getUsername());
        if ($activityContext->getActivity()->isPublic()) {
            //$item->withUrl($this->generateUrl('shared-activity', array('activityHash' => $activityContext->getActivity()->getId()), UrlGeneratorInterface::ABSOLUTE_URL));
        }
        $this->FeedBuilder->withItem($item);

        //$account = $this->getDoctrine()->getRepository('CoreBundle:Account')->findByUsername($username);
        //$privacy = $this->get('app.configuration_manager')->getList($account)->getPrivacy();
    }
    private function createItems()
    {
        foreach($this->Activities as $activity) {
            $this->createItem($activity);
        }
    }

}