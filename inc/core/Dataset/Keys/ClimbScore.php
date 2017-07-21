<?php

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

class ClimbScore extends AbstractKey
{
	public function id()
	{
		return \Runalyze\Dataset\Keys::CLIMB_SCORE;
	}

	public function column()
	{
		return 'climb_score';
	}

	public function label()
	{
		return __('Climb Score');
	}

	public function shortLabel()
	{
		return __('CS');
	}

	public function description()
	{
		return __(
			'' // TODO
		);
	}

	public function stringFor(Context $context)
	{
        if (!\Request::isOnSharedPage()) {
            return '<a class="window" href="activity/'.$context->activity()->id().'/climb-score">'.$context->dataview()->climbScore()."</a>";
        } else {
            return $context->dataview()->climbScore();
        }
    }

}
