<?php

namespace Runalyze\Bundle\CoreBundle\Controller\My;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Raceresult;
use Runalyze\Bundle\CoreBundle\Entity\RaceresultRepository;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Metrics\LegacyUnitConverter;
use Runalyze\Sports\Running\VO2max\Estimation\DanielsGilbertFormula;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Runalyze\Bundle\CoreBundle\Form\RaceResultType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/my/raceresult")
 * @Security("has_role('ROLE_USER')")
 */
class RaceResultController extends Controller
{
    /**
     * @return RaceresultRepository
     */
    protected function getRaceresultRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:Raceresult');
    }

    /**
     * @Route("/{activityId}", name="raceresult-form", requirements={"activityId" = "\d+"})
     * @param int $activityId
     * @param Account $account
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function raceresultFormAction($activityId, Account $account, Request $request)
    {
        /** @var null|Training $activity */
        $activity = $this->getDoctrine()->getRepository('CoreBundle:Training')->findForAccount($activityId, $account->getId());

        if (null === $activity) {
            throw $this->createAccessDeniedException();
        }

        /** @var null|Raceresult $raceResult */
        $raceResult = $this->getRaceresultRepository()->findForAccount($activityId, $account->getId());
        $isNew = false;

        if (null === $raceResult) {
            $isNew = true;
            $raceResult = new Raceresult();
            $raceResult->setAccount($account);
            $raceResult->fillFromActivity($activity);
        }

        $form = $this->createForm(RaceResultType::class, $raceResult, array(
            'action' => $this->generateUrl('raceresult-form', array('activityId' => $activityId))
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getRaceresultRepository()->save($raceResult);
        }

        return $this->render('my/raceresult/form.html.twig', [
            'form' => $form->createView(),
            'isNew' => $isNew,
            'activity' => $activity,
            'unitConverter' => new LegacyUnitConverter()
        ]);
    }

    /**
     * @Route("/{activityId}/delete", name="raceresult-delete", requirements={"activityId" = "\d+"})
     */
    public function raceresultDeleteAction($activityId, Request $request, Account $account)
    {
        /** @var null|Raceresult $raceResult */
        $raceResult = $this->getRaceresultRepository()->findForAccount($activityId, $account->getId());

        if ($raceResult) {
           $this->getRaceresultRepository()->delete($raceResult);
        } else {
            throw $this->createAccessDeniedException();
        }

        return $this->render('my/raceresult/deleted.html.twig');
    }

    /**
     * @Route("/performance-chart", name="race-results-performance-chart")
     * @Security("has_role('ROLE_USER')")
     */
    public function performanceChartAction(Account $account)
    {
        $danielsGilbertFormula = new DanielsGilbertFormula();
        $ageGradeLookup = $this->get('app.age_grade_lookup')->getLookup() ?: $this->get('app.age_grade_lookup')->getDefaultLookup();
        $distances = [0.06, 0.1, 0.2, 0.4, 0.8, 1.0, 1.5, 3.0, 5.0, 10.0, 21.1, 42.2, 50.0];
        $distanceTicks = [0.06, 0.1, 0.2, 0.4, 0.8, 1.5, 3.0, 5.0, 10.0, 21.1, 42.2];
        $ageStandardTimes = array_map(function($kilometer) use ($ageGradeLookup) {
            return $ageGradeLookup->getAgeStandard($kilometer);
        }, $distances);
        $ageStandardVO2max = array_map(function($kilometer, $seconds) use ($danielsGilbertFormula) {
            return $danielsGilbertFormula->estimateFromRaceResult($kilometer, $seconds);
        }, $distances, $ageStandardTimes);

        return $this->render('my/raceresult/performance_chart.html.twig', [
            'runningSportId' => $this->getDoctrine()->getRepository('CoreBundle:Sport')->findRunningFor($account)->getId(),
            'mainDistances' => $distances,
            'mainDistanceTicks' => $distanceTicks,
            'ageStandardTimes' => $ageStandardTimes,
            'ageStandardVO2max' => $ageStandardVO2max
        ]);
    }
}
