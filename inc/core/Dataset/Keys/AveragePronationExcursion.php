<?php

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

class AveragePronationExcursion extends AbstractKey
{
	public function id()
	{
		return \Runalyze\Dataset\Keys::AVG_PRONATION_EXCURSION;
	}

	public function column()
	{
        return ['avg_pronation_excursion_left', 'avg_pronation_excursion_right'];
	}

	public function label()
	{
		return __('Pronation excursion');
	}

    public function shortLabel()
    {
        return __('Pronation');
    }

	public function stringFor(Context $context)
	{
		return $context->dataview()->pronationExcursion();
	}

	public function cssClass()
	{
		return 'small';
	}
}
