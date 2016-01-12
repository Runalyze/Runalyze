<?php
/**
 * This file contains class::Tags
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

/**
 * Dataset key: Tags
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class Tags extends AbstractKey
{
	/** @var string */
	const CONCAT_TAGIDS_KEY = 'concat_tags';

	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::TAGS;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return '';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Tags');
	}

	/**
	 * @return bool
	 */
	public function requiresJoin()
	{
		return true;
	}

	/**
	 * @return array array('column' => '...', 'join' => 'LEFT JOIN ...', 'field' => '`x`.`y`)
	 */
	public function joinDefinition()
	{
		return array(
			'column' => self::CONCAT_TAGIDS_KEY,
			'join' => 'LEFT JOIN `'.PREFIX.'activity_tag` AS `atag` ON `t`.`id` = `atag`.activityid',
			'field' => 'GROUP_CONCAT(`atag`.`tagid` SEPARATOR \',\') AS `'.self::CONCAT_TAGIDS_KEY.'`'
		);
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		$string = '';

		if ($context->hasData(self::CONCAT_TAGIDS_KEY) && $context->data(self::CONCAT_TAGIDS_KEY) != '') {
			$ids = explode(',', $context->data(self::CONCAT_TAGIDS_KEY));
			$Factory = new \Runalyze\Model\Factory(\SessionAccountHandler::getId());

			foreach (array_unique($ids) as $id) {
				$string .= '#'.$Factory->tag($id)->tag().' ';
			}
		}

		return $string;
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function cssClass()
	{
		return 'small';
	}
}