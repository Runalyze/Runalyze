<?php
/**
 * This file contains class::ExporterType
 * @package Runalyze\Export
 */
/**
 * Enum of export types
 *
 * @author Hannes Christiansen
 * @package Runalyze\Export
 */
abstract class ExporterType {
	/**
	 * @var Enum: File
	 */
	const File = 0;

	/**
	 * @var Enum: Code
	 */
	const Code = 1;

	/**
	 * @var Enum: Social
	 */
	const Social = 2;

	/**
	 * Heading
	 * @param enum $Type
	 * @return string
	 */
	static public function heading($Type) {
		switch ($Type) {
			case self::File:
				return __('Export as file');

			case self::Social:
				return __('Export to social media');

			case self::Code:
			default:
				return __('Export as code');
		}
	}
}