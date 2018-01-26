<?php

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;
use Runalyze\View\Activity\Plot\Series;

class FootstrikeType extends ActivityPlot
{
	protected function setKey()
    {
		$this->key   = 'footstrike_type';
	}

	protected function initData(Activity\Context $context)
    {
        $this->addSeries(new Series\FootstrikeType($context, true), 1);
        $this->addSeries(new Series\FootstrikeType($context, false), 1);
    }
}
