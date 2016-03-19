<?php
namespace Runalyze\Bundle\CoreBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Runalyze\View\Activity\Context;
use Runalyze\View\Activity\Linker;
use Runalyze\View\Activity\Dataview;
use Runalyze\Export\Share;
use Runalyze\Model\Activity;
use Runalyze\Export\File;
use Runalyze\View\Window\Laps\Window;
use Runalyze\Activity\DuplicateFinder;
use Runalyze\Context as RunalyzeContext;
use Runalyze\Model\Route as RunalyzeRoute;
use Runalyze\Calculation\Route\Calculator;
use Runalyze\Data\Elevation\Correction\NoValidStrategyException;

require_once '../inc/class.Frontend.php';
require_once '../inc/class.FrontendShared.php';

/**
 * @Route("/call")
 */
class CallController extends Controller
{
    /**
    * @Route("/call.DataBrowser.display.php", name="databrowser")
    */
    public function DataBrowserAction()
    {
        $Frontend = new \Frontend(true);
        $DataBrowser = new \DataBrowser();
        $DataBrowser->display();
        return new Response;
    }
    
    /**
    * @Route("/call.garminCommunicator.php")
    */
    public function garminCommunicatorAction()
    {
        $Frontend = new \Frontend(true);
        include '../call/call.garminCommunicator.php';
        return new Response;
    }
    
    /**
    * @Route("/savePng.php")
    */
    public function savePngAction()
    {
        $Frontend = new \Frontend(true);
        header("Content-type: image/png");
        header("Content-Disposition: attachment; filename=".strtolower(str_replace(' ', '_', $_POST['filename'])));
        
        $encodeData = substr($_POST['image'], strpos($_POST['image'], ',') + 1);
        echo base64_decode($encodeData);
        return new Response;
    }
    
    /**
    * @Route("/call.Training.vdotInfo.php")
    */
    public function trainingVdotInfoAction()
    {
        $Frontend = new \Frontend(true);
        $VDOTinfo = new \VDOTinfo(new Context(\Request::sendId(), \SessionAccountHandler::getId()));
        $VDOTinfo->display();
        return new Response;
    }
    
    /**
     * @Route("/call.Training.elevationCorrection.php")
     */
    public function trainingElevationCorrectionAction()
    {
        
        $Frontend = new \Frontend();
        
        $Factory = RunalyzeContext::Factory();
        $Activity = $Factory->activity(\Request::sendId());
        $ActivityOld = clone $Activity;
        $Route = $Factory->route($Activity->get(Activity\Entity::ROUTEID));
        $RouteOld = clone $Route;
        
        try {
        	$Calculator = new Calculator($Route);
        	$result = $Calculator->tryToCorrectElevation(\Request::param('strategy'));
        } catch (NoValidStrategyException $Exception) {
        	$result = false;
        }
        
        if ($result) {
        	$Calculator->calculateElevation();
        	$Activity->set(Activity\Entity::ELEVATION, $Route->elevation());
        
        	$UpdaterRoute = new RunalyzeRoute\Updater(DB::getInstance(), $Route, $RouteOld);
        	$UpdaterRoute->setAccountID(\SessionAccountHandler::getId());
        	$UpdaterRoute->update();
        
        	$UpdaterActivity = new Activity\Updater(DB::getInstance(), $Activity, $ActivityOld);
        	$UpdaterActivity->setAccountID(\SessionAccountHandler::getId());
        	$UpdaterActivity->update();
        
        	if (Request::param('strategy') == 'none') {
        		echo __('Corrected elevation data has been removed.');
        	} else {
        		echo __('Elevation data has been corrected.');
        	}
        
        	Ajax::setReloadFlag( Ajax::$RELOAD_DATABROWSER_AND_TRAINING );
        	echo Ajax::getReloadCommand();
        	echo Ajax::wrapJS(
        		'if ($("#ajax").is(":visible") && $("#training").length) {'.
        			'Runalyze.Overlay.load(\''.Linker::EDITOR_URL.'?id='.Request::sendId().'\');'.
        		'} else if ($("#ajax").is(":visible") && $("#gps-results").length) {'.
        			'Runalyze.Overlay.load(\''.Linker::ELEVATION_INFO_URL.'?id='.Request::sendId().'\');'.
        		'}'
        	);
        } else {
        	echo __('Elevation data could not be retrieved.');
        }
        return new Response;
    }
    
    /**
    * @Route("/call.Training.roundsInfo.php")
    */
    public function TrainingRoundsInfoAction()
    {
        $Frontend = new \Frontend();
        $Window = new Window(new Context(\Request::sendId(), \SessionAccountHandler::getId()));
        $Window->display();
        return new Response;
    }
    
    /**
    * @Route("/call.Training.elevationInfo.php")
    */
    public function TrainingElevationInfoAction()
    {
        $Frontend = new \Frontend();
        $ElevationInfo = new \ElevationInfo(new Context(\Request::sendId(), \SessionAccountHandler::getId()));
        $ElevationInfo->display();
        return new Response;
    }
    
    /**
    * @Route("/call.MetaCourse.php")
    */
    public function MetaCourseAction() {
        $Frontend = new \FrontendShared(true);
        
        $Meta = new HTMLMetaForFacebook();
        $Meta->displayCourse();
        return new Response;
    }
    
    /**
    * @Route("/call.Exporter.export.php")
    */
    public function ExporterExportAction() {
        $Frontend = new \Frontend(true);
        
        if (isset($_GET['social']) && Share\Types::isValidValue((int)$_GET['typeid'])) {
            $Context = new Context((int)$_GET['id'], \SessionAccountHandler::getId());
            $Exporter = Share\Types::get((int)$_GET['typeid'], $Context);
        
            if ($Exporter instanceof Share\AbstractSnippetSharer) {
                $Exporter->display();
            }
        } elseif (isset($_GET['file']) && File\Types::isValidValue((int)$_GET['typeid'])) {
            $Context = new Context((int)$_GET['id'], \SessionAccountHandler::getId());
            $Exporter = File\Types::get((int)$_GET['typeid'], $Context);
        
            if ($Exporter instanceof File\AbstractFileExporter) {
                $Exporter->downloadFile();
                exit;
            }
        }
        return new Reponse;
    }
    
    /**
    * @Route("/window.config.php", name="config")
    */
    public function WindowConfigAction() {
        $Frontend = new \Frontend(true);
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
     * @Route("/call.ContentPanels.php")
     */
     public function ContentPanelsAction()
     {
         $Frontend = new \Frontend();
         $Frontend->displayPanels();
         return new Response;
     }
     
    /**
    * @Route("/call.PluginTool.display.php")
    */
    public function PluginToolDisplayAction()
    {
        $Frontend = new \Frontend();
        if (!isset($_GET['list'])) {
        \PluginTool::displayToolsHeader();
        }
        \PluginTool::displayToolsContent();
        return new Response;
    }
    
    /**
    * @Route("/ajax.saveTcx.php")
    */
    public function ajaxSaveTcxAction()
    {
        $Frontend = new \Frontend(true);
        
        \Filesystem::writeFile('../data/import/'.$_POST['activityId'].'.tcx', $_POST['data']);
        
        return new Response;
    }
    
    /**
     * @Route("/ajax.change.Config.php")
     */
    public function ajaxChanceConfigAction()
    {
        $Frontend = new \Frontend(true);
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
    * @Route("/call.Plugin.config.php")
    */
    public function PluginConfigAction()
    {
        $Frontend = new \Frontend();
        $Factory = new \PluginFactory();
        
        if (isset($_GET['key'])) {
        	$Factory->uninstallPlugin( filter_input(INPUT_GET, 'key') );
        
        	echo Ajax::wrapJSforDocumentReady('Runalyze.Overlay.load("call/window.config.php");');
        } elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
        	$Plugin = $Factory->newInstanceFor( $_GET['id'] );
        	$Plugin->displayConfigWindow();
        } else {
        	echo '<em>'.__('Something went wrong ...').'</em>';
        }
        return new Response;
    }
    
    /**
    * @Route("/call.Plugin.display.php")
    */
    public function PluginDisplayAction()
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
    * @Route("/call.PluginPanel.move.php", name="PluginPanelMove")
    */
    public function PluginPanelMoveAction()
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
    * @Route("/call.PluginPanel.clap.php", name="PluginPanelClap")
    */
    public function PluginPanelAction()
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
     * @Route("/window.delete.php")
     */
     public function windowDeleteAction()
     {
        $Frontend = new \Frontend();
        $Errors   = array();
        \AccountHandler::setAndSendDeletionKeyFor($Errors);
        
        echo \HTML::h1( __('Delete your account.') );
        
        if (!empty($Errors)) {
        	foreach ($Errors as $Error)
        		echo \HTML::error($Error);
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
     * @Route("window.search.php")
     */
    public function windowSearchAction()
    {
        $showResults = !empty($_POST);
        
        if (isset($_GET['get']) && $_GET['get'] == 'true') {
        	$_POST = array_merge($_POST, $_GET);
        	$showResults = true;
        
        	\SearchFormular::transformOldParamsToNewParams();
        }
        
        if (empty($_POST) || \Request::param('get') == 'true') {
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
    
    /**
     * @Route("/call.Training.create.php")
     */
    public function callTrainingCreateAction()
    {
        $Frontend = new \Frontend(isset($_GET['json']));
        
        \System::setMaximalLimits();
        
        if (class_exists('Normalizer')) {
        	if (isset($_GET['file'])) {
        		$_GET['file'] = \Normalizer::normalize($_GET['file']);
        	}
        
        	if (isset($_GET['files'])) {
        		$_GET['files'] = \Normalizer::normalize($_GET['files']);
        	}
        
        	if (isset($_POST['forceAsFileName'])) {
        		$_POST['forceAsFileName'] = \Normalizer::normalize($_POST['forceAsFileName']);
        	}
        
        	if (isset($_FILES['qqfile']) && isset($_FILES['qqfile']['name'])) {
        		$_FILES['qqfile']['name'] = \Normalizer::normalize($_FILES['qqfile']['name']);
        	}
        }
        
        $Window = new \ImporterWindow();
        $Window->display();
        return new Response;
    }
    
    /**
     * @Route("/call.Training.display.php")
     */
    public function callTrainingDisplayAction()
    {
        $Frontend = new \Frontend(true);

        $Context = new Context(\Request::sendId(), \SessionAccountHandler::getId());
        
        switch (\Request::param('action')) {
            case 'changePrivacy':
                $oldActivity = clone $Context->activity();
                $Context->activity()->set(Activity\Entity::IS_PUBLIC, !$Context->activity()->isPublic());
                $Updater = new Activity\Updater(\DB::getInstance(), $Context->activity(), $oldActivity);
                $Updater->setAccountID(\SessionAccountHandler::getId());
                $Updater->update();
                break;
            case 'delete':
                $Factory = Runalyze\Context::Factory();
                $Deleter = new Activity\Deleter(\DB::getInstance(), $Context->activity());
                $Deleter->setAccountID(\SessionAccountHandler::getId());
                $Deleter->setEquipmentIDs($Factory->equipmentForActivity(\Request::sendId(), true));
                $Deleter->delete();
        
                echo '<div class="panel-content"><p id="submit-info" class="error">'.__('The activity has been removed').'</p></div>';
                echo '<script>Runalyze.Statistics.resetUrl();Runalyze.reloadContent();</script>';
                exit();
                break;
        }
        
        if (!\Request::param('silent')) {
            $View = new \TrainingView($Context);
            $View->display();
        }
        return new Response;
    }
    /**
     * @Route("/call.Training.edit.php")
     */
    public function callTrainingEditAction()
    {
        $Frontend = new \Frontend(true);

        if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        	$Factory = Runalyze\Context::Factory();
        	$Deleter = new Activity\Deleter(\DB::getInstance(), $Factory->activity($_GET['delete']));
        	$Deleter->setAccountID(\SessionAccountHandler::getId());
        	$Deleter->setEquipmentIDs($Factory->equipmentForActivity($_GET['delete'], true));
        	$Deleter->delete();
        
        	echo '<div class="panel-content"><p id="submit-info" class="error">'.__('The activity has been removed').'</p></div>';
        	echo '<script>$("#multi-edit-'.((int)$_GET['delete']).'").remove();Runalyze.Statistics.resetUrl();Runalyze.reloadContent();</script>';
        	exit();
        }
        
        $Training = new \TrainingObject(\Request::sendId());
        $Activity = new Activity\Entity($Training->getArray());
        
        $Linker = new Linker($Activity);
        $Dataview = new Dataview($Activity);
        
        echo $Linker->editNavigation();
        
        echo '<div class="panel-heading">';
        echo '<h1>'.$Dataview->titleWithComment().', '.$Dataview->dateAndDaytime().'</h1>';
        echo '</div>';
        echo '<div class="panel-content">';
        
        $Formular = new \TrainingFormular($Training, \StandardFormular::$SUBMIT_MODE_EDIT);
        $Formular->setId('training');
        $Formular->setLayoutForFields( \FormularFieldset::$LAYOUT_FIELD_W50 );
        $Formular->display();
        
        echo '</div>';
        return new Response;
    }
    
    protected function plotSumData() {
        if (!isset($_GET['y']))
        	$_GET['y'] = \PlotSumData::LAST_12_MONTHS;
        
        if (!isset($_GET['type']))
        	$_GET['type'] = 'month';
        
        if ($_GET['type'] == 'week') {
        	$Plot = new \PlotWeekSumData();
        	$Plot->display();
        } elseif ($_GET['type'] == 'month') {
        	$Plot = new \PlotMonthSumData();
        	$Plot->display();
        } else {
        	echo \HTML::error( __('There was a problem.') );
        }
    }
    
    /**
     * @Route("/window.plotSumData.php")
     */
    public function windowsPlotSumDataAction()
    {
        $Frontend = new \Frontend();
        $this->plotSumData();
        return new Response;
    }
    
    /**
     * @Route("/window.plotSumDataShared.php")
     */
    public function windowsPlotSumDataSharedAction()
    {
        $Frontend = new \FrontendSharedList();
        $this->plotSumData();
        return new Response;
    }
    
    /**
     * @Route("/ajax.activityMatcher.php")
     */
    public function ajaxActivityMatcher()
    {
        $Frontend = new \Frontend(true);
        
        $IDs     = array();
        $Matches = array();
        $Array   = explode('&', urldecode(file_get_contents('php://input')));
        foreach ($Array as $String) {
        	if (substr($String,0,12) == 'externalIds=')
        		$IDs[] = substr($String,12);
        }
        
        $IgnoreIDs = \Runalyze\Configuration::ActivityForm()->ignoredActivityIDs();
        $DuplicateFinder = new DuplicateFinder(\DB::getInstance(), \SessionAccountHandler::getId());
        
        $IgnoreIDs = array_map(function($v){
        	return (int)floor(strtotime($v)/60)*60;
        }, $IgnoreIDs);
        
        foreach ($IDs as $ID) {
        	$dup = $DuplicateFinder->checkForDuplicate((int)floor(strtotime($ID)/60)*60);
        	$found = $dup || in_array($ID, $IgnoreIDs);
        	$Matches[$ID] = array('match' => $found);
        }
        
        $Response = array('matches' => $Matches);
        
        return new JsonResponse($Response);
    }
    
    /**
     * @Route("/call.Plugin.install.php")
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
     * @Route("call.Plugin.uninstall.php")
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
        	\Ajax::setReloadFlag(Ajax::$RELOAD_ALL);
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
     * @Route("/login.php")
     */
    public function loginAction()
    {
        $Frontend = new \Frontend();
        echo '<p class="error">';
    	_e('You are not logged in anymore.');
    	echo '<br><br><a href="login" title="Runalyze: Login"><strong>&raquo; '. _e('Login').'</strong></a></p>';
    	return new Response;
    }
}