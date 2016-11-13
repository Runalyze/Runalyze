<?php
/**
 * This file contains class::HRVdifferencesWithoutAnomalies
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model;
use Runalyze\View\Activity;

/**
 * Plot for: heart rate variability
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class HRVdifferencesWithoutAnomalies extends HRVwithoutAnomalies
{
	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context)
    {
		parent::__construct($context);

        $this->setDifferencesFromData();
	}

    /**
     * Set differences from data
     */
    protected function setDifferencesFromData()
    {
        $prev = false;

        foreach ($this->Data as $key => $value) {
            if ($prev === false) {
                unset($this->Data[$key]);
            } else {
                $this->Data[$key] = ($value - $prev);
            }

            $prev = $value;
        }
    }

    /**
     * Init options
     */
    protected function initOptions()
    {
        parent::initOptions();

        $this->Label = __('Difference');
    }
}
