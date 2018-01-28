<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Activity;

use Runalyze\Bundle\CoreBundle\Component\Activity\ActivityContext;
use Runalyze\Bundle\CoreBundle\Component\Activity\ActivityPreview;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Raceresult;
use Runalyze\Bundle\CoreBundle\Entity\Route as EntityRoute;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Form\ActivityType;
use Runalyze\Bundle\CoreBundle\Form\MultiImporterType;
use Runalyze\Bundle\CoreBundle\Services\AutomaticReloadFlagSetter;
use Runalyze\Bundle\CoreBundle\Services\Import\FileImportResultCollection;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Util\LocalTime;
use Runalyze\Util\ServerParams;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CreateController extends Controller
{
    /**
     * @return \Runalyze\Bundle\CoreBundle\Entity\TrainingRepository
     */
    protected function getTrainingRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:Training');
    }

    /**
     * @Route("/activity/add", name="activity-add")
     * @Security("has_role('ROLE_USER')")
     */
    public function createAction()
    {
        $defaultUploadMode = $this->get('app.configuration_manager')->getList()->getActivityForm()->get('TRAINING_CREATE_MODE');

        if ('garmin' == $defaultUploadMode) {
            return $this->forward('CoreBundle:Activity\Create:communicator');
        } elseif ('form' == $defaultUploadMode) {
            return $this->forward('CoreBundle:Activity\Create:new');
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
        return $this->render('import/garmin_communicator.html.twig', [
            'garminAPIKey' => $this->getParameter('garmin_api_key'),
        ]);
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
        $results->completeAndFilterResults($this->get('app.activity_data_container.filter'));

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
            return $this->getResponseForMultipleNewActivities($results, $request, $account);
        }

        return $this->getUploadFormResponse();
    }

    /**
     * @return Response
     */
    protected function getUploadFormResponse()
    {
        $serverParams = new ServerParams();
        $maxFileSize = min($serverParams->getPostMaxSizeInBytes(), $serverParams->getUploadMaxFilesize());

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
        return $this->get('app.activity_data_container.converter')->getActivityFor($container, $account);
    }

    protected function getResponseForMultipleNewActivities(FileImportResultCollection $results, Request $request, Account $account)
    {
        $cache = $this->get('app.activity_context.cache');
        $duplicateFinder = $this->get('app.activity_duplicate_finder');
        $activityHashes = [];
        $errors = [];
        $previews = [];

        foreach ($results as $result) {
            if ($result->isFailed()) {
                $errors[] = sprintf('%s: %s', $result->getOriginalFileName(), $result->getException()->getMessage());
            } else {
                foreach ($result->getContainer() as $container) {
                    $activity = $this->containerToActivity($container, $account);
                    $previews[] = new ActivityPreview($activity, $duplicateFinder->isPossibleDuplicate($activity));
                    $activityHashes[] = $cache->save($activity);
                }
            }
        }

        $form = $this->createForm(MultiImporterType::class, $activityHashes, [
            'action' => $this->generateUrl('activity-multi-importer')
        ]);
        $form->handleRequest($request);

        return $this->render('activity/multi_importer.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'previews' => $previews
        ]);
    }

    /**
     * @Route("/activity/multi-import", name="activity-multi-importer")
     * @Security("has_role('ROLE_USER')")
     */
    public function multiImporterAction(Request $request, Account $account)
    {
        $form = $this->createForm(MultiImporterType::class, [], [
            'action' => $this->generateUrl('activity-multi-importer')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $hashes = $form->get('activity')->getData();

            if (!is_array($hashes)) {
                return $this->redirectToRoute('activity-upload');
            }

            $repository = $this->getTrainingRepository();
            $cache = $this->get('app.activity_context.cache');
            $contextAdapterFactory = $this->get('app.activity_context_adapter_factory');
            $defaultLocation = $this->get('app.configuration_manager')->getList()->getActivityForm()->getDefaultLocationForWeatherForecast();
            $activityIds = [];

            foreach ($hashes as $hash) {
                $activity = $cache->get($hash, null, true);
                $activity->setAccount($account);
                $activity->getAdapter()->setAccountToRelatedEntities();

                $context = new ActivityContext($activity, null, null, $activity->getRoute());
                $contextAdapterFactory->getAdapterFor($context)->guessWeatherConditions($defaultLocation);

                $activityIds[] = $repository->save($activity, true);
            }

            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_ALL);

            if ($form->get('show_multi_editor')->getData()) {
                return $this->redirectToRoute('multi-editor', ['ids' => implode(',', $activityIds)]);
            }

            $this->addFlash('success', $this->get('translator')->trans('The activities have been successfully imported.'));

            return $this->render('util/close_overlay.html.twig');
        }

        return $this->redirectToRoute('activity-upload');
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
            'isDuplicate' => $this->get('app.activity_duplicate_finder')->isPossibleDuplicate($activity),
            'isPowerLocked' => null !== $activity->isPowerCalculated()
        ]);
    }

    protected function handleSubmitOfNewActivityForm(Training $newActivity, Form $form)
    {
        $activity = $this->get('app.activity_context.cache')->get($form->get('temporaryHash')->getData(), $newActivity, true);

        if ('' != $activity->getRouteName()) {
            if (!$activity->hasRoute()) {
                $activity->setRoute((new EntityRoute())->setAccount($activity->getAccount()));
            }

            $activity->getRoute()->setName($activity->getRouteName());

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
        $activity->setPublic(!$activity->getSport()->getDefaultPrivacy());

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
        $sport = $this->getDoctrine()->getRepository('CoreBundle:Sport')->findThisOrAny($mainSportId, $account);

        if (null === $sport || $account->getId() != $sport->getAccount()->getId()) {
            return null;
        }

        return $sport;
    }
}
