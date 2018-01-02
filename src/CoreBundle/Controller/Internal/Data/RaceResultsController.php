<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Internal\Data;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Sports\Running\VO2max\Estimation\DanielsGilbertFormula;
use Runalyze\Util\LocalTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/_internal/data/race-results")
 */
class RaceResultsController extends Controller
{
    /**
     * @return \Runalyze\Bundle\CoreBundle\Entity\RaceresultRepository
     */
    protected function getRaceResultRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:Raceresult');
    }

    /**
     * @Route("/test", name="internal-data-race-results-test")
     * @Security("has_role('ROLE_USER')")
     */
    public function testAction()
    {
        $danielsGilbertFormula = new DanielsGilbertFormula();
        $ageGradeLookup = $this->get('app.age_grade_lookup')->getLookup() ?: $this->get('app.age_grade_lookup')->getDefaultLookup();
        $distances = [0.06, 0.1, 0.2, 0.4, 0.8, 1.0, 1.5, 3.0, 5.0, 10.0, 21.1, 42.2, 50.0];
        $distanceTicks = [0.06, 0.1, 0.2, 0.4, 0.8, 1.5, 3.0, 5.0, 10.0, 21.1, 42.2];
        $ageStandardTimes = array_map(function($kilometer) use ($ageGradeLookup) {
            // TODO: use better way (see https://github.com/Runalyze/age-grade/issues/3; these values are rounded)
            return $ageGradeLookup->getAgeGrade($kilometer, 1)->getAgeStandard();
        }, $distances);
        $ageStandardVO2max = array_map(function($kilometer, $seconds) use ($danielsGilbertFormula) {
            return $danielsGilbertFormula->estimateFromRaceResult($kilometer, $seconds);
        }, $distances, $ageStandardTimes);

        return $this->render('my/raceresult/performance_chart.html.twig', [
            'mainDistances' => $distances,
            'mainDistanceTicks' => $distanceTicks,
            'ageStandardTimes' => $ageStandardTimes,
            'ageStandardVO2max' => $ageStandardVO2max
        ]);
    }

    /**
     * @Route("/all", name="internal-data-race-results-all")
     * @Security("has_role('ROLE_USER')")
     */
    public function allRaceResultsAction(Account $account)
    {
        $result = [];
        $races = $this->getRaceResultRepository()->findAllWithActivityStats($account);
        $ageGradeLookup = $this->get('app.age_grade_lookup')->getLookup() ?: $this->get('app.age_grade_lookup')->getDefaultLookup();
        $runningId = $this->getDoctrine()->getRepository('CoreBundle:Sport')->findRunningFor($account)->getId();

        foreach ($races as $race) {
            // TODO: this should be done in the performance chart template
            if ($race->getActivity()->getSport()->getId() != $runningId) {
                continue;
            }

            $ageGrade = $ageGradeLookup->getAgeGrade(
                $race->getOfficialDistance(),
                $race->getOfficialTime(),
                (int) $race->getActivity()->getDateTime()->diff(new LocalTime())->format('%y')
            );

            $result[] = [
                'name' => $race->getName(),
                'date' => $race->getActivity()->getDateTime()->format('c'),
                'sport_id' => $race->getActivity()->getSport()->getId(),
                'distance' => $race->getOfficialDistance(),
                'duration' => $race->getOfficialTime(),
                'officially_measured' => $race->getOfficiallyMeasured(),
                'place_total' => $race->getPlaceTotal(),
                'place_gender' => $race->getPlaceGender(),
                'place_ageclass' => $race->getPlaceAgeclass(),
                'participants_total' => $race->getParticipantsTotal(),
                'participants_gender' => $race->getParticipantsGender(),
                'participants_ageclass' => $race->getParticipantsAgeclass(),
                'vo2max' => $race->getActivity()->getVO2max(),
                'vo2max_by_time' => $race->getActivity()->getVO2maxByTime(),
                'vo2max_with_elevation' => $race->getActivity()->getVO2maxWithElevation(),
                'age_grade' => $ageGrade->getPerformance()
            ];
        }

        return new JsonResponse($result);
    }
}
