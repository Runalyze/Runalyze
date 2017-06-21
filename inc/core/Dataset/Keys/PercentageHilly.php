<?php

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

class PercentageHilly extends AbstractKey
{
	public function id()
	{
		return \Runalyze\Dataset\Keys::PERCENTAGE_HILLY;
	}

	public function column()
	{
		return 'percentage_hilly';
	}

	public function label()
	{
		return __('Percentage hilly');
	}

	public function shortLabel()
	{
		return __('Hilly');
	}

	public function description()
	{
		return __(
			'' // TODO
		);
	}

	public function stringFor(Context $context)
	{
		return $context->dataview()->percentageHilly();
	}
}
