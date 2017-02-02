<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class UpdateController
 * @author Hannes Christiansen <hannes@runalyze.de>
 * @author Michael Pohl <michael@runalyze.de>
 * @package Runalyze\Bundle\CoreBundle\Controller
 */
class UpdateController extends Controller
{
    /**
     * @Route("/update", name="update", condition="'%update_disabled%' == 'no'")
     */
    public function updateAction($entity_manager = 'default')
    {
        $application = new Application($this->get('kernel'));
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
           'command' => 'doctrine:migrations:status',
           '--em' => $entity_manager,
        ));
        $output = new BufferedOutput();
        $application->run($input, $output);

        $content = $output->fetch();

        $input = new ArrayInput(array(
            'command' => 'cache:clear',
            '--env' => 'prod',
        ));
        $output = new BufferedOutput();
        $application->run($input, $output);

        $content .= $output->fetch();

		$updateAvailable = true;
		if (substr_count($content, 'Already at latest version') == 1) {
		    $updateAvailable = false;
		}

        return $this->render('system/update.html.twig', [
            'updateAvailable' => $updateAvailable,
            'migrationDump' => $content
        ]);
    }

    /**
     * @Route("/update/start", name="update_start", condition="'%update_disabled%' == 'no'")
     */
    public function updateStartAction()
    {
        $application = new Application($this->get('kernel'));
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
           'command' => 'doctrine:migrations:migrate',
           '--no-interaction',
        ));
        $output = new BufferedOutput();
        $application->run($input, $output);

        $content = $output->fetch();

		if (strpos($content, 'Could not find any migrations to execute.') || strpos($content, 'No migrations to execute.')) {
		    $migrationStatus = 'uptodate';
		} elseif (strpos($content, 'migrations executed')) {
		    $migrationStatus = 'executed';
		} else {
		    $migrationStatus = false;
		}

        return $this->render('system/update_start.html.twig', [
            'migrationStatus' => $migrationStatus,
	        'migrationDump' => $content
        ]);
    }
}
