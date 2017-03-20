<?php

namespace Runalyze\Bundle\CoreBundle\Controller\My;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Equipment;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentRepository;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentType;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentTypeRepository;
use Runalyze\Bundle\CoreBundle\Form;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/my/equipment")
 */
class EquipmentController extends Controller
{
    /**
     * @return EquipmentRepository
     */
    protected function getEquipmentRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:Equipment');
    }

    /**
     * @return EquipmentTypeRepository
     */
    protected function getEquipmentTypeRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:EquipmentType');
    }

    /**
     * @Route("/category/{typeid}/table", name="equipment-category-table", requirements={"typeid" = "\d+"})
     */
    public function categoryTableAction($typeid, Account $account)
    {
        /** @var EquipmentType $equipmentType */
        $equipmentType = $this->getEquipmentTypeRepository()->findOneBy(['id' => $typeid, 'account' => $account->getId()]);
        $equipmentStatistics = $this->getEquipmentRepository()->getStatisticsForType($typeid, $account);

        if (null === $equipmentType) {
            throw $this->createAccessDeniedException();
        }

        $unitSystem = $this->get('app.configuration_manager')->getList()->getUnitSystem();

        if (1 == $equipmentType->getSport()->count()) {
            $unitSystem->setPaceUnitFromSport($equipmentType->getSport()->first());
        }

        return $this->render('my/equipment/category/table.html.twig', [
            'unitSystem' => $unitSystem,
            'category' => $equipmentType,
            'statistics' => $equipmentStatistics
        ]);
    }

    /**
     * @Route("/category/add", name="equipment-category-add")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function typeAddAction(Request $request, Account $account)
    {
        $equipmentType = new EquipmentType();
        $form = $this->createForm(Form\EquipmentCategoryType::class, $equipmentType ,[
            'action' => $this->generateUrl('equipment-category-add')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getEquipmentTypeRepository()->save($equipmentType, $account);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_ALL);
        }

        return $this->render('my/equipment/form-category.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/overview", name="equipment-overview")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function overviewAction(Account $account)
    {
        return $this->render('my/equipment/overview.html.twig', [
        ]);
    }

    /**
     * @Route("/add", name="equipment-add")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function equipmentAddAction(Request $request, Account $account)
    {
        $equipment = new Equipment();
        $form = $this->createForm(Form\EquipmentType::class, $equipment,[
            'action' => $this->generateUrl('equipment-add')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getEquipmentRepository()->save($equipment, $account);
            $this->get('app.automatic_reload_flag_setter')->set(AutomaticReloadFlagSetter::FLAG_ALL);
        }

        return $this->render('my/equipment/form-equipment.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
