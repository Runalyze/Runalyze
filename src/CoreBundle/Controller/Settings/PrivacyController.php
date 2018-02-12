<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Settings;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\ConfRepository;
use Runalyze\Bundle\CoreBundle\Form\Settings\PrivacyData;
use Runalyze\Bundle\CoreBundle\Form\Settings\PrivacyType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PrivacyController extends Controller
{
    /**
     * @return ConfRepository
     */
    protected function getConfRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:Conf');
    }

    /**
     * @Route("/settings/privacy", name="settings-privacy")
     * @Security("has_role('ROLE_USER')")
     */
    public function settingsAccountAction(Request $request, Account $account)
    {
        $privacyConfig = $this->get('app.configuration_manager')->getList()->getPrivacy();

        $privacy = new PrivacyData();
        $privacy->setDataFrom($privacyConfig);

        $form = $this->createForm(PrivacyType::class, $privacy, array(
            'action' => $this->generateUrl('settings-privacy')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.configuration_updater')->updatePrivacyDetails($account, $privacy->getDataForConfiguration());

            $this->addFlash('success', $this->get('translator')->trans('Your changes have been saved!'));
        }

        return $this->render('settings/privacy.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
