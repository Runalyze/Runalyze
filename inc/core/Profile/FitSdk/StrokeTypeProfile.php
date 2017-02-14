<?php

namespace Runalyze\Profile\Athlete;

use Runalyze\Util\AbstractEnum;

class StrokeTypeProfile extends AbstractEnum
{
	/** @var int */
	const FREESTYLE = 0;

	/** @var int */
	const BACK = 1;

	/** @var int */
    const BREAST = 2;

    /** @var int */
    const BUTTERFLY = 3;

    /** @var int */
    const DRILL = 4;

    /** @var int */
    const MIXED = 5;
}
