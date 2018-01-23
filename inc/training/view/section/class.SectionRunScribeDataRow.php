<?php

use Runalyze\Model\Trackdata;
use Runalyze\View\Activity;

class SectionRunScribeDataRow extends TrainingViewSectionRowTabbedPlot
{
	protected function setContent()
    {
        $this->BoxedValues[] = new BoxedValue(Helper::Unknown($this->Context->activity()->impactGsLeft(), '-'), 'G', __('Impact').' ('.__('left').')');
        $this->BoxedValues[] = new BoxedValue(Helper::Unknown($this->Context->activity()->impactGsRight(), '-'), 'G', __('Impact').' ('.__('right').')');

        $this->BoxedValues[] = new BoxedValue(Helper::Unknown($this->Context->activity()->brakingGsLeft(), '-'), 'G', __('Braking').' ('.__('left').')');
        $this->BoxedValues[] = new BoxedValue(Helper::Unknown($this->Context->activity()->brakingGsRight(), '-'), 'G', __('Braking').' ('.__('right').')');

        $this->BoxedValues[] = new BoxedValue(Helper::Unknown($this->Context->activity()->footstrikeTypeLeft(), '-'), '', __('Footstrike').' ('.__('left').')');
        $this->BoxedValues[] = new BoxedValue(Helper::Unknown($this->Context->activity()->footstrikeTypeRight(), '-'), '', __('Footstrike').' ('.__('right').')');

        $this->BoxedValues[] = new BoxedValue(Helper::Unknown($this->Context->activity()->pronationExcursionLeft(), '-'), '&deg;', __('Pronation excursion').' ('.__('left').')');
        $this->BoxedValues[] = new BoxedValue(Helper::Unknown($this->Context->activity()->pronationExcursionRight(), '-'), '&deg;', __('Pronation excursion').' ('.__('right').')');

        foreach ($this->BoxedValues as $boxedValue) {
            $boxedValue->defineAsFloatingBlock('w50');
        }
	}

	protected function setRightContent()
    {
		if ($this->Context->trackdata()->has(Trackdata\Entity::IMPACT_GS_LEFT) || $this->Context->trackdata()->has(Trackdata\Entity::IMPACT_GS_RIGHT)) {
			$Plot = new Activity\Plot\ImpactGs($this->Context);
			$this->addRightContent('impact_gs', __('Impact Gs'), $Plot);
		}

        if ($this->Context->trackdata()->has(Trackdata\Entity::BRAKING_GS_LEFT) || $this->Context->trackdata()->has(Trackdata\Entity::BRAKING_GS_RIGHT)) {
            $Plot = new Activity\Plot\BrakingGs($this->Context);
            $this->addRightContent('braking_gs', __('Braking Gs'), $Plot);
        }

        if ($this->Context->trackdata()->has(Trackdata\Entity::FOOTSTRIKE_TYPE_LEFT) || $this->Context->trackdata()->has(Trackdata\Entity::FOOTSTRIKE_TYPE_RIGHT)) {
            $Plot = new Activity\Plot\FootstrikeType($this->Context);
            $this->addRightContent('footstrike_type', __('Footstrike'), $Plot);
        }

        if ($this->Context->trackdata()->has(Trackdata\Entity::PRONATION_EXCURSION_LEFT) || $this->Context->trackdata()->has(Trackdata\Entity::PRONATION_EXCURSION_RIGHT)) {
            $Plot = new Activity\Plot\PronationExcursion($this->Context);
            $this->addRightContent('pronation_excursion', __('Pronation excursion'), $Plot);
        }
	}
}
