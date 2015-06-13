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
	static public $PERCENT = '&#37;';

	/**
	 * Unit: bpm
	 * @var string
	 */
	static public $BPM = 'bpm';

	/**
	 * Unit: spm
	 * @var string
	 */
	static public $SPM = 'spm';

	/**
	 * Unit: rpm
	 * @var string
	 */
	static public $RPM = 'rpm';

	/**
	 * Unit: kg
	 * @var string
	 */
	static public $KG = 'kg';
	
	/**
	 * Unit: weight in gram
	 * @var string
	 */
	static public $G = 'g';

	/**
	 * Unit: km
	 * @var string
	 */
	static public $KM = 'km';

	/**
	 * Unit: m
	 * @var string
	 */
	static public $M = 'm';

	/**
	 * Unit: cm
	 * @var string
	 */
	static public $CM = 'cm';

	/**
	 * Unit: ms
	 * @var string
	 */
	static public $MS = 'ms';

	/**
	 * Unit: user
	 * @var string
	 */
	static public $USER = '&nbsp;<i class="fa fa-fw fa-user"></i>'; // Icon::$USER

	/**
	 * Unit: password
	 * @var string
	 */
	static public $PASS = '&nbsp;<i class="fa fa-fw fa-lock"></i>'; // Icon::$PASSWORD

	/**
	 * Unit: mail-adress
	 * @var string
	 */
	static public $MAIL = '&nbsp;<i class="fa fa-fw fa-envelope"></i>'; // Icon::$MAIL

	/**
	 * Unit: temperature in degree celsius
	 * @var string
	 */
	static public $CELSIUS = '&deg;C';

	/**
	 * Unit: elevation
	 * @var string
	 */
	static public $ELEVATION = 'hm';

	/**
	 * Unit: kcal
	 * @var string
	 */
	static public $KCAL = 'kcal';

	/**
	 * Unit: pace in km/h
	 * @var string
	 */
	static public $KMH = 'km/h';

	/**
	 * Unit: pace in min/km
	 * @var string
	 */
	static public $PACE = '/km';

	/**
	 * Unit: power
	 * @var string
	 */
	static public $POWER = 'W';
        
        /**
         * Unit: hour
         * @var string
         */
        static public $HOUR = 'h';
}