<?php
/**
 * This file contains class::TrackdataAverages
 * @package Runalyze\Calculation\Distribution
 */

namespace Runalyze\Calculation\Distribution;

use Runalyze\Model\Trackdata;

/**
 * Calculate averages for trackdata
 *
 * This class is more efficient than constructing a TimeSeries for every key.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Distribution
 */
class TrackdataAverages
{
	/** @var \Runalyze\Model\Trackdata\Entity */
	protected $Trackdata;

	/**
	 * @var string[] enums of requested keys
	 */
	protected $Keys;

	/**
	 * @var array ['key' => mean]
	 */
	protected $Averages = [];

    /** @var bool */
    protected $IgnoreZeros = true;

    /** @var array */
    protected $KeysThatAllowZero;

	/**
	 * Constructor
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
	 * @param string[] $keys enums
     * @param bool $ignoreZeros by default 0s are ignored for mean
     * @param array|bool $keysThatAllowZero some keys may allow 0s, by default: cadence, temperature
     * @throws \InvalidArgumentException
	 */
	public function __construct(Trackdata\Entity $trackdata, array $keys, $ignoreZeros = true, $keysThatAllowZero = false)
	{
		$this->Trackdata = $trackdata;
		$this->Keys = $keys;
        $this->IgnoreZeros = $ignoreZeros;
        $this->KeysThatAllowZero = ($keysThatAllowZero !== false) ? $keysThatAllowZero : $this->defaultKeysThatAllowZero();

        $this->checkData();
		$this->prepareData();
		$this->collectData();
	}

    /**
     * @return array
     */
    protected function defaultKeysThatAllowZero()
    {
        return [
            Trackdata\Entity::CADENCE,
            Trackdata\Entity::TEMPERATURE
        ];
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function checkData()
    {
        if (!$this->Trackdata->has(Trackdata\Entity::TIME)) {
            throw new \InvalidArgumentException('Trackdata must have time data.');
        }

        if ($this->Trackdata->totalTime() <= 0) {
            throw new \InvalidArgumentException('Trackdata\'s time array must not be empty.');
        }
    }

	/**
	 * Remove keys without data and set their average to null
	 */
	protected function prepareData()
	{
		foreach ($this->Keys as $i => $key) {
			if (!$this->Trackdata->has($key)) {
				$this->Averages[$key] = null;
                unset($this->Keys[$i]);
			}
		}
	}

	/**
	 * Collect data
	 */
	protected function collectData()
	{
		$time = $this->Trackdata->get(Trackdata\Entity::TIME);
		$weightedSums = [];
        $timeInZero = [];
        $rawdata = [];
        $lastTime = 0;

		foreach ($this->Keys as $key) {
            $weightedSums[$key] = 0;
            $timeInZero[$key] = 0;
			$rawdata[$key] = $this->Trackdata->get($key);
		}

		foreach ($time as $timeIndex => $currentTime) {
            $timeDiff = $currentTime - $lastTime;

            foreach ($this->Keys as $key) {
                $weightedSums[$key] += $timeDiff * $rawdata[$key][$timeIndex];

                if ($rawdata[$key][$timeIndex] == 0) {
                    $timeInZero[$key] += $timeDiff;
                }
            }

            $lastTime = $currentTime;
		}

        foreach ($this->Keys as $key) {
            if ($this->IgnoreZeros && !in_array($key, $this->KeysThatAllowZero) && $timeInZero[$key] < $lastTime) {
                $this->Averages[$key] = $weightedSums[$key] / ($lastTime - $timeInZero[$key]);
            } else {
                $this->Averages[$key] = $weightedSums[$key] / $lastTime;
            }
        }
	}

    /**
     * @param string $key
     * @return double|null
     * @throws \InvalidArgumentException
     */
    public function average($key)
    {
        if (!array_key_exists($key, $this->Averages)) {
            throw new \InvalidArgumentException('No average calculated for "'.$key.'".');
        }

        return $this->Averages[$key];
    }

	/**
	 * @return array[] array('key' => mean), mean can be null if array was empty
	 */
	public function averages()
    {
		return $this->Averages;
	}
}