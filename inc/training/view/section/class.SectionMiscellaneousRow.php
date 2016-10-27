<?php
/**
 * This file contains class::SectionMiscellaneousRow
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Activity\Duration;
use Runalyze\Activity\Temperature;
use Runalyze\AgeGrade\Lookup;
use Runalyze\AgeGrade\Table\FemaleTable;
use Runalyze\AgeGrade\Table\MaleTable;
use Runalyze\Configuration;
use Runalyze\Model\Trackdata;
use Runalyze\Profile\Athlete\Gender;
use Runalyze\View;
use Runalyze\View\Activity;
use Runalyze\View\Activity\Box;

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
	 * @var bool
	 */
	protected $showCadence = true;

	/**
	 * Constructor
	 */
	public function __construct(Activity\Context $Context = null, $showCadence = true) {
		$this->showCadence = $showCadence;

		parent::__construct($Context);
	}

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

		if ($this->showCadence && $this->Context->trackdata()->has(Trackdata\Entity::CADENCE)) {
			$Plot = new Activity\Plot\Cadence($this->Context);
			$this->addRightContent('cadence', __('Cadence plot'), $Plot);
		}

		if ($this->Context->trackdata()->has(Trackdata\Entity::POWER)) {
			$Plot = new Activity\Plot\Power($this->Context);
			$this->addRightContent('power', __('Power plot'), $Plot);
		}

		if ($this->Context->trackdata()->has(Trackdata\Entity::TEMPERATURE)) {
			$Plot = new Activity\Plot\Temperature($this->Context);
			$this->addRightContent('temperature', __('Temperature plot'), $Plot);
		}

        if ($this->Context->trackdata()->has(Trackdata\Entity::SMO2_0) ||
            $this->Context->trackdata()->has(Trackdata\Entity::THB_0)) {
            $Plot = new Activity\Plot\Smo2AndThb($this->Context);
            $this->addRightContent('smo2AndThb', __('Smo2 & Thb'), $Plot);
        }

	}

	/**
	 * Set boxed values
	 */
	protected function setBoxedValues() {
		$this->addDateAndTime();
		$this->addRPE();
		$this->addCadenceAndPower();
		$this->addStrokeandSwolf();
		$this->addWeather();
		$this->addTags();
		$this->addEquipment();
		$this->addTrainingPartner();
        $this->addSmo2AndThb();
	}

	/**
	 * Add date and time
	 */
	protected function addDateAndTime() {
		$Date = new BoxedValue($this->Context->dataview()->date(), '', __('Date'));

		if ($this->Context->dataview()->daytime() != '') {
			$Daytime = new BoxedValue($this->Context->dataview()->daytime(), '', __('Time of day'));
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
		if ($this->showCadence && ($this->Context->activity()->cadence() > 0 || $this->Context->activity()->power() > 0)) {
			$Cadence = new BoxedValue(Helper::Unknown($this->Context->dataview()->cadence()->value(), '-'), $this->Context->dataview()->cadence()->unitAsString(), $this->Context->dataview()->cadence()->label());
			$Cadence->defineAsFloatingBlock('w50');

			$TotalCadence = new Box\TotalCadence($this->Context);
			$TotalCadence->defineAsFloatingBlock('w50');

			if ($this->Context->activity()->strideLength() > 0) {
				$Power = new Activity\Box\StrideLength($this->Context);
				$Power->defineAsFloatingBlock('w50');
			} else {
				$Power = new BoxedValue(Helper::Unknown($this->Context->activity()->power(), '-'), 'W', __('Power'));
				$Power->defineAsFloatingBlock('w50');
			}

			$this->BoxedValues[] = $Cadence;
			$this->BoxedValues[] = $TotalCadence;
			$this->BoxedValues[] = $Power;
		} elseif (!$this->showCadence && $this->Context->activity()->power() > 0) {
			$Power = new BoxedValue(Helper::Unknown($this->Context->activity()->power(), '-'), 'W', __('Power'));
			$Power->defineAsFloatingBlock('w100');

			$this->BoxedValues[] = $Power;
		}
	}

	/*
	 * Add swolf and total strokes
	 */
	protected function addStrokeandSwolf() {
		if ($this->Context->hasSwimdata() && ($this->Context->activity()->totalStrokes() > 0 || $this->Context->activity()->swolf() > 0)) {
			if ($this->Context->activity()->totalStrokes() > 0) {
				$Strokes = new BoxedValue($this->Context->activity()->totalStrokes(), '', __('Strokes'));
				$Strokes->defineAsFloatingBlock('w50');
				$this->BoxedValues[] = $Strokes;
			}

			if ($this->Context->activity()->swolf() > 0) {
				$Swolf = new BoxedValue($this->Context->activity()->swolf(), '', __('Swolf'));
				$Swolf->defineAsFloatingBlock('w50');
				$this->BoxedValues[] = $Swolf;
			}

			if ($this->Context->swimdata()->poollength() > 0) {
				$PoolLength = new Box\PoolLength($this->Context);
				$PoolLength->defineAsFloatingBlock('w50');
				$this->BoxedValues[] = $PoolLength;
			}
		}
	}

        /**
	 * Add running dynamics
	 */
	protected function addRunningDynamics() {
		if ($this->Context->activity()->groundcontact() > 0 || $this->Context->activity()->verticalOscillation() > 0) {
			$Contact = new BoxedValue(Helper::Unknown($this->Context->activity()->groundcontact(), '-'), 'ms', __('Ground contact'));
			$Contact->defineAsFloatingBlock('w50');

			$Oscillation = new BoxedValue(Helper::Unknown(round($this->Context->activity()->verticalOscillation()/10, 1), '-'), 'cm', __('Vertical oscillation'));
			$Oscillation->defineAsFloatingBlock('w50');

			$this->BoxedValues[] = $Contact;
			$this->BoxedValues[] = $Oscillation;
		}
	}

	/**
	 * Add weather
	 */
	protected function addWeather() {
		$WeatherObject = $this->Context->activity()->weather();

		if (!$WeatherObject->isEmpty()) {
			$WeatherIcon = $WeatherObject->condition()->icon();

			if ($this->Context->activity()->isNight()) {
				$WeatherIcon->setAsNight();
			}

			$Temperature = new Temperature($WeatherObject->temperature()->value());
			$Weather = new BoxedValue($WeatherObject->condition()->string(), '', __('Weather condition'), $WeatherIcon->code());
			$Weather->defineAsFloatingBlock('w50');

			$Temp = new BoxedValue($Temperature->string(false, false), $Temperature->unit(), __('Temperature'));
			$Temp->defineAsFloatingBlock('w50');

			$this->BoxedValues[] = $Weather;
			$this->BoxedValues[] = $Temp;

			if (!$WeatherObject->windSpeed()->isUnknown()) {
				$WindSpeed = new Box\WeatherWindSpeed($this->Context);
				$WindSpeed->defineAsFloatingBlock('w50');
				$this->BoxedValues[] = $WindSpeed;
			}

			if (!$WeatherObject->windDegree()->isUnknown()) {
				$WindDegree = new Box\WeatherWindDegree($this->Context);
				$WindDegree->defineAsFloatingBlock('w50');
				$this->BoxedValues[] = $WindDegree;
			}

			if (!$WeatherObject->humidity()->isUnknown()) {
				$Humidity = new Box\WeatherHumidity($this->Context);
				$Humidity->defineAsFloatingBlock('w50');
				$this->BoxedValues[] = $Humidity;
			}

			if (!$WeatherObject->pressure()->isUnknown()) {
				$Pressure = new Box\WeatherPressure($this->Context);
				$Pressure->defineAsFloatingBlock('w50');
				$this->BoxedValues[] = $Pressure;
			}

			if (!$WeatherObject->windSpeed()->isUnknown() && !$WeatherObject->temperature()->isUnknown() && $this->Context->activity()->distance() > 0 && $this->Context->activity()->duration() > 0) {
				$WindChill = new Box\WeatherWindChillFactor($this->Context);
				$WindChill->defineAsFloatingBlock('w50');
				$this->BoxedValues[] = $WindChill;
			}
			
			if (!$WeatherObject->temperature()->isUnknown() && !$WeatherObject->humidity()->isUnknown()) {
				$heatIndexObject = $this->Context->dataview()->heatIndex();
				echo $heatIndexObject->value();
				$HeatIndex = new BoxedValue(Helper::Unknown($heatIndexObject->value(), '-'), $heatIndexObject->unit(), $heatIndexObject->label(), $heatIndexObject->getIcon());
				$HeatIndex->defineAsFloatingBlock('w50');
				$this->BoxedValues[] = $HeatIndex;
			}
		}
	}

	/**
	 * Add equipment
	 */
	protected function addEquipment() {
		$Types = array();
		$Factory = new \Runalyze\Model\Factory(SessionAccountHandler::getId());
		$Equipment = $Factory->equipmentForActivity($this->Context->activity()->id());

		foreach ($Equipment as $Object) {
			$Link = Request::isOnSharedPage() ? $Object->name() : SearchLink::to('equipmentid', $Object->id(), $Object->name());

			if (isset($Types[$Object->typeid()])) {
				$Types[$Object->typeid()][] = $Link;
			} else {
				$Types[$Object->typeid()] = array($Link);
			}
		}

		foreach ($Types as $typeid => $links) {
			$Type = $Factory->equipmentType($typeid);

			$Value = new BoxedValue(implode(', ', $links), '', $Type->name());
			$Value->defineAsFloatingBlock('w100 flexible-height');

			$this->BoxedValues[] = $Value;
		}
	}

	/**
	 * Add tags
	 */
	protected function addTags() {
		$Links = array();
		$Factory = new \Runalyze\Model\Factory(SessionAccountHandler::getId());
		$SelectedTags = $Factory->tagForActivity($this->Context->activity()->id());

		foreach ($SelectedTags as $Object) {
			$Links[] = Request::isOnSharedPage() ? '#'.$Object->tag() : SearchLink::to('tagid', $Object->id(), '#'.$Object->tag());
		}

		if (!empty($Links)) {
			$Value = new BoxedValue(implode(', ', $Links), '', __('Tags'));
			$Value->defineAsFloatingBlock('w100 flexible-height');

			$this->BoxedValues[] = $Value;
		}
	}

	/**
	 * Add training partner
	 */
	protected function addTrainingPartner() {
		if (!$this->Context->activity()->partner()->isEmpty()) {
			$TrainingPartner = new BoxedValue($this->Context->dataview()->partnerAsLinks(), '', __('Training partner'));
			$TrainingPartner->defineAsFloatingBlock('w100 flexible-height');

			$this->BoxedValues[] = $TrainingPartner;
		}
	}

    /**
     * Add smo2 and thb
     */
    protected function addSmo2AndThb() {
        if ($this->Context->trackdata()->has(Trackdata\Entity::SMO2_0)) {
            $Smo2_0 = new Box\Smo2($this->Context);
            $Smo2_0->defineAsFloatingBlock('w50');
            $this->BoxedValues[] = $Smo2_0;
        }

        if ($this->Context->trackdata()->has(Trackdata\Entity::SMO2_1)) {
            $Smo2_1 = new Box\Smo2($this->Context, 1);
            $Smo2_1->defineAsFloatingBlock('w50');
            $this->BoxedValues[] = $Smo2_1;
        }

        if ($this->Context->trackdata()->has(Trackdata\Entity::THB_0)) {
            $Thb_0 = new Box\Thb($this->Context);
            $Thb_0->defineAsFloatingBlock('w50');
            $this->BoxedValues[] = $Thb_0;
        }

        if ($this->Context->trackdata()->has(Trackdata\Entity::THB_1)) {
            $Thb_1 = new Box\Thb($this->Context, 1);
            $Thb_1->defineAsFloatingBlock('w50');
            $this->BoxedValues[] = $Thb_1;
        }
    }

	/**
	 * Fill notes content
	 */
	protected function fillNotesContent() {
		$this->NotesContent = '<div class="panel-content">';

		$this->addRaceResult();
		$this->addNotes();
		$this->addWeatherSourceInfo();
		$this->addCreationAndModificationTime();

		$this->NotesContent .= '</div>';
	}

	/**
	 * Add race result
	 */
	protected function addRaceResult() {
		if ($this->Context->hasRaceResult()) {
			$raceResult = $this->Context->raceResult();
			$athlete = \Runalyze\Context::Athlete();
			$ageGrade = null;
			$ageGradeIcon = '';

			if (Configuration::General()->runningSport() == $this->Context->sport()->id() && $athlete->knowsGender() && $athlete->knowsAge()) {
				$table = Gender::FEMALE === $athlete->gender() ? new FemaleTable() : new MaleTable();
				$lookup = new Lookup($table, $athlete->age());

				if ($this->Context->raceResult()->officialDistance() >= $lookup->getMinimalDistance()) {
					$ageGrade = $lookup->getAgeGrade($raceResult->officialDistance(), $raceResult->officialTime(), date('Y') - date('Y', $this->Context->activity()->timestamp()));
					$ageGradeIcon = new View\Icon('fa-info-circle');
					$ageGradeIcon->setTooltip(
						__('Age standard').': '.Duration::format($ageGrade->getAgeStandard()).', '.
						__('Open standard').': '.Duration::format($ageGrade->getOpenStandard()).'<br>'.
						'<small><em>'.sprintf(__('via tables by %s'), 'Alan Jones / WMA / USATF').'</em></small>'
					);
				}
			}

			$RaceResultView = new View\RaceResult\Dataview($raceResult);

			$this->NotesContent .= HTML::info('<strong>'.__('Race Result').':</strong>');
			$this->NotesContent .= '<ul>'.
				($raceResult->officialDistance() ? '<li><strong>'.__('Official distance').'</strong>: '.$RaceResultView->officialDistance().'</li>' : '') .
				($raceResult->officialTime() ? '<li><strong>'.__('Official time').'</strong>: '.$RaceResultView->officialTime()->string(Duration::FORMAT_COMPETITION).'</li>' : '').
				(null !== $ageGrade ? '<li><strong>'.__('Age grade').'</strong>: '.number_format(100 * $ageGrade->getPerformance(), 2).' &#37; '.$ageGradeIcon->code().'</li>' : '').
				($raceResult->placeTotal() ? '<li><strong>'.__('Place overall').'</strong>: '.$RaceResultView->placementTotalWithParticipants().'</li>' : '') .
				($raceResult->placeAgeclass() ? '<li><strong>'.__('Place age group').'</strong>: '.$RaceResultView->placementAgeClassWithParticipants().'</li>' : '') .
				($raceResult->placeGender() ? '<li><strong>'.__('Place gender').'</strong>: '.$RaceResultView->placementGenderWithParticipants().'</li>' : '') .'</ul>';
		}
	}

	/**
	 * Add notes
	 */
	protected function addNotes() {
		if ($this->Context->activity()->notes() != '') {
			$Notes = '<strong>'.__('Notes').':</strong><br>'.$this->Context->dataview()->notes();
			$this->NotesContent .= HTML::fileBlock($Notes);
		}
	}

	/**
	 * Add weather sources
	 */
	protected function addWeatherSourceInfo() {
		if ($this->Context->activity()->weather()->sourceIsKnown()) {
			$this->NotesContent .= HTML::info(
				sprintf(__('Source of weather data: %s'), $this->Context->activity()->weather()->sourceAsString())
			);
		}
	}

	/**
	 * Add created/edited
	 */
	protected function addCreationAndModificationTime() {
	    if (!Request::isOnSharedPage()) {
		$created = $this->Context->activity()->get(\Runalyze\Model\Activity\Entity::TIMESTAMP_CREATED);
		$edited = $this->Context->activity()->get(\Runalyze\Model\Activity\Entity::TIMESTAMP_EDITED);

		if ($created > 0 || $edited > 0) {
			$createdDate = new \DateTime();
			$createdDate->setTimestamp($created);
			$CreationTime = ($created == 0) ? '' : sprintf( __('You created this training on <strong>%s</strong> at <strong>%s</strong>.'),
				$createdDate->format('d.m.Y'),
				$createdDate->format('H:i')
			);

			$editedDate = new \DateTime();
			$editedDate->setTimestamp($edited);
			$ModificationTime = ($edited == 0) ? '' : '<br>'.sprintf( __('Last modification on <strong>%s</strong> at <strong>%s</strong>.'),
				$editedDate->format('d.m.Y'),
				$editedDate->format('H:i')
			);

			$this->NotesContent .= HTML::fileBlock($CreationTime.$ModificationTime);
		}
	    }
	}

	/**
	 * Add: RPE
	 */
	protected function addRPE() {
	    if ($this->Context->activity()->rpe()) {
		$this->BoxedValues[] = new Box\RPE($this->Context);
	    }
	}
}
