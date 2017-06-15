<?php

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

class PercentageFlat extends AbstractKey
{
	public function id()
	{
		return \Runalyze\Dataset\Keys::PERCENTAGE_FLAT;
	}

	public function column()
	{
		return 'percentage_flat';
	}

	public function label()
	{
		return __('Percentage flat');
	}

	public function shortLabel()
	{
		return __('Flat');
	}

	public function description()
	{
		return __(
			'' // TODO
		);
	}

	public function stringFor(Context $context)
	{
		return $context->dataview()->percentageFlat();
	}
}
