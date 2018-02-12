<?php
/**
 * This file contains class::ValueBox
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\Activity;

/**
 * Boxed value for classes implementing Runalyze\Activity\ValueInterface
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class ValueBox extends AbstractBox
{
	public function __construct(Activity\ValueInterface $Value, $icon = '', $glossary = '')
	{
		parent::__construct(
			\Helper::Unknown($Value->string(false), '-'),
			$Value->unit(),
			$Value->label(),
            $icon,
            $glossary
		);
	}
}
