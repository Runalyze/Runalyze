<?php
/**
 * This file contains class::ConfigTabSports
 * @package Runalyze\System\Config
 */
/**
 * ConfigTabSports
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
 */
class ConfigTabSports extends ConfigTab {
	/**
	 * Set key and title for form 
	 */
	protected function setKeyAndTitle() {
		$this->key = 'config_tab_sports';
		$this->title = __('Sport Types');
	}

	/**
	 * Set all fieldsets and fields
	 */
	public function setFieldsetsAndFields() {
		$Sports = new FormularFieldset(__('Your Sport Types'));
		$Sports->setHtmlCode($this->getCode().$this->getInfoFieldsAfterCode());
		$Sports->addInfo(__('Hover over the headings if the abbreviations are unclear.'));

		$this->Formular->addFieldset($Sports);
	}

	/**
	 * Get info fields
	 * @return string
	 */
	private function getInfoFieldsAfterCode() {
		$Code  = HTML::info(__('The mode <em>short</em> has the effect that in the week view is only shown a symbol and no workout data for a workout.'));
		$Code .= HTML::info(__('The values for  <em>&Oslash; HF</em> and <em>RPE</em> are necessary for the TRIMP-calculation.'));
		$Code .= HTML::info(__('The following icons are available:') .$this->getSportIconList());

		return $Code;
	}

	/**
	 * Get list with sport icons
	 * @return array
	 */
	private function getSportIconList() {
		$Icons       = array();
		$IconOptions = SportFactory::getIconOptions();

		foreach ($IconOptions as $gif)
			$Icons[] = Icon::getSportIconForGif($gif);

		return implode('&nbsp;', $Icons);
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
						<th class="small">'.Ajax::tooltip(__('short'), __('There will be only shown a symbol before the given day.')).'</th>
						<th class="small" colspan="2">'.__('Image').'</th>
						<th>'.Ajax::tooltip(__('Sport type'), __('Name of the sport type')).'</th>
						<th>'.Ajax::tooltip(__('kcal/h'), __('Average energy turnover in  in kilocalories per hour')).'</th>
						<th>'.Ajax::tooltip('&Oslash;&nbsp;'.__('HF'), __('The average heart rate (for example use for TRIMP calculation)')).'</th>
						<th>'.Ajax::tooltip(__('RPE'), __('Rating of Perceived Exertion (based on Borg) = average exertion on a scale of 1 (easy) to 10 (extremely hard)')).'</th>
						<th>'.Ajax::tooltip(__('km'), __('Has a distance')).'</th>
						<th>'.Ajax::tooltip(__('Unit'), __('Unit for the speed')).'</th>
						<th>'.Ajax::tooltip(__('Types'), __('The sport type has different training types')).'</th>
						<th>'.Ajax::tooltip(__('Pulse'), __('The heart rate is recorded.')).'</th>
						<th>'.Ajax::tooltip(__('Power'), __('The power is recorded or calculated.')).'</th>
						<th>'.Ajax::tooltip(__('Outside'), __('The sport type is done in the open air (Route/Weather)')).'</th>
						<th>'.Ajax::tooltip(Icon::$CROSS_SMALL, __('A sport type can only be deleted no references exists.')).'</th>
					</tr>
				</thead>
				<tbody>';

		$Sports   = SportFactory::AllSports();
		$Sports[] = array('id' => -1, 'new' => true, 'img' => 'unknown.gif', 'short' => 0, 'kcal' => '', 'HFavg' => '', 'RPE' => '', 'distances' => 0, 'speed' => SportSpeed::$DEFAULT, 'types' => 0, 'pulse' => 0, 'power' => 0, 'outside' => '');
		$SportCount = SportFactory::CountArray();
		foreach($SportCount as $is => $SC)
			$Sports[$is]['counts'] = $SC;

		$IconOptions = SportFactory::getIconOptions();

		foreach ($Sports as $i => $Data) {
			$id         = $Data['id'];
			$icon       = Icon::getSportIcon($id, $Data['img']);
			$iconSelect = HTML::selectBox('sport[img]['.$id.']', $IconOptions, $Data['img']);
			if (isset($Data['new'])) {
				$name = '<input type="text" name="sport[name]['.$id.']" value="">';
			} else {
				$name = '<input type="hidden" name="sport[name]['.$id.']" value="'.$Data['name'].'">'.$Data['name'];
			}
			
			
			if ($id == -1)
				$delete = '';
			elseif (!isset($SportCount[$id]) || $SportCount[$id] == 0)
				$delete = '<input type="checkbox" name="sport[delete]['.$id.']">';
			else
				$delete = SearchLink::to('sportid', $id, '<small>('.$SportCount[$id].')</small>');

			$Code .= '
					<tr class="'.(isset($Data['new']) ? ' unimportant' : '').'">
						<td><input type="checkbox" name="sport[short]['.$id.']"'.($Data['short'] == 1 ? ' checked' : '').'></td>
						<td>'.$icon.'</td>
						<td>'.$iconSelect.'</td>
						<td>'.$name.'</td>
						<td><input type="text" size="3" name="sport[kcal]['.$id.']" value="'.$Data['kcal'].'"></td>
						<td><input type="text" size="3" name="sport[HFavg]['.$id.']" value="'.$Data['HFavg'].'"></td>
						<td><input type="text" size="1" name="sport[RPE]['.$id.']" value="'.$Data['RPE'].'"></td>
						<td><input type="checkbox" name="sport[distances]['.$id.']"'.($Data['distances'] == 1 ? ' checked' : '').'></td>
						<td>'.SportSpeed::getSelectBox($Data['speed'], 'sport[speed]['.$id.']').'</td>
						<td><input type="checkbox" name="sport[types]['.$id.']"'.($Data['types'] == 1 ? ' checked' : '').'></td>
						<td><input type="checkbox" name="sport[pulse]['.$id.']"'.($Data['pulse'] == 1 ? ' checked' : '').'></td>
						<td><input type="checkbox" name="sport[power]['.$id.']"'.($Data['power'] == 1 ? ' checked' : '').'></td>
						<td><input type="checkbox" name="sport[outside]['.$id.']"'.($Data['outside'] == 1 ? ' checked' : '').'></td>
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
		$Sports   = SportFactory::AllSports();
		$Sports[] = array('id' => -1);

		foreach ($Sports as $Data) {
			$id = $Data['id'];

			$columns = array(
				'name',
				'img',
				'short',
				'kcal',
				'HFavg',
				'RPE',
				'distances',
				'speed',
				'types',
				'pulse',
				'power',
				'outside',
			);

			$values  = array(
				$_POST['sport']['name'][$id],
				$_POST['sport']['img'][$id],
				isset($_POST['sport']['short'][$id]),
				$_POST['sport']['kcal'][$id],
				$_POST['sport']['HFavg'][$id],
				$_POST['sport']['RPE'][$id],
				isset($_POST['sport']['distances'][$id]),
				$_POST['sport']['speed'][$id],
				isset($_POST['sport']['types'][$id]),
				isset($_POST['sport']['pulse'][$id]),
				isset($_POST['sport']['power'][$id]),
				isset($_POST['sport']['outside'][$id]),
			);

			if (isset($_POST['sport']['delete'][$id]))
				DB::getInstance()->deleteByID('sport', (int)$id);	
			elseif ($Data['id'] != -1)
				DB::getInstance()->update('sport', $id, $columns, $values);
			elseif (strlen($_POST['sport']['name'][$id]) > 2)
				Db::getInstance()->insert('sport', $columns, $values);
		}

		SportFactory::reInitAllSports();

		Ajax::setReloadFlag(Ajax::$RELOAD_DATABROWSER);
	}
}