<?php
namespace Runalyze\Bundle\CoreBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

require_once '../inc/class.Frontend.php';
class DefaultController extends Controller
{
    protected function includeOldScript($file, $initFrontend = true)
    {
        if ($initFrontend)
            $Frontend = new \Frontend();
        include $file;
        return new Response;
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
     * @Route("/login")
     */
    public function loginAction()
    {
        return $this->includeOldScript('../login.php');
    }
    
    /**
     * @Route("/install.php")
     */
    public function installAction()
    {
        return $this->includeOldScript('../install.php', false);
    }
    
    /**
     * @Route("/update.php")
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
     * @Route("/call/{file}")
     */
    public function callAction($file)
    {
        return $this->includeOldScript('../call/'.$file);
    }
    
    /**
     * @Route("/plugin/{plugin}/{file}")
     */
    public function pluginAction($plugin, $file)
    {
        return $this->includeOldScript('../plugin/'.$plugin.'/'.$file);
    }
    
    /**
     * @Route("/dashboard/help")
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
        include '../call/call.SharedTraining.php';
        return new Response;
    }
    
    /**
     * @Route("/shared/{user}/")
     */
    public function sharedUserAction($user)
    {
        $_GET['user']=$user;
        include '../call/call.SharedList.php';
        return new Response;
    }
    

    /**
     * @Route("/json")
     * @Method({"GET"})
     */
    public function jsonAction()
    {
        return new JsonResponse([
            'pi' => 3.14159265358979323846264338327950288419716939937510582097494459230781640628998
        ]);
    }
}