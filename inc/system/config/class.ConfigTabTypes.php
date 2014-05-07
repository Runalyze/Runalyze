<?php
/**
 * This file contains class::ConfigTabTypes
 * @package Runalyze\System\Config
 */
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
		$Types = new FormularFieldset( __('Activity Types') );
		$Types->setHtmlCode($this->getCode());
		$Types->addInfo( __('Activity types are useful to seperate your training into different categories. '.
							'An activity type can only belong to one sport.') );
		$Types->addInfo( __('Types with a RPE-value &ge; 5 will be emphasized in the activity log.') );
		$Types->addInfo( __('One type (for running) has to be set as the \'Race\'-type in your configuration, to find your personal bests.') );

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
						<th>'.Ajax::tooltip(__('RPE'), __('Rating of Perceived Exertion (based on Borg): average exertion on a scale of 1 (easy) to 10 (extremely hard)')).'</th>
						<th>'.Ajax::tooltip( __('Sport'), __('A type can only belong to one sport.')).'</th>
						<th>'.Ajax::tooltip(Icon::$CROSS_SMALL, __('A type can only be deleted if no references exists.')).'</th>
					</tr>
				</thead>
				<tbody>';

		$Types   = DB::getInstance()->query('
			SELECT ty.id, ty.name, ty.abbr, ty.RPE, ty.sportid, ty.accountid, (
				SELECT COUNT(*) 
				FROM `'.PREFIX.'training` tr
				WHERE tr.typeid = ty.id AND
					`accountid`="'.SessionAccountHandler::getId().'"
			) AS tcount
			FROM `'.PREFIX.'type` ty
			WHERE `accountid`="'.SessionAccountHandler::getId().'"
			ORDER BY `id` ASC
		')->fetchAll();

		//TODO Change all locations where Typeid is used 
		$Types[] = array('id' => -1, 'sportid' => -1, 'name' => '', 'abbr' => '', 'RPE' => 5);

		foreach ($Types as $Data) {
			$id     = $Data['id'];

			if ($id == -1)
				$delete = '';
			elseif ($Data['tcount'] == 0)
				$delete = '<input type="checkbox" name="type[delete]['.$id.']">';
			else
				$delete = SearchLink::to('typeid', $id, '<small>('.$Data['tcount'].')</small>');

			$Sports = SportFactory::AllSportsWithTypes();
	
			$Code .= '
				<tr class="'.($id == -1 ? ' unimportant' : '').'">
					<td><input type="text" size="20" name="type[name]['.$id.']" value="'.$Data['name'].'"></td>
					<td><input type="text" size="3" name="type[abbr]['.$id.']" value="'.$Data['abbr'].'"></td>
					<td><input type="text" size="1" name="type[RPE]['.$id.']" value="'.$Data['RPE'].'"></td>
					<td><select name="type[sportid]['.$id.']">';
			foreach ($Sports as $SData)
				$Code .= '<option value="'.$SData['id'].'"'.HTML::Selected($SData['id'] == $Data['sportid']).'>'.$SData['name'].'</option>';

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
		$Types = DB::getInstance()->query('SELECT `id` FROM `'.PREFIX.'type`')->fetchAll();
		$Types[] = array('id' => -1);

		foreach ($Types as $Type) {
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
				'sportid',
			);
			$values  = array(
				$_POST['type']['name'][$id],
				$_POST['type']['abbr'][$id],
				$rpe,
				$_POST['type']['sportid'][$id],
			);

			if (isset($_POST['type']['delete'][$id]))
				DB::getInstance()->deleteByID('type', (int)$id);
			elseif ($id != -1)
				DB::getInstance()->update('type', $id, $columns, $values);
			elseif (strlen($_POST['type']['name'][$id]) > 2)
				DB::getInstance()->insert('type', $columns, $values);
		}

		Ajax::setReloadFlag(Ajax::$RELOAD_DATABROWSER);
	}
}