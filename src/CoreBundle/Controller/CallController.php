<?php
namespace Runalyze\Bundle\CoreBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Runalyze\View\Activity\Context;
use Runalyze\Export\Share;
use Runalyze\Export\File;
use Runalyze\View\Window\Laps\Window;

require_once '../inc/class.Frontend.php';
require_once '../inc/class.FrontendShared.php';

/**
 * @Route("/call")
 */
class CallController extends Controller
{
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
     public function TrainingVdotInfoAction()
     {
        $Frontend = new \Frontend(true);
        $VDOTinfo = new \VDOTinfo(new Context(\Request::sendId(), \SessionAccountHandler::getId()));
        $VDOTinfo->display();
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
        * @Route("/window.config.php")
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
            
            echo Ajax::wrapJSforDocumentReady('Runalyze.Overlay.removeClasses();');
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
    
}