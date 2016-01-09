<?php
/**
 * This file contains class::Translator
 * @package Runalyze\Data\Weather
 */

namespace Runalyze\Data\Weather;

/**
 * Weather translator
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Weather
 */
class Translator {
	/**
	 * Try to get weather id for string
	 * @param string $string
	 * @return int
	 */
	public static function IDfor($string) {
		switch ($string) {
			case 'Mostly Sunny':
			case 'Sunny':
			case 'Clear':
				return Condition::SUNNY;

			case 'Partly Sunny':
			case 'Partly Cloudy':
				return Condition::FAIR;

			case 'Overcast':
			case 'Mostly Cloudy':
			case 'Cloudy':
			case 'Fog':
				return Condition::CLOUDY;

			case 'Mist':
			case 'Storm':
			case 'Chance of rain':
			case 'Scattered showers':
			case 'Scattered thunderstorms':
			case 'Windy':
			case 'Drizzle':
				return Condition::CHANGEABLE;

			case 'Rain':
			case 'Light rain':
			case 'Showers':
			case 'Rain and snow':
			case 'Freezing drizzle':
			case 'Chance of tstorm':
			case 'Thunderstorm':
			case 'Sleet':
				return Condition::RAINY;

			case 'Haze':
			case 'Flurries':
			case 'Icy':
			case 'Snow':
			case 'Light snow':
			case 'Chance of snow':
			case 'Scattered snow showers':
				return Condition::SNOWING;

			default:
				return Condition::UNKNOWN;
		}
	}
}