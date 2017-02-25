<?php

namespace Runalyze;

use Runalyze\Profile\Athlete\Gender;

class Athlete
{
    /** @var int */
    protected $Gender;

    /** @var null|int [bpm] */
    protected $maximalHR = null;

    /** @var null|int [bpm] */
    protected $restingHR = null;

    /** @var null|float [kg] */
    protected $weight = null;

    /** @var null|int */
    protected $birthyear = null;

    /** @var float [ml/kg/min] */
    protected $vo2max = 0.0;

    /**
     * @param null|int $Gender see \Runalyze\Profile\Athlete\Gender
     * @param null|int $maximalHR [bpm]
     * @param null|int $restingHR [bpm]
     * @param null|float $weight [kg]
     * @param null|int $birthyear
     * @param float $vo2max [ml/kg/min]
     */
    public function __construct(
        $Gender = null,
        $maximalHR = null,
        $restingHR = null,
        $weight = null,
        $birthyear = null,
        $vo2max = 0.0
    )
    {
        $this->Gender = $Gender ?: Gender::NONE;
        $this->maximalHR = $maximalHR;
        $this->restingHR = $restingHR;
        $this->weight = $weight;
        $this->birthyear = $birthyear;
        $this->vo2max = $vo2max;
    }

    /**
     * Gender
     *
     * @see \Runalyze\Profile\Athlete\Gender
     * @return null|int
     */
    public function gender()
    {
        return $this->Gender;
    }

    /**
     * @return null|int [bpm]
     */
    public function maximalHR()
    {
        return $this->maximalHR;
    }

    /**
     * @return null|int [bpm]
     */
    public function restingHR()
    {
        return $this->restingHR;
    }

    /**
     * @return null|int [kg]
     */
    public function weight()
    {
        return $this->weight;
    }

    /**
     * @return null|int [years]
     */
    public function age()
    {
        return (null !== $this->birthyear) ? date("Y") - $this->birthyear : null;
    }

    /**
     * @return null|int
     */
    public function birthyear()
    {
        return $this->birthyear;
    }

    /**
     * @return float
     */
    public function vo2max()
    {
        return $this->vo2max;
    }

    /**
     * @return bool
     */
    public function knowsGender()
    {
        return ($this->Gender !== Gender::NONE && null !== $this->Gender);
    }

    /**
     * @return bool
     */
    public function knowsMaximalHeartRate()
    {
        return (null !== $this->maximalHR);
    }

    /**
     * @return bool
     */
    public function knowsRestingHeartRate()
    {
        return (null !== $this->restingHR);
    }

    /**
     * @return bool
     */
    public function knowsWeight()
    {
        return (null !== $this->weight);
    }

    /**
     * @return bool
     */
    public function knowsBirthyear()
    {
        return (null !== $this->birthyear);
    }

    /**
     * @return bool
     */
    public function knowsAge()
    {
        return (null !== $this->birthyear);
    }

    /**
     * @return bool
     */
    public function knowsEffectiveVO2max()
    {
        return (0.0 !== $this->vo2max);
    }
}
