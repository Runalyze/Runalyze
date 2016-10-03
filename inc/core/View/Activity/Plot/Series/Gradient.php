<?php

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Calculation;
use Runalyze\Model\Trackdata;
use Runalyze\View\Activity;

class Gradient extends ActivitySeries
{
	/** @var string */
	const COLOR = 'rgb(127,127,127)';

    /** @var int */
    protected $KernelWidth;

	/**
	 * @var \Runalyze\View\Activity\Context $context
     * @param int $kernelWidth
	 */
	public function __construct(Activity\Context $context, $kernelWidth = 20)
    {
        $this->KernelWidth = $kernelWidth;

		$this->initOptions();
		$this->initDataWithRoute($context);
	}

	/**
	 * @var \Runalyze\View\Activity\Context $context
	 */
	protected function initDataWithRoute(Activity\Context $context)
    {
		if (!$context->hasRoute() || !$context->route()->hasElevations() || !$context->hasTrackdata() || !$context->trackdata()->has(Trackdata\Entity::DISTANCE)) {
			$this->Data = array();
			return;
		}

        $gradient = new Calculation\Route\Gradient();
        $gradient->setDataFrom($context->route(), $context->trackdata());
        $gradient->setMovingAverageKernel(new Calculation\Math\MovingAverage\Kernel\Uniform($this->KernelWidth));
        $gradient->calculate();

		$collector = new DataCollectorForArray($context->trackdata(), $gradient->getSeries());
		$this->Data = $collector->data();
		$this->XAxis = $collector->xAxis();
	}

	/**
	 * Init options
	 */
	protected function initOptions()
    {
		$this->Label = __('Gradient');
		$this->Color = self::COLOR;

		$this->UnitString = '%';
		$this->UnitDecimals = 1;

		$this->TickSize = 1;
		$this->TickDecimals = 0;

		$this->ShowAverage = false;
		$this->ShowMaximum = false;
		$this->ShowMinimum = false;
	}
}
