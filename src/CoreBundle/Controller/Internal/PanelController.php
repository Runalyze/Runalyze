<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Internal;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Runalyze\Util\LocalTime;

/**
 * @Route("/_internal/panel")
 */
class PanelController extends Controller
{

    /**
     * @Route("/sport", name="internal-sport-panel")
     * @Security("has_role('ROLE_USER')")
     */
    public function sportStatAction(Request $request, Account $account)
    {
        $sportRepository = $this->getDoctrine()->getRepository('CoreBundle:Sport');
        $today = (new LocalTime())->setTime(0, 0, 0);

        return new JsonResponse( [
            'weekStatistics' => $sportRepository->getSportStatisticsSince($today->weekstart(), $account, true),
            'monthStatistics' => $sportRepository->getSportStatisticsSince($today->setDate($today->format('Y'), $today->format('m'), 1)->getTimestamp(), $account, true),
            'yearStatistics' => $sportRepository->getSportStatisticsSince($today->setDate($today->format('Y'), 1, 1)->getTimestamp(), $account, true),
            'totalStatistics' => $sportRepository->getSportStatisticsSince(null, $account, true)
        ]);
    }
}
