<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Bundle\CoreBundle\Entity\AccountRepository;
use Runalyze\Bundle\CoreBundle\Form\Settings\ChangeMailType;
use Runalyze\Bundle\CoreBundle\Form\Settings\ChangePasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Runalyze\Bundle\CoreBundle\Form\Settings\AccountType;
use Runalyze\Configuration;
use Runalyze\Language;

class SettingsController extends Controller
{
    /**
     * @return AccountRepository
     */
    protected function getAccountRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:Account');
    }

    /**
     * @Route("/settings/account", name="settings-account")
     * @Security("has_role('ROLE_USER')")
     */
    public function settingsAccountAction(Request $request, Account $account)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $form = $this->createForm(AccountType::class, $account, array(
            'action' => $this->generateUrl('settings-account')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formdata = $request->request->get($form->getName());

            if (isset($formdata['reset_configuration'])) {
                Configuration::resetConfiguration($account->getId());
                $this->addFlash('success', $this->get('translator')->trans('Default configuration has been restored!'));
            }

            if (isset($formdata['language'])) {
                $this->get('session')->set('_locale', $formdata['language']);
                Language::setLanguage($formdata['language']);
            }

            $this->getAccountRepository()->save($account);

            $this->addFlash('success', $this->get('translator')->trans('Your changes have been saved!'));
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
    public function settingsPasswordAction(Request $request, Account $account)
    {
        $form = $this->createForm(ChangePasswordType::class, $account, array(
            'action' => $this->generateUrl('settings-password')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formdata = $request->request->get($form->getName());
            $account->setPlainPassword($formdata['plainPassword']['first']);
            $this->encodePassword($account);
            $this->getAccountRepository()->save($account);

            $this->addFlash('success', $this->get('translator')->trans('Your new password has been saved!'));
        }

        return $this->render('settings/account-password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/settings/mail", name="settings-mail")
     * @Security("has_role('ROLE_USER')")
     */
    public function settingsMailAction(Request $request, Account $account)
    {
        $form = $this->createForm(ChangeMailType::class, $account, array(
            'action' => $this->generateUrl('settings-mail')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getAccountRepository()->save($account);
            $this->addFlash('success', $this->get('translator')->trans('Your mail address has been changed!'));
        }

        return $this->render('settings/account-mail.html.twig', [
            'form' => $form->createView()
        ]);
    }

    protected function encodePassword(Account $account)
    {
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($account);

        $account->setNewSalt();
        $account->setPassword($encoder->encodePassword($account->getPlainPassword(), $account->getSalt()));
    }

    /**
     * @Route("/settings/account/delete", name="settings-account-delete")
     * @Security("has_role('ROLE_USER')")
     */
    public function windowDeleteAction(Account $account)
    {
        $account->setNewDeletionHash();
        $this->getAccountRepository()->save($account);

        $this->get('app.mailer.account')->sendDeleteLinkTo($account);

        return $this->render('settings/account-delete.html.twig');
    }
}
