<?php

namespace Runalyze\View\Activity\Box;

use Runalyze\View\Activity\Context;

class FlightRatio extends AbstractBox
{
	public function __construct(Context $context)
	{
	    $flightRatio = $context->activity()->flightRatio();

		parent::__construct(
			null !== $flightRatio ? number_format(100 * $flightRatio, 1) : '-',
			'&#37;',
			__('Flight ratio')
		);
	}
}
