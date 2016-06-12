<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

//use Runalyze\View\Activity\Context;
//use Runalyze\View\Activity\Linker;
//use Runalyze\View\Activity\Dataview;
//use Runalyze\Model\Activity;
//use Runalyze\View\Window\Laps\Window;

require_once '../inc/class.Frontend.php';
require_once '../inc/class.FrontendShared.php';
require_once '../inc/class.FrontendSharedList.php';


class CallController extends Controller
{
    /**
     * @Route("/call/call.DataBrowser.display.php")
     * @Route("databrowser", name="databrowser")
     * @Security("has_role('ROLE_USER')")
     */
    public function dataBrowserAction()
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));
        $DataBrowser = new \DataBrowser();
        $DataBrowser->display();
        return new Response;
    }
    
    /**
     * @Route("/call/call.garminCommunicator.php")
     * @Route("/upload/garminCommunicator")
     * @Security("has_role('ROLE_USER')")
     */
    public function garminCommunicatorAction()
    {
        $Frontend = new \Frontend(true);

        return $this->render('CoreBundle:Upload:garminCommunicator.html.twig', array(
            'htmlBase' => \System::getFullDomain(),
            'garminAPIBase' => \Request::getProtocol().'://'.$_SERVER['HTTP_HOST'],
            'garminAPIKey' => GARMIN_API_KEY,
        ));
    }
    
    /**
     * @Route("/call/savePng.php")
     * @Method("POST")
     * @Security("has_role('ROLE_USER')")
     */
    public function savePngAction()
    {
        header("Content-type: image/png");
        header("Content-Disposition: attachment; filename=".strtolower(str_replace(' ', '_', $_POST['filename'])));
        
        $encodeData = substr($_POST['image'], strpos($_POST['image'], ',') + 1);
        echo base64_decode($encodeData);
        return new Response;
    }
    
    /**
     * @Route("/call/call.MetaCourse.php")
     */
    public function metaCourseAction() {
        $Frontend = new \FrontendShared(true);
        
        $Meta = new HTMLMetaForFacebook();
        $Meta->displayCourse();
        return new Response;
    }
    
    /**
     * @Route("/call/window.config.php")
     * @Route("/settings", name="settings")
     * @Security("has_role('ROLE_USER')")
     */
    public function windowConfigAction() {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));
        $ConfigTabs = new \ConfigTabs();
        $ConfigTabs->addDefaultTab(new  \ConfigTabGeneral());
        $ConfigTabs->addTab(new \ConfigTabPlugins());
        $ConfigTabs->addTab(new \ConfigTabDataset());
        $ConfigTabs->addTab(new \ConfigTabSports());
        $ConfigTabs->addTab(new \ConfigTabTypes());
        $ConfigTabs->addTab(new \ConfigTabEquipment());
        $ConfigTabs->addTab(new \ConfigTabAccount());
        $ConfigTabs->display();
        
        echo \Ajax::wrapJSforDocumentReady('Runalyze.Overlay.removeClasses();');
        return new Response;
    }

    /**
     * @Route("/call/ajax.saveTcx.php")
     * @Security("has_role('ROLE_USER')")
     */
    public function ajaxSaveTcxAction()
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));
        
        \Filesystem::writeFile('../data/import/'.$_POST['activityId'].'.tcx', $_POST['data']);
        
        return new Response;
    }
    
    /**
     * @Route("/call/ajax.change.Config.php")
     * @Security("has_role('ROLE_USER')")
     */
    public function ajaxChanceConfigAction()
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));
        switch ($_GET['key']) {
        	case 'garmin-ignore':
        		\Runalyze\Configuration::ActivityForm()->ignoreActivityID($_GET['value']);
        		break;
        
        	case 'leaflet-layer':
        		\Runalyze\Configuration::ActivityView()->updateLayer($_GET['value']);
        		break;
        
        	default:
        		if (substr($_GET['key'], 0, 5) == 'show-') {
        			$key = substr($_GET['key'], 5);
        			\Runalyze\Configuration::ActivityForm()->update($key, $_GET['value']);
        		}
        }
        return new Response;
    }
    
    /**
     * @Route("/call/window.delete.php")
     * @Security("has_role('ROLE_USER')")
     */
     public function windowDeleteAction()
     {
        new \Frontend(false, $this->get('security.token_storage'));
        
        echo \HTML::h1( __('Delete your account.') );
        
        if (!\AccountHandler::setAndSendDeletionKeyFor(SessionAccountHandler::getId())) {
        	echo \HTML::error(__('Sending the link did not work. Please contact the administrator.'));
        } else {
        	echo \HTML::info(
        			__('<em>A confirmation has been sent via mail.</em><br>'.
        				'How sad, that you\'ve decided to delete your account.<br>'.
        				'Your account will be deleted as soon as you click on the confirmation link in your mail.')
        	);
        }
        return new Response;
     }
     
    /**
     * @Route("/call/window.search.php")
     * @Route("/search", name="search")
     * @Security("has_role('ROLE_USER')")
     */
    public function windowSearchAction()
    {
        $Frontend = new \Frontend(false, $this->get('security.token_storage'));
        $showResults = !empty($_POST);
        
        if (isset($_GET['get']) && $_GET['get'] == 'true') {
        	$_POST = array_merge($_POST, $_GET);
        	$showResults = true;
        
        	\SearchFormular::transformOldParamsToNewParams();
        }
        
        if (empty($_POST) || Request::createFromGlobals()->query->get('get') == 'true') {
        	echo '<div class="panel-heading">';
        	echo '<h1>'.__('Search for activities').'</h1>';
        	echo '</div>';
        
        	$Formular = new \SearchFormular();
        	$Formular->display();
        }
        
        $Results = new \SearchResults($showResults);
        $Results->display();
        return new Response;
    }
    
    protected function plotSumData() {

        $Request = Request::createFromGlobals();
        if (is_null($Request->query->get('y'))) {
        	$_GET['y'] = \PlotSumData::LAST_12_MONTHS;
        }
        
        $type = $Request->query->get('type', 'month');

        if ($type == 'week') {
        	$Plot = new \PlotWeekSumData();
        	$Plot->display();
        } elseif ($type == 'month') {
        	$Plot = new \PlotMonthSumData();
        	$Plot->display();
        } else {
        	echo \HTML::error( __('There was a problem.') );
        }
    }
    
    /**
     * @Route("/call/window.plotSumData.php")
     */
    public function windowsPlotSumDataAction()
    {
        $Frontend = new \Frontend(false, $this->get('security.token_storage'));
        $this->plotSumData();
        return new Response;
    }
    
    /**
     * @Route("/call/window.plotSumDataShared.php")
     */
    public function windowsPlotSumDataSharedAction()
    {
        $Frontend = new \FrontendSharedList();
        $this->plotSumData();
        return new Response;
    }
    
    /**
     * @Route("/call/login.php")
     */
    public function loginAction()
    {
        $Frontend = new \Frontend(false, $this->get('security.token_storage'));
        echo '<p class="error">';
    	_e('You are not logged in anymore.');
    	echo '<br><br><a href="login" title="Runalyze: Login"><strong>&raquo; '. _e('Login').'</strong></a></p>';
    	return new Response;
    }
}