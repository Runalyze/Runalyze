<?php
/**
 * This file contains class::FormularInputUTCDayAndDaytime
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for a double field for day and daytime
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
use Runalyze\Util\UTCTime;
class FormularInputUTCDayAndDaytime extends FormularInputDayAndDaytime {
	/**
	 * Internal field for time
	 * @var FormularInput
	 */
	protected $FieldTime = null;

	/**
	 * Internal field for day
	 * @var FormularInput
	 */
	protected $FieldDay = null;

	/**
	 * Internal field for daytime
	 * @var FormularInput
	 */
	protected $FieldDaytime = null;

	/**
	 * Internal layout to forward o internal fields
	 * @var string
	 */
	protected $internalLayout = '';

	/**
	 * Internal layout classes to forward to internal fields
	 * @var array
	 */
	protected $internalLayoutClasses = array();

	/**
	 * Get field name for day
	 * @return string
	 */
	private function getFieldDayName() {
		return $this->name.'_day';
	}

	/**
	 * Get field name for daytime
	 * @return string
	 */
	private function getFieldDaytimeName() {
		return $this->name.'_daytime';
	}
	
	/**
	 * Validate value
	 * @return boolean
	 */
	public function validate() {
		$this->setFields();

		if (!isset($_POST[$this->getFieldDayName()]) || !isset($_POST[$this->getFieldDaytimeName()]))
			return;

		$date = $_POST[$this->getFieldDayName()];
		$time = $_POST[$this->getFieldDaytimeName()];

		$this->FieldDay->validate();
		$this->FieldDaytime->validate();

		$_POST[$this->name] = (new UTCTime($date.' '.$time))->getTimestamp();
	}

}