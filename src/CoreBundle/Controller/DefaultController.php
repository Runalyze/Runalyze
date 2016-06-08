<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SessionAccountHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
            $Frontend = new \Frontend();
        }

        include $file;

        return $this->render('CoreBundle:Default:end.html.twig');
    }
    
    /**
     * @Route("/", name="base_url")
     * @Route("/dashboard")
     */
    public function indexAction()
    {
        return $this->includeOldScript('../index.php');
    }
    
    /**
     * @Route("/login", name="login")
     */
    public function loginAction()
    {
        return $this->includeOldScript('../login.php');
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        new \Frontend();
        SessionAccountHandler::logout();

        return $this->redirectToRoute('login');
    }
    
    /**
     * @Route("/install.php", name="install")
     */
    public function installAction()
    {
        return $this->includeOldScript('../install.php', false);
    }
    
    /**
     * @Route("/update.php", name="update")
     */
    public function updateAction()
    {
        return $this->includeOldScript('../update.php', false);
    }
    
    /**
     * @Route("/admin.php")
     */
    public function adminAction()
    {
        return $this->includeOldScript('../admin.php', false);
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
        new \Frontend();

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
    
}