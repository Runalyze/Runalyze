<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

    use Runalyze\Bundle\CoreBundle\Form\Settings\ChangeMailType;
use Runalyze\Bundle\CoreBundle\Form\Settings\ChangePasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Runalyze\Bundle\CoreBundle\Form\Settings\AccountType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Runalyze\Configuration;
use Runalyze\Language;
use \Swift_Message;

class SettingsController extends Controller
{
    /**
     * @Route("/settings/account", name="settings-account")
     * @Security("has_role('ROLE_USER')")
     */
    public function settingsAccountAction(Request $request)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('CoreBundle:Account')->find($user->getId());

        $form = $this->createForm(AccountType::class, $account, array(
            'action' => $this->generateUrl('settings-account')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formdata = $request->request->get($form->getName());
            $em = $this->getDoctrine()->getManager();

            if (isset($formdata['reset_configuration'])) {
                Configuration::resetConfiguration($user->getId());
                $this->addFlash('notice', $this->get('translator')->trans('Default configuration has been restored!'));
            }

            if (isset($formdata['language'])) {
                $this->get('session')->set('_locale', $formdata['language']);
                Language::setLanguage($formdata['language']);
               // echo Ajax::wrapJS('document.cookie = "lang=" + encodeURIComponent("'.addslashes($_POST['language']).'");');
            }
            $em->persist($account);
            $em->flush();
            $this->addFlash('notice', $this->get('translator')->trans('Your changes have been saved!'));
        }

        return $this->render('settings/account.html.twig', [
            'form' => $form->createView(),
            'language' => $account->getLanguage()
        ]);
    }

    /**
     * @Route("/settings/password", name="settings-password")
     * @Security("has_role('ROLE_USER')")
     */
    public function settingsPasswordAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('CoreBundle:Account')->find($user->getId());
        $form = $this->createForm(ChangePasswordType::class, $account, array(
            'action' => $this->generateUrl('settings-password')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formdata = $request->request->get($form->getName());
            $newSalt = \AccountHandler::getNewSalt();
            $hash = $this->encodePassword($account, $newSalt, $formdata['plainPassword']['first']);
            $account->setPassword($hash);
            $account->setSalt($newSalt);
            $em->persist($account);
            $em->flush();
            $this->addFlash('notice', $this->get('translator')->trans('Your new password has been saved!'));
        }

            return $this->render('settings/account-password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/settings/mail", name="settings-mail")
     * @Security("has_role('ROLE_USER')")
     */
    public function settingsMailAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('CoreBundle:Account')->find($user->getId());
        $form = $this->createForm(ChangeMailType::class, $account, array(
            'action' => $this->generateUrl('settings-mail')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($account);
            $em->flush();
            $this->addFlash('notice', $this->get('translator')->trans('Your mail address has been changed!'));
        }
            return $this->render('settings/account-mail.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function encodePassword(Account $Account, $salt)
    {
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($Account);

        return $encoder->encodePassword($Account->getPlainPassword(), $salt);
    }
    /**
     * @Route("/settings/account/delete", name="settings-account-delete")
     * @Security("has_role('ROLE_USER')")
     */
    public function windowDeleteAction(Account $account)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        //$account = $em->getRepository('CoreBundle:Account')->find($user->getId());
        $hash = bin2hex(random_bytes(16));
        $account->setDeletionHash($hash);
        $em->persist($account);
        $em->flush();

        $message = Swift_Message::newInstance($this->get('translator')->trans('Deletion request of your RUNALYZE account'))
            ->setFrom(array($this->getParameter('mail_sender') => $this->getParameter('mail_name')))
            ->setTo(array($account->getMail() => $account->getUsername()))
            ->setBody($this->renderView('mail/account/deleteAccountRequest.html.twig',
                array('username' => $account->getUsername(),
                    'deletion_hash' => $hash)
            ),'text/html');
        $this->get('mailer')->send($message);

        return $this->render('settings/account-delete.html.twig');
    }

}