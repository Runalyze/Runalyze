<?php

namespace Runalyze\Calculation\Distribution;

use Runalyze\Mathematics\Distribution\TimeSeries;
use Runalyze\Model\Trackdata;

/**
 * Empirical distribution for time series for trackdata
 */
class TimeSeriesForTrackdata extends TimeSeries
{
    /** @var Trackdata\Entity */
    protected $Trackdata;

    /** @var string enum */
    protected $IndexKey;

    /** @var string[] enums */
    protected $SumDifferencesKeys;

    /** @var string[] enums */
    protected $AvgValuesKeys;

    /** @var array */
    protected $Data = array();

    /**
     * @param Trackdata\Entity $trackdata
     * @param string $indexKey enum
     * @param string[] $sumDifferencesKeys enums
     * @param string[] $avgValuesKeys enums
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Trackdata\Entity $trackdata, $indexKey, array $sumDifferencesKeys = [], array $avgValuesKeys = [])
    {
        $this->Trackdata = $trackdata;
        $this->IndexKey = $indexKey;
        $this->SumDifferencesKeys = $sumDifferencesKeys;
        $this->AvgValuesKeys = $avgValuesKeys;

        $this->fillMissingTrackdataArrays();

        parent::__construct($trackdata->get($indexKey), $trackdata->get(Trackdata\Entity::TIME));
        $this->collectData();
    }

    protected function fillMissingTrackdataArrays()
    {
        $emptyArray = array_fill(0, $this->Trackdata->num(), 0);

        foreach (array_merge($this->SumDifferencesKeys, $this->AvgValuesKeys) as $key) {
            if (!$this->Trackdata->has($key)) {
                $this->Trackdata->set($key, $emptyArray);
            }
        }
    }

    protected function collectData()
    {
        $data = $this->Trackdata->get($this->IndexKey);
        $time = $this->Trackdata->get(Trackdata\Entity::TIME);
        $rawdata = array();

        foreach (array_merge($this->SumDifferencesKeys, $this->AvgValuesKeys) as $key) {
            $rawdata[$key] = $this->Trackdata->get($key);
        }

        foreach ($data as $i => $val) {
            if (!isset($this->Data[$val])) {
                $this->Data[$val] = array();

                foreach (array_merge($this->SumDifferencesKeys, $this->AvgValuesKeys) as $key) {
                    $this->Data[$val][$key] = 0;
                }
            }

            foreach ($this->SumDifferencesKeys as $key) {
                $prev = $i == 0 ? 0 : $rawdata[$key][$i - 1];
                $this->Data[$val][$key] += $rawdata[$key][$i] - $prev;
            }

            $timeDiff = $i == 0 ? $time[0] : $time[$i] - $time[$i - 1];

            foreach ($this->AvgValuesKeys as $key) {
                $this->Data[$val][$key] += $rawdata[$key][$i] * $timeDiff;
            }
        }

        $this->calculateAverages();
    }

    protected function calculateAverages()
    {
        if (!empty($this->AvgValuesKeys)) {
            foreach ($this->Data as $index => $data) {
                foreach ($this->AvgValuesKeys as $key) {
                    if ($this->Histogram[$index] > 0) {
                        $this->Data[$index][$key] = $data[$key] / $this->Histogram[$index];
                    }
                }
            }
        }
    }

    /**
     * @return array[] There is an array for each index of the histogram with data for each requested key
     */
    public function data()
    {
        return $this->Data;
    }
}
