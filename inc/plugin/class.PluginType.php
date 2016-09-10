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
	 * String
	 * @param int $Type enum
	 * @return string
	 */
	public static function string($Type) {
		switch ($Type) {
			case self::PANEL:
				return 'panel';

			case self::STAT:
			default:
			return 'stat';
		}
	}

	/**
	 * Readable string
	 * @param int $Type enum
	 * @return string
	 */
	public static function readableString($Type) {
		switch ($Type) {
			case self::PANEL:
				return __('Panel');

			case self::STAT:
			default:
			return __('Statistics');
		}
	}
}
