<?php
/**
 * Class: ConfigTabSports
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ConfigTabSports extends ConfigTab {
	/**
	 * Set key and title for form 
	 */
	protected function setKeyAndTitle() {
		$this->key = 'config_tab_sports';
		$this->title = 'Sportarten';
	}

	/**
	 * Set all fieldsets and fields
	 */
	public function setFieldsetsAndFields() {
		$Sports = new FormularFieldset('Deine Sportarten');
		$Sports->setHtmlCode($this->getCode());
		$Sports->addInfo('Fahre mit der Maus &uuml;ber die &Uuml;berschrift, falls dir die Bezeichnungen unklar sind.');

		$this->Formular->addFieldset($Sports);
	}

	/**
	 * Get code
	 * @return string 
	 */
	private function getCode() {
		$Code = '
			<table class="c" style="width:100%;">
				<thead>
					<tr class="b">
						<th class="small">'.Ajax::tooltip('Aktiv', 'Diese Sportart wird verwendet').'</th>
						<th class="small">'.Ajax::tooltip('kurz', 'Es wird nur ein Symbol vor dem jeweiligen Tag angezeigt').'</th>
						<th colspan="2">'.Ajax::tooltip('Sportart', 'Name der Sportart').'</th>
						<th>'.Ajax::tooltip('kcal/h', 'Durchschnittlicher Energieumsatz in Kilokalorien pro Stunde').'</th>
						<th>'.Ajax::tooltip('&Oslash; HF', 'Die durchschnittliche Herzfrequenz (wird z.B. f&uuml;r TRIMP verwendet)').'</th>
						<th>'.Ajax::tooltip('RPE', 'Rating of Perceived Exertion (nach Borg) = durchschnittliche Anstrengung auf einer Skala von 1 (leicht) bis 10 (extrem hart)').'</th>
						<th>'.Ajax::tooltip('km', 'Es wird eine Distanz zur&uuml;ckgelegt').'</th>
						<th>'.Ajax::tooltip('Einheit', 'Einheit f&uuml;r die Geschwindigkeit').'</th>
						<th>'.Ajax::tooltip('Typen', 'Es werden Trainingstypen wie Intervalltraining verwendet').'</th>
						<th>'.Ajax::tooltip('Puls', 'Der Puls wird dabei aufgezeichnet').'</th>
						<th>'.Ajax::tooltip('Drau&szlig;en', 'Der Sport wird an der freien Luft betrieben (Strecke/Wetter)').'</th>
						<th>'.Ajax::tooltip('l&ouml;schen?', 'Eine Sportart kann nur gel&ouml;scht werden, wenn keine Referenzen bestehen').'</th>
					</tr>
				</thead>
				<tbody>';

		$Sports   = Sport::getSports();
		$Sports[] = array('id' => -1, 'new' => true, 'online' => 1, 'short' => 0, 'kcal' => '', 'HFavg' => '', 'RPE' => '', 'distances' => 0, 'speed' => SportSpeed::$DEFAULT, 'types' => 0, 'pulse' => 0, 'outside' => '');
		$SportCount = Sport::getSportsCount();
		foreach($SportCount as $is => $SC)
			$Sports[$is]['counts'] = $SC;

		foreach ($Sports as $i => $Data) {
			$id     = $Data['id'];
			if (isset($Data['new'])) {
				$icon = '?';
				$name = '<input type="text" name="sport[name]['.$id.']" value="" />';
			} else {
				$icon = Icon::getSportIcon($id);
				$name = '<input type="hidden" name="sport[name]['.$id.']" value="'.$Data['name'].'" />'.$Data['name'];
			}
			
			
			if ($id == -1)
				$delete = '';
			elseif ($SportCount[$id] == 0)
				$delete = '<input type="checkbox" name="sport[delete]['.$id.']" />';
			else
				$delete = DataBrowser::getSearchLink('<small>('.$SportCount[$id].')</small>', 'opt[typeid]=is&val[sportid][0]='.$id);

			$Code .= '
					<tr class="a'.($i%2+1).($icon == '?' ? ' unimportant' : '').'">
						<td><input type="checkbox" name="sport[online]['.$id.']" '.($Data['online'] == 1 ? 'checked="checked" ' : '').'/></td>
						<td><input type="checkbox" name="sport[short]['.$id.']" '.($Data['short'] == 1 ? 'checked="checked" ' : '').'/></td>
						<td>'.$icon.'</td>
						<td>'.$name.'</td>
						<td><input type="text" size="3" name="sport[kcal]['.$id.']" value="'.$Data['kcal'].'" /></td>
						<td><input type="text" size="3" name="sport[HFavg]['.$id.']" value="'.$Data['HFavg'].'" /></td>
						<td><input type="text" size="1" name="sport[RPE]['.$id.']" value="'.$Data['RPE'].'" /></td>
						<td><input type="checkbox" name="sport[distances]['.$id.']" '.($Data['distances'] == 1 ? 'checked="checked" ' : '').'/></td>
						<td>'.SportSpeed::getSelectBox($Data['speed'], 'sport[speed]['.$id.']').'</td>
						<td><input type="checkbox" name="sport[types]['.$id.']" '.($Data['types'] == 1 ? 'checked="checked" ' : '').'/></td>
						<td><input type="checkbox" name="sport[pulse]['.$id.']" '.($Data['pulse'] == 1 ? 'checked="checked" ' : '').'/></td>
						<td><input type="checkbox" name="sport[outside]['.$id.']" '.($Data['outside'] == 1 ? 'checked="checked" ' : '').'/></td>
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
		$Sports   = Sport::getSports();
		$Sports[] = array('id' => -1);

		foreach ($Sports as $Data) {
			$id = $Data['id'];

			$columns = array(
				'name',
				'short',
				'online',
				'kcal',
				'HFavg',
				'RPE',
				'distances',
				'speed',
				'types',
				'pulse',
				'outside',
			);
			$values  = array(
				$_POST['sport']['name'][$id],
				isset($_POST['sport']['short'][$id]),
				isset($_POST['sport']['online'][$id]),
				$_POST['sport']['kcal'][$id],
				$_POST['sport']['HFavg'][$id],
				$_POST['sport']['RPE'][$id],
				isset($_POST['sport']['distances'][$id]),
				$_POST['sport']['speed'][$id],
				isset($_POST['sport']['types'][$id]),
				isset($_POST['sport']['pulse'][$id]),
				isset($_POST['sport']['outside'][$id]),
			);
			if (isset($_POST['sport']['delete'][$id]))
				Mysql::getInstance()->delete(PREFIX.'sport', (int)$id);	
			elseif ($Data['id'] != -1)
				Mysql::getInstance()->update(PREFIX.'sport', $id, $columns, $values);
			elseif (strlen($_POST['sport']['name'][$id]) > 2)
				Mysql::getInstance()->insert(PREFIX.'sport', $columns, $values);
		}

		Ajax::setReloadFlag(Ajax::$RELOAD_DATABROWSER);
	}
}