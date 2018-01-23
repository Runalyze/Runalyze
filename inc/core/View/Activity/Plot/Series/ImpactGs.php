<?php

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\View\Activity;

class ImpactGs extends ActivitySeries
{
    /**
     * @param Activity\Context $context
     * @param bool $isLeft
     */
	public function __construct(Activity\Context $context, $isLeft = true)
    {
		$this->initOptions($isLeft);
		$this->initData($context->trackdata(), $isLeft ? Trackdata::IMPACT_GS_LEFT : Trackdata::IMPACT_GS_RIGHT);
		$this->manipulateData();
	}

	protected function manipulateData()
    {
		$this->Data = array_filter($this->Data);
	}

	protected function initOptions($isLeft)
    {
		$this->Label = __('Impact Gs').' ('.($isLeft ? __('left') : __('right')).')';
		$this->Color = $isLeft ? 'rgb(98,182,0)' : 'rgb(47,131,0)';

		$this->UnitString = 'G';
		$this->UnitDecimals = 1;

		$this->TickSize = 0.2;
		$this->TickDecimals = 1;
	}
}
