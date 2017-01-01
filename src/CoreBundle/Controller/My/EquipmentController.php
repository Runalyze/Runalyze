<?php

namespace Runalyze\Bundle\CoreBundle\Controller\My;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentRepository;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentType;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentTypeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
}
