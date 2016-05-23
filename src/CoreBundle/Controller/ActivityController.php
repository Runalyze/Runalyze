<?php
namespace Runalyze\Bundle\CoreBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Runalyze\View\Activity\Context;
use Runalyze\View\Activity\Linker;
use Runalyze\View\Activity\Dataview;
use Runalyze\Export\Share;
use Runalyze\Model\Activity;
use Runalyze\Util\LocalTime;
use Runalyze\Export\File;
use Runalyze\View\Window\Laps\Window;
use Runalyze\Activity\DuplicateFinder;
use Runalyze\Calculation\Route\Calculator;
use Runalyze\Service\ElevationCorrection\NoValidStrategyException;

require_once '../inc/class.Frontend.php';
require_once '../inc/class.FrontendShared.php';

class ActivityController extends Controller
{
    
    /**
     * @Route("/call/call.Training.create.php")
     * @Route("/activity/create", name="ActivityCreate")
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
        return new Response($Window->display());
    }
    
    /**
     * @Route("/call/call.Training.display.php")
     * @Route("/activity/{id}", name="ActivityShow")
     */
    public function callTrainingDisplayAction($id = null)
    {
        $Frontend = new \Frontend(true);
        if (is_null($id)) {
            $id = Request::createFromGlobals()->query->get('id');
        }

        $Context = new Context($id, \SessionAccountHandler::getId());
        
        switch (Request::createFromGlobals()->query->get('action')) {
            case 'changePrivacy':
                $oldActivity = clone $Context->activity();
                $Context->activity()->set(Activity\Entity::IS_PUBLIC, !$Context->activity()->isPublic());
                $Updater = new Activity\Updater(\DB::getInstance(), $Context->activity(), $oldActivity);
                $Updater->setAccountID(\SessionAccountHandler::getId());
                $Updater->update();
                break;
            case 'delete':
                $Factory = \Runalyze\Context::Factory();
                $Deleter = new Activity\Deleter(\DB::getInstance(), $Context->activity());
                $Deleter->setAccountID(\SessionAccountHandler::getId());
                $Deleter->setEquipmentIDs($Factory->equipmentForActivity($id, true));
                $Deleter->delete();
        
                echo '<div class="panel-content"><p id="submit-info" class="error">'.__('The activity has been removed').'</p></div>';
                echo '<script>Runalyze.Statistics.resetUrl();Runalyze.reloadContent();</script>';
                exit();
                break;
        }
        
        if (!Request::createFromGlobals()->query->get('silent')) {
            $View = new \TrainingView($Context);
            $View->display();
        }
        return new Response;
    }
    
    /**
     * @Route("/call/call.Training.edit.php")
     * @Route("/activity/edit", name="ActivityEdit")
     */
    public function callTrainingEditAction()
    {
        $Frontend = new \Frontend(true);

        if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        	$Factory = \Runalyze\Context::Factory();
        	$Deleter = new Activity\Deleter(\DB::getInstance(), $Factory->activity($_GET['delete']));
        	$Deleter->setAccountID(\SessionAccountHandler::getId());
        	$Deleter->setEquipmentIDs($Factory->equipmentForActivity($_GET['delete'], true));
        	$Deleter->delete();
        
        	echo '<div class="panel-content"><p id="submit-info" class="error">'.__('The activity has been removed').'</p></div>';
        	echo '<script>$("#multi-edit-'.((int)$_GET['delete']).'").remove();Runalyze.Statistics.resetUrl();Runalyze.reloadContent();</script>';
        	exit();
        }
        
        $Training = new \TrainingObject(Request::createFromGlobals()->query->get('id'));
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
    
    /**
     * @Route("/call/call.Training.vdotInfo.php")
     * @Route("/activity/vdotInfo")
    */
    public function trainingVdotInfoAction()
    {
        $Frontend = new \Frontend(true);
        $VDOTinfo = new \VDOTinfo(new Context(Request::createFromGlobals()->query->get('id'), \SessionAccountHandler::getId()));
        return new Response($VDOTinfo->display());
    }
    
    /**
     * @Route("/call/call.Training.elevationCorrection.php")
     * @Route("/activity/elevationCorrection")
     */
    public function trainingElevationCorrectionAction()
    {
        
        $Frontend = new \Frontend();
        
        $Factory = \Runalyze\Context::Factory();
        $Activity = $Factory->activity(Request::createFromGlobals()->query->get('id'));
        $ActivityOld = clone $Activity;
        $Route = $Factory->route($Activity->get(Activity\Entity::ROUTEID));
        $RouteOld = clone $Route;
        
        try {
        	$Calculator = new Calculator($Route);
        	$result = $Calculator->tryToCorrectElevation(Request::createFromGlobals()->query->get('strategy'));
        } catch (NoValidStrategyException $Exception) {
        	$result = false;
        }
        
        if ($result) {
        	$Calculator->calculateElevation();
        	$Activity->set(Activity\Entity::ELEVATION, $Route->elevation());
        
        	$UpdaterRoute = new \Runalyze\Model\Route\Updater(DB::getInstance(), $Route, $RouteOld);
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
        			'Runalyze.Overlay.load(\''.Linker::EDITOR_URL.'?id='.Request::createFromGlobals()->query->get('id').'\');'.
        		'} else if ($("#ajax").is(":visible") && $("#gps-results").length) {'.
        			'Runalyze.Overlay.load(\''.Linker::ELEVATION_INFO_URL.'?id='.Request::createFromGlobals()->query->get('id').'\');'.
        		'}'
        	);
        } else {
        	echo __('Elevation data could not be retrieved.');
        }
        return new Response;
    }
    
    /**
     * @Route("/call/call.Training.roundsInfo.php")
     * @Route("/activity/roundsInfo")
    */
    public function trainingRoundsInfoAction()
    {
        $Frontend = new \Frontend();
        $Window = new Window(new Context(Request::createFromGlobals()->query->get('id'), \SessionAccountHandler::getId()));
        return new Response($Window->display());
    }
    
    /**
     * @Route("/call/call.Training.elevationInfo.php")
     * @Route("/activity/elevationInfo")
    */
    public function trainingElevationInfoAction()
    {
        $Frontend = new \Frontend();
        $ElevationInfo = new \ElevationInfo(new Context(Request::createFromGlobals()->query->get('id'), \SessionAccountHandler::getId()));
        return new Response($ElevationInfo->display());
    }
    
    /**
     * @Route("/call/call.Exporter.export.php")
     * @Route("/activity/export")
    */
    public function exporterExportAction() {
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
        return new Response;
    }
    
    /**
     * @Route("/call/ajax.activityMatcher.php")
     * @Route("/activity/matcher", name="activityMatcher")
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
        $DuplicateFinder = new DuplicateFinder(DB::getInstance(), SessionAccountHandler::getId());
        
        $IgnoreIDs = array_map(function($v){
        	try {
        		return (int)floor(parserStrtotime($v)/60)*60;
        	} catch (Exception $e) {
        		return 0;
        	}
        }, $IgnoreIDs);
        
        foreach ($IDs as $ID) {
        	$dup = $DuplicateFinder->checkForDuplicate((int)floor(parserStrtotime($ID)/60)*60);
        	$found = $dup || in_array($ID, $IgnoreIDs);
        	$Matches[$ID] = array('match' => $found);
        }
        
        $Response = array('matches' => $Matches);
        
        return new JsonResponse($Response);
    }
    
    /**
     * Adjusted strtotime
     * Timestamps are given in UTC but local timezone offset has to be considered!
     * @param $string
     * @return int
     */
    private function parserStrtotime($string) {
            if (substr($string, -1) == 'Z') {
                    return LocalTime::fromServerTime((int)strtotime(substr($string, 0, -1).' UTC'))->getTimestamp();
            }

            return LocalTime::fromString($string)->getTimestamp();
    }
}