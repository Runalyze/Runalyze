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
						<th>'.__('Default max. km').'</th>
						<th>'.__('Default max. Time').'</th>
                                                <th>'.__('Sports').'</th>    
					</tr>
				</thead>
				<tbody>';
                $Code .= '
				</tbody>
			</table>';
                return $Code;
        }
        
        private function getEquipmentCode() {    
		$Code = '
			<table class="fullwidth zebra-style c">
				<thead>
					<tr>
						<th>'.__('Name').'</th>
						<th>'.__('Equipment Type').'</th>
						<th>'.__('prev. distance').'</th>
						<th>'.__('Start of use').'</th>
                                                <th>'.__('End of use').'</th>
					</tr>
				</thead>
				<tbody>';
                $Code .= '
				</tbody>
			</table>';
                return $Code;
        }


	/**
	 * Parse all post values 
	 */
	public function parsePostData() {
		echo "all I heard was nothing";
	}
}