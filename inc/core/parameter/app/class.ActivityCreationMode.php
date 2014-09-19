<?php
/**
 * This file contains class::ActivityCreationMode
 * @package Runalyze\Parameter\Application
 */
/**
 * Activity creation mode
 * @author Hannes Christiansen
 * @package Runalyze\Parameter\Application
 */
class ActivityCreationMode extends ParameterSelect {
	/**
	 * Upload form
	 * @var string
	 */
	const UPLOAD = 'upload';

	/**
	 * Garmin Communicator
	 * @var string
	 */
	const GARMIN = 'garmin';

	/**
	 * Standard form
	 * @var string
	 */
	const FORM = 'form';

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct(self::UPLOAD, array(
			'options'		=> array(
				self::UPLOAD	=> __('Upload form'),
				self::GARMIN	=> __('Garmin-Communicator'),
				self::FORM		=> __('Standard form')
			)
		));
	}

	/**
	 * Uses upload form
	 * @return bool
	 */
	public function usesUpload() {
		return ($this->value() == self::UPLOAD);
	}

	/**
	 * Uses Garmin-Communicator
	 * @return bool
	 */
	public function usesGarminCommunicator() {
		return ($this->value() == self::GARMIN);
	}

	/**
	 * Uses standard form
	 * @return bool
	 */
	public function usesForm() {
		return ($this->value() == self::FORM);
	}
}