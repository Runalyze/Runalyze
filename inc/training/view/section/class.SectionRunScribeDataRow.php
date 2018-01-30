<?php

use Runalyze\Model\Trackdata;
use Runalyze\View\Activity;

class SectionRunScribeDataRow extends TrainingViewSectionRowTabbedPlot
{
	protected function setContent()
    {
        $this->BoxedValues[] = new BoxedValue($this->formatNumber($this->Context->activity()->impactGsLeft(), 1), 'G', __('Impact').' ('.__('left').')');
        $this->BoxedValues[] = new BoxedValue($this->formatNumber($this->Context->activity()->impactGsRight(), 1), 'G', __('Impact').' ('.__('right').')');

        $this->BoxedValues[] = new BoxedValue($this->formatNumber($this->Context->activity()->brakingGsLeft(), 1), 'G', __('Braking').' ('.__('left').')');
        $this->BoxedValues[] = new BoxedValue($this->formatNumber($this->Context->activity()->brakingGsRight(), 1), 'G', __('Braking').' ('.__('right').')');

        $this->BoxedValues[] = new BoxedValue($this->formatFootstrikeType($this->Context->activity()->footstrikeTypeLeft()), '', __('Footstrike').' ('.__('left').')');
        $this->BoxedValues[] = new BoxedValue($this->formatFootstrikeType($this->Context->activity()->footstrikeTypeRight()), '', __('Footstrike').' ('.__('right').')');

        $this->BoxedValues[] = new BoxedValue($this->formatNumber($this->Context->activity()->pronationExcursionLeft(), 1), '&deg;', __('Pronation excursion').' ('.__('left').')');
        $this->BoxedValues[] = new BoxedValue($this->formatNumber($this->Context->activity()->pronationExcursionRight(), 1), '&deg;', __('Pronation excursion').' ('.__('right').')');

        foreach ($this->BoxedValues as $boxedValue) {
            $boxedValue->defineAsFloatingBlock('w50');
        }
	}

    /**
     * @param mixed $value
     * @param int $precision
     * @param string $unknown
     *
     * @return string
     */
    protected function formatNumber($value, $precision = 0, $unknown = '-')
    {
        if (null === $value || 0 == $value) {
            return $unknown;
        }

        return number_format($value, $precision);
    }

    /**
     * @param mixed $value
     * @param string $unknown
     *
     * @return string
     */
    protected function formatFootstrikeType($value, $unknown = '-')
    {
        if (null === $value || 0 == $value) {
            return $unknown;
        }

        $type = $value >= 12 ? __('Fore-foot') : ($value >= 6 ? __('Mid-foot') : __('Heel'));

        return (string)$value.' / '.$type;
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
