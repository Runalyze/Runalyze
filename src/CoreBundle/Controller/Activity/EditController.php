<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Activity;

use Runalyze\Bundle\CoreBundle\Component\Activity\ActivityDecorator;
use Runalyze\Bundle\CoreBundle\Component\Activity\ActivityPreview;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Common\AccountRelatedEntityInterface;
use Runalyze\Bundle\CoreBundle\Entity\Raceresult;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Bundle\CoreBundle\Form\ActivityType;
use Runalyze\Bundle\CoreBundle\Services\AutomaticReloadFlagSetter;
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
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->has('data_series_remover')) {
                $this->get('app.data_series_remover')->handleRequest($form->get('data_series_remover')->getData(), $activity);
            }

            if ($form->get('is_race')->getData() && !$activity->hasRaceresult()) {
                $raceResult = (new Raceresult())->fillFromActivity($activity);
                $activity->setRaceresult($raceResult);
            } elseif (!$form->get('is_race')->getData() && $activity->hasRaceresult()) {
                $this->getDoctrine()->getRepository('CoreBundle:Raceresult')->delete($activity->getRaceresult());
                $activity->setRaceresult(null);
            }

            $repository->save($activity);
            $this->get('app.legacy_cache')->clearActivityCache($activity);

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
            'showElevationCorrectionLink' => $activity->hasRoute() && $activity->getRoute()->hasGeohashes() && !$activity->getRoute()->hasCorrectedElevations(),
            'isPowerLocked' => null !== $activity->isPowerCalculated()
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
    * @Route("/activity/{id}/delete", name="activity-delete", requirements={"id" = "\d+"})
    * @Security("has_role('ROLE_USER')")
    * @ParamConverter("activity", class="CoreBundle:Training")
    */
   public function deleteAction(Training $activity, Account $account)
   {
       $activityId = $activity->getId();

       $this->checkThatEntityBelongsToActivity($activity, $account);

       $this->getTrainingRepository()->remove($activity);

        return $this->render('activity/activity_has_been_removed.html.twig', [
            'multiEditorId' => (int)$activityId
        ]);
   }

    /**
     * @Route("/activity/{id}/elevation-correction", name="activity-elevation-correction", requirements={"id" = "\d+"})
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("activity", class="CoreBundle:Training")
     */
    public function elevationCorrectionAction(Request $request, Training $activity, Account $account)
    {
        $this->checkThatEntityBelongsToActivity($activity, $account);

        $translator = $this->get('translator');
        $success = false;

        if ($activity->hasRoute()) {
            $routeAdapter = $activity->getRoute()->getAdapter();
            $strategy = null;

            if ('none' == $request->query->get('strategy')) {
                $routeAdapter->removeElevation();
                $this->addFlash('notice', $translator->trans('Corrected elevation data has been removed.'));
                $success = true;
            } else {
                $strategy = $this->getElevationCorrectionStrategyFromRequest($request->query->get('strategy'));

                if ($routeAdapter->correctElevation($this->get('app.elevation_correction'), $strategy)) {
                    $this->addFlash('success', $translator->trans('Elevation data has been corrected.'));
                    $success = true;
                }
            }
        }

        if ($success) {
            $this->adjustAndSaveRouteAndActivityForElevationCorrection($activity);
        } else {
            $this->addFlash('error', $translator->trans('Elevation data could not be retrieved.'));
        }

        return $this->render('util/flashmessages_only.html.twig', [
            'reloadActivityOverlay' => $success,
            'activityId' => $activity->getId()
        ]);
    }

    protected function adjustAndSaveRouteAndActivityForElevationCorrection(Training $activity)
    {
        $configuration = $this->get('app.configuration_manager')->getList($activity->getAccount())->getActivityView();

        $activity->getRoute()->getAdapter()->calculateElevation(
            $configuration->getElevationCalculationMethod(),
            $configuration->getElevationCalculationThreshold()
        );

        $activityAdapter = $activity->getAdapter();
        $activityAdapter->useElevationFromRoute();
        $activityAdapter->calculateClimbScore();

        $this->getTrainingRepository()->save($activity);

        $this->get('app.legacy_cache')->clearActivityCache($activity);
        $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_TRAINING_AND_DATA_BROWSER);
    }

    /**
     * @param $string
     * @return null|\Runalyze\Service\ElevationCorrection\Strategy\StrategyInterface
     */
    protected function getElevationCorrectionStrategyFromRequest($string)
    {
        if ('GeoTIFF' == $string) {
            return $this->get('app.elevation_correction.geotiff');
        } elseif ('Geonames' == $string) {
            return $this->get('app.elevation_correction.geonames');
        } elseif ('GoogleMaps' == $string) {
            return $this->get('app.elevation_correction.google_maps');
        }

        return null;
    }
}
