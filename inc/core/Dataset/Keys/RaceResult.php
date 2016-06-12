<?php
/**
 * This file contains class::RaceResult
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Model;
use Runalyze\View;
use Runalyze\Activity;


/**
 * Dataset key: Race Result
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Dataset\Keys
 */
class RaceResult extends AbstractKey
{

	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::RACE_RESULT;
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Race result');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return '';
	}
	
	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return '';
	}
	
	/**
	 * @return bool
	 */
	public function requiresJoin()
	{
		return true;
	}

	/**
	 * @return array array('column' => '...', 'join' => 'LEFT JOIN ...', 'field' => '`x`.`y`)
	 */
	public function joinDefinition()
	{
		return array(
			'column' => 'raceresult_placements',
			'join' => 'LEFT JOIN `'.PREFIX.'raceresult` AS `rrd` ON `rrd`.`activity_id` = `t`.id',
			'field' => '`rrd`.`name` as `race_name`, `rrd`.`official_time`, `rrd`.`official_distance`, `rrd`.`place_total`, `rrd`.`place_gender`, `rrd`.`place_ageclass`, `rrd`.`participants_total`, `rrd`.`participants_ageclass`, `rrd`.`participants_gender`'
		);
	}


	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __('Your placements at races');
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		switch ($context->activity()->id()) {
			case 0:
				return '';
			case -1:
				return $this->FakeString();
				break;
			default:

		if ($context->hasData('official_distance')) {
			$RaceResult = new Model\RaceResult\Entity(array(
				Model\RaceResult\Entity::NAME => $context->data('race_name'),
				Model\RaceResult\Entity::OFFICIAL_TIME => $context->data('official_time'),
				Model\RaceResult\Entity::OFFICIAL_DISTANCE => $context->data('official_distance'),
				Model\RaceResult\Entity::PLACE_TOTAL => $context->data('place_total', true),
				Model\RaceResult\Entity::PLACE_GENDER => $context->data('place_gender', true),
				Model\RaceResult\Entity::PLACE_AGECLASS => $context->data('place_ageclass', true),
				Model\RaceResult\Entity::PARTICIPANTS_TOTAL => $context->data('participants_total', true),
				Model\RaceResult\Entity::PARTICIPANTS_GENDER => $context->data('participants_gender', true),
				Model\RaceResult\Entity::PARTICIPANTS_AGECLASS => $context->data('participants_ageclass', true)
			));
			
			$RaceResultView = new View\RaceResult\Dataview($RaceResult);
			
			$TooltipCode = '';

			if ($context->hasData('race_name') && $context->data('race_name') != '') {
				$TooltipCode .= $RaceResult->name().'<br>';
			}

			$TooltipCode .= __('Official distance').': '.$RaceResultView->officialDistance().'<br>';
			$TooltipCode .= __('Official time').': '.$RaceResultView->officialTime()->string(Activity\Duration::FORMAT_COMPETITION).'<br>';

			if ($context->hasData('place_total')) {
				$TooltipCode .= __('Total').': '.$RaceResultView->placementTotalWithParticipants().'<br>';
			}

			if ($context->hasData('place_ageclass')) {
				$TooltipCode .= __('Age group').': '.$RaceResultView->placementAgeClassWithParticipants().'<br>';
			}

			if ($context->hasData('place_gender')) {
				$TooltipCode .= __('Gender').': '.$RaceResultView->placementGenderWithParticipants().'<br>';
			}

			$Icon = new View\Icon('fa-trophy');
			$Icon->setTooltip($TooltipCode);

			return $Icon->code();
		}

		return '';
		break;
		}
	}
	
	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	protected function fakeString()
	{
			$RaceResult = new Model\RaceResult\Entity(array(
				Model\RaceResult\Entity::NAME => __('Race name'),
				Model\RaceResult\Entity::OFFICIAL_TIME => 2400,
				Model\RaceResult\Entity::OFFICIAL_DISTANCE => 10.00,
				Model\RaceResult\Entity::PLACE_TOTAL => 4,
				Model\RaceResult\Entity::PLACE_GENDER => 5,
				Model\RaceResult\Entity::PLACE_AGECLASS => 2,
				Model\RaceResult\Entity::PARTICIPANTS_TOTAL => 2000,
				Model\RaceResult\Entity::PARTICIPANTS_GENDER => 300,
				Model\RaceResult\Entity::PARTICIPANTS_AGECLASS => 100
			));
			
			$RaceResultView = new View\RaceResult\Dataview($RaceResult);
			
			$TooltipCode = $RaceResult->name().'<br>';

			$TooltipCode .= __('Official distance').': '.$RaceResultView->officialDistance().'<br>';
			$TooltipCode .= __('Official time').': '.$RaceResultView->officialTime()->string(Activity\Duration::FORMAT_COMPETITION).'<br>';
			$TooltipCode .= __('Total').': '.$RaceResultView->placementTotalWithParticipants().'<br>';
			$TooltipCode .= __('Age group').': '.$RaceResultView->placementAgeClassWithParticipants().'<br>';
			$TooltipCode .= __('Gender').': '.$RaceResultView->placementGenderWithParticipants().'<br>';

			$Icon = new View\Icon('fa-trophy');
			$Icon->setTooltip($TooltipCode);

			return $Icon->code();
	}
}