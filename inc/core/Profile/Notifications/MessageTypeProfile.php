<?php

namespace Runalyze\Profile\Notifications;

use Runalyze\Common\Enum\AbstractEnum;

class MessageTypeProfile extends AbstractEnum
{
	/** @var int */
	const TEMPLATE_BASED_MESSAGE = 1;

	/** @var int */
	const POSTER_GENERATED_MESSAGE = 2;

	/** @var int */
	const BACKUP_READY_MESSAGE = 3;
}
