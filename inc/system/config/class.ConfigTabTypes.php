<?php
/**
 * This file contains class::ConfigTabTypes
 * @package Runalyze\System\Config
 */

use Runalyze\Configuration;

/**
 * ConfigTabTypes
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
 */
class ConfigTabTypes extends ConfigTab {
	/**
	 * Set key and title for form 
	 */
	protected function setKeyAndTitle() {
		$this->key = 'config_tab_types';
		$this->title = __('Activity Types');
	}

	/**
	 * Set all fieldsets and fields
	 */
	public function setFieldsetsAndFields() {
		$Types = new FormularFieldset( __('Activity types') );
		$Types->setHtmlCode($this->getCode());
		$Types->addInfo( __('Activity types are useful to seperate your training into different categories. '.
							'An activity type can only belong to one sport.') );
		$Types->addInfo( __('Finding your personal bests requires one type (for running) to be set as the \'Race\'-type.') );

		$this->Formular->addFieldset($Types);
	}

	/**
	 * Get code
	 * @return string 
	 */
	private function getCode() {
		$Code = '
			<table class="fullwidth zebra-style c">
				<thead>
					<tr class="b">
						<th>'.__('Name').'</th>
						<th>'.__('Abbreviation').'</th>
						<th>'.Ajax::tooltip( __('Sport'), __('A type can only belong to one sport.')).'</th>
						<th>'.Ajax::tooltip('&Oslash;&nbsp;'.__('HR'), __('Average heart rate (used for calculation of TRIMP)')).'</th>
						<th>'.Ajax::tooltip( __('Quality?'), __('Quality sessions will be emphasized in your calendar.')).'</th>
						<th>'.Ajax::tooltip( __('Race'), __('You need to set one type for running as race type.')).'</th>
						<th>'.Ajax::tooltip(__('Calendar view'), __('Mode for displaying activities in calendar')).'</th>
						<th>'.Ajax::tooltip(Icon::$CROSS_SMALL, __('A type can only be deleted if no references exist.')).'</th>
					</tr>
				</thead>
				<tbody>';

		$Types   = DB::getInstance()->query('
			SELECT ty.id, ty.name, ty.abbr, ty.sportid, ty.short, ty.hr_avg, ty.quality_session, ty.accountid, (
				SELECT COUNT(*) 
				FROM `'.PREFIX.'training` tr
				WHERE tr.typeid = ty.id AND
					`accountid`="'.SessionAccountHandler::getId().'"
			) AS tcount
			FROM `'.PREFIX.'type` ty
			WHERE `accountid`="'.SessionAccountHandler::getId().'"
			ORDER BY `sportid` ASC, `tcount` DESC
		')->fetchAll();

		//TODO Change all locations where Typeid is used 
		$Types[] = array('id' => -1, 'sportid' => -1, 'name' => '', 'abbr' => '', 'short' => 0, 'hr_avg' => 120, 'quality_session' => 0);
		$raceID = Configuration::General()->competitionType();
		$sportid = false;

		foreach ($Types as $Data) {
			$id = $Data['id'];

			if ($id == -1)
				$delete = '';
			elseif ($Data['tcount'] == 0)
				$delete = '<input type="checkbox" name="type[delete]['.$id.']">';
			else
				$delete = SearchLink::to('typeid', $id, '<small>('.$Data['tcount'].')</small>');

			$Sports = SportFactory::AllSportsWithTypes();
			$ShortOptions = array(
				0 => __('complete row'),
				1 => __('only icon')
			);
	
			$Code .= '
				<tr class="'.($sportid !== false && $sportid != $Data['sportid'] ? 'top-separated-light' : '').($id == -1 ? ' unimportant' : '').'">
					<td><input type="text" size="20" name="type[name]['.$id.']" value="'.$Data['name'].'"></td>
					<td><input type="text" size="3" name="type[abbr]['.$id.']" value="'.$Data['abbr'].'"></td>
					<td><select name="type[sportid]['.$id.']">';
			foreach ($Sports as $SData)
				$Code .= '<option value="'.$SData['id'].'"'.HTML::Selected($SData['id'] == $Data['sportid']).'>'.$SData['name'].'</option>';

			$Code .= '</select></td>
					<td>
						<span class="input-with-unit">
							<input type="text" name="type[hr_avg]['.$id.']" value="'.$Data['hr_avg'].'" id="type_hr_avg_'.$id.'" class="small-size">
							<label for="type_hr_avg_'.$id.'" class="input-unit">bpm</label>
						</span>
					</td>
					<td><input type="checkbox" name="type[quality_session]['.$id.']"'.($Data['quality_session'] ? ' checked' : '').'></td>
					<td>'.($id == -1 ? '' : '<input type="radio" name="racetype" value="'.$id.'"'.($id == $raceID ? ' checked' : '').'>').'</td>
					<td>'.HTML::selectBox('type[short]['.$id.']', $ShortOptions, $Data['short']).'</td>
					<td>'.$delete.'</td>
				</tr>';

			$sportid = $Data['sportid'];
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
		$Types = DB::getInstance()->query('SELECT `id` FROM `'.PREFIX.'type` WHERE `accountid` = '.SessionAccountHandler::getId())->fetchAll();
		$Types[] = array('id' => -1);

		foreach ($Types as $Type) {
			$id  = $Type['id'];

			$columns = array(
				'name',
				'abbr',
				'sportid',
				'short',
				'hr_avg',
				'quality_session'
			);
			$values  = array(
				$_POST['type']['name'][$id],
				$_POST['type']['abbr'][$id],
				$_POST['type']['sportid'][$id],
				$_POST['type']['short'][$id],
				$_POST['type']['hr_avg'][$id],
				isset($_POST['type']['quality_session'][$id])
			);

			if (isset($_POST['type']['delete'][$id]))
				DB::getInstance()->deleteByID('type', (int)$id);
			elseif ($id != -1)
				DB::getInstance()->update('type', $id, $columns, $values);
			elseif (strlen($_POST['type']['name'][$id]) > 2)
				DB::getInstance()->insert('type', $columns, $values);
		}

		if (
			isset($_POST['type']['name'][$_POST['racetype']]) && !isset($_POST['type']['delete'][$_POST['racetype']]) &&
			$_POST['type']['sportid'][$_POST['racetype']] == Configuration::General()->runningSport() &&
			$_POST['racetype'] != Configuration::General()->competitionType()
		) {
			// TODO: this needs a recalculation of vdot factor
			Configuration::General()->updateCompetitionType($_POST['racetype']);
			Ajax::setReloadFlag(Ajax::$RELOAD_PLUGINS);
		}

		TypeFactory::reInitAllTypes();

		Ajax::setReloadFlag(Ajax::$RELOAD_DATABROWSER);
	}
}
