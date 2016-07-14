<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Activity\Distance;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

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

        return $this->render('CoreBundle:Default:end.html.twig');
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

        return $this->render('CoreBundle:Default:end.html.twig');
    }

    /**
     * @Route("/register", name="register")
     */
    public function registerAction()
    {
        new \Frontend(true, $this->get('security.token_storage'));

        if (!$this->getParameter('user_can_register') || $this->getParameter('user_cant_login')) {
            return $this->render('register/disabled.html.twig');
        }

        if (isset($_POST['new_username'])) {
            $registrationResult = \AccountHandler::tryToRegisterNewUser();

            if (true === $registrationResult) {
                if (\System::isAtLocalhost() || USER_DISABLE_ACCOUNT_ACTIVATION) {
                    return $this->render('account/activate/success.html.twig');
                }

                return $this->render('register/mail_delivered.html.twig');
            }
        } else {
            $registrationResult = [
                'failure' => [
                    'username' => false,
                    'email' => false,
                    'password' => false
                ],
                'errors' => []
            ];
        }

        return $this->render('register/form.html.twig', [
            'failure' => $registrationResult['failure'],
            'error_messages' => $registrationResult['errors'],
            'data' => [
                'username' => isset($_POST['new_username']) ? $_POST['new_username'] : '',
                'name' => isset($_POST['name']) ? $_POST['name'] : '',
                'email' => isset($_POST['email']) ? $_POST['email'] : ''
            ],
            'num' => $this->collectStatistics()
        ]);
    }
    
    /**
     * @Route("/login", name="login")
     */
    public function loginAction()
    {
	if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
	    return $this->redirect($this->generateUrl('dashboard'));
	}
	$authenticationUtils = $this->get('security.authentication_utils');

	$error = $authenticationUtils->getLastAuthenticationError();

	$lastUsername = $authenticationUtils->getLastUsername();
        new \Frontend(true, $this->get('security.token_storage'));
        if ($this->getParameter('user_cant_login')) {
            return $this->render('login/maintenance.html.twig');
        }

        if (\SessionAccountHandler::isLoggedIn()) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('login/form.html.twig', [
   	    'error'         => $error,
            'num' => $this->collectStatistics()
        ]);
    }
    
    /**
     * @Route("/login_check", name="login_check")
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
        \DB::getInstance()->stopAddingAccountID();

        $numUser = \Cache::get('NumUser', 1);
        if ($numUser == null) {
            $numUser = \DB::getInstance()->query('SELECT COUNT(*) FROM `'.PREFIX.'account` WHERE `activation_hash` = ""')->fetchColumn();
            \Cache::set('NumUser', $numUser, '500', 1);
        }

        $numDistance = \Cache::get('NumKm', 1);
        if ($numDistance == null) {
            $numDistance = \DB::getInstance()->query('SELECT SUM(`distance`) FROM `'.PREFIX.'training`')->fetchColumn();
            \Cache::set('NumKm', $numDistance, '500', 1);
        }
        \DB::getInstance()->startAddingAccountID();

        return ['user' => (int)$numUser, 'distance' => Distance::format($numDistance)];
    }

    /**
     * @Route("/install.php", name="installe")
     */
    public function installAction()
    {
        return $this->includeOldScript('../install.php', false);
    }
    
    /**
     * @Route("/admin.php")
     */
    public function adminAction()
    {
	$Frontend = new \Frontend(true);
	return new Response($Frontend->displayAdminView());
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
        new \Frontend(false, $this->get('security.token_storage'));

        return $this->render('CoreBundle:Default:help.html.twig', [
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

        return $this->render('CoreBundle:Default:end.html.twig');
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

        return $this->render('CoreBundle:Default:end.html.twig');
    }
    
    /**
     * @Route("/index.php")
     */
    public function indexPhpAction()
    {
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