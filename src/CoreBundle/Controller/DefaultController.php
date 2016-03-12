<?php
namespace Runalyze\Bundle\CoreBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('CoreBundle:Default:index.html.twig');
    }
    
    /**
     * @Route("/test")
     * @Method({"GET"})
     */
    public function testAction()
    {
        return $this->render('CoreBundle:Default:index.html.twig');
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