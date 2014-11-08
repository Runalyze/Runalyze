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
class SectionMiscellaneousRow extends TrainingViewSectionRowTabbedPlot {
	/**
	 * Right content: notes
	 * @var string
	 */
	protected $NotesContent = '';

	/**
	 * Set content
	 */
	protected function setContent() {
		//$this->withShadow = true;

		$this->setBoxedValues();
	}

	/**
	 * Set content right
	 */
	protected function setRightContent() {
		$this->fillNotesContent();

		$this->addRightContent('notes', __('Additional notes'), $this->NotesContent);

		if ($this->Training->hasArrayCadence())
			$this->addRightContent('cadence', __('Cadence plot'), new TrainingPlotCadence($this->Training));

		if ($this->Training->hasArrayPower())
			$this->addRightContent('power', __('Power plot'), new TrainingPlotPower($this->Training));

		if ($this->Training->hasArrayTemperature())
			$this->addRightContent('temperature', __('Temperature plot'), new TrainingPlotTemperature($this->Training));
	}

	/**
	 * Set boxed values
	 */
	protected function setBoxedValues() {
		$this->addDateAndTime();
		$this->addCadenceAndPower();
		$this->addRunningDynamics();
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
	 * Add cadence and power
	 */
	protected function addCadenceAndPower() {
		if ($this->Training->getCadence() > 0 || $this->Training->getPower() > 0) {
			$Cadence = new BoxedValue(Helper::Unknown($this->Training->Cadence()->value(), '-'), $this->Training->Cadence()->unitAsString(), $this->Training->Cadence()->label());
			$Cadence->defineAsFloatingBlock('w50');

			$Power = new BoxedValue(Helper::Unknown($this->Training->getPower(), '-'), 'W', __('Power'));
			$Power->defineAsFloatingBlock('w50');

			$this->BoxedValues[] = $Cadence;
			$this->BoxedValues[] = $Power;
		}
	}

	/**
	 * Add running dynamics
	 */
	protected function addRunningDynamics() {
		if ($this->Training->getGroundContactTime() > 0 || $this->Training->getVerticalOscillation() > 0) {
			$Contact = new BoxedValue(Helper::Unknown($this->Training->getGroundContactTime(), '-'), 'ms', __('Ground contact'));
			$Contact->defineAsFloatingBlock('w50');

			$Oscillation = new BoxedValue(Helper::Unknown(round($this->Training->getVerticalOscillation()/10,1), '-'), 'cm', __('Vertical oscillation'));
			$Oscillation->defineAsFloatingBlock('w50');

			$this->BoxedValues[] = $Contact;
			$this->BoxedValues[] = $Oscillation;
		}
	}

	/**
	 * Add weather
	 */
	protected function addWeather() {
		if (!$this->Training->Weather()->isEmpty()) {
			$Weather = new BoxedValue($this->Training->Weather()->condition()->string(), '', __('Weather condition'), $this->Training->Weather()->condition()->icon()->code());
			$Weather->defineAsFloatingBlock('w50');

			$Temp = new BoxedValue(Helper::Unknown($this->Training->Weather()->temperature()->value()), $this->Training->Weather()->temperature()->unit(), __('Temperature'));
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
	 * Fill notes content
	 */
	protected function fillNotesContent() {
		$this->NotesContent = '<div class="panel-content">';

		$this->addNotes();
		$this->addCreationAndModificationTime();

		$this->NotesContent .= '</div>';
	}

	/**
	 * Add notes
	 */
	protected function addNotes() {
		if (strlen($this->Training->getNotes()) > 0) {
			$Notes = '<strong>'.__('Notes').':</strong><br>'.$this->Training->getNotes();
			$this->NotesContent .= HTML::fileBlock($Notes);
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

			$this->NotesContent .= HTML::fileBlock($CreationTime.$ModificationTime);
		}
	}
}