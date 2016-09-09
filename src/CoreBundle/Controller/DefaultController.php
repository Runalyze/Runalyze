<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Activity\Distance;
use Runalyze\Parameter\Application\Timezone;
use Runalyze\Bundle\CoreBundle\Form\RegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use SessionAccountHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 * @package Runalyze\Bundle\CoreBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @param string $file
     * @param bool $initFrontend
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function includeOldScript($file, $initFrontend = true)
    {
        if ($initFrontend) {
            $Frontend = new \Frontend(false, $this->get('security.token_storage'));
        }

        include $file;

        return $this->render('legacy_end.html.twig');
    }

    /**
     * @Route("/dashboard", name="dashboard")
     * @Route("/", name="base_url")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction()
    {
	    $Frontend = new \Frontend(true, $this->get('security.token_storage'));
	    include '../dashboard.php';

        return $this->render('legacy_end.html.twig');
    }

    /**
     * @Route("/{_locale}/register", name="register")
     */
    public function registerAction(Request $request)
    {
        new \Frontend(true, $this->get('security.token_storage'));

        if (!$this->getParameter('user_can_register')) {
            return $this->render('register/disabled.html.twig');
        }

        $account = new Account();
        $form = $this->createForm(RegistrationType::class, $account);
        $form->handleRequest($request);

        //<a href="https://blog.runalyze.com/nutzungsbedingungen/
        if ($form->isSubmitted() && $form->isValid()) {
            $formdata = $request->request->get($form->getName());
            $em = $this->getDoctrine()->getManager();
            $account->setLanguage($request->getLocale());
            try {
                $account->setTimezone(Timezone::getEnumByOriginalName($formdata['textTimezone']));
            } catch (\InvalidArgumentException $e) {
                $account->setTimezone(Timezone::getEnumByOriginalName(date_default_timezone_get()));
            }
            if (!\System::isAtLocalhost() || $this->getParameter('user_disable_account_activation')) {
                $account->setActivationHash(\AccountHandler::getRandomHash());
            }

            $encoder = $this->container->get('security.encoder_factory')->getEncoder($account);
            $account->setPassword($encoder->encodePassword($account->getPlainPassword(), $account->getSalt()));

            $em->persist($account);
            $em->flush();

            $mailSent = \AccountHandler::createNewUserFrom($account->getId());

            if (\System::isAtLocalhost() || $this->getParameter('user_disable_account_activation') || !$mailSent) {
                return $this->render('account/activate/success.html.twig');
            }

            return $this->render('register/mail_delivered.html.twig');

        }

        return $this->render('register/form.html.twig', [
            'form' => $form->createView(),
            'num' => $this->collectStatistics()
        ]);
    }

    /**
     * @Route("/{_locale}/login", name="login")
     */
    public function loginAction(Request $request)
    {
    	if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
    	    return $this->redirect($this->generateUrl('dashboard'));
    	}

        if ($request->isXmlHttpRequest()) {
            return $this->render('login/ajax_not_logged_in.html.twig');
        }

    	$authenticationUtils = $this->get('security.authentication_utils');
    	$error = $authenticationUtils->getLastAuthenticationError();
    	$lastUsername = $authenticationUtils->getLastUsername();

        new \Frontend(true, $this->get('security.token_storage'));

        if (\SessionAccountHandler::isLoggedIn()) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('login/form.html.twig', [
   	        'error' => $error,
            'num' => $this->collectStatistics()
        ]);
    }

    /**
     * @Route("/{_locale}/login_check", name="login_check")
     */
    public function loginCheckAction()
    {
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        return $this->redirectToRoute('login');
    }

    /**
     * @return array ['user' => (int)..., 'distance' => (string)...]
     */
    protected function collectStatistics()
    {
	$repository = $this->getDoctrine()->getRepository('CoreBundle:Account');
	$numUser =  $repository->getAmountOfActivatedUsers();

	$repository = $this->getDoctrine()->getRepository('CoreBundle:Training');
	$numDistance =  $repository->getAmountOfLoggedKilometers();

        return ['user' => (int)$numUser, 'distance' => Distance::format($numDistance)];
    }

    /**
     * @Route("/admin.php", name="admin")
     */
    public function adminAction()
    {
    	$Frontend = new \Frontend(true);
    	$Frontend->displayAdminView();

    	return new Response();
    }

    /**
     * @Route("/plugin/{plugin}/{file}")
     */
    public function pluginAction($plugin, $file)
    {
        return $this->includeOldScript('../plugin/'.$plugin.'/'.$file);
    }

    /**
     * @Route("/dashboard/help", name="help")
     */
    public function dashboardHelpAction()
    {
        new \Frontend(true, $this->get('security.token_storage'));

        return $this->render('pages/help.html.twig', [
            'version' => RUNALYZE_VERSION
        ]);
    }

    /**
     * @Route("/shared/{training}")
     */
    public function sharedTrainingAction($training, Request $request)
    {
        $_GET['url'] = $training;
        $Frontend = new \FrontendShared();

        if (\FrontendShared::$IS_IFRAME)
        	echo '<div id="statistics-inner" class="panel" style="width:97%;margin:0 auto;">';
        elseif (!$request->isXmlHttpRequest())
        	echo '<div id="statistics-inner" class="panel" style="width:960px;margin:5px auto;">';
        else
        	echo '<div>';

        $Frontend->displaySharedView();

        echo '</div>';

        return $this->render('legacy_end.html.twig');
    }

    /**
     * @Route("/shared/{user}/")
     */
    public function sharedUserAction($user, Request $request)
    {
        $_GET['user'] = $user;

        if (isset($_GET['view'])) {
            $_GET['type'] = ($_GET['view'] == 'monthkm') ? 'month' : 'week';

            return $this->forward('CoreBundle:Call:windowsPlotSumDataShared');
        }

        $Frontend = new \FrontendSharedList();

        if (!$request->isXmlHttpRequest()) {
        	if ($Frontend->userAllowsStatistics()) {
        		echo '<div class="panel" style="width:960px;margin:5px auto;">';
        		$Frontend->displayGeneralStatistics();
        		echo '</div>';
        	}

        	echo '<div id="data-browser" class="panel" style="width:960px;margin:5px auto;">';
        	echo '<div id="'.DATA_BROWSER_SHARED_ID.'">';
        }

        $Frontend->displaySharedView();

        if (!$request->isXmlHttpRequest()) {
        	echo '</div>';
        	echo '</div>';

        	echo '<div id="statistics-inner" class="panel" style="width:960px;margin:5px auto;">
        	<div class="panel-content">
        		<p class="info">
        			'.__('Click on an activity to see more details.').'<br>
        			'.__('Public activities are marked: ').' '.\Icon::$ADD_SMALL_GREEN.'.
        		</p>
        	</div>
        </div>';
        }

        return $this->render('legacy_end.html.twig');
    }

    /**
     * @Route("/index.php")
     */
    public function indexPhpAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $Frontend = new \Frontend(true, $this->get('security.token_storage'));

            include $this->getParameter('kernel.root_dir').'/../dashboard.php';

            return new Response();
        }

        return $this->redirectToRoute('base_url');
    }

    /**
     * @Route("/login.php")
     */
    public function loginPhpAction()
    {
        return $this->redirectToRoute('login');
    }

}
