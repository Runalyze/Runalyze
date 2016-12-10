<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StaticController extends Controller
{
    /**
     * @Route("/dashboard/help", name="help")
     * @Security("has_role('ROLE_USER')")
     */
    public function dashboardHelpAction()
    {
        return $this->render('static/help.html.twig', [
            'version' => $this->getParameter('RUNALYZE_VERSION')
        ]);
    }

    /**
     * @Route("/dashboard/help-calculations", name="help-calculations")
     * @Security("has_role('ROLE_USER')")
     */
    public function dashboardHelpCalculationsAction()
    {
        return $this->render('static/help_calculations.html.twig');
    }
}
