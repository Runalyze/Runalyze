<?php
/**
 * This file contains class::ConfigTabEquipment
 * @package Runalyze\System\Config
 */
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
		$EquipmentType->addInfo( __('Here you can define your equipment typ/category in which can be put equipment') );
                $EquipmentType->addInfo( __('You can assign a typ/category to several sports') );
                
		$Equipment = new FormularFieldset(__('Your equipment'));
		$Equipment->setHtmlCode($this->getEquipmentCode());
		$Equipment->addInfo( __('Foobla.') );
                
                $this->Formular->addFieldset($EquipmentType);
		$this->Formular->addFieldset($Equipment);
	}

        
        private function getEquipmentTypeCode() {
		$Code = '
			<table class="fullwidth zebra-style c">
				<thead>
					<tr>
						<th>'.__('Name').'</th>
						<th>'.__('Typ').'</th>
						<th>'.__('max. km').'</th>
						<th>'.__('max. Time').'</th>
                                                <th>'.__('Sports').'</th> 
                                                <th>'.Ajax::tooltip(Icon::$CROSS_SMALL, __('A equipment type can only be deleted if no references (equipment) exist.')).'</th>
					</tr>
				</thead>
				<tbody>';
                $eqt   = EquipmentFactory::AllTypes();
                $eqt[] =  array('id' => -1, 'new' => true, 'name' => '', 'input' => '0', 'max_km' => '1000', 'max_time' => '0');  
		foreach ($eqt as $Data) {
			$id     = $Data['id'];
			$delete = (isset($Data['new'])) ? Icon::$ADD_SMALL : '<input type="checkbox" name="equipmenttype[delete]['.$id.']">';

			$Code .= '
					<tr class="'.(isset($Data['new']) ? ' unimportant' : '').'">
						<td><input type="text" size="30" name="equipmenttype[name]['.$id.']" value="'.$Data['name'].'"></td>
                                             <td><select name="equipmenttype[input]['.$id.']" value="'.$Data['input'].'">
                                             <option value="'.EquipmentFactory::TYPE_INPUT_SINGLE.'" '.HTML::Selected($Data['input'] == EquipmentFactory::TYPE_INPUT_SINGLE).'>Single choice</option>
                                             <option value="'.EquipmentFactory::TYPE_INPUT_SINGLE.'" '.HTML::Selected($Data['input'] == EquipmentFactory::TYPE_INPUT_CHOICE).'>Multiple Choice</option>
                                             </select></td>';

                                                    
			$Code .= '              <td><input type="text" size="3" name="equipmenttype[max_km]['.$id.']" value="'.$Data['max_km'].'"></td>
                                                <td><input type="text" size="3" name="equipmenttype[max_time]['.$id.']" value="'.$Data['max_time'].'"></td>
                                                <td><select name="type[sportid]['.$id.']"><option value="all">'.__('all').'</option>';
                        $Sports = SportFactory::AllSports();
			foreach ($Sports as $SData) {
				$Code .= '<option value="'.$SData['id'].'"'.HTML::Selected($SData['id'] == $Data['sportid']).'>'.$SData['name'].'</option>';
                        }

			$Code .= '</select></td>
						<td>'.$delete.'</td>
					</tr>';
		}
                
                $Code .= '
				</tbody>
			</table><input type="submit" name="submit" value="Save">';
                return $Code;
        }
        
        private function getEquipmentCode() {    
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
                                                <th>'.Ajax::tooltip(Icon::$CROSS_SMALL, __('A equipment can only be deleted if no references exist.')).'</th>
					</tr>
				</thead>
				<tbody>';
                $eq   = EquipmentFactory::AllEquipment();
                $eq[] =  array('id' => -1, 'new' => true, 'name' => '', 'typeid' => '0', 'notes' => '', 'additional_km' => '0', 'date_start' => '', 'date_end' => '');  
                $eqt   = EquipmentFactory::AllTypes();
		foreach ($eq as $Data) {
			$id     = $Data['id'];
			$delete = (isset($Data['new'])) ? Icon::$ADD_SMALL : '<input type="checkbox" name="equipment[delete]['.$id.']">';

			$Code .= '
					<tr class="'.(isset($Data['new']) ? ' unimportant' : '').'">
						<td><input type="text" size="30" name="equipment[name]['.$id.']" value="'.$Data['name'].'"></td>
						<td><select name="equipment[typeid]['.$id.']">';
                        foreach ($eqt as $type) {
				$Code .= '<option value="'.$type['id'].'"'.HTML::Selected($type['id'] == $Data['typeid']).'>'.$type['name'].'</option>';
                        }
			$Code .=		'</select></td><td><input type="text" size="4" name="equipment[additional_km]['.$id.']" value="'.$Data['additional_km'].'"></td>
                                                <td><input type="text" class="pick-a-date" size="7" name="equipment[date_start]['.$id.']" value="'.$Data['date_start'].'"></td>
                                                <td><input type="text" class="pick-a-date" size="7" name="equipment[date_end]['.$id.']" value="'.$Data['date_end'].'"></td>
                                                <td><input type="text" size="4" name="equipment[notes]['.$id.']" value="'.$Data['notes'].'"></td>
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

		$Equipment   = EquipmentFactory::AllEquipment();
		$Equipment[] = array('id' => -1);
		

		foreach ($Equipment as $Data) {
			$id      = $Data['id'];
			$columns = array(
				'name',
                                'additional_km',
                                'date_start',
                                'date_end',
                                'notes'
			);
			$values  = array(
				$_POST['equipment']['name'][$id],
                                $_POST['equipment']['additional_km'][$id],
                                $_POST['equipment']['date_start'][$id],
                                $_POST['equipment']['date_end'][$id],
                                $_POST['equipment']['notes'][$id],
			);

			if (isset($_POST['equipment']['delete'][$id]))
				DB::getInstance()->deleteByID('equipment', (int)$Data['id']);
			elseif ($Data['id'] != -1)
				DB::getInstance()->update('equipment', $Data['id'], $columns, $values);
			elseif (strlen($_POST['equipment']['name'][$id]) > 2)
				DB::getInstance()->insert('equipment', $columns, $values);
		}

		EquipmentFactory::reInitAllEquipment();

            	$EquipmentType   = EquipmentFactory::AllTypes();
		$EquipmentType[] = array('id' => -1);
		

		foreach ($EquipmentType as $Data) {
			$id      = $Data['id'];
			$columns = array(
				'name',
                                'input',
                                'max_km',
                                'max_time'
			);
			$values  = array(
				$_POST['equipmenttype']['name'][$id],
                                $_POST['equipmenttype']['input'][$id],
                                $_POST['equipmenttype']['max_km'][$id],
                                $_POST['equipmenttype']['max_time'][$id]
			);

			if (isset($_POST['equipmenttype']['delete'][$id]))
				DB::getInstance()->deleteByID('equipment_type', (int)$Data['id']);
			elseif ($Data['id'] != -1)
				DB::getInstance()->update('equipment_type', $Data['id'], $columns, $values);
			elseif (strlen($_POST['equipmenttype']['name'][$id]) > 2)
				DB::getInstance()->insert('equipment_type', $columns, $values);
		}

		EquipmentFactory::reInitAllTypes();    
	}
}