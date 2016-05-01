<?php
/**
 * This file contains class::HRVwithoutAnomalies
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Calculation\HRV\Calculator;
use Runalyze\Model;
use Runalyze\View\Activity;

/**
 * Plot for: heart rate variability without anomalies
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class HRVwithoutAnomalies extends HRV
{
    /**
     * Init data
     * @param \Runalyze\Model\HRV\Entity $hrv
     */
    protected function initHRVData(Model\HRV\Entity $hrv)
    {
        if (count($this->XAxisData) == $hrv->num()) {
            $this->XAxis = DataCollector::X_AXIS_TIME;
            $this->defineFilteredDataAndAxis($hrv);
        } else {
            $this->XAxis = DataCollector::X_AXIS_INDEX;
            $this->Data = (new Calculator($hrv))->filteredObject()->data();
        }
    }

    /**
     * @param \Runalyze\Model\HRV\Entity $hrv
     */
    protected function defineFilteredDataAndAxis(Model\HRV\Entity $hrv)
    {
        $OriginalData = $hrv->data();
        $FilteredData = (new Calculator($hrv))->filteredObject()->data();
        $filteredIndex = 0;
        $num = $hrv->num();

        for ($i = 0; $i < $num; ++$i) {
            if (!isset($FilteredData[$filteredIndex]) || $OriginalData[$i] != $FilteredData[$filteredIndex]) {
                unset($this->XAxisData[$i]);
            } else {
                $filteredIndex++;
            }
        }

        $this->Data = array_combine($this->XAxisData, $FilteredData);
    }
}