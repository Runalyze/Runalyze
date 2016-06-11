<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/account")
 */
class AccountController extends Controller
{
    /**
     * @Route("/delete/{hash}/confirmed", name="account_delete_confirmed", requirements={"hash": "[[:xdigit:]]{32}"})
     */
    public function deleteAccountConfirmedAction($hash)
    {
        new \Frontend(true);

        if (\AccountHandler::tryToDeleteAccount($hash)) {
            return $this->render('account/delete/success.html.twig');
        }

        return $this->render('account/delete/problem.html.twig');
    }

    /**
     * @Route("/delete/{hash}", name="account_delete", requirements={"hash": "[[:xdigit:]]{32}"})
     */
    public function deleteAccountAction($hash)
    {
        new \Frontend(true);

        $username = \AccountHandler::getUsernameForDeletionHash($hash);

        if (false === $username) {
            return $this->render('account/delete/problem.html.twig');
        }

        return $this->render('account/delete/please_confirm.html.twig', [
            'deletionHash' => $hash,
            'username' => $username
        ]);
    }

    /**
     * @Route("/recover/{hash}", name="account_recover_hash", requirements={"hash": "[[:xdigit:]]{32}"})
     */
    public function recoverForHashAction($hash)
    {
        new \Frontend(true);

        $successOrErrors = \AccountHandler::tryToSetNewPassword($hash);
        $username = \AccountHandler::getUsernameForChangePasswordHash($hash);

        if (true === $successOrErrors) {
            return $this->render('account/recover/success.html.twig');
        }

        if (false === $username) {
            return $this->render('account/recover/hash_invalid.html.twig', ['recoverHash' => $hash]);
        }

        return $this->render('account/recover/form_new_password.html.twig', [
            'recoverHash' => $hash,
            'username' => $username,
            'errors' => $successOrErrors
        ]);
    }

    /**
     * @Route("/recover", name="account_recover")
     */
    public function recoverAction(Request $request)
    {
        new \Frontend(true);

        $username = $request->request->get('send_username');
        $userIsUnknown = false;

        if ($username) {
            try {
                if (\AccountHandler::sendPasswordLinkTo($username)) {
                    return $this->render('account/recover/mail_delivered.html.twig');
                } else {
                    return $this->render('account/recover/mail_could_not_be_delivered.html.twig');
                }
            } catch (\InvalidArgumentException $e) {
                $userIsUnknown = true;
            }
        }

        return $this->render('account/recover/form_send_mail.html.twig', [
            'username' => $username,
            'user_is_unknown' => $userIsUnknown
        ]);
    }
}