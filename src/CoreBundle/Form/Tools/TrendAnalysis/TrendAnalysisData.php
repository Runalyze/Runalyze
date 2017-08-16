<?php

namespace Runalyze\Bundle\CoreBundle\Form\Tools\TrendAnalysis;

use DateTime;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\Type;

class TrendAnalysisData
{
    /** @var DateTime */
    protected $DateFrom;

    /** @var DateTime */
    protected $DateTo;

    /** @var Sport[] */
    protected $Sport = [];

    /** @var Type[] */
    protected $Type = [];

    /** @var string|null */
    protected $ValueToLookAt = null;

    /**
     * @param Sport[] $sports
     * @return TrendAnalysisData
     */
    public static function getDefault(array $sports, array $types)
    {
        $data = new self;
        $data->setSport($sports);
        $data->setType($types);
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
     * @param Sport[] $sports
     */
    public function setSport(array $sports)
    {
        $this->Sport = $sports;
    }

    /**
     * @return Sport[]
     */
    public function getSport()
    {
        return $this->Sport;
    }

    /**
     * @param Type[] $types
     */
    public function setType(array $types)
    {
        $this->Type = $types;
    }

    /**
     * @return Type[]
     */
    public function getType()
    {
        return $this->Type;
    }

    /**
     * @param string|null $valueToLookAt
     */
    public function setValueToLookAt($valueToLookAt)
    {
        $this->ValueToLookAt = $valueToLookAt;
    }

    /**
     * @return string|null
     */
    public function getValueToLookAt()
    {
        return $this->ValueToLookAt;
    }
}
