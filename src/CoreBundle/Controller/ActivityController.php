<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\ClimbScoreCalculator;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\FlatOrHillyAnalyzer;
use Runalyze\Bundle\CoreBundle\Component\Activity\ActivityDecorator;
use Runalyze\Bundle\CoreBundle\Component\Activity\Tool\BestSubSegmentsStatistics;
use Runalyze\Bundle\CoreBundle\Component\Activity\Tool\TimeSeriesStatistics;
use Runalyze\Bundle\CoreBundle\Component\Activity\VO2maxCalculationDetailsDecorator;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Trackdata;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Metrics\Velocity\Unit\PaceEnum;
use Runalyze\Service\ElevationCorrection\StepwiseElevationProfileFixer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Runalyze\View\Activity\Context;
use Runalyze\View\Activity\Linker;
use Runalyze\View\Activity\Dataview;
use Runalyze\Export\Share;
use Runalyze\Model\Activity;
use Runalyze\Util\LocalTime;
use Runalyze\Export\File;
use Runalyze\View\Window\Laps\Window;
use Runalyze\Activity\DuplicateFinder;
use Runalyze\Calculation\Route\Calculator;
use Runalyze\Service\ElevationCorrection\NoValidStrategyException;

class ActivityController extends Controller
{
    /**
     * @Route("/activity/add", name="activity-add")
     * @Security("has_role('ROLE_USER')")
     */
    public function createAction()
    {
        $Frontend = new \Frontend(isset($_GET['json']), $this->get('security.token_storage'));

        if (class_exists('Normalizer')) {
        	if (isset($_GET['file'])) {
        		$_GET['file'] = \Normalizer::normalize($_GET['file']);
        	}

        	if (isset($_GET['files'])) {
        		$_GET['files'] = \Normalizer::normalize($_GET['files']);
        	}

        	if (isset($_POST['forceAsFileName'])) {
        		$_POST['forceAsFileName'] = \Normalizer::normalize($_POST['forceAsFileName']);
        	}

        	if (isset($_FILES['qqfile']) && isset($_FILES['qqfile']['name'])) {
        		$_FILES['qqfile']['name'] = \Normalizer::normalize($_FILES['qqfile']['name']);
        	}
        }

        if (isset($_FILES['qqfile']) && isset($_FILES['qqfile']['name'])) {
            $_FILES['qqfile']['name'] = str_replace(';', '_-_', $_FILES['qqfile']['name']);
        }

        $Window = new \ImporterWindow();
        $Window->display();

        return new Response();
    }

    /**
     * @Route("/activity/{id}", name="ActivityShow", requirements={"id" = "\d+"})
     * @Security("has_role('ROLE_USER')")
     */
    public function displayAction($id, Account $account)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $Context = new Context($id, $account->getId());

        switch (Request::createFromGlobals()->query->get('action')) {
            case 'changePrivacy':
                $oldActivity = clone $Context->activity();
                $Context->activity()->set(Activity\Entity::IS_PUBLIC, !$Context->activity()->isPublic());
                $Updater = new Activity\Updater(\DB::getInstance(), $Context->activity(), $oldActivity);
                $Updater->setAccountID($account->getId());
                $Updater->update();
                break;
            case 'delete':
                $Factory = \Runalyze\Context::Factory();
                $Deleter = new Activity\Deleter(\DB::getInstance(), $Context->activity());
                $Deleter->setAccountID($account->getId());
                $Deleter->setEquipmentIDs($Factory->equipmentForActivity($id, true));
                $Deleter->delete();

                return $this->render('activity/activity_has_been_removed.html.twig');
        }

        if (!Request::createFromGlobals()->query->get('silent')) {
            $View = new \TrainingView($Context);
            $View->display();
        }

        return new Response();
    }

    /**
     * @Route("/activity/{id}/edit", name="ActivityEdit")
     * @Security("has_role('ROLE_USER')")
     */
    public function editAction($id)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $Training = new \TrainingObject($id);
        $Activity = new Activity\Entity($Training->getArray());

        $Training->setStartPoint(
            $this->getDoctrine()->getRepository('CoreBundle:Route')->getStartCoordinatesFor(
                $Training->get('routeid')
            )
        );

        $Linker = new Linker($Activity);
        $Dataview = new Dataview($Activity);

        echo $Linker->editNavigation();

        echo '<div class="panel-heading">';
        echo '<h1>'.$Dataview->titleWithComment().', '.$Dataview->dateAndDaytime().'</h1>';
        echo '</div>';
        echo '<div class="panel-content">';

        $Formular = new \TrainingFormular($Training, \StandardFormular::$SUBMIT_MODE_EDIT);
        $Formular->setId('training');
        $Formular->setLayoutForFields( \FormularFieldset::$LAYOUT_FIELD_W50 );
        $Formular->display();

        echo '</div>';

        return new Response();
    }

    /**
     * @Route("/activity/multi-editor/{id}", name="multi-editor", requirements={"id" = "\d+"}, defaults={"id" = null})
     * @Security("has_role('ROLE_USER')")
     */
    public function multiEditorAction($id)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        if (null === $id) {
            return $this->generateResponseForMultiEditorOverview();
        }

        return $this->generateResponseForMultiEditor($id);
    }

    /**
     * @return Response
     */
    protected function generateResponseForMultiEditorOverview()
    {
        $IDs = \DB::getInstance()->query('SELECT `id` FROM `'.PREFIX.'training` ORDER BY `id` DESC LIMIT 20')->fetchAll(\PDO::FETCH_COLUMN, 0);

        $MultiEditor = new \MultiEditor($IDs);
        $MultiEditor->display();

        return new Response(\Ajax::wrapJS('$("#ajax").addClass("small-window");'));
    }

    /**
     * @param int $id
     * @return Response
     */
    protected function generateResponseForMultiEditor($id)
    {
        $MultiEditor = new \MultiEditor();
        $MultiEditor->displayEditor($id);

        return new Response();
    }

   /**
    * @Route("/activity/{id}/delete", name="ActivityDelete")
    * @Security("has_role('ROLE_USER')")
    */
   public function deleteAction($id, Account $account)
   {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $Factory = \Runalyze\Context::Factory();
        $Deleter = new Activity\Deleter(\DB::getInstance(), $Factory->activity($id));
        $Deleter->setAccountID($account->getId());
        $Deleter->setEquipmentIDs($Factory->equipmentForActivity($id, true));
        $Deleter->delete();

        return $this->render('activity/activity_has_been_removed.html.twig', [
            'multiEditorId' => (int)$id
        ]);
   }

    /**
     * @Route("/activity/{id}/vo2max-info")
     * @ParamConverter("activity", class="CoreBundle:Training")
     * @Security("has_role('ROLE_USER')")
     */
    public function vo2maxInfoAction(Training $activity, Account $account)
    {
        if ($activity->getAccount()->getId() != $account->getId()) {
            throw $this->createAccessDeniedException();
        }

        $configList = $this->get('app.configuration_manager')->getList();
        $activityContext = $this->get('app.activity_context.factory')->getContext($activity);

        return $this->render('activity/vo2max_info.html.twig', [
            'context' => $activityContext,
            'details' => new VO2maxCalculationDetailsDecorator($activityContext, $configList)
        ]);
    }

    /**
     * @Route("/activity/{id}/elevation-correction")
     * @Security("has_role('ROLE_USER')")
     */
    public function elevationCorrectionAction($id, Account $account)
    {
        $Frontend = new \Frontend(false, $this->get('security.token_storage'));

        $Factory = \Runalyze\Context::Factory();
        $Activity = $Factory->activity($id);
        $ActivityOld = clone $Activity;
        $Route = $Factory->route($Activity->get(Activity\Entity::ROUTEID));
        $RouteOld = clone $Route;

        try {
        	$Calculator = new Calculator($Route);
        	$result = $Calculator->tryToCorrectElevation(Request::createFromGlobals()->query->get('strategy'));
        } catch (NoValidStrategyException $Exception) {
        	$result = false;
        }

        if ($result) {
        	$Calculator->calculateElevation();
        	$Activity->set(Activity\Entity::ELEVATION, $Route->elevation());

        	$trackdata = $Factory->trackdata($id);
            $newRouteEntity = new \Runalyze\Bundle\CoreBundle\Entity\Route();
            $newRouteEntity->setDistance($Route->distance());

            if ($Route->hasCorrectedElevations()) {
                $newRouteEntity->setElevationsCorrected((new StepwiseElevationProfileFixer(
                    5, StepwiseElevationProfileFixer::METHOD_VARIABLE_GROUP_SIZE
                ))->fixStepwiseElevations(
                    $Route->elevationsCorrected(),
                    $trackdata->distance()
                ));
            } elseif ($Route->hasOriginalElevations()) {
                $newRouteEntity->setElevationsOriginal($Route->elevationsOriginal());
            }

            $newTrackdataEntity = new Trackdata();
            $newTrackdataEntity->setDistance($trackdata->distance());

            $newActivityEntity = new Training();
            $newActivityEntity->setRoute($newRouteEntity);
            $newActivityEntity->setTrackdata($newTrackdataEntity);

            if ($newRouteEntity->hasElevations()) {
                (new FlatOrHillyAnalyzer())->calculatePercentageHillyFor($newActivityEntity);
                (new ClimbScoreCalculator())->calculateFor($newActivityEntity);

                $Activity->set(Activity\Entity::CLIMB_SCORE, $newActivityEntity->getClimbScore());
                $Activity->set(Activity\Entity::PERCENTAGE_HILLY, $newActivityEntity->getPercentageHilly());
            } else {
                $Activity->set(Activity\Entity::CLIMB_SCORE, null);
                $Activity->set(Activity\Entity::PERCENTAGE_HILLY, null);
            }

        	$UpdaterRoute = new \Runalyze\Model\Route\Updater(\DB::getInstance(), $Route, $RouteOld);
        	$UpdaterRoute->setAccountID($account->getId());
        	$UpdaterRoute->update();

        	$UpdaterActivity = new Activity\Updater(\DB::getInstance(), $Activity, $ActivityOld);
        	$UpdaterActivity->setAccountID($account->getId());
        	$UpdaterActivity->update();

        	if (Request::createFromGlobals()->query->get('strategy') == 'none') {
        		echo __('Corrected elevation data has been removed.');
        	} else {
        		echo __('Elevation data has been corrected.');
        	}

        	\Ajax::setReloadFlag( \Ajax::$RELOAD_DATABROWSER_AND_TRAINING );
        	echo \Ajax::getReloadCommand();
        	echo \Ajax::wrapJS(
        		'if ($("#ajax").is(":visible") && $("#training").length) {'.
        			'Runalyze.Overlay.load(\'activity/'.$id.'/edit\');'.
        		'} else if ($("#ajax").is(":visible") && $("#gps-results").length) {'.
        			'Runalyze.Overlay.load(\'activity/'.$id.'/elevation-info\');'.
        		'}'
        	);
        } else {
        	echo __('Elevation data could not be retrieved.');
        }

        return new Response;
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

        $Window = new Window($context);
        $Window->display();

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

        $ElevationInfo = new \ElevationInfo($context);
        $ElevationInfo->display();

        return new Response();
    }

    /**
     * @Route("/activity/{id}/time-series-info", requirements={"id" = "\d+"}, name="activity-tool-time-series-info")
     * @ParamConverter("trackdata", class="CoreBundle:Trackdata", options={"activity" = "id"}, isOptional="true")
     * @Security("has_role('ROLE_USER')")
     */
    public function timeSeriesInfoAction($id, Account $account, Trackdata $trackdata = null)
    {
        if (null === $trackdata) {
            return $this->render('activity/tool/not_possible.html.twig');
        }

        if ($trackdata->getAccount()->getId() != $account->getId()) {
            throw $this->createAccessDeniedException();
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
     * @ParamConverter("trackdata", class="CoreBundle:Trackdata", options={"activity" = "id"}, isOptional="true")
     * @Security("has_role('ROLE_USER')")
     */
    public function subSegmentInfoAction($id, Account $account, Trackdata $trackdata = null)
    {
        if (null === $trackdata) {
            return $this->render('activity/tool/not_possible.html.twig');
        }

        if ($trackdata->getAccount()->getId() != $account->getId()) {
            throw $this->createAccessDeniedException();
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

        if ((!$activity->isPublic() && $account == null)) {
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

    /**
     * @Route("/call/call.Exporter.export.php")
     * @Route("/activity/{id}/export/{type}/{typeid}", requirements={"id" = "\d+"})
     * @Security("has_role('ROLE_USER')")
     */
    public function exporterExportAction($id, $type, $typeid, Account $account) {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        if ($type == 'social' && Share\Types::isValidValue((int)$typeid)) {
            $Context = new Context((int)$id, $account->getId());
            $Exporter = Share\Types::get((int)$typeid, $Context);

            if ($Exporter instanceof Share\AbstractSnippetSharer) {
                $Exporter->display();
            }
        } elseif ($type == 'file' && File\Types::isValidValue((int)$typeid)) {
            $Context = new Context((int)$id, $account->getId());
            $Exporter = File\Types::get((int)$typeid, $Context);

            if ($Exporter instanceof File\AbstractFileExporter) {
                $Exporter->downloadFile();
                exit;
            }
        }

        return new Response();
    }

    /**
     * @Route("/call/ajax.activityMatcher.php")
     * @Route("/activity/matcher", name="activityMatcher")
     * @Security("has_role('ROLE_USER')")
     */
    public function ajaxActivityMatcher(Account $account)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $IDs     = array();
        $Matches = array();
        $Array   = explode('&', urldecode(file_get_contents('php://input')));
        foreach ($Array as $String) {
        	if (substr($String,0,12) == 'externalIds=')
        		$IDs[] = substr($String,12);
        }

        $IgnoreIDs = \Runalyze\Configuration::ActivityForm()->ignoredActivityIDs();
        $DuplicateFinder = new DuplicateFinder(\DB::getInstance(), $account->getId());

        $IgnoreIDs = array_map(function($v){
        	try {
        		return (int)floor($this->parserStrtotime($v)/60)*60;
        	} catch (\Exception $e) {
        		return 0;
        	}
        }, $IgnoreIDs);

        foreach ($IDs as $ID) {
            try {
                $dup = $DuplicateFinder->checkForDuplicate((int)floor($this->parserStrtotime($ID)/60)*60);
            } catch (\Exception $e) {
                $dup = false;
            }

            $found = $dup || in_array($ID, $IgnoreIDs);
            $Matches[$ID] = array('match' => $found);
        }

        return new JsonResponse([
            'matches' => $Matches
        ]);
    }

    /**
     * Adjusted strtotime
     * Timestamps are given in UTC but local timezone offset has to be considered!
     * @param string $string
     * @return int
     */
    private function parserStrtotime($string)
    {
        if (substr($string, -1) == 'Z') {
            return LocalTime::fromServerTime((int)strtotime(substr($string, 0, -1).' UTC'))->getTimestamp();
        }

        return LocalTime::fromString($string)->getTimestamp();
    }
}
