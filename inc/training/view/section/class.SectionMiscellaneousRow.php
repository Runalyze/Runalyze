<?php
/**
 * This file contains class::SectionMiscellaneousRow
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Row: Miscellaneous
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionMiscellaneousRow extends TrainingViewSectionRowOnlyText {
	/**
	 * Boxed values
	 * @var BoxedValue[]
	 */
	protected $BoxedValues = array();

	/**
	 * Set content
	 */
	protected function setContent() {
		$this->setContentLeft();
		$this->setContentRight();
	}

	/**
	 * Set content left
	 */
	protected function setContentLeft() {
		$this->setBoxedValues();
		$this->setBoxedValuesToLeftContent();
	}

	/**
	 * Set content right
	 */
	protected function setContentRight() {
		$this->ContentRight  = '<div class="panel-content">';
		$this->fillRightContent();
		$this->ContentRight .= '</div>';
	}

	/**
	 * Set boxed values to content
	 */
	protected function setBoxedValuesToLeftContent() {
		$ValuesString = '';
		foreach ($this->BoxedValues as &$Value)
			$ValuesString .= $Value->getCode();

		$this->ContentLeft = BoxedValue::getWrappedValues($ValuesString);
	}

	/**
	 * Set boxed values
	 */
	protected function setBoxedValues() {
		$this->addDateAndTime();
		$this->addWeather();
		$this->addEquipment();
		$this->addTrainingPartner();
	}

	/**
	 * Add date and time
	 */
	protected function addDateAndTime() {
		$Date = new BoxedValue($this->Training->DataView()->getDate(false), '', __('Date'));

		if (strlen($this->Training->DataView()->getDaytimeString()) > 0) {
			$Daytime = new BoxedValue($this->Training->DataView()->getDaytimeString(), '', __('Time of day'));
			$Daytime->defineAsFloatingBlock('w50');
			$Date->defineAsFloatingBlock('w50');

			$this->BoxedValues[] = $Date;
			$this->BoxedValues[] = $Daytime;
		} else {
			$Date->defineAsFloatingBlock('w100');
			$this->BoxedValues[] = $Date;
		}
	}

	/**
	 * Add weather
	 */
	protected function addWeather() {
		if (!$this->Training->Weather()->isEmpty()) {
			$Weather = new BoxedValue($this->Training->Weather()->name(), '', __('Weather condition'), $this->Training->Weather()->icon());
			$Weather->defineAsFloatingBlock('w50');

			$Temp = new BoxedValue(Helper::Unknown($this->Training->Weather()->temperature()), '&deg;C', __('Temperature'));
			$Temp->defineAsFloatingBlock('w50');

			$this->BoxedValues[] = $Weather;
			$this->BoxedValues[] = $Temp;
		}

		if (!$this->Training->Clothes()->areEmpty()) {
			$Clothes = new BoxedValue($this->Training->Clothes()->asLinks(), '', __('Clothes'));
			$Clothes->defineAsFloatingBlock('w100 flexible-height');

			$this->BoxedValues[] = $Clothes;
		}
	}

	/**
	 * Add equipment
	 */
	protected function addEquipment() {
		if (!$this->Training->Shoe()->isDefaultId()) {
			$RunningShoe = new BoxedValue($this->Training->Shoe()->getSearchLink(), '', __('Running shoe'));
			$RunningShoe->defineAsFloatingBlock('w100 flexible-height');

			$this->BoxedValues[] = $RunningShoe;
		}
	}

	/**
	 * Add training partner
	 */
	protected function addTrainingPartner() {
		if (strlen($this->Training->getPartner()) > 0) {
			$TrainingPartner = new BoxedValue($this->Training->DataView()->getPartnerAsLinks(), '', __('Training partner'));
			$TrainingPartner->defineAsFloatingBlock('w100 flexible-height');

			$this->BoxedValues[] = $TrainingPartner;
		}
	}

	/**
	 * Fill right content
	 */
	protected function fillRightContent() {
		$this->addNotes();
		$this->addCreationAndModificationTime();
	}

	/**
	 * Add notes
	 */
	protected function addNotes() {
		if (strlen($this->Training->getNotes()) > 0) {
			$Notes = '<strong>'.__('Notes').':</strong><br>'.$this->Training->getNotes();
			$this->ContentRight .= HTML::fileBlock($Notes);
		}
	}

	/**
	 * Add created/edited
	 */
	protected function addCreationAndModificationTime() {
		if ($this->Training->getCreatedTimestamp() > 0) {
			$CreationTime = sprintf( __('You created this training on <strong>%s</strong> at <strong>%s</strong>.'),
				date('d.m.Y', $this->Training->getCreatedTimestamp()),
				date('H:i', $this->Training->getCreatedTimestamp())
			);

			if ($this->Training->getEditedTimestamp() > 0) {
				$ModificationTime = '<br>'.sprintf( __('Last modification on <strong>%s</strong> at <strong>%s</strong>.'),
					date('d.m.Y', $this->Training->getEditedTimestamp()),
					date('H:i', $this->Training->getEditedTimestamp())
				);
			} else {
				$ModificationTime = '';
			}

			$this->ContentRight .= HTML::fileBlock($CreationTime.$ModificationTime);
		}
	}
}