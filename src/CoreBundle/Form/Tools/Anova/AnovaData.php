<?php

namespace Runalyze\Bundle\CoreBundle\Form\Tools\Anova;

use DateTime;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\Type;

class AnovaData
{
    /** @var DateTime */
    protected $DateFrom;

    /** @var DateTime */
    protected $DateTo;

    /** @var Sport[] */
    protected $Sport = [];

    /** @var Type[] */
    protected $Type = [];

    /** @var string */
    protected $ValueToGroupBy;

    /** @var string */
    protected $ValueToLookAt;

    /**
     * @param Sport[] $sport
     * @param Type[] $type
     * @return AnovaData
     */
    public static function getDefault(array $sport, array $type)
    {
        $data = new self;
        $data->setSport($sport);
        $data->setType($type);
        $data->setDateFrom((new \DateTime())->sub(new \DateInterval('P6M')));
        $data->setDateTo(new \DateTime());

        return $data;
    }

    public function setDateFrom(DateTime $dateFrom)
    {
        $this->DateFrom = $dateFrom;
    }

    /**
     * @return DateTime
     */
    public function getDateFrom()
    {
        return $this->DateFrom;
    }

    /**
     * @return int
     */
    public function getDateFromTimestamp()
    {
        return $this->getDateFrom()->setTimezone(new \DateTimeZone('UTC'))->getTimestamp();
    }

    public function setDateTo(DateTime $dateTo)
    {
        $this->DateTo = $dateTo;
    }

    /**
     * @return DateTime
     */
    public function getDateTo()
    {
        return $this->DateTo;
    }

    /**
     * @return int
     */
    public function getDateToTimestamp()
    {
        return $this->getDateTo()->setTimezone(new \DateTimeZone('UTC'))->getTimestamp();
    }

    /**
     * @param Sport[] $sport
     */
    public function setSport(array $sport)
    {
        $this->Sport = $sport;
    }

    /**
     * @return Sport[]
     */
    public function getSport()
    {
        return $this->Sport;
    }

    /**
     * @param Type[] $type
     */
    public function setType(array $type)
    {
        $this->Type = $type;
    }

    /**
     * @return Type[]
     */
    public function getType()
    {
        return $this->Type;
    }

    public function setValueToGroupBy($valueToGroupBy)
    {
        $this->ValueToGroupBy = $valueToGroupBy;
    }

    /**
     * @return string
     */
    public function getValueToGroupBy()
    {
        return $this->ValueToGroupBy;
    }

    public function setValueToLookAt($valueToLookAt)
    {
        $this->ValueToLookAt = $valueToLookAt;
    }

    /**
     * @return string
     */
    public function getValueToLookAt()
    {
        return $this->ValueToLookAt;
    }
}
