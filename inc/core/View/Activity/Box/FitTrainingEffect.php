<?php

namespace Runalyze\View\Activity\Box;

use Runalyze\Activity;
use Runalyze\View\Activity\Context;

class FitTrainingEffect extends ValueBox
{
	public function __construct(Context $Context)
	{
		parent::__construct(
			new Activity\TrainingEffect($Context->activity()->fitTrainingEffect()),
            '',
            'training-effect'
		);
	}
}
