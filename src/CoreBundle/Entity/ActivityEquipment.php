<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityEquipment
 *
 * @ORM\Table(name="activity_equipment", indexes={@ORM\Index(name="equipmentid", columns={"equipmentid"})})
 * @ORM\Entity
 */
class ActivityEquipment
{
    /**
     * @var Training
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Training")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="activityid", referencedColumnName="id", nullable=false)
     * })
     */
    private $activity;

    /**
     * @var Equipment
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Equipment")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="equipmentid", referencedColumnName="id", nullable=false)
     * })
     */
    private $equipment;

    /**
     * @param Training $activity
     * @return self
     */
    public function setActivity(Training $activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * @return Training
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param Equipment $equipment
     * @return self
     */
    public function setEquipment(Equipment $equipment)
    {
        $this->equipment = $equipment;

        return $this;
    }

    /**
     * @return Equipment
     */
    public function getEquipment()
    {
        return $this->equipment;
    }
}

