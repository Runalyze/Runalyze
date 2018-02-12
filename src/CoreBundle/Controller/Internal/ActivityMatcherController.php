<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Internal;

use Runalyze\Bundle\CoreBundle\Entity\Training;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Runalyze\Util\LocalTime;

class ActivityMatcherController extends Controller
{
    /**
     * @Route("/_internal/activity/matcher", name="internal-activity-matcher")
     * @Security("has_role('ROLE_USER')")
     */
    public function ajaxActivityMatcher()
    {
        $ids = [];
        $matches = [];
        $input = explode('&', urldecode(file_get_contents('php://input')));

        foreach ($input as $line) {
            if (substr($line,0,12) == 'externalIds=') {
                $ids[] = substr($line,12);
            }
        }

        $duplicateFinder = $this->get('app.activity_duplicate_finder');
        $ignoredActivityIds = array_map(function($v) {
            try {
                return (int)floor($this->parserStrtotime($v) / 60.0) * 60.0;
            } catch (\Exception $e) {
                return 0;
            }
        }, $this->get('app.configuration_manager')->getList()->getActivityForm()->getIgnoredActivityIds());

        foreach ($ids as $id) {
            try {
                $possibleDuplicate = $duplicateFinder->isPossibleDuplicate(
                    (new Training())->setTime($this->parserStrtotime($id))
                );
            } catch (\Exception $e) {
                $possibleDuplicate = false;
            }

            $matches[$id] = ['match' => $possibleDuplicate || in_array($id, $ignoredActivityIds)];
        }

        return new JsonResponse([
            'matches' => $matches
        ]);
    }

    /**
     * Adjusted strtotime
     * Timestamps are given in UTC but local timezone offset has to be considered!
     *
     * @param string $string
     *
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
