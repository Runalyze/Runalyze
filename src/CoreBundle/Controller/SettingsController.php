<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Runalyze\Bundle\CoreBundle\Form\Settings\AccountType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SettingsController extends Controller
{
    /**
     * @Route("/settings/account", name="settings-account")
     * @Security("has_role('ROLE_USER')")
     */
    public function settingsAccountAction(Request $request)
    {
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
            $em->persist($account);
            $em->flush();
            $this->addFlash('notice', $this->get('translator')->trans('Your changes have been saved!'));

            if (isset($formdata['reset_configuration'])) {
                Configuration::resetConfiguration($user->getId());
                $this->addFlash('notice', $this->get('translator')->trans('Default configuration has been restored!'));
            }

            if (isset($formdata['language'])) {
               // Language::setLanguage($_POST['language']);
               // echo Ajax::wrapJS('document.cookie = "lang=" + encodeURIComponent("'.addslashes($_POST['language']).'");');
            }

            if (isset($formdata['new_password_first'])) {
                $this->addFlash('notice', $this->get('translator')->trans('Your password has been changed.'));
                //TODO Change password
                /*if (AccountHandler::comparePasswords($_POST['old_pw'], $Account['password'], $Account['salt'])) {
                    if (strlen($_POST['new_pw']) < AccountHandler::$PASS_MIN_LENGTH) {
                        ConfigTabs::addMessage( HTML::error(sprintf( __('The password has to contain at least %s characters.'), AccountHandler::$PASS_MIN_LENGTH)) );
                    } else {
                        AccountHandler::setNewPassword(SessionAccountHandler::getUsername(), $_POST['new_pw']);
                        ConfigTabs::addMessage( HTML::okay (__('Your password has been changed.')) );
                    }*/
                //look at https://knpuniversity.com/screencast/symfony2-ep2/form-submit
            }
        }

        return $this->render('settings/account.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function encodePassword(User $user, $plainPassword)
    {
        $encoder = $this->container->get('security.encoder_factory')
            ->getEncoder($user)
        ;

        return $encoder->encodePassword($plainPassword, $user->getSalt());
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