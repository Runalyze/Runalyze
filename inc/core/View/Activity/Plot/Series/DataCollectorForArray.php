<?php

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Entity as Trackdata;

class DataCollectorForArray extends DataCollector
{
    /** @var array */
    protected $RawData;

    /** @var bool */
    protected $InterpolateData;

	/**
	 * @param \Runalyze\Model\Trackdata\Entity $trackdata
     * @param array $data
	 * @throws \InvalidArgumentException
	 */
	public function __construct(Trackdata $trackdata, array $data, $interpolateData = true) {
        if (count($data) !== $trackdata->num()) {
            throw new \InvalidArgumentException('Provided data must be of same size as trackdata.');
        }

        $this->RawData = $data;
        $this->InterpolateData = $interpolateData;

		parent::__construct($trackdata, Trackdata::DISTANCE);
	}

	protected function collect()
    {
		do {
			$this->move();

            if ($this->InterpolateData) {
                $value = array_sum(
                        array_slice($this->RawData, $this->Loop->lastIndex(), $this->Loop->currentStepSize())
                    ) / $this->Loop->currentStepSize();
            } else {
    			$value = $this->RawData[$this->Loop->index()];
            }

			if ($this->XAxis == self::X_AXIS_DISTANCE) {
				$this->Data[(string)$this->Loop->current(Trackdata::DISTANCE)] = $value;
			} elseif ($this->XAxis == self::X_AXIS_TIME) {
				$this->Data[(string)$this->Loop->current(Trackdata::TIME).'000'] = $value;
			} else {
				$this->Data[] = $value;
			}
		} while (!$this->Loop->isAtEnd());
	}
}
