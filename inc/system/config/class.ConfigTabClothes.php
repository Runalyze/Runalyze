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
		$this->title = 'Kleidung';
	}

	/**
	 * Set all fieldsets and fields
	 */
	public function setFieldsetsAndFields() {
		$Clothes = new FormularFieldset('Deine Kleidung');
		$Clothes->setHtmlCode($this->getCode());
		$Clothes->addInfo('Die Kleidung kann wenn gew&uuml;nscht f&uuml;r weitere Statistiken bei jedem Training protokolliert werden.<br />
						Alle Kleidungsst&uuml;cke werden nach Kategorie geordnet mit der Abk&uuml;rzung im Formular angezeigt.');
		$Clothes->addInfo('Die Kategorie sollte eine Zahl sein und dient der Ordnung der Kleidungsst&uuml;cke.');
		$Clothes->addInfo('F&uuml;lle einfach die leere letzte Zeile aus, um ein neues Kleidungsst&uuml;ck hinzuzuf&uuml;gen.');

		$this->Formular->addFieldset($Clothes);
	}

	/**
	 * Get code
	 * @return string 
	 */
	private function getCode() {
		$Code = '
			<table class="c">
				<thead>
					<tr>
						<th>Name</th>
						<th>Abk&uuml;rzung</th>
						<th>Kategorie</th>
						<th>l&ouml;schen?</th>
					</tr>
				</thead>
				<tbody>';

		$Clothes   = ClothesFactory::OrderedClothes();
		$Clothes[] = array('new' => true, 'name' => '', 'short' => '', 'order' => '', 'id' => -1);

		foreach ($Clothes as $i => $Data) {
			$id     = $Data['id'];
			$delete = (isset($Data['new'])) ? Icon::$ADD_SMALL : '<input type="checkbox" name="clothes[delete]['.$id.']" />';

			$Code .= '
					<tr class="a'.($i%2+1).($delete == '' ? ' unimportant' : '').'">
						<td><input type="text" size="30" name="clothes[name]['.$id.']" value="'.$Data['name'].'" /></td>
						<td><input type="text" size="15" name="clothes[short]['.$id.']" value="'.$Data['short'].'" /></td>
						<td><input type="text" size="4" name="clothes[order]['.$id.']" value="'.$Data['order'].'" /></td>
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
				Mysql::getInstance()->delete(PREFIX.'clothes', (int)$Data['id']);
			elseif ($Data['id'] != -1)
				Mysql::getInstance()->update(PREFIX.'clothes', $Data['id'], $columns, $values);
			elseif (strlen($_POST['clothes']['name'][$id]) > 2)
				Mysql::getInstance()->insert(PREFIX.'clothes', $columns, $values);
		}
	}
}