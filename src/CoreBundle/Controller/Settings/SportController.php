<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Settings;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Bundle\CoreBundle\Entity\Type;
use Runalyze\Bundle\CoreBundle\Entity\TypeRepository;
use Runalyze\Bundle\CoreBundle\Form;
use Runalyze\Bundle\CoreBundle\Services\AutomaticReloadFlagSetter;
use Runalyze\Profile\View\DataBrowserRowProfile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/settings/sport")
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
     * @return TrainingRepository
     */
    protected function getTrainingRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:Training');
    }


    /**
     * @Route("", name="settings-sports")
     */
    public function overviewAction(Account $account)
    {
        return $this->render('settings/sport/overview.html.twig', [
            'sports' => $this->getSportRepository()->findAllFor($account),
            'hasTrainings' => array_flip($this->getTrainingRepository()->getSportsWithTraining($account)),
            'calendarView' => new DataBrowserRowProfile()
        ]);
    }

    /**
     * @Route("/type/add", name="sport-type-add")
     */
    public function typeAddAction(Request $request, Account $account)
    {
        $type = new Type();
        $type->setAccount($account);
        $form = $this->createForm(Form\Settings\SportTypeType::class, $type ,[
            'action' => $this->generateUrl('sport-type-add')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getTypeRepository()->save($type);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_PLUGINS);
        }

        return $this->render('settings/sport/form-type.html.twig', [
            'form' => $form->createView(),
            'sport_id' => null
        ]);
    }

    /**
     * @Route("/type/{id}/edit", name="sport-type-edit")
     * @ParamConverter("type", class="CoreBundle:Type")
     */
    public function typeEditAction(Request $request, Type $type, Account $account)
    {
        if ($type->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(Form\Settings\SportTypeType::class, $type ,[
            'action' => $this->generateUrl('sport-type-edit', ['id' => $type->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getTypeRepository()->save($type);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_DATA_BROWSER);

            return $this->redirectToRoute('sport-type-edit', ['id' => $type->getId()]);
        }
        return $this->render('settings/sport/form-type.html.twig', [
            'form' => $form->createView(),
            'sport_id' => $type->getSport()->getId()
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

            return $this->redirect($this->generateUrl('settings-sports'));
        }

        if ($type->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }

        if ($type->getTrainings()->count() == NULL) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($type);
            $em->flush();
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_DATA_BROWSER);
            $this->addFlash('notice', $this->get('translator')->trans('Activity type has been deleted.'));
        } else {
            $this->addFlash('notice', $this->get('translator')->trans('Activity type cannot be deleted.').' '.$this->get('translator')->trans('You have activities associated with this type.'));
        }
        return $this->redirect($this->generateUrl('settings-sports'));
    }

    /**
     * @Route("/add", name="sport-add")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sportAddAction(Request $request, Account $account)
    {
        $sport = new Sport();
        $sport->setAccount($account);
        $form = $this->createForm(Form\Settings\SportType::class, $sport,[
            'action' => $this->generateUrl('sport-add')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getSportRepository()->save($sport);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_DATA_BROWSER);

            return $this->redirectToRoute('sport-type-edit', ['id' => $sport->getId()]);
        }

        return $this->render('settings/sport/form-sport.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="sport-edit")
     * @ParamConverter("sport", class="CoreBundle:Sport")
     */
    public function sportEditAction(Request $request, Sport $sport, Account $account)
    {
        if ($sport->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(Form\Settings\SportType::class, $sport,[
            'action' => $this->generateUrl('sport-edit', ['id' => $sport->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getSportRepository()->save($sport);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_DATA_BROWSER);

            return $this->redirectToRoute('sport-edit', ['id' => $sport->getId()]);
        }
        return $this->render('settings/sport/form-sport.html.twig', [
            'form' => $form->createView(),
            'types' => $this->getTypeRepository()->findAllFor($account, $sport),
            'calendarView' => new DataBrowserRowProfile(),
            'hasTrainings' => array_flip($this->getTrainingRepository()->getTypesWithTraining($account)),
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

            return $this->redirect($this->generateUrl('sport-edit', ['id' => $sport->getId()]));
        }

        if ($sport->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }

        if (0 == $sport->getTrainings()->count()) {
            $this->getSportRepository()->remove($sport);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_DATA_BROWSER);
            $this->addFlash('notice', $this->get('translator')->trans('Sport type has been deleted.'));
        } else {
            $this->addFlash('notice', $this->get('translator')->trans('Sport type cannot be deleted.').' '.$this->get('translator')->trans('You have activities associated with this type.'));
        }

        return $this->redirect($this->generateUrl('settings-sports'));
    }
}
