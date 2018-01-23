<?php

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;
use Runalyze\View\Activity\Plot\Series;

class ImpactGs extends ActivityPlot
{
	protected function setKey()
    {
		$this->key   = 'impact_gs';
	}

	protected function initData(Activity\Context $context)
    {
        $this->addSeries(new Series\ImpactGs($context, true), 1);
        $this->addSeries(new Series\ImpactGs($context, false), 1);
    }
}
