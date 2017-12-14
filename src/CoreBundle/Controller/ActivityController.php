<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Activity\DuplicateFinder;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\ClimbScoreCalculator;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\FlatOrHillyAnalyzer;
use Runalyze\Bundle\CoreBundle\Component\Activity\ActivityDecorator;
use Runalyze\Bundle\CoreBundle\Component\Activity\Tool\BestSubSegmentsStatistics;
use Runalyze\Bundle\CoreBundle\Component\Activity\Tool\TimeSeriesStatistics;
use Runalyze\Bundle\CoreBundle\Component\Activity\VO2maxCalculationDetailsDecorator;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Common\AccountRelatedEntityInterface;
use Runalyze\Bundle\CoreBundle\Entity\Raceresult;
use Runalyze\Bundle\CoreBundle\Entity\Route as EntityRoute;
use Runalyze\Bundle\CoreBundle\Entity\Trackdata;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Bundle\CoreBundle\Form\ActivityType;
use Runalyze\Bundle\CoreBundle\Services\AutomaticReloadFlagSetter;
use Runalyze\Bundle\CoreBundle\Services\Import\FileImportResultCollection;
use Runalyze\Calculation\Route\Calculator;
use Runalyze\Export\File;
use Runalyze\Export\Share;
use Runalyze\Metrics\Velocity\Unit\PaceEnum;
use Runalyze\Model\Activity;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Common\Filter\DefaultFilterCollection;
use Runalyze\Service\ElevationCorrection\Exception\NoValidStrategyException;
use Runalyze\Service\ElevationCorrection\StepwiseElevationProfileFixer;
use Runalyze\Util\LocalTime;
use Runalyze\View\Activity\Context;
use Runalyze\View\Activity\Dataview;
use Runalyze\View\Activity\Linker;
use Runalyze\View\Window\Laps\Window;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivityController extends Controller
{
    /**
     * @return TrainingRepository
     */
    protected function getTrainingRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:Training');
    }

    protected function checkThatEntityBelongsToActivity(AccountRelatedEntityInterface $entity, Account $account)
    {
        if ($entity->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }
    }

    /**
     * @Route("/activity/{id}/edit", name="activity-edit", requirements={"id" = "\d+"})
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("activity", class="CoreBundle:Training")
     */
    public function activityEditAction(Request $request, Training $activity, Account $account)
    {
        $this->checkThatEntityBelongsToActivity($activity, $account);

        $form = $this->createForm(ActivityType::class, $activity, [
            'action' => $this->generateUrl('activity-edit', ['id' => $activity->getId()])
        ]);
        ActivityType::setStartCoordinates($form, $activity);
        ActivityType::addDataSeriesRemoverFields($form, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.data_series_remover')->handleRequest($form->get('data_series_remover')->getData(), $activity);

            $this->getTrainingRepository()->save($activity);

            $this->addFlash('success', $this->get('translator')->trans('Changes have been saved.'));
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_ALL);
        }

        $context = $this->get('app.activity_context.factory')->getContext($activity);

        return $this->render('activity/form.html.twig', [
            'form' => $form->createView(),
            'isNew' => false,
            'decorator' => new ActivityDecorator($context),
            'activity_id' => $activity->getId(),
            'showElevationCorrectionLink' => $context->hasRoute() && !$context->getRoute()->hasCorrectedElevations()
        ]);
    }

    /**
     * @Route("/activity/add", name="activity-add")
     * @Security("has_role('ROLE_USER')")
     */
    public function createAction(Request $request)
    {
        $defaultUploadMode = $this->get('app.configuration_manager')->getList()->getActivityForm()->get('TRAINING_CREATE_MODE');

        if ('garmin' == $defaultUploadMode) {
            return $this->forward('CoreBundle:Activity:communicator');
        } elseif ('form' == $defaultUploadMode) {
            return $this->forward('CoreBundle:Activity:new');
        }

        return $this->getUploadFormResponse();
    }

    /**
     * @Route("/activity/communicator", name="activity-communicator")
     * @Security("has_role('ROLE_USER')")
     */
    public function communicatorAction()
    {
        return $this->render('activity/import_garmin_communicator.html.twig');
    }

    /**
     * @Route("/activity/communicator/iframe", name="activity-communicator-iframe")
     * @Security("has_role('ROLE_USER')")
     */
    public function communicatorIFrameAction()
    {
        return $this->render('import/garmin_communicator.html.twig');
    }

    /**
     * @Route("/activity/upload", name="activity-upload")
     * @Security("has_role('ROLE_USER')")
     */
    public function uploadAction(Request $request, Account $account)
    {
        $importResult = null;
        $importDir = $this->getParameter('data_directory').'/import/';

        if ($request->query->has('file')) {
            $importer = $this->get('app.file_importer');
            $importResult = $importer->importSingleFile($importDir.$request->query->get('file'));
        } elseif ($request->query->has('files')) {
            $importer = $this->get('app.file_importer');
            $importResult = $importer->importFiles(
                array_map(function ($file) use ($importDir) {
                    return $importDir.$file;
                }, explode(';', $request->query->get('files')))
            );
        }

        if (null !== $importResult) {
            return $this->getResponseForImportResults($importResult, $account, $request);
        }

        return $this->getUploadFormResponse();
    }

    /**
     * @param FileImportResultCollection $results
     * @param Account $account
     * @param Request $request
     * @return Response
     */
    protected function getResponseForImportResults(FileImportResultCollection $results, Account $account, Request $request)
    {
        foreach ($results as $result) {
            if ($result->isFailed()) {
                $this->addFlash('error', sprintf('%s: %s', pathinfo($result->getOriginalFileName(), PATHINFO_BASENAME), $result->getException()->getMessage()));
            }
        }

        $numActivities = $results->getTotalNumberOfActivities();

        if (1 == $numActivities) {
            return $this->getResponseForNewSingleActivity(
                $this->containerToActivity($results[0]->getContainer()[0], $account),
                $request
            );
        } elseif (1 < $numActivities) {
            // TODO: Multi importer form (DuplicateFinder + cache activity) + save form (convert to activity, weather forecast, persist)
            $this->addFlash('error', 'MultiImporter is not refactored so far.');
        }

        return $this->getUploadFormResponse();
    }

    /**
     * @return Response
     */
    protected function getUploadFormResponse()
    {
        $maxFileSize = \Filesystem::getMaximumFilesize();

        return $this->render('activity/import_upload.html.twig', [
            'maxFileSize' => $maxFileSize < PHP_INT_MAX ? $maxFileSize : false
        ]);
    }

    /**
     * @param ActivityDataContainer $container
     * @param Account $account
     * @return Training
     */
    protected function containerToActivity(ActivityDataContainer $container, Account $account)
    {
        $container->completeActivityData();

        $this->get('app.activity_data_container.filter')->filter($container);

        return $this->get('app.activity_data_container.converter')->getActivityFor($container, $account);
    }

    /**
     * @Route("/activity/new", name="activity-new")
     * @Security("has_role('ROLE_USER')")
     */
    public function newAction(Request $request, Account $account)
    {
        if ($request->query->has('date')) {
            $time = LocalTime::fromString($request->query->get('date'))->getTimestamp();
        } else {
            $time = null;
        }

        return $this->getResponseForNewSingleActivity(
            $this->getDefaultNewActivity($account, $time),
            $request,
            false
        );
    }

    protected function getResponseForNewSingleActivity(Training $activity, Request $request = null, $setCache = true)
    {
        $form = $this->createForm(ActivityType::class, $activity, [
            'action' => $this->generateUrl('activity-new')
        ]);
        ActivityType::setStartCoordinates($form, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleSubmitOfNewActivityForm($activity, $form);

            $this->addFlash('success', $this->get('translator')->trans('The activity has been successfully created.'));
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_ALL);

            return $this->render('util/close_overlay.html.twig');
        } elseif (!$form->isSubmitted() && $setCache) {
            $form->get('temporaryHash')->setData(
                $this->get('app.activity_context.cache')->save($activity)
            );
        }

        return $this->render('activity/form.html.twig', [
            'form' => $form->createView(),
            'isNew' => true,
            'isDuplicate' => $this->get('app.activity_duplicate_finder')->isPossibleDuplicate($activity)
        ]);
    }

    protected function handleSubmitOfNewActivityForm(Training $newActivity, Form $form)
    {
        // TODO: delete cache item as well?
        $activity = $this->get('app.activity_context.cache')->get($form->get('temporaryHash')->getData(), $newActivity);

        if ('' != $activity->getRouteName()) {
            if (!$activity->hasRoute()) {
                $activity->setRoute((new EntityRoute())->setAccount($activity->getAccount()));
            }

            if (0 != (int)$activity->getElevation() && 0 == $activity->getRoute()->getElevation()) {
                $activity->getRoute()->setElevation($activity->getElevation());
            }
        }

        if ($form->get('is_race')->getData()) {
            $raceResult = (new Raceresult())->fillFromActivity($activity);
            $activity->setRaceresult($raceResult);
        }

        $this->getTrainingRepository()->save($activity);
    }

    /**
     * @param Account $account
     * @param int|null $time
     * @return Training
     */
    protected function getDefaultNewActivity(Account $account, $time = null)
    {
        $activity = new Training();
        $activity->setAccount($account);
        $activity->setTime($time ?: LocalTime::now());
        $activity->setSport($this->getMainSport($account));

        if (null !== $activity->getSport()) {
            $activity->setType($activity->getSport()->getDefaultType());
        }

        return $activity;
    }

    /**
     * @param Account $account
     * @return null|\Runalyze\Bundle\CoreBundle\Entity\Sport
     */
    protected function getMainSport(Account $account)
    {
        $mainSportId = $this->get('app.configuration_manager')->getList()->getGeneral()->getMainSport();
        $sport = $this->getDoctrine()->getRepository('CoreBundle:Sport')->find($mainSportId);

        if (null === $sport || $account->getId() != $sport->getAccount()->getId()) {
            return null;
        }

        return $sport;
    }

    /**public function createAction()
    {
        $Frontend = new \Frontend(isset($_GET['json']), $this->get('security.token_storage'));

        if (class_exists('Normalizer')) {
        	if (isset($_POST['forceAsFileName'])) {
        		$_POST['forceAsFileName'] = \Normalizer::normalize($_POST['forceAsFileName']);
        	}
        }

        $Window = new \ImporterWindow();
        $Window->display();

        return new Response();
    }*/

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

    /*public function editAction($id)
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
    }*/

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
        $this->checkThatEntityBelongsToActivity($activity, $account);

        $configList = $this->get('app.configuration_manager')->getList();
        $activityContext = $this->get('app.activity_context.factory')->getContext($activity);

        return $this->render('activity/vo2max_info.html.twig', [
            'context' => $activityContext,
            'details' => new VO2maxCalculationDetailsDecorator($activityContext, $configList)
        ]);
    }

    /**
     * @Route("/activity/{id}/elevation-correction", name="activity-elevation-correction")
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

        $this->checkThatEntityBelongsToActivity($trackdata, $account);

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

        $this->checkThatEntityBelongsToActivity($trackdata, $account);

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

    /**
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
