<?php
/**
 * This file contains class::ConfigTabEquipment
 * @package Runalyze\System\Config
 */

use Runalyze\Model\EquipmentType;
use Runalyze\Model\Equipment;

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
		$Equipment->setHtmlCode($this->getEquipmentCode());

		$this->Formular->addFieldset($EquipmentType);
		$this->Formular->addFieldset($Equipment);
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
						<th>'.__('max. km').'</th>
						<th>'.__('max. Time').'</th>
						<th>'.__('Sports').'</th> 
						<th>'.Ajax::tooltip(Icon::$CROSS_SMALL, __('A equipment type can only be deleted if no references (equipment) exist.')).'</th>
					</tr>
				</thead>
				<tbody>';

		$Sports = $this->Model->allSports();
		$Types = $this->Model->allEquipmentTypes();
		$Types[] = new EquipmentType\Object();

		foreach ($Types as $Type) {
			$isNew = !$Type->hasID();
			$id = $isNew ? -1 : $Type->id();
			$delete = $isNew ? Icon::$ADD_SMALL : '<input type="checkbox" name="equipmenttype[delete]['.$id.']">';
			$sportIDs = $isNew ? array() : $this->Model->sportForEquipmentType($id, true);

			$Code .= '
					<tr class="'.($isNew ? ' unimportant' : '').'">
						<td><input type="text" class="middle-size" name="equipmenttype[name]['.$id.']" value="'.$Type->name().'"></td>
						<td><select name="equipmenttype[input]['.$id.']"">
								<option value="'.EquipmentType\Object::CHOICE_SINGLE.'" '.HTML::Selected(!$Type->allowsMultipleValues()).'>'.__('Single choice').'</option>
								<option value="'.EquipmentType\Object::CHOICE_MULTIPLE.'" '.HTML::Selected($Type->allowsMultipleValues()).'>'.__('Multiple choice').'</option>
							</select></td>
						<td><span class="input-with-unit"><input type="text" class="small-size" name="equipmenttype[max_km]['.$id.']" value="'.$Type->maxDistance().'"><label class="input-unit">km</label></span></td>
						<td><span class="input-with-unit"><input type="text" class="small-size" name="equipmenttype[max_time]['.$id.']" value="'.$Type->maxDuration().'"><label class="input-unit">s</label></span></td>
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
						<th>'.__('Type').'</th>
						<th>'.__('prev. distance').'</th>
						<th>'.__('Start of use').'</th>
						<th>'.__('End of use').'</th>
						<th>'.__('Notes').'</th>   
						<th>'.Ajax::tooltip(Icon::$CROSS_SMALL, __('An equipment can only be deleted if no references exist.')).'</th>
					</tr>
				</thead>
				<tbody>';

		$Types = $this->Model->allEquipmentTypes();
		$Equipments = $this->Model->allEquipments();
		$Equipments[] = new Equipment\Object();

		foreach ($Equipments as $Equipment) {
			$isNew = !$Equipment->hasID();
			$id = $isNew ? -1 : $Equipment->id();
			$delete = $isNew ? Icon::$ADD_SMALL : '<input type="checkbox" name="equipment[delete]['.$id.']">';

			$Code .= '
					<tr class="'.($isNew ? ' unimportant' : '').'">
						<td><input type="text" size="30" name="equipment[name]['.$id.']" value="'.$Equipment->name().'"></td>
						<td><select name="equipment[typeid]['.$id.']">';

			foreach ($Types as $Type) {
				$Code .= '<option value="'.$Type->id().'"'.HTML::Selected($Type->id() == $Equipment->typeid()).'>'.$Type->name().'</option>';
			}

			$Code .= '</select></td>
						<td><span class="input-with-unit"><input type="text" class="small-size" name="equipment[additional_km]['.$id.']" value="'.$Equipment->additionalDistance().'"><label class="input-unit">km</label></span></td>
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
		$Types[] = new EquipmentType\Object();

		foreach ($Types as $Type) {
			$isNew = !$Type->hasID();
			$id = $isNew ? -1 : $Type->id();

			$NewType = clone $Type;
			$NewType->set(EquipmentType\Object::NAME, $_POST['equipmenttype']['name'][$id]);
			$NewType->set(EquipmentType\Object::INPUT, (int)$_POST['equipmenttype']['input'][$id]);
			$NewType->set(EquipmentType\Object::MAX_KM, (int)$_POST['equipmenttype']['max_km'][$id]);
			$NewType->set(EquipmentType\Object::MAX_TIME, (int)$_POST['equipmenttype']['max_time'][$id]);

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
		$Equipments[] = new Equipment\Object();

		foreach ($Equipments as $Equipment) {
			$isNew = !$Equipment->hasID();
			$id = $isNew ? -1 : $Equipment->id();

			$NewEquipment = clone $Equipment;
			$NewEquipment->set(Equipment\Object::NAME, $_POST['equipment']['name'][$id]);
			$NewEquipment->set(Equipment\Object::TYPEID, (int)$_POST['equipment']['typeid'][$id]);
			$NewEquipment->set(Equipment\Object::ADDITIONAL_KM, (int)$_POST['equipment']['additional_km'][$id]);
			$NewEquipment->set(Equipment\Object::DATE_START, $this->stringToDatetime($_POST['equipment']['date_start'][$id]));
			$NewEquipment->set(Equipment\Object::DATE_END, $this->stringToDatetime($_POST['equipment']['date_end'][$id]));
			$NewEquipment->set(Equipment\Object::NOTES, $_POST['equipment']['notes'][$id]);

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

		return NULL;
	}
}