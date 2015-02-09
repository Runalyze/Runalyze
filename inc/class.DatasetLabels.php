<?php
/**
 * This file contains class::DatasetLabels
 * @package Runalyze\DataBrowser\Dataset
 */
/**
 * Labels for dataset
 * @author Hannes Christiansen
 * @package Runalyze\DataBrowser\Dataset
 */
class DatasetLabels {
	/**
	 * Labels
	 * @var array
	 */
	private $Labels = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->Labels = array(
			'sportid'		=> __('Sport type'),
			'typeid'		=> __('Activity type'),
			'time'			=> __('Daytime'),
			'distance'		=> __('Distance'),
			's'				=> __('Duration'),
			'pace'			=> __('Pace'),
			'elevation'		=> __('Elevation'),
			'kcal'			=> __('Calories'),
			'pulse_avg'		=> __('avg. heart rate'),
			'pulse_max'		=> __('max. heart rate'),
			'trimp'			=> __('TRIMP'),
			'temperature'	=> __('Temperature'),
			'weatherid'		=> __('Weather'),
			'routeid'		=> __('Route'),
			'clothes'		=> __('Clothes'),
			'splits'		=> __('Splits'),
			'comment'		=> __('Comment'),
			'shoeid'		=> __('Shoe'),
			'vdot'			=> __('VDOT'),
			'partner'		=> __('Training partner'),
			'abc'			=> __('Running drills'),
			'cadence'		=> __('Cadence'),
			'groundcontact'	=> __('Ground contact time'),
			'vertical_oscillation'	=> __('Vertical oscillation'),
			'power'			=> __('Power'),
			'jd_intensity'	=> __('JD intensity')
		);
	}

	/**
	 * Get
	 * @param string $key
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function get($key) {
		if (isset($this->Labels[$key])) {
			return $this->Labels[$key];
		}

		throw new InvalidArgumentException('Invalid key "'.$key.'" for dataset label.');
	}
}