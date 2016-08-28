<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Bundle\CoreBundle\Services\Activity\VdotInfo;
use Runalyze\Configuration;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Route("/activity/add", name="ActivityAdd")
     * @Security("has_role('ROLE_USER')")
     */
    public function createAction()
    {
        $Frontend = new \Frontend(isset($_GET['json']), $this->get('security.token_storage'));

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

        return new Response();
    }

    /**
     * @Route("/call/call.Training.display.php")
     * @Route("/activity/{id}", name="ActivityShow", requirements={"id" = "\d+"})
     */
    public function displayAction($id = null)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        if (null === $id) {
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

        return new Response();
    }

    /**
     * @Route("/activity/{id}/edit", name="ActivityEdit")
     * @Security("has_role('ROLE_USER')")
     */
    public function editAction($id = null)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $Training = new \TrainingObject($id);
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

        return new Response();
    }

    /**
     * @Route("/activity/multi-editor/{id}", name="Multi-Editor", requirements={"id" = "\d+"}, defaults={"id" = null})
     * @Security("has_role('ROLE_USER')")
     */
    public function multiEditorAction($id)
    {
        $_GET['mode'] = 'multi';
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $Training = new \TrainingObject($id);
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

        return new Response();
    }

   /**
    * @Route("/activity/{id}/delete", name="ActivityDelete")
    * @Security("has_role('ROLE_USER')")
    */
   public function deleteAction($id)
   {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $Factory = \Runalyze\Context::Factory();
        $Deleter = new Activity\Deleter(\DB::getInstance(), $Factory->activity($id));
        $Deleter->setAccountID(\SessionAccountHandler::getId());
        $Deleter->setEquipmentIDs($Factory->equipmentForActivity($id, true));
        $Deleter->delete();

        echo '<div class="panel-content"><p id="submit-info" class="error">'.__('The activity has been removed').'</p></div>';
        echo '<script>$("#multi-edit-'.((int)$id).'").remove();Runalyze.Statistics.resetUrl();Runalyze.reloadContent();</script>';
        exit();
   }

    /**
     * @Route("/activity/{id}/vdot-info")
     * @Security("has_role('ROLE_USER')")
     */
    public function vdotInfoAction($id)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $VdotInfo = new VdotInfo();
        $VdotInfo->setContext(new Context($id, \SessionAccountHandler::getId()));
        $VdotInfo->setConfiguration(Configuration::Data(), Configuration::Vdot());

        return $this->render(':activity:vdot_info.html.twig', [
            'title' => $VdotInfo->getTitle(),
            'raceDetails' => $VdotInfo->getRaceCalculationDetails(),
            'hrDetails' => $VdotInfo->getHeartRateCalculationDetails(),
            'factorDetails' => $VdotInfo->getCorrectionFactorDetails(),
            'elevationDetails' => $VdotInfo->getElevationDetails(),
            'useElevationAdjustment' => $VdotInfo->usesElevationAdjustment()
        ]);
    }

    /**
     * @Route("/activity/{id}/elevation-correction")
     * @Security("has_role('ROLE_USER')")
     */
    public function elevationCorrectionAction($id)
    {

        $Frontend = new \Frontend(false, $this->get('security.token_storage'));

        $Factory = \Runalyze\Context::Factory();
        $Activity = $Factory->activity($id);
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

        	$UpdaterRoute = new \Runalyze\Model\Route\Updater(\DB::getInstance(), $Route, $RouteOld);
        	$UpdaterRoute->setAccountID(\SessionAccountHandler::getId());
        	$UpdaterRoute->update();

        	$UpdaterActivity = new Activity\Updater(\DB::getInstance(), $Activity, $ActivityOld);
        	$UpdaterActivity->setAccountID(\SessionAccountHandler::getId());
        	$UpdaterActivity->update();

        	if (Request::createFromGlobals()->query->get('strategy') == 'none') {
        		echo __('Corrected elevation data has been removed.');
        	} else {
        		echo __('Elevation data has been corrected.');
        	}

        	\Ajax::setReloadFlag( \Ajax::$RELOAD_DATABROWSER_AND_TRAINING );
        	echo \Ajax::getReloadCommand();
        	echo \Ajax::wrapJS(
        		'if ($("#ajax").is(":visible") && $("#training").length) {'.
        			'Runalyze.Overlay.load(\'activity/'.$id.'/edit\');'.
        		'} else if ($("#ajax").is(":visible") && $("#gps-results").length) {'.
        			'Runalyze.Overlay.load(\'activity/'.$id.'/elevation-info\');'.
        		'}'
        	);
        } else {
        	echo __('Elevation data could not be retrieved.');
        }

        return new Response;
    }

    /**
     * @Route("/activity/{id}/splits-info", requirements={"id" = "\d+"})
     * @Security("has_role('ROLE_USER')")
     */
    public function splitsInfoAction($id)
    {
        $Frontend = new \Frontend(false, $this->get('security.token_storage'));
        $Window = new Window(new Context($id, \SessionAccountHandler::getId()));
        $Window->display();

        return new Response();
    }

    /**
     * @Route("/call/call.Training.elevationInfo.php")
     * @Route("/activity/{id}/elevation-info", requirements={"id" = "\d+"})
     * @Security("has_role('ROLE_USER')")
     */
    public function elevationInfoAction($id)
    {
        $Frontend = new \Frontend(false, $this->get('security.token_storage'));
        $ElevationInfo = new \ElevationInfo(new Context($id, \SessionAccountHandler::getId()));
        $ElevationInfo->display();

        return new Response();
    }

    /**
     * @Route("/call/call.Exporter.export.php")
     * @Route("/activity/{id}/export/{type}/{typeid}", requirements={"id" = "\d+"})
     * @Security("has_role('ROLE_USER')")
     */
    public function exporterExportAction($id, $type, $typeid) {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        if ($type == 'social' && Share\Types::isValidValue((int)$typeid)) {
            $Context = new Context((int)$id, \SessionAccountHandler::getId());
            $Exporter = Share\Types::get((int)$typeid, $Context);

            if ($Exporter instanceof Share\AbstractSnippetSharer) {
                $Exporter->display();
            }
        } elseif ($type == 'file' && File\Types::isValidValue((int)$typeid)) {
            $Context = new Context((int)$id, \SessionAccountHandler::getId());
            $Exporter = File\Types::get((int)$typeid, $Context);

            if ($Exporter instanceof File\AbstractFileExporter) {
                $Exporter->downloadFile();
                exit;
            }
        }

        return new Response();
    }

    /**
     * @Route("/call/ajax.activityMatcher.php")
     * @Route("/activity/matcher", name="activityMatcher")
     * @Security("has_role('ROLE_USER')")
     */
    public function ajaxActivityMatcher()
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

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
        	try {
        		return (int)floor($this->parserStrtotime($v)/60)*60;
        	} catch (\Exception $e) {
        		return 0;
        	}
        }, $IgnoreIDs);

        foreach ($IDs as $ID) {
            try {
                $dup = $DuplicateFinder->checkForDuplicate((int)floor($this->parserStrtotime($ID)/60)*60);
            } catch (\Exception $e) {
                $dup = false;
            }

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
