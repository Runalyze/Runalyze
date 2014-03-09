<?php
/**
 * This file contains class::ConfigTabDataset
 * @package Runalyze\System\Config
 */
/**
 * ConfigTabDataset
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
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
			<table class="c fullwidth zebra-style" id="conf-tab-dataset">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th>'.Ajax::tooltip('Anzeige', 'Die Information wird in der Tabelle direkt angezeigt').'</th>
						<th colspan="2">'.Ajax::tooltip('Auswertung', 'Die Daten werden in der Auswertung der Sportart angezeigt').'</th>
						<th>'.Ajax::tooltip('Reihenfolge', 'Gibt die Reihenfolge der Anzeige vor').'</th>
						<th>'.Ajax::tooltip('CSS-Class', '\'c\': zentriert<br>\'l\': linksb&uuml;ndig<br>\'small\': klein<br>\'b\': fett').'</th>
						<th>'.Ajax::tooltip('CSS-Style', 'beliebige CSS-Anweisung').'</th>
						<th>Beispiel</th>
					</tr>
				</thead>
				<tbody>';

		$DatasetObject = new Dataset();
		$DatasetObject->setTrainingId(DataObject::$DEFAULT_ID, $this->getExampleTraining());

		$Dataset = DB::getInstance()->query('SELECT *, (`position` = 0) as `hidden` FROM `'.PREFIX.'dataset` ORDER BY `position` ASC')->fetchAll();
		foreach ($Dataset as $Data) {
			$disabled    = ($Data['modus'] == 3) ? ' disabled' : '';
			$checked_2   = ($Data['modus'] >= 2) ? ' checked' : '';
			$checked     = ($Data['summary'] == 1) ? ' checked' : '';
			$SummarySign = '';

			switch ($Data['summary_mode']) {
				case 'YES':
				case 'NO':
					$checked .= ' disabled';
					break;
				case 'AVG':
					$SummarySign = '&Oslash;';
					break;
				case 'SUM':
					$SummarySign = '&sum;';
					break;
				case 'MAX':
					$SummarySign = 'max';
					break;
			}

			$Example = $DatasetObject->getDataset($Data['name']);

			$Code .= '
				<tr class="r" id="'.$Data['id'].'_tr">
					<td class="l b">'.Ajax::tooltip($Data['label'], $Data['description']).'</td>
					<td class="c">
						<input type="hidden" name="'.$Data['id'].'_modus_3" value="'.$Data['modus'].'">
						<input type="checkbox" name="'.$Data['id'].'_modus"'.$checked_2.$disabled.'>
					</td>
					<td class="c"><input type="checkbox" name="'.$Data['id'].'_summary"'.$checked.'></td>
					<td class="c small">'.$SummarySign.'</td>
					<td class="c">
						<input class="dataset-position" type="text" name="'.$Data['id'].'_position" value="'.$Data['position'].'" size="2">
						'.($Data['position'] > 0 ? '
						<span class="link" onclick="datasetMove('.$Data['id'].', \'up\')">'.Icon::$UP.'</span>
						<span class="link" onclick="datasetMove('.$Data['id'].', \'down\')">'.Icon::$DOWN.'</span>
							' : '').'
					</td>
					<td class="c"><input type="text" name="'.$Data['id'].'_class" value="'.$Data['class'].'" size="7"></td>
					<td class="c"><input type="text" name="'.$Data['id'].'_style" value="'.$Data['style'].'" size="15"></td>
					<td class="'.$Data['class'].'" style="'.$Data['style'].'">'.$Example.'</td>
				</tr>';
		}

		$Code .= '
				</tbody>
			</table>';

		$Code .= Ajax::wrapJS('
			function datasetMove(id, way) {
				var pos = parseInt($("input[name=\'"+id+"_position\']").val()),
					tr = $("#"+id+"_tr");

				if (way == "up" && pos > 1) {
					$("#"+id+"_tr .dataset-position").val(pos-1);
					tr.prev().find(".dataset-position").val(pos);
					tr.prev().toggleClass("swapped");
					tr.prev().before(tr);
				} else if (way == "down" && tr.next().find(".dataset-position").val() > 0) {
					$("#"+id+"_tr .dataset-position").val(pos+1);
					tr.next().find(".dataset-position").val(pos);
					tr.next().toggleClass("swapped");
					tr.next().after(tr);
				}

				tr.toggleClass("swapped");
			}
		');

		return $Code;
	}

	/**
	 * Parse all post values 
	 */
	public function parsePostData() {
		$dataset = DB::getInstance()->query('SELECT `id` FROM `'.PREFIX.'dataset`')->fetchAll();

		foreach ($dataset as $set) {
			$id = $set['id'];
			$modus = isset($_POST[$id.'_modus']) && $_POST[$id.'_modus'] == 'on' ? 2 : 1;
			if (isset($_POST[$id.'_modus_3']) && $_POST[$id.'_modus_3'] == 3)
				$modus = 3;

			$columns = array(
				'modus',
				'summary',
				'position',
				'style',
				'class'
			);
			$values  = array(
				$modus,
				(isset($_POST[$id.'_summary']) && $_POST[$id.'_summary'] == 'on' ? 1 : 0),
				isset($_POST[$id.'_position']) ? (int)$_POST[$id.'_position'] : '',
				isset($_POST[$id.'_style']) ? htmlentities($_POST[$id.'_style']) : '',
				isset($_POST[$id.'_class']) ? htmlentities($_POST[$id.'_class']) : ''
			);

			DB::getInstance()->update('dataset', $id, $columns, $values);
		}

		Ajax::setReloadFlag(Ajax::$RELOAD_DATABROWSER);
	}

	/**
	 * Get array for exemplary training data
	 * @return array 
	 */
	protected function getExampleTraining() {
		$ClothesID = $this->getRandIdFor('clothes');
		$ShoeID    = $this->getRandIdFor('shoe');

		$Data = array(
			'id'		=> DataObject::$DEFAULT_ID,
			'sportid'	=> CONF_RUNNINGSPORT,
			'typeid'	=> CONF_WK_TYPID,
			'time'		=> time(),
			'created'	=> time(),
			'edited'	=> time(),
			'is_public'	=> 1,
			'is_track'	=> 1,
			'distance'	=> 10,
			's'			=> 51*60+27,
			'pace'		=> '-:--',
			'elevation'	=> 57,
			'kcal'		=> 691,
			'pulse_avg'	=> 186,
			'pulse_max'	=> 193,
			'vdot_with_elevation'	=> VDOT_FORM + 1,
			'vdot'		=> VDOT_FORM + 1,
			'use_vdot'	=> 0,
			'jd_intensity'	=> 27,
			'trimp'		=> 121,
			'cadence'	=> 90,
			'power'		=> 520,
			'temperature'	=> 17,
			'weatherid'	=> 5,
			'route'		=> 'Sportplatz',
			'clothes'	=> $ClothesID,
			'splits'	=> '5|26:51-5|24:36',
			'comment'	=> 'Testtraining',
			'partner'	=> 'Achim',
			'abc'		=> 1,
			'shoeid'	=> $ShoeID,
			'notes'		=> 'Das war ein tolles Training.',
			'arr_time'	=> '',
			'arr_lat'	=> '',
			'arr_lon'	=> '',
			'arr_alt'	=> '',
			'arr_dist'	=> '',
			'arr_heart'	=> '',
			'arr_pace'	=> '',
			'arr_cadence'	=> '',
			'arr_power'	=> '',
			'arr_temperature'	=> '',
			'accountid'	=> SessionAccountHandler::getId(),
			'creator'	=> '',
			'creator_details'	=> '',
			'activity_id'	=> '',
			'elevation_corrected'	=> 1
		);

		return $Data;
	}

	/**
	 * Get random ID from database for a specific table
	 * @param string $table
	 * @return int 
	 */
	protected function getRandIdFor($table) {
		$Result = DB::getInstance()->query('SELECT id FROM `'.PREFIX.$table.'` WHERE `accountid`='.(int)SessionAccountHandler::getId().' LIMIT 1')->fetch();

		if (isset($Result['id']))
			return $Result['id'];

		return 0;
	}
}