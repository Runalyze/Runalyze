<?php

namespace Runalyze\Bundle\CoreBundle\Controller\My;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\Type;
use Runalyze\Bundle\CoreBundle\Entity\TypeRepository;
use Runalyze\Bundle\CoreBundle\Form;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Runalyze\Bundle\CoreBundle\Services\AutomaticReloadFlagSetter;

/**
 * @Route("/my/sport")
 * @Security("has_role('ROLE_USER')")
 */
class SportController extends Controller
{
    /**
     * @return SportRepository
     */
    protected function getSportRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:Sport');
    }

    /**
     * @return TypeRepository
     */
    protected function getTypeRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:Type');
    }


    /**
     * @Route("/", name="settings-sports")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function overviewAction(Account $account)
    {
        $sport = $this->getSportRepository()->findAllFor($account);
        return $this->render('my/sport/overview.html.twig', [
            'sports' => $sport
        ]);
    }

    /**
     * @Route("/type/add", name="sport-type-add")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function typeAddAction(Request $request, Account $account)
    {
        $type = new Type();
        $type->setAccount($account);
        $form = $this->createForm(Form\SportTypeType::class, $type ,[
            'action' => $this->generateUrl('sport-type-add')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getTypeRepository()->save($type);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_PLUGINS);
        }

        return $this->render('my/sport/form-type.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/type/{id}/edit", name="sport-type-edit")
     * @ParamConverter("type", class="CoreBundle:Type")
     * @param Request $request
     * @param Type $type
     * @param Account $account
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function typeEditAction(Request $request, Type $type, Account $account)
    {
        if ($type->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(Form\SportTypeType::class, $type ,[
            'action' => $this->generateUrl('sport-type-edit', ['id' => $type->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getTypeRepository()->save($type);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_PLUGINS);
            return $this->redirectToRoute('sport-type-edit', ['id' => $type->getId()]);
        }

        return $this->render('my/sport/form-type.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/type/{id}/delete", name="sport-type-delete")
     * @ParamConverter("type", class="CoreBundle:Type")
     */
    public function deleteSportTypeAction(Request $request, Type $type, Account $account)
    {
        if (!$this->isCsrfTokenValid('deleteSportType', $request->get('t'))) {
            $this->addFlash('notice', $this->get('translator')->trans('Invalid token.'));
            return $this->redirect($this->generateUrl('sport-overview'));
        }
        if ($type->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($type);
        $em->flush();
        $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_ALL);
        $this->addFlash('notice', $this->get('translator')->trans('Sport type has been deleted.'));
        return $this->redirect($this->generateUrl('sport-overview'));
    }

    /**
     * @Route("/add", name="sport-add")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sportAddAction(Request $request, Account $account)
    {
        $sport = new Sport();
        $sport->setAccount($account);
        $form = $this->createForm(Form\SportType::class, $sport,[
            'action' => $this->generateUrl('sport-add')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getSportRepository()->save($sport);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_ALL);
            return $this->redirectToRoute('sport-type-edit', ['id' => $sport->getType()->getId()]);
        }

        return $this->render('my/sport/form-sport.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="sport-edit")
     * @ParamConverter("sport", class="CoreBundle:Sport")
     * @param Request $request
     * @param Sport $sport
     * @param Account $account
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sportEditAction(Request $request, Sport $sport, Account $account)
    {
        if ($sport->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(Form\SportType::class, $sport,[
            'action' => $this->generateUrl('sport-edit', ['id' => $sport->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getSportRepository()->save($sport, $account);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_PLUGINS);
            return $this->redirectToRoute('sport-edit', ['id' => $sport->getId()]);
        }

        return $this->render('my/sport/form-sport.html.twig', [
            'form' => $form->createView(),
            'types' => $this->getTypeRepository()->findAllFor($account, $sport)
        ]);
    }

    /**
     * @Route("/{id}/delete", name="sport-delete")
     * @ParamConverter("sport", class="CoreBundle:Sport")
     */
    public function sportDeleteAction(Request $request, Sport $sport, Account $account)
    {
        if (!$this->isCsrfTokenValid('deleteSport', $request->get('t'))) {
            $this->addFlash('notice', $this->get('translator')->trans('Invalid token.'));
            return $this->redirect($this->generateUrl('sport-edit', ['id' => $sport->getType()->getId()]));
        }
        if ($sport->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($sport);
        $em->flush();
        $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_ALL);
        $this->addFlash('notice', $this->get('translator')->trans('Sport has been deleted.'));
        return $this->redirect($this->generateUrl('setting-sports'));
    }
}