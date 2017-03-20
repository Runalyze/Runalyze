<?php

namespace Runalyze\Profile\Notifications;

use Runalyze\Util\AbstractEnum;

class MessageProfile extends AbstractEnum
{
	/** @var int */
	const GENERAL_MESSAGE = 1;

	/** @var int */
	const POSTER_GENERATED_MESSAGE = 2;
}
