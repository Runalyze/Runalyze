<?php
/**
 * This file contains class::PluginType
 * @package Runalyze\Plugin
 */
/**
 * Types for plugins
 * @author Hannes Christiansen
 * @package Runalyze\Plugin
 */
abstract class PluginType {
	/**
	 * @var Enum: Stat
	 */
	const Stat = 0;

	/**
	 * @var Enum: Panel
	 */
	const Panel = 1;

	/**
	 * @var Enum: Tool
	 */
	const Tool = 2;

	/**
	 * String
	 * @param enum $Type
	 * @return string
	 */
	static public function string($Type) {
		switch ($Type) {
			case self::Stat:
				return 'stat';

			case self::Panel:
				return 'panel';

			case self::Tool:
			default:
				return 'tool';
		}
	}

	/**
	 * Readable string
	 * @param enum $Type
	 * @return string
	 */
	static public function readableString($Type) {
		switch ($Type) {
			case self::Stat:
				return __('Statistic');

			case self::Panel:
				return __('Panel');

			case self::Tool:
			default:
				return __('Tool');
		}
	}
}