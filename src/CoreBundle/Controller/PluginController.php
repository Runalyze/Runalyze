<?php
namespace Runalyze\Bundle\CoreBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Runalyze\View\Activity\Context;
use Runalyze\View\Activity\Linker;
use Runalyze\Model\Activity;
use Runalyze\View\Window\Laps\Window;

require_once '../inc/class.Frontend.php';
require_once '../inc/class.FrontendShared.php';

class PluginController extends Controller
{
    /**
     * @Route("/call/call.Plugin.install.php")
     */
    public function pluginInstallAction()
    {
        $Frontend = new \Frontend();
        $Pluginkey = filter_input(INPUT_GET, 'key');
        
        $Installer = new \PluginInstaller($Pluginkey);
        
        echo '<h1>'.__('Install').' '.$Pluginkey.'</h1>';
        
        if ($Installer->install()) {
        	$Factory = new \PluginFactory();
        	$Plugin = $Factory->newInstance($Pluginkey);
        
        	echo \HTML::okay( __('The plugin has been successfully installed.') );
        
        	echo '<ul class="blocklist">';
        	echo '<li>';
        	echo $Plugin->getConfigLink(\Icon::$CONF.' '.__('Configuration'));
        	echo '</li>';
        	echo '</ul>';
        
        	\Ajax::setReloadFlag(\Ajax::$RELOAD_ALL);
        	echo \Ajax::getReloadCommand();
        } else {
        	echo \HTML::error( __('There was a problem, the plugin could not be installed.') );
        }
        
        echo '<ul class="blocklist">';
        echo '<li>';
        echo \Ajax::window('<a href="'.\ConfigTabPlugins::getExternalUrl().'">'.\Icon::$TABLE.' '.__('back to list').'</a>');
        echo '</li>';
        echo '</ul>';
        return new Response;
    }
    
    /**
     * @Route("/call/call.Plugin.uninstall.php")
     */
    public function pluginUninstallAction()
    {
        $Frontend = new \Frontend();
        $Pluginkey = filter_input(INPUT_GET, 'key');
        
        $Installer = new \PluginInstaller($Pluginkey);
        
        echo '<h1>'.__('Uninstall').' '.$Pluginkey.'</h1>';
        
        if ($Installer->uninstall()) {
        	echo \HTML::okay( __('The plugin has been uninstalled.') );
        
        	\PluginFactory::clearCache();
        	\Ajax::setReloadFlag(\Ajax::$RELOAD_ALL);
        	echo \Ajax::getReloadCommand();
        } else {
        	echo \HTML::error( __('There was a problem, the plugin could not be uninstalled.') );
        }
        
        echo '<ul class="blocklist">';
        echo '<li>';
        echo \Ajax::window('<a href="'.\ConfigTabPlugins::getExternalUrl().'">'.\Icon::$TABLE.' '.__('back to list').'</a>');
        echo '</li>';
        echo '</ul>';
        return new Response;
    }
    
    
    /**
    * @Route("/call/call.Plugin.display.php")
    */
    public function pluginDisplayAction()
    {
         $Frontend = new \Frontend();
         $Factory = new \PluginFactory();
        
        try {
        	$Plugin = $Factory->newInstanceFor( filter_input(INPUT_GET, 'id') );
        } catch (Exception $E) {
        	$Plugin = null;
        
        	echo HTML::error( __('The plugin could not be found.') );
        }
        
        if ($Plugin !== null) {
        	if ($Plugin instanceof PluginPanel) {
        		$Plugin->setSurroundingDivVisible(false);
        	}
        
        	$Plugin->display();
        }
        return new Response;
    }
    
    /**
    * @Route("/call/call.PluginPanel.move.php", name="PluginPanelMove")
    */
    public function pluginPanelMoveAction()
    {
        $Frontend = new \Frontend(true);
        if (is_numeric($_GET['id'])) {
        $Factory = new \PluginFactory();
        $Panel = $Factory->newInstanceFor( $_GET['id'] );
        
        if ($Panel->type() == \PluginType::PANEL) {
        	$Panel->move( filter_input(INPUT_GET, 'mode') );
        }
        }
        return new Response;
    }
    
    /**
    * @Route("/call/call.PluginPanel.clap.php", name="PluginPanelClap")
    */
    public function pluginPanelAction()
    {
        $Frontend = new \Frontend();
    
        if (is_numeric($_GET['id'])) {
    	    $Factory = new \PluginFactory();
    	    $Panel = $Factory->newInstanceFor( $_GET['id'] );
    
    	    if ($Panel->type() == \PluginType::PANEL) {
    		    $Panel->clap();
        	}
        }
        return new Response;
    }
    
    /**
    * @Route("/call/call.Plugin.config.php")
    */
    public function pluginConfigAction()
    {
        $Frontend = new \Frontend();
        $Factory = new \PluginFactory();
        
        if (isset($_GET['key'])) {
        	$Factory->uninstallPlugin( filter_input(INPUT_GET, 'key') );
        
        	echo \Ajax::wrapJSforDocumentReady('Runalyze.Overlay.load("call/window.config.php");');
        } elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
        	$Plugin = $Factory->newInstanceFor( $_GET['id'] );
        	$Plugin->displayConfigWindow();
        } else {
        	echo '<em>'.__('Something went wrong ...').'</em>';
        }
        return new Response;
    }
    
    
    /**
     * @Route("/call/call.ContentPanels.php")
     */
     public function contentPanelsAction()
     {
         $Frontend = new \Frontend();
         return new Response($Frontend->displayPanels());
     }
     
    /**
    * @Route("/call/call.PluginTool.display.php")
    */
    public function pluginToolDisplayAction()
    {
        $Frontend = new \Frontend();
        if (!isset($_GET['list'])) {
        \PluginTool::displayToolsHeader();
        }
        \PluginTool::displayToolsContent();
        return new Response;
    }
    
}