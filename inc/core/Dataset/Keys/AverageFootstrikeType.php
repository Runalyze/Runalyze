<?php

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

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

	public function cssClass()
	{
		return 'small';
	}
}
