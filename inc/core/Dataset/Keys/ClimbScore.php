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
        if ((!$context->activity()->isPublic() && \Request::isOnSharedPage()) OR $context->activity()->id() <= 0) {
            return $context->dataview()->climbScore();
        } else {
            return '<a class="window" href="activity/'.$context->activity()->id().'/climb-score">'.$context->dataview()->climbScore()."</a>";
        }
    }

}
