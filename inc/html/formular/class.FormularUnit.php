<?php
/**
 * This file contains class::FormularUnit
 * @package Runalyze\HTML\Formular
 */
/**
 * Class holds only enum for units
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularUnit {
	/**
	 * Unit: percent
	 * @var string
	 */
	static public $PERCENT = 'unitPercent';

	/**
	 * Unit: bpm
	 * @var string
	 */
	static public $BPM = 'unitBpm';

	/**
	 * Unit: spm
	 * @var string
	 */
	static public $SPM = 'unitSpm';

	/**
	 * Unit: kg
	 * @var string
	 */
	static public $KG = 'unitKg';

	/**
	 * Unit: km
	 * @var string
	 */
	static public $KM = 'unitKm';

	/**
	 * Unit: m
	 * @var string
	 */
	static public $M = 'unitM';

	/**
	 * Unit: user
	 * @var string
	 */
	static public $USER = 'unitUser';

	/**
	 * Unit: password
	 * @var string
	 */
	static public $PASS = 'unitPass';

	/**
	 * Unit: mail-adress
	 * @var string
	 */
	static public $MAIL = 'unitMail';

	/**
	 * Unit: temperature in degree celsius
	 * @var string
	 */
	static public $CELSIUS = 'unitCelsius';

	/**
	 * Unit: elevation
	 * @var string
	 */
	static public $ELEVATION = 'unitElevation';

	/**
	 * Unit: kcal
	 * @var string
	 */
	static public $KCAL = 'unitKcal';

	/**
	 * Unit: pace in km/h
	 * @var string
	 */
	static public $KMH = 'unitKmh';

	/**
	 * Unit: pace in min/km
	 * @var string
	 */
	static public $PACE = 'unitPace';

	/**
	 * Unit: pace in min/km
	 * @var string
	 */
	static public $POWER = 'unitPower';
}