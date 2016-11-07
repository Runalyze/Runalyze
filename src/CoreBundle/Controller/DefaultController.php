<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Activity\Distance;
use Runalyze\Bundle\CoreBundle\Component\Account\Registration;
use Runalyze\Bundle\CoreBundle\Form\RegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use SessionAccountHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swift_Message;

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
        if (!$this->getParameter('user_can_register')) {
            return $this->render('register/disabled.html.twig');
        }

        $account = new Account();
        $form = $this->createForm(RegistrationType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registration = new Registration($this->getDoctrine()->getManager(), $account);
            $formdata = $request->request->get($form->getName());

            $registration->setLocale($request->getLocale());
            $registration->setTimezoneByName($formdata['textTimezone']);

            if (!$this->getParameter('user_disable_account_activation')) {
                $registration->requireAccountActivation();
            }
            $registration->setPassword($account->getPlainPassword(), $this->get('security.encoder_factory'));
            $account = $registration->registerAccount();

            $message = Swift_Message::newInstance($this->get('translator')->trans('Please activate your RUNALYZE account'))
                ->setFrom(array($this->getParameter('mail_sender') => $this->getParameter('mail_name')))
                ->setTo(array($account->getMail() => $account->getUsername()))
                ->setBody($this->renderView('mail/account/registration.html.twig',
                    array('username' => $account->getUsername(),
                        'activationHash' => $account->getActivationHash())
                    ),'text/html');
            $this->get('mailer')->send($message);

            if ($this->getParameter('user_disable_account_activation')) {
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
     * @Security("has_role('ROLE_USER')")
     */
    public function dashboardHelpAction()
    {
        return $this->render('pages/help.html.twig', [
            'version' => $this->getParameter('RUNALYZE_VERSION')
        ]);
    }

    /**
     * @Route("/shared/{training}")
     */
    public function sharedTrainingAction($training, Request $request)
    {
        $_GET['url'] = $training;
        $Frontend = new \FrontendShared();

        if ($request->query->get('mode') == 'iframe')
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
        return $this->redirectToRoute('dashboard');
    }

    /**
     * @Route("/unsubscribe/{mail}/{hash}", name="unsubscribe-mail")
     */
    public function unsubscribeMailAction($mail, $hash)
    {
        $repo = $this->getDoctrine()->getRepository('CoreBundle:Account');
        $account = $repo->findOneBy(array('mail' => $mail));

        if ($account && $hash == md5($account->getUsername())) {
            return $this->render('account/unsubscribe_info.html.twig', array('mail' => $mail, 'hash' => $hash));
        }

        return $this->render('account/unsubscribe_failure.html.twig');
    }

    /**
     * @Route("/unsubscribe/{mail}/{hash}/confirm", name="unsubscribe-mail-confirm")
     */
    public function unsubscribeMailConfirmAction($mail, $hash)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository('CoreBundle:Account');
        $account = $repo->findOneBy(array('mail' => $mail));
        if ($account && $hash == md5($account->getUsername())) {
            $account->setAllowMails(false);
            $em->persist($account);
            $em->flush();
            return $this->render('account/unsubscribe_success.html.twig');
        }

        return $this->render('account/unsubscribe_failure.html.twig');
    }

}
