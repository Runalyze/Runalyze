<?php

namespace Runalyze\Bundle\CoreBundle\Controller\My;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\User;
use Runalyze\Bundle\CoreBundle\Entity\UserRepository;
use Runalyze\Bundle\CoreBundle\Services\AutomaticReloadFlagSetter;
use Runalyze\Metrics\HeartRate\Unit\BeatsPerMinute;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
     */
    public function addAction()
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $user = new \UserData(\DataObject::$LAST_OBJECT);
        $user->setCurrentTimestamp();

        $form = new \StandardFormular($user, \StandardFormular::$SUBMIT_MODE_CREATE);
        $form->addCSSclass('no-automatic-reload');
        $form->setId('sportler');
        $form->setLayoutForFields(\FormularFieldset::$LAYOUT_FIELD_W33);

        if ($form->submitSucceeded()) {
            return $this->redirectToRoute('body-values-table');
        }

        return $this->render('my/body-values/form.html.twig', [
            'isNew' => true,
            'form' => $form
        ]);
    }

    /**
     * @Route("/{id}/edit", name="body-values-edit")
     * @ParamConverter("user", class="CoreBundle:User")
     */
    public function editAction(User $user, Account $account)
    {
        if ($user->getAccount()->getId() != $account->getId()) {
            throw $this->createNotFoundException();
        }

        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $user = new \UserData($user->getId());

        $form = new \StandardFormular($user, \StandardFormular::$SUBMIT_MODE_EDIT);
        $form->addCSSclass('no-automatic-reload');
        $form->setId('sportler');
        $form->setLayoutForFields(\FormularFieldset::$LAYOUT_FIELD_W33);

        return $this->render('my/body-values/form.html.twig', [
            'isNew' => false,
            'form' => $form
        ]);
    }

    /**
     * @Route("/{id}/delete", name="body-values-delete")
     * @ParamConverter("user", class="CoreBundle:User")
     */
    public function deleteAction(User $user, Account $account)
    {
        if ($user->getAccount()->getId() != 0 && $account->getId()) {
            throw $this->createNotFoundException('No user entry found.');
        }

        // Frontend is needed as long as user repository uses \Cache::delete()
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $this->getUserRepository()->remove($user, $account);
        $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_ALL);

        return $this->redirectToRoute('body-values-table');
    }

    /**
     * @Route("/table", name="body-values-table")
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
