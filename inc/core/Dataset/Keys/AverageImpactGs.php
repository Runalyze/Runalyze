<?php

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

class AverageImpactGs extends AbstractKey
{
	public function id()
	{
		return \Runalyze\Dataset\Keys::AVG_IMPACT_GS;
	}

	public function column()
	{
        return ['avg_impact_gs_left', 'avg_impact_gs_right'];
	}

	public function label()
	{
		return __('Impact Gs');
	}

	public function stringFor(Context $context)
	{
		return $context->dataview()->impactGs();
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
