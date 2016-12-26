<?php

namespace Runalyze\Bundle\CoreBundle\Controller\My;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Raceresult;
use Runalyze\Bundle\CoreBundle\Entity\RaceresultRepository;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Runalyze\Bundle\CoreBundle\Form\RaceResultType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
     * @Route("/my/raceresult/{activityId}", name="raceresult-form")
     */
    public function raceresultFormAction($activityId, Account $account, Request $request)
    {
        /** @var null|Training $activity */
        $activity = $this->getDoctrine()->getRepository('CoreBundle:Training')->findForAccount($activityId, $account->getId());

        if (null === $activity) {
            return $this->render('my/raceresult/error.html.twig');
        }

        /** @var null|Raceresult $raceResult */
        $raceResult = $this->getDoctrine()->getRepository('CoreBundle:Raceresult')->findForAccount($activityId, $account->getId());

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
            //dump($form->getClickedButton());

            //$this->getRaceresultRepository()->save($raceResult);
        }
        //Add race result to activity


        return $this->render('my/raceresult/form.html.twig', [
            'form' => $form->createView(),
            'isNew' => $isNew,
            'activityId' => $activityId
        ]);
    }

    /**
     * @Route("/my/raceresult/{activityId}/delete", name="raceresult-delete")
     */
    public function raceresultDeleteAction($activityId, Request $request)
    {

    }

}