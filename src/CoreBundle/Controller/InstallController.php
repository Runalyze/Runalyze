<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Bundle\CoreBundle\Console\Formatter\HtmlOutputFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class InstallController
 * @author Hannes Christiansen <hannes@runalyze.de>
 * @author Michael Pohl <michael@runalyze.de>
 * @package Runalyze\Bundle\CoreBundle\Controller
 */
class InstallController extends Controller
{
     /**
      * @Route("/install", name="install", condition="'%update_disabled%' == 'no'")
      */
    public function installAction(Request $request)
    {
        $session = $request->getSession();

        if ($session->has('installer/successful')) {
            return $this->redirectToRoute('install_finish');
        }

        $app = new Application($this->get('kernel'));
        $app->setAutoExit(false);

        $input = new StringInput('runalyze:install:check');
        $output = new BufferedOutput(BufferedOutput::VERBOSITY_NORMAL, true, new HtmlOutputFormatter(true));
        $exitCode = $app->run($input, $output);

        $session->set('installer/possible', $exitCode == 0);

        return $this->render('system/install.html.twig', [
            'output' => '$ php bin/console runalyze:install:check'."\n\n".$output->fetch(),
            'installationPossible' => $exitCode == 0,
            'installationSuccessful' => false
        ]);
    }

    /**
     * @Route("/install/start", name="install_start", condition="'%update_disabled%' == 'no'")
     */
    public function startAction(Request $request)
    {
        $session = $request->getSession();

        if (!$session->has('installer/possible') || !$session->get('installer/possible')) {
            return $this->redirectToRoute('install');
        }

        if ($session->has('installer/successful')) {
            return $this->redirectToRoute('install_finish');
        }

        $app = new Application($this->get('kernel'));
        $app->setAutoExit(false);

        $input = new StringInput('runalyze:install:database --skip=check');
        $output = new BufferedOutput(BufferedOutput::VERBOSITY_NORMAL, true, new HtmlOutputFormatter(true));
        $exitCode = $app->run($input, $output);
        $outputString = '$ php bin/console runalyze:install:database --skip=check'."\n\n".$output->fetch();

        if ($exitCode > 0) {
            return $this->render('system/install.html.twig', [
                'output' => $outputString,
                'installationPossible' => false,
                'installationSuccessful' => false
            ]);
        }

        $session->set('installer/successful', true);
        $session->set('installer/output', $outputString);

        return $this->redirectToRoute('install_finish');
    }

    /**
     * @Route("/install/finish", name="install_finish", condition="'%update_disabled%' == 'no'")
     */
    public function finishAction(Request $request)
    {
        $session = $request->getSession();

        if (!$session->has('installer/successful')) {
            return $this->redirectToRoute('install');
        }

        return $this->render('system/install.html.twig', [
            'output' => $session->get('installer/output', ''),
            'installationPossible' => false,
            'installationSuccessful' => true
        ]);
    }
}
