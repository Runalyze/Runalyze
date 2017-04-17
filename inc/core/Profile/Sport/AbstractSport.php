<?php

namespace Runalyze\Profile\Sport;

use Runalyze\Metrics\LegacyUnitConverter;

abstract class AbstractSport implements ProfileInterface
{
    /** @var int enum from SportProfile */
    private $Enum;

    /**
     * @param $enum int from SportProfile
     */
    public function __construct($enum)
    {
        $this->Enum = $enum;
    }

    public function getInternalProfileEnum()
    {
        return $this->Enum;
    }

    /**
     * @return bool
     */
    final public function isCustom()
    {
        return SportProfile::GENERIC == $this->Enum;
    }

    /**
     * @return bool
     */
    final public function isRunning()
    {
        return SportProfile::RUNNING == $this->Enum;
    }

    /**
     * @return bool
     */
    final public function isCycling()
    {
        return SportProfile::CYCLING == $this->Enum;
    }

    /**
     * @return bool
     */
    final public function isSwimming()
    {
        return SportProfile::SWIMMING == $this->Enum;
    }

    /**
     * @return int
     * @see \Runalyze\Parameter\Application\PaceUnit
     */
    public function getLegacyPaceUnitEnum()
    {
        return (new LegacyUnitConverter())->getLegacyPaceUnit($this->getPaceUnitEnum(), true);
    }
}
