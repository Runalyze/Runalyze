<?php
/**
 * This file contains class::ConfigTabEquipment
 * @package Runalyze\System\Config
 */

use Runalyze\Activity\Duration;
use Runalyze\Model\EquipmentType;
use Runalyze\Model\Equipment;
use Runalyze\Activity\Distance;
use Runalyze\View\Icon;

/**
 * ConfigTabEquipment
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\System\Config
 */
class ConfigTabEquipment extends ConfigTab {
	/**
	 * Set key and title for form 
	 */
	protected function setKeyAndTitle() {
		$this->key = 'config_tab_equipment';
		$this->title = __('Equipment');
	}

	/**
	 * Set all fieldsets and fields
	 */
	public function setFieldsetsAndFields() {
		$EquipmentType = new FormularFieldset(__('Your equipment categories'));
		$EquipmentType->setHtmlCode($this->getEquipmentTypeCode());
		$EquipmentType->addInfo(
			__('All your equipment is grouped into some categories. '
				.'For each category, you can define whether one or multiple objects of that category can belong to one activity. '
				.'In addition, you can specify to which sports a category belongs.')
		);

		$Equipment = new FormularFieldset(__('Your equipment'));
		$Equipment->setHtmlCode($this->getEquipmentCode().$this->getJS());
		$Equipment->addInfo(
			__('RUNALYZE collects data and calculates statistics for each of your equipment objects. '
				.'To correctly estimate its lifetime you can specify a \'previous distance\' '
				.'which is added to its calculated distance, e.g. if you used your running shoes for 200 km before tracking them. '
				.'In addition, you can specify a start and end date for its usage. '
				.'Objects will be displayed as \'inactive\' as soon as you have entered an end date.')
		);

		$this->Formular->addFieldset($EquipmentType);
		$this->Formular->addFieldset($Equipment);
	}

	/**
	 * @return string
	 */
	protected function getDeleteIcon() {
		$DeleteIcon = new Icon('fa-exclamation-triangle');
		$DeleteIcon->setTooltip(__('Attention: This operation cannot be undone.'));

		return $DeleteIcon->code();
	}

	/**
	 * @return string
	 */
	protected function getJS() {
		return Ajax::wrapJSasFunction(
			'$("input.delete-checkbox").change(function(){$(this).parent().parent().toggleClass("ERROR unimportant")});'
		);
	}

	/**
	 * @return string
	 */
	protected function getEquipmentTypeCode() {
		$Code = '
			<table class="fullwidth zebra-style c">
				<thead>
					<tr>
						<th>'.__('Name').'</th>
						<th>'.__('Type').'</th>
						<th>'.__('max.').Runalyze\Configuration::General()->distanceUnitSystem()->distanceUnit().'</th>
						<th>'.__('max. Time').'</th>
						<th>'.__('Sports').'</th>
						<th>'.__('delete').' '.$this->getDeleteIcon().'</th>
					</tr>
				</thead>
				<tbody>';

		$Sports = $this->Model->allSports();
		$Types = $this->Model->allEquipmentTypes();
		$Types[] = new EquipmentType\Entity();

		foreach ($Types as $Type) {
			$isNew = !$Type->hasID();
			$id = $isNew ? -1 : $Type->id();
			$delete = $isNew ? '' : '<input type="checkbox" class="delete-checkbox" name="equipmenttype[delete]['.$id.']">';
			$sportIDs = $isNew ? array() : $this->Model->sportForEquipmentType($id, true);
			$MaxDistance = new Distance($Type->maxDistance());

			$Code .= '
					<tr class="'.($isNew ? ' unimportant' : '').'">
						<td><input type="text" class="middle-size" name="equipmenttype[name]['.$id.']" value="'.$Type->name().'"></td>
						<td><select name="equipmenttype[input]['.$id.']"">
								<option value="'.EquipmentType\Entity::CHOICE_SINGLE.'" '.HTML::Selected(!$Type->allowsMultipleValues()).'>'.__('Single choice').'</option>
								<option value="'.EquipmentType\Entity::CHOICE_MULTIPLE.'" '.HTML::Selected($Type->allowsMultipleValues()).'>'.__('Multiple choice').'</option>
							</select></td>
						<td><span class="input-with-unit"><input type="text" class="small-size" name="equipmenttype[max_km]['.$id.']" value="'.round($MaxDistance->valueInPreferredUnit()).'"><label class="input-unit">'.$MaxDistance->unit().'</label></span></td>
						<td><input type="text" class="small-size" name="equipmenttype[max_time]['.$id.']" value="'.($Type->maxDuration() > 0 ? Duration::format($Type->maxDuration()) : '').'" placeholder="d hh:mm:ss"></td>
						<td><input name="equipmenttype[sportid_old]['.$id.']" type="hidden" value="'.implode(',', $sportIDs).'">
							<select name="equipmenttype[sportid]['.$id.'][]" class="middle-size" multiple>';

			foreach ($Sports as $Sport) {
				$Code .= '<option value="'.$Sport->id().'"'.HTML::Selected(in_array($Sport->id(), $sportIDs)).'>'.$Sport->name().'</option>';
			}

			$Code .= '</select></td>
						<td>'.$delete.'</td>
					</tr>';
		}

		$Code .= '
			</tbody>
		</table>';

		return $Code;
	}

	/**
	 * @return string
	 */
	protected function getEquipmentCode() {
		$Code = '
			<table class="fullwidth zebra-style c">
				<thead>
					<tr>
						<th>'.__('Name').'</th>
						<th>'.__('Category').'</th>
						<th>'.__('prev. distance').'</th>
						<th>'.__('Start of use').'</th>
						<th>'.__('End of use').'</th>
						<th>'.__('Notes').'</th>
						<th>'.__('delete').' '.$this->getDeleteIcon().'</th>
					</tr>
				</thead>
				<tbody>';

		$Types = $this->Model->allEquipmentTypes();
		$Equipments = $this->Model->allEquipments();
		$Equipments[] = new Equipment\Entity();

		foreach ($Equipments as $Equipment) {
			$isNew = !$Equipment->hasID();
			$id = $isNew ? -1 : $Equipment->id();
			$delete = $isNew ? '' : '<input type="checkbox" class="delete-checkbox" name="equipment[delete]['.$id.']">';

			$Code .= '
					<tr class="'.($isNew ? ' unimportant' : '').'">
						<td><input type="text" size="30" name="equipment[name]['.$id.']" value="'.$Equipment->name().'"></td>
						<td><select name="equipment[typeid]['.$id.']">';

			foreach ($Types as $Type) {
				$Code .= '<option value="'.$Type->id().'"'.HTML::Selected($Type->id() == $Equipment->typeid()).'>'.$Type->name().'</option>';
			}

			$AdditionalDistance = new Distance($Equipment->additionalDistance());

			$Code .= '</select></td>
						<td><span class="input-with-unit"><input type="text" class="small-size" name="equipment[additional_km]['.$id.']" value="'.round($AdditionalDistance->valueInPreferredUnit()).'"><label class="input-unit">'.$AdditionalDistance->unit().'</label></span></td>
						<td><input type="text" class="small-size pick-a-date" placeholder="dd.mm.YYYY" name="equipment[date_start]['.$id.']" value="'.$this->datetimeToString($Equipment->startDate()).'"></td>
						<td><input type="text" class="small-size pick-a-date" placeholder="dd.mm.YYYY" name="equipment[date_end]['.$id.']" value="'.$this->datetimeToString($Equipment->endDate()).'"></td>
						<td><input type="text" size="fullwidth" name="equipment[notes]['.$id.']" value="'.$Equipment->notes().'"></td>
						<td>'.$delete.'</td>
					</tr>';
		}

		$Code .= '
			</tbody>
		</table>';

		return $Code;
	}


	/**
	 * Parse all post values 
	 */
	public function parsePostData() {
		$this->parsePostDataForEquipmentTypes();
		$this->parsePostDataForSingleEquipment();
	}

	/**
	 * Parse post data for equipment types
	 */
	protected function parsePostDataForEquipmentTypes() {
		$DB = DB::getInstance();
		$accountId = SessionAccountHandler::getId();

		$Types = $this->Model->allEquipmentTypes();
		$Types[] = new EquipmentType\Entity();

		foreach ($Types as $Type) {
			$isNew = !$Type->hasID();
			$id = $isNew ? -1 : $Type->id();

			$MaxTime = new Duration($_POST['equipmenttype']['max_time'][$id]);
			$MaxDistance = new Distance();
			$MaxDistance->setInPreferredUnit($_POST['equipmenttype']['max_km'][$id]);

			$NewType = clone $Type;
			$NewType->set(EquipmentType\Entity::NAME, $_POST['equipmenttype']['name'][$id]);
			$NewType->set(EquipmentType\Entity::INPUT, (int)$_POST['equipmenttype']['input'][$id]);
			$NewType->set(EquipmentType\Entity::MAX_KM, $MaxDistance->kilometer());
			$NewType->set(EquipmentType\Entity::MAX_TIME, $MaxTime->seconds());

			if ($isNew) {
				if ($NewType->name() != '') {
					$Inserter = new EquipmentType\Inserter($DB, $NewType);
					$Inserter->setAccountID($accountId);
					$Inserter->insert();

					$RelationUpdater = new EquipmentType\RelationUpdater($DB, $Inserter->insertedID());
					$RelationUpdater->update($_POST['equipmenttype']['sportid'][$id]);
				}
			} elseif (isset($_POST['equipmenttype']['delete'][$id])) {
				$DB->deleteByID('equipment_type', (int)$id);
			} else {
				$Updater = new EquipmentType\Updater($DB, $NewType, $Type);
				$Updater->setAccountID($accountId);
				$Updater->update();

				$RelationUpdater = new EquipmentType\RelationUpdater($DB, $Type->id());
				$RelationUpdater->update(
					$_POST['equipmenttype']['sportid'][$id],
					explode(',', $_POST['equipmenttype']['sportid_old'][$id])
				);
			}
		}

		$this->Model->clearCache('equipment_type');
	}

	/**
	 * Parse post data for equipment
	 */
	protected function parsePostDataForSingleEquipment() {
		$DB = DB::getInstance();
		$accountId = SessionAccountHandler::getId();

		$Equipments = $this->Model->allEquipments();
		$Equipments[] = new Equipment\Entity();

		foreach ($Equipments as $Equipment) {
			$isNew = !$Equipment->hasID();
			$id = $isNew ? -1 : $Equipment->id();

			$AdditionalDistance = new Distance();
			$AdditionalDistance->setInPreferredUnit($_POST['equipment']['additional_km'][$id]);

			$NewEquipment = clone $Equipment;
			$NewEquipment->set(Equipment\Entity::NAME, $_POST['equipment']['name'][$id]);
			$NewEquipment->set(Equipment\Entity::TYPEID, (int)$_POST['equipment']['typeid'][$id]);
			$NewEquipment->set(Equipment\Entity::ADDITIONAL_KM, $AdditionalDistance->kilometer());
			$NewEquipment->set(Equipment\Entity::DATE_START, $this->stringToDatetime($_POST['equipment']['date_start'][$id]));
			$NewEquipment->set(Equipment\Entity::DATE_END, $this->stringToDatetime($_POST['equipment']['date_end'][$id]));
			$NewEquipment->set(Equipment\Entity::NOTES, $_POST['equipment']['notes'][$id]);

			if ($isNew) {
				if ($NewEquipment->name() != '') {
					$Inserter = new Equipment\Inserter($DB, $NewEquipment);
					$Inserter->setAccountID($accountId);
					$Inserter->insert();
				}
			} elseif (isset($_POST['equipment']['delete'][$id])) {
				$DB->deleteByID('equipment', (int)$id);
			} else {
				$Updater = new Equipment\Updater($DB, $NewEquipment, $Equipment);
				$Updater->setAccountID($accountId);
				$Updater->update();
			}
		}

		$this->Model->clearCache('equipment');
	}

	/**
	 * @param string $datetime
	 * @return string
	 */
	protected function datetimeToString($datetime) {
		if (empty($datetime)) {
			return '';
		}

		return date('d.m.Y', strtotime($datetime));
	}

	/**
	 * @param string $string
	 * @return mixed
	 */
	protected function stringToDatetime($string) {
		$time = strtotime($string);

		if ($time) {
			return date('Y-m-d', $time);
		}

		return null;
	}
}