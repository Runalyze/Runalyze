<?php

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

class FlightRatio extends AbstractKey
{
	public function id()
	{
		return \Runalyze\Dataset\Keys::FLIGHT_RATIO;
	}

	public function column()
	{
		return ['cadence', 'groundcontact'];
	}

	public function label()
	{
		return __('Flight ratio');
	}

	public function stringFor(Context $context)
	{
	    return $context->dataview()->flightRatio();
	}

	public function summaryMode()
	{
		return SummaryMode::AVG;
	}

	public function cssClass()
	{
		return 'small';
	}
}
