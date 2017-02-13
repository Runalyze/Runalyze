<?php

namespace Runalyze\Sports\Performance;

use Runalyze\Mathematics\Scale;

/**
 * Monotony
 *
 * @see http://fellrnr.com/wiki/Training_Monotony
 */
class Monotony
{
    /** @var float Maximum */
    const MAX = 10.0;

    /** @var float Minimum */
    const MIN = 0.0;

    /** @var float Warning */
    const WARNING = 1.5;

    /** @var float Critical value */
    const CRITICAL = 2;

    /** @var int Number of days */
    const DAYS = 7;

    /** @var array */
    protected $Trimp = [];

    /** @var int */
    protected $Avg = 0;

    /** @var float|null */
    protected $Value = null;

    /** @var float|null */
    protected $MaximumPerDay = null;

    /**
     * @param array $trimpData
     * @param float|null $maximumPerDay
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $trimpData, $maximumPerDay = null)
    {
        $this->Trimp = $trimpData;
        $this->MaximumPerDay = $maximumPerDay;
    }

    public function calculate()
    {
        if (empty($this->Trimp)) {
            $this->Value = 0;

            return;
        }

        $this->Avg = array_sum($this->Trimp) / static::DAYS;
        $var = 0;

        foreach ($this->Trimp as $trimp) {
            $var += ($trimp - $this->Avg) * ($trimp - $this->Avg);
        }

        $var += (static::DAYS - count($this->Trimp)) * $this->Avg * $this->Avg;

        $var /= static::DAYS;

        $this->Value = ($var == 0) ? self::MAX : $this->Avg / sqrt($var);
    }

    /**
     * @return float
     *
     * @throws \RuntimeException
     */
    public function value()
    {
        if (null === $this->Value) {
            throw new \RuntimeException('Monotony has to be calculated first.');
        }

        return max(self::MIN, min($this->Value, self::MAX));
    }

    /**
     * Training strain
     *
     * We use the Trimp-average instead of the sum to keep this value comparable.
     *
     * @return float
     */
    public function trainingStrain()
    {
        return $this->Avg * static::DAYS * $this->value();
    }

    /**
     * @return float
     */
    public function valueAsPercentage()
    {
        return (new Scale\TwoPartPercental(self::MIN, self::CRITICAL, self::MAX))->transform($this->value());
    }

    /**
     * @return float
     *
     * @throws \RuntimeException
     */
    public function trainingStrainAsPercentage()
    {
        if (null === $this->MaximumPerDay) {
            throw new \RuntimeException('Maximum per day needs to be set to get a percentage.');
        }

        return (new Scale\Percental(0.0, $this->MaximumPerDay * static::DAYS))->transform($this->trainingStrain());
    }
}
