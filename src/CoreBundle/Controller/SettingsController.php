<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

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
            if (!empty($formdata['newPassword']['first'])) {
                if (empty($formdata['oldPassword'])) {
                    $form->get('oldPassword')->addError(new FormError($this->get('translator')->trans('To change your password you need to enter your current password.')));
                } else {
                    $oldPw = $this->encodePassword($account, $account->getSalt(), $formdata['oldPassword']);
                    if ($oldPw != $account->getPassword()) {
                        $form->get('oldPassword')->addError(new FormError($this->get('translator')->trans('Your password was not correct.')));
                    } else {
                        $newSalt = \AccountHandler::getNewSalt();
                    $hash = $this->encodePassword($account, $newSalt, $formdata['newPassword']['first']);
                    $account->setPassword($hash);
                    $account->setSalt($newSalt);
                    $this->addFlash('notice', $this->get('translator')->trans('Your password has been changed.'));
                    }
                }
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

    private function encodePassword(Account $Account, $salt, $plainPassword)
    {
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($Account);

        return $encoder->encodePassword($plainPassword, $salt);
    }
    /**
     * @Route("/settings/account/delete", name="settings-account-delete")
     * @Security("has_role('ROLE_USER')")
     */
    public function windowDeleteAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        new \Frontend(false, $this->get('security.token_storage'));

        return $this->render('settings/account-delete.html.twig', [
            'mailSent' => \AccountHandler::setAndSendDeletionKeyFor($user->getId())
        ]);
    }

}