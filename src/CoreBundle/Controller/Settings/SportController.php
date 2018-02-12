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
use Runalyze\Profile\Sport\SportProfile;
use Runalyze\Profile\View\DataBrowserRowProfile;
use Runalyze\Common\Enum\AbstractEnumFactoryTrait;
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
            'freeInternalTypes' => $this->getSportRepository()->getFreeInternalTypes($account),
            'calendarView' => new DataBrowserRowProfile()
        ]);
    }

    /**
     * @Route("/{sportid}/type/add", name="sport-type-add", requirements={"sportid" = "\d+"})
     */
    public function typeAddAction(Request $request, $sportid, Account $account)
    {
        $em = $this->getDoctrine()->getManager();
        $type = new Type();
        $type->setAccount($account);
        $form = $this->createForm(Form\Settings\SportTypeType::class, $type ,[
            'action' => $this->generateUrl('sport-type-add', ['sportid' => $sportid])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $type->setSport($em->getReference('CoreBundle:Sport', $sportid));
            $this->getTypeRepository()->save($type);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_PLUGINS);
            return $this->redirectToRoute('sport-edit', ['id' => $sportid]);
        }

        return $this->render('settings/sport/form-type.html.twig', [
            'form' => $form->createView(),
            'sport_id' => $sportid
        ]);
    }

    /**
     * @Route("/type/{id}/edit", name="sport-type-edit", requirements={"id" = "\d+"})
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
     * @Route("/type/{id}/delete", name="sport-type-delete", requirements={"id" = "\d+"})
     * @ParamConverter("type", class="CoreBundle:Type")
     */
    public function deleteSportTypeAction(Request $request, Type $type, Account $account)
    {
        if (!$this->isCsrfTokenValid('deleteSportType', $request->get('t'))) {
            $this->addFlash('error', $this->get('translator')->trans('Invalid token.'));

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
            $this->addFlash('success', $this->get('translator')->trans('The object has been deleted.'));
        } else {
            $this->addFlash('error', $this->get('translator')->trans('Object cannot be deleted.').' '.$this->get('translator')->trans('You have activities associated with this type.'));
        }
        return $this->redirect($this->generateUrl('settings-sports'));
    }

    /**
     * @Route("/add/{internalType}", name="sport-add", requirements={"internalType" = "\d+"})
     * @Route("/add/custom", name="sport-add-custom")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sportAddAction(Request $request, Account $account, $internalType = null)
    {
        $sport = new Sport();
        $sport->setAccount($account);

        if (null !== $internalType && $this->getSportRepository()->isInternalTypeFree($internalType, $account)) {
            $sport->setDataFrom(SportProfile::get($internalType));
            $this->getSportRepository()->save($sport);

            return $this->redirectToRoute('sport-edit', [
                'id' => $sport->getId()
            ]);
        }

        $form = $this->createForm(Form\Settings\SportType::class, $sport,[
            'action' => $this->generateUrl('sport-add')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getSportRepository()->save($sport);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_DATA_BROWSER);

            return $this->redirectToRoute('sport-edit', ['id' => $sport->getId()]);
        }

        return $this->render('settings/sport/form-sport.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="sport-edit", requirements={"id" = "\d+"})
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
     * @Route("/{id}/delete", name="sport-delete", requirements={"id" = "\d+"})
     * @ParamConverter("sport", class="CoreBundle:Sport")
     */
    public function sportDeleteAction(Request $request, Sport $sport, Account $account)
    {
        if (!$this->isCsrfTokenValid('deleteSport', $request->get('t'))) {
            $this->addFlash('error', $this->get('translator')->trans('Invalid token.'));

            return $this->redirect($this->generateUrl('sport-edit', ['id' => $sport->getId()]));
        }

        if ($sport->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }

        if (0 == $sport->getTrainings()->count()) {
            $this->getSportRepository()->remove($sport);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_DATA_BROWSER);
            $this->addFlash('success', $this->get('translator')->trans('The object has been deleted.'));
        } else {
            $this->addFlash('error', $this->get('translator')->trans('Object cannot be deleted.').' '.$this->get('translator')->trans('You have activities associated with this type.'));
        }

        return $this->redirect($this->generateUrl('settings-sports'));
    }
}
