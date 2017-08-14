<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Export\Share\Facebook;
use Runalyze\View\Activity\Context;
use Runalyze\View\Activity\Linker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SharedController extends Controller
{
    /**
     * @Route("/shared/{activityHash}&{foo}&{bar}&{baz}", requirements={"activityHash": "[a-zA-Z0-9]+"})
     * @Route("/shared/{activityHash}&{foo}&{bar}", requirements={"activityHash": "[a-zA-Z0-9]+"})
     * @Route("/shared/{activityHash}&{foo}", requirements={"activityHash": "[a-zA-Z0-9]+"})
     * @Route("/shared/{activityHash}", requirements={"activityHash": "[a-zA-Z0-9]+"})
     */
    public function sharedTrainingAction($activityHash, Request $request)
    {
        /** @var null|Training $activity */
        $activity = $this->getDoctrine()->getRepository('CoreBundle:Training')->find((int)base_convert((string)$activityHash, 35, 10));

        if (null === $activity || !$activity->isPublic()) {
            return $this->render('shared/invalid_activity.html.twig');
        }

        $_GET['user'] = $activity->getAccount()->getUsername();
        $account = $this->getDoctrine()->getRepository('CoreBundle:Account')->findByUsername($activity->getAccount()->getUsername());
        $publicList = $this->get('app.configuration_manager')->getList($account)->getPrivacy()->isListPublic();

        $Frontend = new \FrontendShared(true);
        $activityContext = $this->get('app.activity_context.factory')->getContext($activity);
        $activityContextLegacy = new Context($activity->getId(), $activity->getAccount()->getId());

        $hasRoute = $activityContext->canShowMap() && $this->get('app.privacy_guard')->isMapVisible($activity, $activityContext->getRaceResult());

        if ('iframe' == $request->query->get('mode')) {
            return $this->render('shared/widget/iframe/base.html.twig', [
                'username' => $activity->getAccount()->getUsername(),
                'hasPublicList' => $publicList,
                'context' => $activityContext,
                'route' => $hasRoute ? new \Runalyze\View\Leaflet\Activity(
                    'route-'.$activity->getId(),
                    $activityContextLegacy->route(),
                    $activityContextLegacy->trackdata()
                ) : false
            ]);
        }

        return $this->render('shared/activity/base.html.twig', [
            'username' => $activity->getAccount()->getUsername(),
            'activity' => $activity,
            'hasPublicList' => $publicList,
            'activityUrl' => (new Linker($activityContextLegacy->activity()))->publicUrl(),
            'activityHasRoute' => $hasRoute,
            'metaTitle' => (new Facebook($activityContextLegacy))->metaTitle(),
            'view' => new \TrainingView($activityContextLegacy)
        ]);
    }

    /**
     * @Route("/shared/{username}/")
     */
    public function oldSharedUserAction($username)
    {
        return $this->redirect($this->generateUrl('shared-athlete', array('username' => $username)), 301);
    }

    /**
     * @Route("/athlete/{username}", name="shared-athlete")
     */
    public function sharedUserAction($username, Request $request) {
        /** @var null|Account $account */
        $account = $this->getDoctrine()->getRepository('CoreBundle:Account')->findByUsername($username);
        $privacy = $this->get('app.configuration_manager')->getList($account)->getPrivacy();

        if (null === $account || !$privacy->isListPublic()) {
            return $this->render('shared/invalid_athlete.html.twig');
        }

        $_GET['user'] = $username;

        $Frontend = new \FrontendSharedList();

        if (isset($_GET['type'])) {
            return $this->render('shared/athlete/base_plot_sum_data.html.twig', [
                'username' => $username,
                'plot' => $this->getPlotSumData()
            ]);
        }

        if ($privacy->isListWithStatistics()) {
            $accountStatistics = $this->getDoctrine()->getRepository('CoreBundle:Training')->getAccountStatistics($account);
            $legacyStatistics = new \FrontendSharedStatistics();
        } else {
            $accountStatistics = null;
            $legacyStatistics = null;
        }


        return $this->render('shared/athlete/base.html.twig', [
            'account' => $account,
            'accountStatistics' => $accountStatistics,
            'legacyStatistics' => $legacyStatistics,
            'dataBrowser' => new \DataBrowserShared()
        ]);
    }

    /**
     * @return \PlotSumData
     */
    protected function getPlotSumData()
    {
        $Request = Request::createFromGlobals();

        if (is_null($Request->query->get('y'))) {
            $_GET['y'] = \PlotSumData::LAST_12_MONTHS;
        }

        return 'week' == $Request->query->get('type', 'month') ? new \PlotWeekSumData() : new \PlotMonthSumData();
    }
}
