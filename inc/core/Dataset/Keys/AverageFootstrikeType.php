<?php

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

class AverageFootstrikeType extends AbstractKey
{
	public function id()
	{
		return \Runalyze\Dataset\Keys::AVG_FOOTSTRIKE_TYPE;
	}

	public function column()
	{
        return ['avg_footstrike_type_left', 'avg_footstrike_type_right'];
	}

	public function label()
	{
		return __('Footstrike type');
	}

	public function stringFor(Context $context)
	{
		return $context->dataview()->footstrikeType();
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
