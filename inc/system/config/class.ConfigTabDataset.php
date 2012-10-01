<?php
/**
 * Class: ConfigTabDataset
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ConfigTabDataset extends ConfigTab {
	/**
	 * Set key and title for form 
	 */
	protected function setKeyAndTitle() {
		$this->key = 'config_tab_dataset';
		$this->title = 'Dataset';
	}

	/**
	 * Set all fieldsets and fields
	 */
	public function setFieldsetsAndFields() {
		$Dataset = new FormularFieldset('Dein Dataset');
		$Dataset->setHtmlCode($this->getCode());
		$Dataset->addInfo('Bei Runalyze kannst du selbst bestimmen, welche Daten du f&uuml;r Trainings
						speichern und anzeigen lassen m&ouml;chtest.');

		$this->Formular->addFieldset($Dataset);
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
						<th>'.Ajax::tooltip('Anzeige', 'Die Information wird in der Tabelle direkt angezeigt').'</th>
						<th>'.Ajax::tooltip('Zusammenfassung', 'Die Daten werden f&uuml;r die Zusammenfassung der Sportart angezeigt').'</th>
						<th style="width: 170px;" />
						<th>'.Ajax::tooltip('Reihenfolge', 'Gibt die Reihenfolge der Anzeige vor').'</th>
					</tr>
				</thead>
				<tbody>';

		$Dataset = Mysql::getInstance()->fetchAsArray('SELECT *, (`position` = 0) as `hidden` FROM `'.PREFIX.'dataset` ORDER BY `hidden` ASC, ABS(2.5-`modus`) ASC, `position` ASC');
		foreach ($Dataset as $i => $Data) {
			$disabled = ($Data['modus'] == 3) ? ' disabled="disabled"' : '';
			$checked_2 = ($Data['modus'] >= 2) ? ' checked="checked"' : '';
			$checked = ($Data['summary'] == 1) ? ' checked="checked"' : '';
			if ($Data['summary_mode'] == "YES" || $Data['summary_mode'] == "NO")
				$checked .= ' disabled="disabled"';

			$Code .= '
				<tr class="a'.($i%2+1).'">
					<td>
						<input type="hidden" name="'.$Data['id'].'_modus_3" value="'.$Data['modus'].'" />
						<input type="checkbox" name="'.$Data['id'].'_modus"'.$checked_2.$disabled.' />
					</td>
					<td><input type="checkbox" name="'.$Data['id'].'_summary"'.$checked.' /></td>
					<td>'.Ajax::tooltip($Data['label'], $Data['description']).'</td>
					<td><input type="text" name="'.$Data['id'].'_position" value="'.$Data['position'].'" size="2" /></td>
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
		$dataset = Mysql::getInstance()->fetchAsArray('SELECT `id` FROM `'.PREFIX.'dataset`');

		foreach ($dataset as $set) {
			$id = $set['id'];
			$modus = isset($_POST[$id.'_modus']) && $_POST[$id.'_modus'] == 'on' ? 2 : 1;
			if (isset($_POST[$id.'_modus_3']) && $_POST[$id.'_modus_3'] == 3)
				$modus = 3;

			$columns = array(
				'modus',
				'summary',
				'position');
			$values  = array(
				$modus,
				(isset($_POST[$id.'_summary']) && $_POST[$id.'_summary'] == 'on' ? 1 : 0),
				isset($_POST[$id.'_position']) ? $_POST[$id.'_position'] : 0);

			Mysql::getInstance()->update(PREFIX.'dataset', $id, $columns, $values);
		}

		Ajax::setReloadFlag(Ajax::$RELOAD_DATABROWSER);
	}
}