<?php
/**
 * This file contains class::WeatherTranslator
 * @package Runalyze\Data\Weather
 */
/**
 * Weather translator
 *
 * @author Hannes Christiansen
 * @package Runalyze\Data\Weather
 */
class WeatherTranslator {
	/**
	 * Try to get weather id for string
	 * @param string $String
	 */
	static public function getIDfor($String) {
		switch ($String) {
			case 'Mostly Sunny':
			case 'Sunny':
			case 'Clear':
				return Weather::conditionToId('sunny');

			case 'Partly Sunny':
			case 'Partly Cloudy':
				return Weather::conditionToId('fair');

			case 'Overcast':
			case 'Mostly Cloudy':
			case 'Cloudy':
			case 'Fog':
				return Weather::conditionToId('cloudy');

			case 'Mist':
			case 'Storm':
			case 'Chance of rain':
			case 'Scattered showers':
			case 'Scattered thunderstorms':
			case 'Windy':
			case 'Drizzle':
				return Weather::conditionToId('changeable');

			case 'Rain':
			case 'Light rain':
			case 'Showers':
			case 'Rain and snow':
			case 'Freezing drizzle':
			case 'Chance of tstorm':
			case 'Thunderstorm':
			case 'Sleet':
				return Weather::conditionToId('rainy');

			case 'Haze':
			case 'Flurries':
			case 'Icy':
			case 'Snow':
			case 'Light snow':
			case 'Chance of snow':
			case 'Scattered snow showers':
				return Weather::conditionToId('snow');

			default:
				return Weather::conditionToId('unknown');
		}
	}
}