<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/settings/account")
 */
class SettingsAccountController extends Controller
{
    /**
     * @TODO check referer
     * @Route("/delete/success", name="settings_account_delete_success")
     */
    public function deleteAccountSuccessAction()
    {
        new \Frontend(true);

        return $this->render('settings/account/delete/success.html.twig');
    }

    /**
     * @Route("/delete/problem", name="settings_account_delete_problem")
     */
    public function deleteAccountProblemAction()
    {
        new \Frontend(true);

        return $this->render('settings/account/delete/problem.html.twig');
    }

    /**
     * @TODO conditions for the hash
     * @Route("/delete/{hash}/confirmed", name="settings_account_delete_confirmed")
     */
    public function deleteAccountConfirmedAction($hash)
    {
        new \Frontend(true);

        if (\AccountHandler::tryToDeleteAccount($hash)) {
            return $this->redirectToRoute('settings_account_delete_success');
        }

        return $this->redirectToRoute('settings_account_delete_problem');
    }

    /**
     * @TODO conditions for the hash
     * @Route("/delete/{hash}", name="settings_account_delete")
     */
    public function deleteAccountAction($hash)
    {
        new \Frontend(true);

        return $this->render('settings/account/delete/please_confirm.html.twig', [
            'deletionHash' => $hash
        ]);
    }
}