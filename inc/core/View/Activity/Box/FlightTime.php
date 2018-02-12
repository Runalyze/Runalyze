<?php

namespace Runalyze\View\Activity\Box;

use Runalyze\View\Activity\Context;

class FlightTime extends AbstractBox
{
	public function __construct(Context $context)
	{
		parent::__construct(
			\Helper::Unknown(round($context->activity()->flightTime()), '-'),
			'ms',
			__('Flight time')
		);
	}
}
