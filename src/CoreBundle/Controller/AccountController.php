<?php

    namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Bundle\CoreBundle\Form\RecoverPasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use  Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Swift_Message;

/**
 * @Route("/{_locale}/account")
 */
class AccountController extends Controller
{
    /**
     * @Route("/delete/{hash}/confirmed", name="account_delete_confirmed", requirements={"hash": "[[:xdigit:]]{32}"})
     */
    public function deleteAccountConfirmedAction($hash)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('CoreBundle:Account')->findOneBy(array('deletionHash' => $hash));

        if (null === $account) {
            return $this->render('account/delete/problem.html.twig');
        }
        $em->remove($account);
        $em->flush();

        return $this->render('account/delete/success.html.twig');
    }

    /**
     * @Route("/delete/{hash}", name="account_delete", requirements={"hash": "[[:xdigit:]]{32}"})
     */
    public function deleteAccountAction($hash)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('CoreBundle:Account')->findOneBy(array('deletionHash' => $hash));

        if (null === $account) {
            return $this->render('account/delete/problem.html.twig');
        }

        $username = $account->getUsername();

        return $this->render('account/delete/please_confirm.html.twig', [
            'deletionHash' => $hash,
            'username' => $username
        ]);
    }

    /**
     * @Route("/recover/{hash}", name="account_recover_hash", requirements={"hash": "[[:xdigit:]]{32}"})
     */
    public function recoverForHashAction($hash, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('CoreBundle:Account')->findOneBy(array('changepwHash' => $hash));

        if (null === $account) {
            return $this->render('account/recover/hash_invalid.html.twig', ['recoverHash' => $hash]);
        }

        $form = $this->createForm(RecoverPasswordType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newSalt = \AccountHandler::getNewSalt();
            $account->setSalt($newSalt);
            $encoder = $this->container->get('security.encoder_factory')->getEncoder($account);
            $account->setPassword($encoder->encodePassword($account->getPlainPassword(), $account->getSalt()));
            $account->setChangepwHash('');
            $account->setChangepwTimeLimit(0);

            $em->persist($account);
            $em->flush();
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
            ->add('username', TextType::class, array(
                'required' => false,
                'attr' => array(
                    'autofocus' => true
                )))->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $request->request->get($form->getName());
            $em = $this->getDoctrine()->getManager();
            $account = $em->getRepository('CoreBundle:Account')->findOneBy(array('username' => $data['username']));
            if (null == $account) {
                $form->get('username')->addError(new FormError($this->get('translator')->trans('The username is not known.')));
            } else {
                $ChangePwHash = bin2hex(random_bytes(16));
                $account->setChangepwHash($ChangePwHash);
                $account->setChangepwTimelimit(time()+86400);
                $em->persist($account);
                $em->flush();

                $message = Swift_Message::newInstance($this->get('translator')->trans('Reset your RUNALYZE password'))
                    ->setFrom(array($this->getParameter('mail_sender') => $this->getParameter('mail_name')))
                    ->setTo(array($account->getMail() => $account->getUsername()))
                    ->setBody($this->renderView('mail/account/recoverPassword.html.twig',
                        array('username' => $account->getUsername(),
                            'changepw_hash' => $ChangePwHash)
                    ),'text/html');
                $this->get('mailer')->send($message);

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
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('CoreBundle:Account')->findOneBy(array('activationHash' => $hash));

        if (null === $account) {
            return $this->render('account/activate/problem.html.twig');
        } else {
            $account->setActivationHash('');
            $em->persist($account);
            $em->flush();
        }

        return $this->render('account/activate/success.html.twig');
    }
}