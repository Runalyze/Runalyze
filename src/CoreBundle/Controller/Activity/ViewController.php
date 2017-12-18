<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Activity;

use Runalyze\Bundle\CoreBundle\Component\Activity\ActivityDecorator;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Common\AccountRelatedEntityInterface;
use Runalyze\Bundle\CoreBundle\Entity\Trackdata;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Component\Activity\Tool\BestSubSegmentsStatistics;
use Runalyze\Bundle\CoreBundle\Component\Activity\Tool\TimeSeriesStatistics;
use Runalyze\Bundle\CoreBundle\Component\Activity\VO2maxCalculationDetailsDecorator;
use Runalyze\Metrics\Velocity\Unit\PaceEnum;
use Runalyze\Model\Activity;
use Runalyze\Service\ElevationCorrection\StepwiseElevationProfileFixer;
use Runalyze\View\Activity\Context;
use Runalyze\View\Window\Laps\Window;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ViewController extends Controller
{
    protected function checkThatEntityBelongsToActivity(AccountRelatedEntityInterface $entity, Account $account)
    {
        if ($entity->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }
    }

    /**
     * @Route("/activity/{id}", name="ActivityShow", requirements={"id" = "\d+"})
     * @Security("has_role('ROLE_USER')")
     */
    public function displayAction($id, Account $account)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $context = new Context($id, $account->getId());

        switch (Request::createFromGlobals()->query->get('action')) {
            case 'changePrivacy':
                $oldActivity = clone $context->activity();
                $context->activity()->set(Activity\Entity::IS_PUBLIC, !$context->activity()->isPublic());
                $updater = new Activity\Updater(\DB::getInstance(), $context->activity(), $oldActivity);
                $updater->setAccountID($account->getId());
                $updater->update();
                break;
            case 'delete':
                $factory = \Runalyze\Context::Factory();
                $deleter = new Activity\Deleter(\DB::getInstance(), $context->activity());
                $deleter->setAccountID($account->getId());
                $deleter->setEquipmentIDs($factory->equipmentForActivity($id, true));
                $deleter->delete();

                return $this->render('activity/activity_has_been_removed.html.twig');
        }

        if (!Request::createFromGlobals()->query->get('silent')) {
            $view = new \TrainingView($context);
            $view->display();
        }

        return new Response();
    }

    /**
     * @Route("/activity/{id}/vo2max-info")
     * @ParamConverter("activity", class="CoreBundle:Training")
     * @Security("has_role('ROLE_USER')")
     */
    public function vo2maxInfoAction(Training $activity, Account $account)
    {
        $this->checkThatEntityBelongsToActivity($activity, $account);

        $configList = $this->get('app.configuration_manager')->getList();
        $activityContext = $this->get('app.activity_context.factory')->getContext($activity);

        return $this->render('activity/vo2max_info.html.twig', [
            'context' => $activityContext,
            'details' => new VO2maxCalculationDetailsDecorator($activityContext, $configList)
        ]);
    }

    /**
     * @Route("/activity/{id}/splits-info", requirements={"id" = "\d+"})
     * @Security("has_role('ROLE_USER')")
     */
    public function splitsInfoAction($id, Account $account)
    {
        $Frontend = new \Frontend(false, $this->get('security.token_storage'));

        $context = new Context($id, $account->getId());

        if (!$context->hasTrackdata()) {
            return $this->render('activity/tool/not_possible.html.twig');
        }

        $window = new Window($context);
        $window->display();

        return new Response();
    }

    /**
     * @Route("/activity/{id}/elevation-info", requirements={"id" = "\d+"})
     * @Security("has_role('ROLE_USER')")
     */
    public function elevationInfoAction($id, Account $account)
    {
        $Frontend = new \Frontend(false, $this->get('security.token_storage'));

        $context = new Context($id, $account->getId());

        if (!$context->hasRoute()) {
            return $this->render('activity/tool/not_possible.html.twig');
        }

        $elevationInfo = new \ElevationInfo($context);
        $elevationInfo->display();

        return new Response();
    }

    /**
     * @Route("/activity/{id}/time-series-info", requirements={"id" = "\d+"}, name="activity-tool-time-series-info")
     * @Security("has_role('ROLE_USER')")
     */
    public function timeSeriesInfoAction($id, Account $account)
    {
        $trackdata = $this->getDoctrine()->getRepository('CoreBundle:Trackdata')->findByActivity($id, $account);

        if (null === $trackdata) {
            return $this->render('activity/tool/not_possible.html.twig');
        }

        $trackdataModel = $trackdata->getLegacyModel();

        $paceUnit = PaceEnum::get(
            $this->getDoctrine()->getManager()->getRepository('CoreBundle:Training')->getSpeedUnitFor($id, $account->getId())
        );

        $statistics = new TimeSeriesStatistics($trackdataModel);
        $statistics->calculateStatistics([0.1, 0.9]);

        return $this->render('activity/tool/time_series_statistics.html.twig', [
            'statistics' => $statistics,
            'paceAverage' => $trackdataModel->totalPace(),
            'paceUnit' => $paceUnit
        ]);
    }

    /**
     * @Route("/activity/{id}/sub-segments-info", requirements={"id" = "\d+"}, name="activity-tool-sub-segments-info")
     * @Security("has_role('ROLE_USER')")
     */
    public function subSegmentInfoAction($id, Account $account)
    {
        $trackdata = $this->getDoctrine()->getRepository('CoreBundle:Trackdata')->findByActivity($id, $account);

        if (null === $trackdata) {
            return $this->render('activity/tool/not_possible.html.twig');
        }

        $trackdataModel = $trackdata->getLegacyModel();

        $paceUnit = PaceEnum::get(
            $this->getDoctrine()->getManager()->getRepository('CoreBundle:Training')->getSpeedUnitFor($id, $account->getId())
        );

        $statistics = new BestSubSegmentsStatistics($trackdataModel);
        $statistics->setDistancesToAnalyze([0.2, 1.0, 1.609, 3.0, 5.0, 10.0, 16.09, 21.1, 42.2, 50, 100]);
        $statistics->setTimesToAnalyze([30, 60, 120, 300, 600, 720, 1800, 3600, 7200]);
        $statistics->findSegments();

        return $this->render('activity/tool/best_sub_segments.html.twig', [
            'statistics' => $statistics,
            'distanceArray' => $trackdataModel->distance(),
            'paceUnit' => $paceUnit
        ]);
    }

    /**
     * @Route("/activity/{id}/climb-score", requirements={"id" = "\d+"}, name="activity-tool-climb-score")
     * @ParamConverter("activity", class="CoreBundle:Training")
     */
    public function climbScoreAction(Training $activity, Account $account = null)
    {
        $activityContext = $this->get('app.activity_context.factory')->getContext($activity);

        if (!$activity->isPublic() && $account === null) {
            throw $this->createNotFoundException('No activity found.');
        }

        if (!$activityContext->hasTrackdata() || !$activityContext->hasRoute()) {
            return $this->render('activity/tool/not_possible.html.twig');
        }

        if (
            $activity->hasRoute() && null !== $activity->getRoute()->getElevationsCorrected() &&
            $activity->hasTrackdata() && null !== $activity->getTrackdata()->getDistance()
        ) {
            $numDistance = count($activity->getTrackdata()->getDistance());
            $numElevations = count($activity->getRoute()->getElevationsCorrected());

            if ($numElevations > $numDistance) {
                $activity->getRoute()->setElevationsCorrected(array_slice($activity->getRoute()->getElevationsCorrected(), 0, $numDistance));
            }
        }

        if (null !== $activity->getRoute()->getElevationsCorrected() && null !== $activity->getTrackdata()->getDistance()) {
            $activity->getRoute()->setElevationsCorrected((new StepwiseElevationProfileFixer(
                5, StepwiseElevationProfileFixer::METHOD_VARIABLE_GROUP_SIZE
            ))->fixStepwiseElevations(
                $activity->getRoute()->getElevationsCorrected(),
                $activity->getTrackdata()->getDistance()
            ));
        }

        return $this->render('activity/tool/climb_score.html.twig', [
            'context' => $activityContext,
            'decorator' => new ActivityDecorator($activityContext),
            'paceUnit' => $activity->getSport()->getSpeedUnit()
        ]);
    }
}
