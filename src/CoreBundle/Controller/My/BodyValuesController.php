<?php

namespace Runalyze\Bundle\CoreBundle\Controller\My;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\User;
use Runalyze\Bundle\CoreBundle\Entity\UserRepository;
use Runalyze\Bundle\CoreBundle\Form\BodyValuesType;
use Runalyze\Bundle\CoreBundle\Services\AutomaticReloadFlagSetter;
use Runalyze\Metrics\HeartRate\Unit\BeatsPerMinute;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\PercentType;

/**
 * @Route("/my/body-values")
 */
class BodyValuesController extends Controller
{
    /**
     * @return UserRepository
     */
    protected function getUserRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:User');
    }

    /**
     * @Route("/add", name="body-values-add")
     * @param Request $request
     * @param Account $account
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request, Account $account)
    {
        /** @var User $oldUser */
        $oldUser = $this->getUserRepository()->getLatestEntryFor($account);
        $user = $oldUser ? $oldUser->cloneObjectForForm() : (new User())->setAccount($account)->setCurrentTimestamp();

        $form = $this->createForm(BodyValuesType::class, $user,[
            'action' => $this->generateUrl('body-values-add')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getUserRepository()->save($user, $account);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_ALL);
            return $this->redirectToRoute('body-values-table');
        }

        return $this->render('my/body-values/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="body-values-edit")
     * @ParamConverter("user", class="CoreBundle:User")
     * @param Request $request
     * @param User $user
     * @param Account $account
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, User $user, Account $account)
    {
        if ($user->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(BodyValuesType::class, $user, [
            'action' => $this->generateUrl('body-values-edit', ['id' => $user->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getUserRepository()->save($user, $account);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_ALL);
            return $this->redirectToRoute('body-values-table');
        }

        return $this->render('my/body-values/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name="body-values-delete")
     * @ParamConverter("user", class="CoreBundle:User")
     * @param User $user
     * @param Account $account
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(User $user, Account $account)
    {
        if ($user->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException('No user entry found.');
        }

        $this->getUserRepository()->remove($user);
        $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_ALL);

        return $this->redirectToRoute('body-values-table');
    }

    /**
     * @Route("/table", name="body-values-table")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tableAction(Account $account)
    {
        return $this->render('my/body-values/table.html.twig', [
            'values' => $this->getUserRepository()->findAllFor($account),
            'unitWeight' => $this->get('app.configuration_manager')->getList()->getUnitSystem()->getWeightUnit(),
            'unitHeartRate' => new BeatsPerMinute()
        ]);
    }
}
