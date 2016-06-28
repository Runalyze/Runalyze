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


/**
 * Class SystemController
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
    
    /**
     * @Route("/install", name="install")
     */
    public function installAction()
    {
	$fs = new Filesystem();
	$confAvailable = $fs->exists($this->get('kernel')->getRootDir().'/../data/config.yml');
	$confAvailable = false;
	
	return $this->render('system/install.html.twig', [
            'confAvailable' => $confAvailable
        ]);
    }
    
    /**
     * @Route("/install/step/1", name="install_step1")
     */
    public function installStepOneAction(Request $request)
    {
	$fs = new Filesystem();
	$confAvailable = $fs->exists($this->get('kernel')->getRootDir().'/../data/config.yml');
	$ConnectionAvailable = false;
	
        $formFactory = Forms::createFormFactoryBuilder()
	    ->addExtension(new HttpFoundationExtension())
	    ->getFormFactory();

        $form = $formFactory->createBuilder()
            ->add('database_host', Type\TextType::class, array('label' => 'Database server'))
	    ->add('database_name', Type\TextType::class, array('label' => 'Database name'))
            ->add('database_port', Type\IntegerType::class, array('label' => 'Database port', 'data' => 3306))
	    ->add('database_user', Type\TextType::class, array('label' => 'User'))
            ->add('database_password', Type\TextType::class, array('label' => 'Password', 'required' => false))
	    ->add('database_prefix', Type\TextType::class, array('label' => 'Prefix*', 'data' => 'runalyze_'))
	    ->add('garmin_api_key', Type\TextType::class, array('label' => 'Garmin API key**', 'required' => false))
	    ->add('mail_sender', Type\EmailType::class, array('label' => 'Sender e-mail'))	
            ->add('save', Type\SubmitType::class, array('label' => 'Check settings & write config'))
            ->getForm();

	$form->handleRequest($request);
	
	if ($form->isValid()) {
	    $data = $form->getData();

	    dump($data);
	    
	    //Check connection
	    $config = new \Doctrine\DBAL\Configuration();

	    $connectionParams = array(
		'dbname' => $data['database_name'],
		'user' => $data['database_user'],
		'password' => $data['database_password'],
		'host' => $data['database_host'],
		'port' => $data['database_port'],
		'charset' => 'utf8',
		'driver' => 'pdo_mysql',
	    );
	    $connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
	    $ConnectionAvailable = $connection->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$data['database_name']."'")->fetch();
	    dump($ConnectionAvailable);
	    $defaultConfig = Yaml::parse(file_get_contents('../app/config/localconfig.yml'));
	    $installConfig = array('database_host',
				'database_name',
				'database_port',
				'database_user',
				'database_password',
				'database_prefix',
				'garmin_api_key',
				'mail_sender');
	    foreach($installConfig as $confVar) {
		if (isset($_POST[$confVar]))
		    $defaultConfig['parameters'][$confVar] = $data[$confVar];
	    }
	    $yaml = Yaml::dump($defaultConfig);
	    $FileHandleNew = fopen('../data/config.yml', 'w' );
	    fwrite($FileHandleNew, $yaml);
	    fclose($FileHandleNew);
	    return $this->redirect($this->generateUrl('install_step2'));
	    //$response->prepare($request);

	    //return $response->send();
	}
	
        return $this->render('system/install-step1.html.twig', [
	    'form' => $form->createView(),
        ]);
    }
	
    /**
     * @Route("/install/step/2", name="install_step2")
     */
    public function installStepTwoAction(Request $request)
    {


        return $this->render('system/install-step2.html.twig', [
	    'form' => 'test',
        ]);
    }
    
}
