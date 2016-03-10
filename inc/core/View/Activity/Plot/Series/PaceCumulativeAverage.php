<?php
/**
 * This file contains class::PaceCumulativeAverage
 * @package Runalyze\View\Activity\Plot\Series
 */

namespace Runalyze\View\Activity\Plot\Series;

use Runalyze\Calculation\Math\MovingAverage;
use Runalyze\Model\Trackdata\Entity as Trackdata;
use Runalyze\View\Activity;


/**
 * Plot for: Pace (with cumulative moving average)
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot\Series
 */
class PaceCumulativeAverage extends Pace
{
	/**
	 * @var string
	 */
	const COLOR = 'rgb(200,200,255)';

	/** @var bool */
	protected $adjustAxis = false;

	/**
	 * Create series
	 * @var \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Activity\Context $context)
	{
		$internalContext = clone $context;

		$this->adjustTrackdata($internalContext);

		parent::__construct($internalContext);
	}

	/**
	 * Init options
	 */
	protected function initOptions()
	{
		parent::initOptions();

		$this->Label = __('Moving average');
		$this->Color = self::COLOR;
	}

	/**
	 * Calculate cumulative moving average
	 * @var \Runalyze\View\Activity\Context $context
	 */
	protected function adjustTrackdata(Activity\Context $context)
	{
		$MovingAverage = new MovingAverage\Cumulative($context->trackdata()->pace(), $context->trackdata()->distance());
		$MovingAverage->calculate();

		$context->trackdata()->set(Trackdata::PACE, $MovingAverage->movingAverage());
	}
}
