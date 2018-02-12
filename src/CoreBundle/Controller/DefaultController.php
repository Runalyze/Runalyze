<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Activity\Distance;
use Runalyze\Bundle\CoreBundle\Component\Account\Registration;
use Runalyze\Bundle\CoreBundle\Form\FeedbackType;
use Runalyze\Bundle\CoreBundle\Form\RegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 * @package Runalyze\Bundle\CoreBundle\Controller
 */
class DefaultController extends AbstractPluginsAwareController
{
    /**
     * @param string $file
     * @param bool $initFrontend
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function includeOldScript($file, $initFrontend = true)
    {
        if ($initFrontend) {
            $Frontend = new \Frontend(false, $this->get('security.token_storage'));
        }

        include $file;

        return $this->render('legacy_end.html.twig');
    }

    /**
     * @Route("/dashboard", name="dashboard")
     * @Security("has_role('ROLE_USER')")
     */
    public function dashboardAction(Request $request, Account $account)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $panelsContent = $this->getResponseForAllEnabledPanels($request, $account)->getContent();

        include '../dashboard.php';

        return $this->render('legacy_end.html.twig');
    }

    /**
     * @Route("/", name="base_url")
     */
    public function indexAction(Request $request)
    {
        $securityContext = $this->container->get('security.authorization_checker');

        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY') || $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('dashboard'));
        }

        return $this->forward('CoreBundle:Default:register', $request->attributes->all());
    }

    /**
     * @Route("/{_locale}/register", name="register")
     */
    public function registerAction(Request $request)
    {
        if (!$this->getParameter('user_can_register')) {
            return $this->render('register/disabled.html.twig');
        }

        $account = new Account();
        $form = $this->createForm(RegistrationType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registration = new Registration($this->getDoctrine()->getManager(), $account);
            $formdata = $request->request->get($form->getName());

            $registration->setLocale($request->getLocale());
            $registration->setTimezoneByName($formdata['textTimezone']);

            if (!$this->getParameter('user_disable_account_activation')) {
                $registration->requireAccountActivation();
            }

            $registration->setPassword($account->getPlainPassword(), $this->get('security.encoder_factory'));
            $account = $registration->registerAccount();

            if ($this->getParameter('user_disable_account_activation')) {
                return $this->render('account/activate/success.html.twig');
            }

            $this->get('app.mailer.account')->sendActivationLinkTo($account);

            return $this->render('register/mail_delivered.html.twig');
        }

        return $this->render('register/form.html.twig', [
            'form' => $form->createView(),
            'num' => $this->collectStatistics()
        ]);
    }

    /**
     * @Route("/{_locale}/login", name="login")
     */
    public function loginAction(Request $request)
    {
    	if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
    	    return $this->redirect($this->generateUrl('dashboard'));
    	}

        if ($request->isXmlHttpRequest()) {
            return $this->render('login/ajax_not_logged_in.html.twig');
        }

    	$authenticationUtils = $this->get('security.authentication_utils');
    	$error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('login/form.html.twig', [
   	        'error' => $error,
            'num' => $this->collectStatistics()
        ]);
    }

    /**
     * @Route("/{_locale}/login_check", name="login_check")
     */
    public function loginCheckAction()
    {
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        return $this->redirectToRoute('login');
    }

    /**
     * @return array ['user' => (int)..., 'distance' => (string)...]
     */
    protected function collectStatistics()
    {
        $repository = $this->getDoctrine()->getRepository('CoreBundle:Account');
        $numUser =  $repository->getAmountOfActivatedUsers();

        $repository = $this->getDoctrine()->getRepository('CoreBundle:Training');
        $numDistance =  $repository->getAmountOfLoggedKilometers();

        return ['user' => (int)$numUser, 'distance' => Distance::format($numDistance)];
    }

    /**
     * @Route("/plugin/{plugin}/{file}")
     */
    public function pluginAction($plugin, $file)
    {
        return $this->includeOldScript('../plugin/'.$plugin.'/'.$file);
    }

    /**
     * @Route("/index.php")
     */
    public function indexPhpAction(Request $request, Account $account)
    {
        if ($request->isXmlHttpRequest()) {
            $Frontend = new \Frontend(true, $this->get('security.token_storage'));

            $panelsContent = $this->getResponseForAllEnabledPanels($request, $account)->getContent();

            include $this->getParameter('kernel.root_dir').'/../dashboard.php';

            return new Response();
        }

        return $this->redirectToRoute('base_url');
    }

    /**
     * @Route("/login.php")
     */
    public function loginPhpAction()
    {
        return $this->redirectToRoute('dashboard');
    }

    /**
     * @Route("/unsubscribe/{mail}/{hash}", name="unsubscribe-mail")
     */
    public function unsubscribeMailAction($mail, $hash)
    {
        $repo = $this->getDoctrine()->getRepository('CoreBundle:Account');
        $account = $repo->findOneBy(array('mail' => $mail));

        if (null !== $account && $hash == md5($account->getUsername())) {
            return $this->render('account/unsubscribe_info.html.twig', array('mail' => $mail, 'hash' => $hash));
        }

        return $this->render('account/unsubscribe_failure.html.twig');
    }

    /**
     * @Route("/unsubscribe/{mail}/{hash}/confirm", name="unsubscribe-mail-confirm")
     */
    public function unsubscribeMailConfirmAction($mail, $hash)
    {
        $repo = $this->getDoctrine()->getRepository('CoreBundle:Account');
        $account = $repo->findOneBy(array('mail' => $mail));

        if (null !== $account && $hash == md5($account->getUsername())) {
            $account->setAllowMails(false);
            $repo->save($account);

            return $this->render('account/unsubscribe_success.html.twig');
        }

        return $this->render('account/unsubscribe_failure.html.twig');
    }

    /**
     * @Route("/feedback", name="feedback")
     * @Security("has_role('ROLE_USER')")
     */
    public function feedbackAction(Request $request, Account $account)
    {
        if (!empty($this->getParameter('feedback_mail'))) {
            $form = $this->createForm(FeedbackType::class, null, [
                'action' => $this->generateUrl('feedback'),
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->get('app.mailer.account')->sendCustomFeedbackToSystem($account, $this->getParameter('feedback_mail'), $form->getData()['message']);
                return $this->render('feedback.html.twig', [
                    'form' => $form->createView()
                ]);
            }

            return $this->render('feedback.html.twig', array(
                'form' => $form->createView()
            ));
        } else {
            throw $this->createNotFoundException();
        }
    }
}
