<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Activity;

use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\ClimbScoreCalculator;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\FlatOrHillyAnalyzer;
use Runalyze\Bundle\CoreBundle\Component\Activity\ActivityDecorator;
use Runalyze\Bundle\CoreBundle\Component\Activity\ActivityPreview;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Common\AccountRelatedEntityInterface;
use Runalyze\Bundle\CoreBundle\Entity\Trackdata;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Bundle\CoreBundle\Form\ActivityType;
use Runalyze\Bundle\CoreBundle\Services\AutomaticReloadFlagSetter;
use Runalyze\Calculation\Route\Calculator;
use Runalyze\Model\Activity;
use Runalyze\Service\ElevationCorrection\Exception\NoValidStrategyException;
use Runalyze\Service\ElevationCorrection\StepwiseElevationProfileFixer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EditController extends Controller
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

        $repository = $this->getTrainingRepository();
        $form = $this->createForm(ActivityType::class, $activity, [
            'action' => $this->generateUrl('activity-edit', ['id' => $activity->getId()])
        ]);
        ActivityType::setStartCoordinates($form, $activity);
        ActivityType::addDataSeriesRemoverFields($form, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->has('data_series_remover')) {
                $this->get('app.data_series_remover')->handleRequest($form->get('data_series_remover')->getData(), $activity);
            }

            // TODO: Handle 'is_race' checkbox

            $repository->save($activity);

            $this->addFlash('success', $this->get('translator')->trans('Changes have been saved.'));
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_ALL);

            $nextId = $form->get('next-multi-editor')->getData();

            if (is_numeric($nextId)) {
                return $this->redirectToRoute('activity-edit', ['id' => (int)$nextId, 'multi' => '1']);
            }
        }

        $context = $this->get('app.activity_context.factory')->getContext($activity);

        return $this->render('activity/form.html.twig', [
            'form' => $form->createView(),
            'isNew' => false,
            'isMulti' => (bool)$request->get('multi', false),
            'decorator' => new ActivityDecorator($context),
            'activity_id' => $activity->getId(),
            'prev_activity_id' => $repository->getIdOfPreviousActivity($activity),
            'next_activity_id' => $repository->getIdOfNextActivity($activity),
            'showElevationCorrectionLink' => $activity->hasRoute() && !$activity->getRoute()->hasCorrectedElevations()
        ]);
    }

    /**
     * @Route("/activity/multi-editor", name="multi-editor")
     * @Security("has_role('ROLE_USER')")
     */
    public function multiEditorAction(Request $request, Account $account)
    {
        $ids = array_filter(explode(',', $request->get('ids', '')));

        return $this->getResponseForMultiEditor($ids, $account);
    }

    /**
     * @param array $activityIds
     * @param Account $account
     * @return Response
     */
    protected function getResponseForMultiEditor(array $activityIds, Account $account)
    {
        $previews = array_map(function (Training $activity) {
            return new ActivityPreview($activity);
        }, $this->getTrainingRepository()->getPartialEntitiesForPreview($activityIds, $account, 20));

        return $this->render('activity/multi_editor_navigation.html.twig', [
            'previews' => $previews
        ]);
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
}
