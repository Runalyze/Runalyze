<?php

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;
use Runalyze\View\Activity\Plot\Series;

class PronationExcursion extends ActivityPlot
{
	protected function setKey()
    {
		$this->key   = 'pronation_excursion';
	}

	protected function initData(Activity\Context $context)
    {
        $this->addSeries(new Series\PronationExcursion($context, true), 1);
        $this->addSeries(new Series\PronationExcursion($context, false), 1);
    }
}
