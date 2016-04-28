<?php
namespace Runalyze\Bundle\CoreBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


require_once '../inc/class.Frontend.php';
require_once '../inc/class.FrontendShared.php';
require_once '../inc/class.FrontendSharedList.php';

class DefaultController extends Controller
{
    protected function includeOldScript($file, $initFrontend = true)
    {
        if ($initFrontend)
            $Frontend = new \Frontend();
        include $file;

        return $this->render(
            'CoreBundle:Default:end.html.twig'
        );
    }
    
    /**
     * @Route("/")
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
        return $this->includeOldScript('../inc/tpl/tpl.help.php');
    }
    
    /**
     * @Route("/shared/{training}")
     */
    public function sharedTrainingAction($training)
    {
        $_GET['url']=$training;
        $Frontend = new \FrontendShared();
        
        if (\FrontendShared::$IS_IFRAME)
        	echo '<div id="statistics-inner" class="panel" style="width:97%;margin:0 auto;">';
        elseif (!\Request::isAjax())
        	echo '<div id="statistics-inner" class="panel" style="width:960px;margin:5px auto;">';
        else
        	echo '<div>';
        
        $Frontend->displaySharedView();
        
        echo '</div>';
        return new Response;
    }
    
    /**
     * @Route("/shared/{user}/")
     */
    public function sharedUserAction($user)
    {
        $_GET['user']=$user;
        if (isset($_GET['view'])) {
        	if ($_GET['view'] == 'monthkm') {
        		$_GET['type'] = 'month';
        		$response = $this->forward('CoreBundle:Call:windowsPlotSumDataShared');
        		exit;
        	} elseif ($_GET['view'] == 'weekkm') {
        		$_GET['type'] = 'week';
        		$response = $this->forward('CoreBundle:Call:windowsPlotSumDataShared');
        		exit;
        	}
        }
        
        $Frontend = new \FrontendSharedList();
        
        if (!\Request::isAjax()) {
        	if ($Frontend->userAllowsStatistics()) {
        		echo '<div class="panel" style="width:960px;margin:5px auto;">';
        		$Frontend->displayGeneralStatistics();
        		echo '</div>';
        	}
        
        	echo '<div id="data-browser" class="panel" style="width:960px;margin:5px auto;">';
        	echo '<div id="'.DATA_BROWSER_SHARED_ID.'">';
        }
        
        $Frontend->displaySharedView();
        
        if (!\Request::isAjax()) {
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
        return new Response;
    }
    
}