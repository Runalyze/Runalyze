<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Runalyze\Model\Activity;

class PluginController extends Controller
{
    /**
     * @Route("/call/call.Plugin.install.php")
     * @Security("has_role('ROLE_USER')")
     */
    public function pluginInstallAction()
    {
        $Frontend = new \Frontend(false, $this->get('security.token_storage'));
        $Pluginkey = filter_input(INPUT_GET, 'key');

        $Installer = new \PluginInstaller($Pluginkey);

        echo '<h1>'.__('Install').' '.$Pluginkey.'</h1>';

        if ($Installer->install()) {
        	$Factory = new \PluginFactory();
        	$Plugin = $Factory->newInstance($Pluginkey);

        	echo \HTML::okay(__('The plugin has been successfully installed.'));

        	echo '<ul class="blocklist"><li>';
        	echo $Plugin->getConfigLink(\Icon::$CONF.' '.__('Configuration'));
        	echo '</li></ul>';

        	\Ajax::setReloadFlag(\Ajax::$RELOAD_ALL);
        	echo \Ajax::getReloadCommand();
        } else {
        	echo \HTML::error(__('There was a problem, the plugin could not be installed.'));
        }

        echo '<ul class="blocklist"><li>';
        echo \Ajax::window('<a href="'.\ConfigTabPlugins::getExternalUrl().'">'.\Icon::$TABLE.' '.__('back to list').'</a>');
        echo '</li></ul>';

        return new Response();
    }

    /**
     * @Route("/call/call.Plugin.uninstall.php")
     * @Security("has_role('ROLE_USER')")
     */
    public function pluginUninstallAction()
    {
        $Frontend = new \Frontend(false, $this->get('security.token_storage'));
        $Pluginkey = filter_input(INPUT_GET, 'key');

        $Installer = new \PluginInstaller($Pluginkey);

        echo '<h1>'.__('Uninstall').' '.$Pluginkey.'</h1>';

        if ($Installer->uninstall()) {
        	echo \HTML::okay(__('The plugin has been uninstalled.'));

        	\PluginFactory::clearCache();
        	\Ajax::setReloadFlag(\Ajax::$RELOAD_ALL);
        	echo \Ajax::getReloadCommand();
        } else {
        	echo \HTML::error(__('There was a problem, the plugin could not be uninstalled.'));
        }

        echo '<ul class="blocklist"><li>';
        echo \Ajax::window('<a href="'.\ConfigTabPlugins::getExternalUrl().'">'.\Icon::$TABLE.' '.__('back to list').'</a>');
        echo '</li></ul>';

        return new Response();
    }


    /**
     * @Route("/my/plugin/{id}", requirements={"id" = "\d+"})
     * @Security("has_role('ROLE_USER')")
    */
    public function pluginDisplayAction($id)
    {
        $Frontend = new \Frontend(false, $this->get('security.token_storage'));
        $Factory = new \PluginFactory();

        try {
        	$Plugin = $Factory->newInstanceFor($id);
        } catch (\Exception $E) {
        	$Plugin = null;

        	echo \HTML::error(__('The plugin could not be found.'));
        }

        if (null !== $Plugin) {
        	if ($Plugin instanceof \PluginPanel) {
        		$Plugin->setSurroundingDivVisible(false);
        	}

        	$Plugin->display();
        }

        return new Response();
    }

    /**
     * @Route("/call/call.PluginPanel.move.php", name="PluginPanelMove")
     * @Security("has_role('ROLE_USER')")
    */
    public function pluginPanelMoveAction()
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        if (is_numeric($_GET['id'])) {
            $Factory = new \PluginFactory();
            /** @var \PluginPanel $Panel */
            $Panel = $Factory->newInstanceFor($_GET['id']);

            if ($Panel->type() == \PluginType::PANEL) {
            	$Panel->move(filter_input(INPUT_GET, 'mode'));
            }
        }

        return new Response;
    }

    /**
     * @Route("/call/call.PluginPanel.clap.php", name="PluginPanelClap")
     * @Security("has_role('ROLE_USER')")
    */
    public function pluginPanelAction()
    {
        $Frontend = new \Frontend(false, $this->get('security.token_storage'));

        if (is_numeric($_GET['id'])) {
    	    $Factory = new \PluginFactory();
            /** @var \PluginPanel $Panel */
    	    $Panel = $Factory->newInstanceFor($_GET['id']);

    	    if ($Panel->type() == \PluginType::PANEL) {
    		    $Panel->clap();
        	}
        }

        return new Response;
    }

    /**
     * @Route("/call/call.Plugin.config.php")
     * @Security("has_role('ROLE_USER')")
    */
    public function pluginConfigAction()
    {
        $Frontend = new \Frontend(false, $this->get('security.token_storage'));
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

        return new Response();
    }


    /**
     * @Route("/call/call.ContentPanels.php")
     * @Security("has_role('ROLE_USER')")
     */
     public function contentPanelsAction()
     {
         $Frontend = new \Frontend(false, $this->get('security.token_storage'));
         $Frontend->displayPanels();

         return new Response();
     }

}
