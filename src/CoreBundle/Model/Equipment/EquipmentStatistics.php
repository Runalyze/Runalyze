<?php

namespace Runalyze\Bundle\CoreBundle\Model\Equipment;

class EquipmentStatistics
{
    /** @var EquipmentStatistic[] */
    protected $Equipments = [];

    /**
     * @param array $queryResult
     */
    public function __construct(array $queryResult)
    {
        $activeEquipment = [];
        $inactiveEquipment = [];

        foreach ($queryResult as $row) {

            $equipmentStatistic = new EquipmentStatistic($row[0]);
            $equipmentStatistic->setNumberOfActivities($row['num']);
            $equipmentStatistic->setMaximalPace($row['max_pace']);
            $equipmentStatistic->setMaximalDistance($row['max_distance']);

            if ($equipmentStatistic->getEquipment()->isActive()) {
                $activeEquipment[] = $equipmentStatistic;
            } else {
                $inactiveEquipment[] = $equipmentStatistic;
            }
        }

        $this->Equipments = array_merge($activeEquipment, $inactiveEquipment);
    }

    /**
     * @return EquipmentStatistic[]
     */
    public function getStatistics()
    {
        return $this->Equipments;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return count($this->Equipments);
    }
}
