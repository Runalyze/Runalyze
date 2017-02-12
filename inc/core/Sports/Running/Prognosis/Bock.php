<?php

namespace Runalyze\Sports\Running\Prognosis;

/**
 * Prognosis by Robert Bock
 *
 * Competition prediction based on CPP method by Robert Bock.
 * CPP stands for 'Competitive Performance Predictor'.
 * This method does not require the additional basic endurance calculation.
 *
 * @see http://www.robert-bock.de/Sport_0/lauf_7/cpp/cpp.html
 */
class Bock implements PrognosisInterface
{
    /** @var float */
    const MINIMAL_DISTANCE_FOR_REFERENCE_RESULT = 3.0;

	/** @var float */
	const K_LOWER_BOUND = 132.0;

	/** @var float */
	const K_UPPER_BOUND = 1800.0;

	/** @var float */
	const E_LOWER_BOUND = 1.0;

	/** @var float */
	const E_UPPER_BOUND = 2.0;

	/** @var float */
	protected $K = 0.0;

	/** @var float */
	protected $E = 1.0;

    /**
     * @param float $distance1 [km]
     * @param float $time1 [s]
     * @param float $distance2 [km]
     * @param float $time2 [s]
     */
    public function __construct($distance1 = 0.0, $time1 = 0.0, $distance2 = 0.0, $time2 = 0.0)
    {
        $this->setFromResults($distance1, $time1, $distance2, $time2);
    }

	/**
	 * Set from results
	 *
	 * Set constants K and e from given results (should not be too similar in distance).
	 * The documented version does not work. Log-version is from source-code.
     *
	 * @see http://www.robert-bock.de/Sport_0/lauf_7/cpp/cpp.html
	 * @see http://www.robert-bock.de/Sonstiges/cpp2.htm
	 *
	 * @param float $distance1 [km]
	 * @param float $time1 [s]
	 * @param float $distance2 [km]
	 * @param float $time2 [s]
     * @return $this
	 */
	public function setFromResults($distance1, $time1, $distance2, $time2)
    {
        if ($distance1 <= 0.0 || $distance2 <= 0.0) {
            return $this;
        }

		if ($distance1 > $distance2) {
			list($distance1, $time1, $distance2, $time2) = array($distance2, $time2, $distance1, $time1);
        }

		$this->E = log($time2 / $time1) / log($distance2 / $distance1);
		$this->K = $time2 / pow($distance2, $this->E);

		return $this;
	}

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/1258
     */
    public function areValuesValid()
    {
        return (
            ($this->K > self::K_LOWER_BOUND) &&
            ($this->K < self::K_UPPER_BOUND) &&
            ($this->E > self::E_LOWER_BOUND) &&
            ($this->E < self::E_UPPER_BOUND)
        );
    }

	public function getSeconds($distance)
    {
		$seconds = $this->K * pow($distance, $this->E);

		return ($distance > 3.0) ? round($seconds) : $seconds;
	}

	/** @return float */
	public function getK()
    {
		return $this->K;
	}

	/** @return float */
	public function getE()
    {
		return $this->E;
	}
}
