<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class SystemControllerAlready at latest version
 * @author Hannes Christiansen <hannes@runalyze.de>
 * @author Michael Pohl <michael@runalyze.de>
 * @package Runalyze\Bundle\CoreBundle\Controller
 */
class SystemController extends Controller {
    
    /**
     * @Route("/update", name="update")
     */
    public function updateAction($entity_manager = 'default')
    {
	$kernel = $this->get('kernel');
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
           'command' => 'doctrine:migrations:status',
           '--em' => $entity_manager,
        ));
        $output = new BufferedOutput();
        $application->run($input, $output);

        $content = $output->fetch();
	$updateAvailable = false;
	if (substr_count($content, 'Already at latest version') == 1) {
	    $updateAvailable = true;
	}
	
        return $this->render('system/update.html.twig', [
            'updateAvailable' => $updateAvailable
        ]);
	
    }
    
    /**
     * @Route("/update/start", name="update_start")
     */
    public function updateStartAction($entity_manager = 'default')
    {
	$kernel = $this->get('kernel');
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
           'command' => 'doctrine:migrations:migrate',
           '--no-interaction',
        ));
        $output = new BufferedOutput();
        $application->run($input, $output);

        $content = $output->fetch();

	if (strpos($content, 'Could not find any migrations to execute.') OR strpos($content, 'No migrations to execute.')) {
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
