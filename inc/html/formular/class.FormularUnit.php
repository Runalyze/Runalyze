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
	public static $PERCENT = '&#37;';

	/**
	 * Unit: bpm
	 * @var string
	 */
	public static $BPM = 'bpm';

	/**
	 * Unit: spm
	 * @var string
	 */
	public static $SPM = 'spm';

	/**
	 * Unit: rpm
	 * @var string
	 */
	public static $RPM = 'rpm';

	/**
	 * Unit: km
	 * @var string
	 */
	public static $KM = 'km';

	/**
	 * Unit: m
	 * @var string
	 */
	public static $M = 'm';
	
	/**
	 * Unit: miles
	 * @var string
	 */
	public static $MILES = 'miles';

	/**
	 * Unit: yards
	 * @var string
	 */
	public static $Y = 'y';

	/**
	 * Unit: cm
	 * @var string
	 */
	public static $CM = 'cm';

	/**
	 * Unit: ms
	 * @var string
	 */
	public static $MS = 'ms';

	/**
	 * Unit: user
	 * @var string
	 */
	public static $USER = '&nbsp;<i class="fa fa-fw fa-user"></i>'; // Icon::$USER

	/**
	 * Unit: password
	 * @var string
	 */
	public static $PASS = '&nbsp;<i class="fa fa-fw fa-lock"></i>'; // Icon::$PASSWORD

	/**
	 * Unit: mail-adress
	 * @var string
	 */
	public static $MAIL = '&nbsp;<i class="fa fa-fw fa-envelope"></i>'; // Icon::$MAIL

	/**
	 * Unit: temperature in degree celsius
	 * @var string
	 */
	public static $CELSIUS = '&deg;C';

	/**
	 * Unit: elevation
	 * @var string
	 */
	public static $ELEVATION = 'hm';

	/**
	 * Unit: kcal
	 * @var string
	 */
	public static $KCAL = 'kcal';
	
	/**
	 * Unit: degree
	 * @var string
	 */
	public static $DEGREE = '&deg;';

	/**
	 * Unit: pace in km/h
	 * @var string
	 */
	public static $KMH = 'km/h';

	/**
	 * Unit: pace in min/km
	 * @var string
	 */
	public static $PACE = '/km';

	/**
	 * Unit: power
	 * @var string
	 */
	public static $POWER = 'W';
        
        /**
         * Unit: hour
         * @var string
         */
        public static $HOUR = 'h';
	
        /**
         * Unit: hpa
         * @var string
         */
        public static $HPA = 'hpa';
}