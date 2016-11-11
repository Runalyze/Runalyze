<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\AccountRepository;
use Runalyze\Bundle\CoreBundle\Form\RecoverPasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Swift_Message;

/**
 * @Route("/{_locale}/account")
 */
class AccountController extends Controller
{
    /**
     * @return AccountRepository
     */
    protected function getAccountRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:Account');
    }

    /**
     * @Route("/delete/{hash}/confirmed", name="account_delete_confirmed", requirements={"hash": "[[:xdigit:]]{32}"})
     */
    public function deleteAccountConfirmedAction($hash)
    {
        if ($this->getAccountRepository()->deleteByHash($hash)) {
            return $this->render('account/delete/success.html.twig');
        }

        return $this->render('account/delete/problem.html.twig');
    }

    /**
     * @Route("/delete/{hash}", name="account_delete", requirements={"hash": "[[:xdigit:]]{32}"})
     */
    public function deleteAccountAction($hash)
    {
        /** @var Account|null $account */
        $account = $this->getAccountRepository()->findOneBy(['deletionHash' => $hash]);

        if (null === $account) {
            return $this->render('account/delete/problem.html.twig');
        }

        return $this->render('account/delete/please_confirm.html.twig', [
            'deletionHash' => $hash,
            'username' => $account->getUsername()
        ]);
    }

    /**
     * @Route("/recover/{hash}", name="account_recover_hash", requirements={"hash": "[[:xdigit:]]{32}"})
     */
    public function recoverForHashAction($hash, Request $request)
    {
        /** @var Account|null $account */
        $account = $this->getAccountRepository()->findOneBy(array('changepwHash' => $hash));

        if (null === $account) {
            return $this->render('account/recover/hash_invalid.html.twig', ['recoverHash' => $hash]);
        }

        $form = $this->createForm(RecoverPasswordType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encoder = $this->container->get('security.encoder_factory')->getEncoder($account);

            $account->setNewSalt();
            $account->setPassword($encoder->encodePassword($account->getPlainPassword(), $account->getSalt()));
            $account->removeChangePasswordHash();

            $this->getAccountRepository()->save($account);

            return $this->render('account/recover/success.html.twig');
        }

        return $this->render('account/recover/form_new_password.html.twig', [
            'recoverHash' => $hash,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/recover", name="account_recover")
     */
    public function recoverAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('username', TextType::class, [
                'required' => false,
                'attr' => [
                    'autofocus' => true
                ]
            ])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /** @var Account|null $account */
            $account = $this->getAccountRepository()->findOneBy([
                'username' => $request->request->get($form->getName())['username']
            ]);

            if (null === $account) {
                $form->get('username')->addError(new FormError($this->get('translator')->trans('The username is not known.')));
            } else {
                $account->setNewChangePasswordHash();
                $this->getAccountRepository()->save($account);

                $this->get('mailer')->send(
                    Swift_Message::newInstance($this->get('translator')->trans('Reset your RUNALYZE password'))
                        ->setFrom([$this->getParameter('mail_sender') => $this->getParameter('mail_name')])
                        ->setTo([$account->getMail() => $account->getUsername()])
                        ->setBody($this->renderView('mail/account/recoverPassword.html.twig', [
                            'username' => $account->getUsername(),
                            'changepw_hash' => $account->getChangepwHash()
                        ]), 'text/html')
                );

                return $this->render('account/recover/mail_delivered.html.twig');
            }
        }

        return $this->render('account/recover/form_send_mail.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/activate/{hash}", name="account_activate", requirements={"hash": "[[:xdigit:]]{32}"})
     */
    public function activateAccountAction($hash)
    {
        if ($this->getAccountRepository()->activateByHash($hash)) {
            return $this->render('account/activate/success.html.twig');
        }

        return $this->render('account/activate/problem.html.twig');
    }
}
