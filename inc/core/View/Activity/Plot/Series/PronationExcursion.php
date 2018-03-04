<?php

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\View\Activity;

class PronationExcursion extends ActivitySeries
{
    /**
     * @param Activity\Context $context
     * @param bool $isLeft
     */
	public function __construct(Activity\Context $context, $isLeft = true)
    {
		$this->initOptions($isLeft);
		$this->initData($context->trackdata(), $isLeft ? Trackdata::PRONATION_EXCURSION_LEFT : Trackdata::PRONATION_EXCURSION_RIGHT);
		$this->manipulateData();
	}

	protected function manipulateData()
    {
		$this->Data = array_filter($this->Data);
	}

	protected function initOptions($isLeft)
    {
		$this->Label = __('Pronation excursion').' ('.($isLeft ? __('left') : __('right')).')';
		$this->Color = $isLeft ? 'rgb(234,46,195)' : 'rgb(183,0,144)';

		$this->UnitString = '°';
		$this->UnitDecimals = 1;

		$this->TickSize = 1;
		$this->TickDecimals = 0;
	}
}
