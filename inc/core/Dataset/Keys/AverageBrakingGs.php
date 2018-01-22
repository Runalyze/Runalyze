<?php

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

class AverageBrakingGs extends AbstractKey
{
	public function id()
	{
		return \Runalyze\Dataset\Keys::AVG_BRAKING_GS;
	}

	public function column()
	{
        return ['avg_braking_gs_left', 'avg_braking_gs_right'];
	}

	public function label()
	{
		return __('Braking Gs');
	}

	public function stringFor(Context $context)
	{
		return $context->dataview()->brakingGs();
	}

	public function cssClass()
	{
		return 'small';
	}
}
