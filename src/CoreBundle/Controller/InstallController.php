<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\DBAL\Configuration;
use Symfony\Component\Yaml\Yaml;
use Installer;




/**
 * Class InstallController
 * @author Hannes Christiansen <hannes@runalyze.de>
 * @author Michael Pohl <michael@runalyze.de>
 * @package Runalyze\Bundle\CoreBundle\Controller
 */
class InstallController extends Controller {
     /**
     * @Route("/install", name="install")
     */
    public function installAction()
    {
		$installer = new Installer($this->getDoctrine(), $this->getParameter('database_prefix'));
		
		return $this->render('system/install.html.twig', [
				'alreadyInstalled' => $installer->isAlreadyInstalled()
	    ]);
    }
    
    /**
     * @Route("/install/step/1", name="install_step1")
     */
    public function installStepOneAction(Request $request)
    {
    	$installer = new Installer($this->getDoctrine(), $this->getParameter('database_prefix'));
    	if (!$installer->isAlreadyInstalled()) {
    		$installer->installRunalyze();
    		$installer->checkIfIsAlreadyInstalled();
    		if ($installer->isAlreadyInstalled()) {
    			return $this->redirect($this->generateUrl('install_finish'));
    		}
    	} else {
			return $this->render('system/install.html.twig', [
					'alreadyInstalled' => $installer->isAlreadyInstalled()
		    ]);
    	}
    }
	
    /**
     * @Route("/install/finish", name="install_finish")
     */
    public function installStepTwoAction(Request $request)
    {
        return $this->render('system/install-finish.html.twig');
    }
    
}
