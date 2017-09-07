<?php

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

class FlightTime extends AbstractKey
{
	public function id()
	{
		return \Runalyze\Dataset\Keys::FLIGHT_TIME;
	}

	public function column()
	{
		return ['cadence', 'groundcontact'];
	}

	public function label()
	{
		return __('Flight time');
	}

	public function stringFor(Context $context)
	{
		return $context->dataview()->flightTime();
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
