<?php

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\View\Activity;

class FootstrikeType extends ActivitySeries
{
    /**
     * @param Activity\Context $context
     * @param bool $isLeft
     */
	public function __construct(Activity\Context $context, $isLeft = true)
    {
		$this->initOptions($isLeft);
		$this->initData($context->trackdata(), $isLeft ? Trackdata::FOOTSTRIKE_TYPE_LEFT : Trackdata::FOOTSTRIKE_TYPE_RIGHT);
		$this->manipulateData();
	}

	protected function manipulateData()
    {
		$this->Data = array_filter($this->Data);
	}

	protected function initOptions($isLeft)
    {
		$this->Label = __('Footstrike').' ('.($isLeft ? __('left') : __('right')).')';
		$this->Color = $isLeft ? 'rgb(124,181,236)' : 'rgb(73,130,185)';

		$this->UnitString = '';
		$this->UnitDecimals = 0;

		$this->TickSize = 1;
		$this->TickDecimals = 0;
	}

	public function addTo(\Plot $Plot, $yAxis, $addAnnotations = true)
	{
		parent::addTo($Plot, $yAxis, $addAnnotations);

		if (!empty($this->Data)) {
			$Plot->setYLimits($yAxis, 0, 16.5);
			$Plot->setYAxisLabels($yAxis, [6, 12]);

			$Plot->addMarkingArea('y'.$yAxis, 6, 12, 'rgba(124,181,236,0.05)');
			$Plot->addMarkingArea('y'.$yAxis, 12, 16.5, 'rgba(124,181,236,0.15)');
		}
	}
}
