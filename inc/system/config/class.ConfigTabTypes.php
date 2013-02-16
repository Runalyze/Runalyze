<?php
/**
 * Class: ConfigTabTypes
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ConfigTabTypes extends ConfigTab {
	/**
	 * Set key and title for form 
	 */
	protected function setKeyAndTitle() {
		$this->key = 'config_tab_types';
		$this->title = 'Trainingstypen';
	}

	/**
	 * Set all fieldsets and fields
	 */
	public function setFieldsetsAndFields() {
		$Types = new FormularFieldset('Deine Trainingstypen');
		$Types->setHtmlCode($this->getCode());
		$Types->addInfo('Mit Trainingstypen k&ouml;nnen die Trainings bequem in Kategorien sortiert werden,
						das dient vor allem der Trainingsanalyse.<br />
						Bestehende Trainingstypen k&ouml;nnen aber nur gel&ouml;scht werden, wenn keine Referenzen bestehen.
						Daher sind die Trainingstypen mit ihren Trainings verlinkt.');
		$Types->addInfo('Trainingstypen mit einem RPE-Wert gr&ouml;&szlig;er gleich 5 werden in der &Uuml;bersicht hervorgehoben.');

		$this->Formular->addFieldset($Types);
	}

	/**
	 * Get code
	 * @return string 
	 */
	private function getCode() {
		$Code = '
			<table class="c">
				<thead>
					<tr class="b">
						<th>Trainingstyp</th>
						<th>Abk&uuml;rzung</th>
						<th>'.Ajax::tooltip('RPE', 'Rating of Perceived Exertion (nach Borg) = durchschnittliche Anstrengung auf einer Skala von 1 (leicht) bis 10 (extrem hart)').'</th>
						<th>'.Ajax::tooltip('Splits', 'Es werden einzelne Kilometerabschnitte aufgezeichnet').'</th>
						<th>'.Ajax::tooltip('Sport', 'F&uuml;r welche Sportart gilt dieser Typ?').'</th>
						<th>'.Ajax::tooltip('l&ouml;schen?', 'Ein Trainingstyp kann nur gel&ouml;scht werden, wenn keine Referenzen bestehen').'</th>
					</tr>
				</thead>
				<tbody>';

		$Types   = Mysql::getInstance()->untouchedFetchArray('SELECT ty.id, ty.name, ty.abbr, ty.RPE, ty.splits, ty.sportid, ty.accountid, (SELECT COUNT(*) 
					FROM `'.PREFIX.'training` tr
					WHERE tr.typeid = ty.id AND
					`accountid`="'.SharedLinker::getUserId().'"
					) AS tcount
					FROM `'.PREFIX.'type` ty
					WHERE `accountid`="'.SharedLinker::getUserId().'"
					ORDER BY `id` ASC');
		//TODO Change all locations where Typeid is used 
		$Types[] = array('id' => -1, 'sportid' => -1, 'name' => '', 'abbr' => '', 'RPE' => 5, 'splits' => 0);

		foreach ($Types as $i => $Data) {
			$id     = $Data['id'];

			if ($id == -1)
				$delete = '';
			elseif ($Data['tcount'] == 0)
				$delete = '<input type="checkbox" name="type[delete]['.$id.']" />';
			else
				$delete = DataBrowser::getSearchLink('<small>('.$Data['tcount'].')</small>', 'opt[typeid]=is&val[typeid][0]='.$id);
			$Sports   = Sport::getSports();
			$Code .= '
				<tr class="a'.($i%2+1).($id == -1 ? ' unimportant' : '').'">
					<td><input type="text" size="20" name="type[name]['.$id.']" value="'.$Data['name'].'" /></td>
					<td><input type="text" size="3" name="type[abbr]['.$id.']" value="'.$Data['abbr'].'" /></td>
					<td><input type="text" size="1" name="type[RPE]['.$id.']" value="'.$Data['RPE'].'" /></td>
					<td><input type="checkbox" name="type[splits]['.$id.']" '.HTML::Checked($Data['splits'] == 1).'/></td>
					<td><select name="type[sportid]['.$id.']">';
					foreach ($Sports as $i => $SData) {
			$Code .= '<option value="'.$SData['id'].'"'.HTML::Selected($SData['id'] == $Data['sportid']).'>'.$SData['name'].'</option>';
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
	 * Parse all post values 
	 */
	public function parsePostData() {
		$Types = Mysql::getInstance()->fetchAsArray('SELECT `id` FROM `'.PREFIX.'type`');
		$Types[] = array('id' => -1);

		foreach ($Types as $i => $Type) {
			$id  = $Type['id'];
			$rpe = (int)$_POST['type']['RPE'][$id];

			if ($rpe < 1)
				$rpe = 1;
			elseif ($rpe > 10)
				$rpe = 10;

			$columns = array(
				'name',
				'abbr',
				'RPE',
				'splits',
				'sportid',
			);
			$values  = array(
				$_POST['type']['name'][$id],
				$_POST['type']['abbr'][$id],
				$rpe,
				isset($_POST['type']['splits'][$id]),
				$_POST['type']['sportid'][$id],
			);

			if (isset($_POST['type']['delete'][$id]))
				Mysql::getInstance()->delete(PREFIX.'type', (int)$id);
			elseif ($id != -1)
				Mysql::getInstance()->update(PREFIX.'type', $id, $columns, $values);
			elseif (strlen($_POST['type']['name'][$id]) > 2)
				Mysql::getInstance()->insert(PREFIX.'type', $columns, $values);
		}

		Ajax::setReloadFlag(Ajax::$RELOAD_DATABROWSER);
	}
}