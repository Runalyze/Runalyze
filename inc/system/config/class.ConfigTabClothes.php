<?php
/**
 * This file contains class::ConfigTabClothes
 * @package Runalyze\System\Config
 */
/**
 * ConfigTabClothes
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
 */
class ConfigTabClothes extends ConfigTab {
	/**
	 * Set key and title for form 
	 */
	protected function setKeyAndTitle() {
		$this->key = 'config_tab_clothes';
		$this->title = __('Equipment');
	}

	/**
	 * Set all fieldsets and fields
	 */
	public function setFieldsetsAndFields() {
		$Clothes = new FormularFieldset(__('Your equipment'));
		$Clothes->setHtmlCode($this->getCode());
		$Clothes->addInfo( __('Equipment can be entered for additional statistics.<br>'.
							'Category and abbreviation are used to order and display them in the form for editing an activity.') );
		$Clothes->addInfo( __('The category should be a number.') );
		$Clothes->addInfo( __('Just fill out the last row to add a new equipment.') );

		$this->Formular->addFieldset($Clothes);
	}

	/**
	 * Get code
	 * @return string 
	 */
	private function getCode() {
		$Code = '
			<table class="fullwidth zebra-style c">
				<thead>
					<tr>
						<th>'.__('Name').'</th>
						<th>'.__('Abbreviation').'</th>
						<th>'.__('Category').'</th>
						<th>'.__('Delete?').'</th>
					</tr>
				</thead>
				<tbody>';

		$Clothes   = ClothesFactory::OrderedClothes();
		$Clothes[] = array('new' => true, 'name' => '', 'short' => '', 'order' => '', 'id' => -1);

		foreach ($Clothes as $Data) {
			$id     = $Data['id'];
			$delete = (isset($Data['new'])) ? Icon::$ADD_SMALL : '<input type="checkbox" name="clothes[delete]['.$id.']">';

			$Code .= '
					<tr class="'.($delete == '' ? ' unimportant' : '').'">
						<td><input type="text" size="30" name="clothes[name]['.$id.']" value="'.$Data['name'].'"></td>
						<td><input type="text" size="15" name="clothes[short]['.$id.']" value="'.$Data['short'].'"></td>
						<td><input type="text" size="4" name="clothes[order]['.$id.']" value="'.$Data['order'].'"></td>
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
		$Clothes   = ClothesFactory::OrderedClothes();
		$Clothes[] = array('id' => -1);
		

		foreach ($Clothes as $Data) {
			$id      = $Data['id'];
			$columns = array(
				'name',
				'short',
				'order',
			);
			$values  = array(
				$_POST['clothes']['name'][$id],
				$_POST['clothes']['short'][$id],
				$_POST['clothes']['order'][$id],
			);

			if (isset($_POST['clothes']['delete'][$id]))
				DB::getInstance()->deleteByID('clothes', (int)$Data['id']);
			elseif ($Data['id'] != -1)
				DB::getInstance()->update('clothes', $Data['id'], $columns, $values);
			elseif (strlen($_POST['clothes']['name'][$id]) > 2)
				DB::getInstance()->insert('clothes', $columns, $values);
		}
	}
}