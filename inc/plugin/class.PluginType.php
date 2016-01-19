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
	 * @var int Enum: Stat
	 */
	const STAT = 0;

	/**
	 * @var int Enum: Panel
	 */
	const PANEL = 1;

	/**
	 * @var int Enum: Tool
	 */
	const TOOL = 2;

	/**
	 * String
	 * @param int $Type enum
	 * @return string
	 */
	public static function string($Type) {
		switch ($Type) {
			case self::STAT:
				return 'stat';

			case self::PANEL:
				return 'panel';

			case self::TOOL:
			default:
				return 'tool';
		}
	}

	/**
	 * Readable string
	 * @param int $Type enum
	 * @return string
	 */
	public static function readableString($Type) {
		switch ($Type) {
			case self::STAT:
				return __('Statistics');

			case self::PANEL:
				return __('Panel');

			case self::TOOL:
			default:
				return __('Tool');
		}
	}
}