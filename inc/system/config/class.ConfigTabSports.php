<?php
/**
 * This file contains class::ConfigTabSports
 * @package Runalyze\System\Config
 */

use Runalyze\Activity\Pace;

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
		$this->title = __('Sports');
	}

	/**
	 * Set all fieldsets and fields
	 */
	public function setFieldsetsAndFields() {
		$Sports = new FormularFieldset(__('Your sports'));
		$Sports->setHtmlCode($this->getCode().$this->getInfoFieldsAfterCode());
		$Sports->addInfo( __('Hover over the headings if the abbreviations are unclear.') );

		$this->Formular->addFieldset($Sports);
	}

	/**
	 * Get info fields
	 * @return string
	 */
	private function getInfoFieldsAfterCode() {
		$Code  = HTML::info( __('The mode <strong>short</strong> means: the activity log contains only a symbol and no other values for activities of this sport.<br>'.
								'This is useful if you are mainly interested in the fact of doing the sport, not in the details, e.g. for stretching.') );
		$Code .= HTML::info( __('The values for <em>&Oslash; HF</em> and <em>RPE</em> are necessary for the calculation of TRIMP.') );

		return $Code;
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
						<th class="small">'.Ajax::tooltip(__('short'), __('Show only a symbol.')).'</th>
						<th class="small" colspan="2">'.__('Icon').'</th>
						<th>'.Ajax::tooltip(__('Name'), __('Name of the sport')).'</th>
						<th>'.Ajax::tooltip(__('kcal/h'), __('Average energy turnover in kilocalories per hour')).'</th>
						<th>'.Ajax::tooltip('&Oslash;&nbsp;'.__('HR'), __('Average heart rate (used for calculation of TRIMP)')).'</th>
						<th>'.Ajax::tooltip(__('RPE'), __('Rating of perceived exertion (based on Borg): average exertion on a scale of 1 (easy) to 10 (extremely hard)')).'</th>
						<th>'.Ajax::tooltip(__('km'), __('Has a distance')).'</th>
						<th>'.Ajax::tooltip(__('Unit'), __('Unit for speed')).'</th>
						<th>'.Ajax::tooltip(__('Types'), __('The sport uses different training types')).'</th>
						<th>'.Ajax::tooltip(__('HR'), __('Heart rate is recorded.')).'</th>
						<th>'.Ajax::tooltip(__('Power'), __('Power is recorded or calculated.')).'</th>
						<th>'.Ajax::tooltip(__('Outside'), __('Sport is performed outdoor: activate route, weather, ...')).'</th>
						<th>'.Ajax::tooltip(Icon::$CROSS_SMALL, __('A sport can only be deleted if no references exist.')).'</th>
					</tr>
				</thead>
				<tbody>';

		$Sports   = SportFactory::AllSports();
		$Sports[] = array('id' => -1, 'new' => true, 'img' => 'unknown.gif', 'short' => 0, 'kcal' => '', 'HFavg' => '', 'RPE' => '', 'distances' => 0, 'speed' => Pace::STANDARD, 'types' => 0, 'pulse' => 0, 'power' => 0, 'outside' => '');
		$SportCount = SportFactory::CountArray();
		foreach($SportCount as $is => $SC)
			$Sports[$is]['counts'] = $SC;

		$IconOptions = SportFactory::getIconOptions();
		$PaceOptions = Pace::options();

		foreach ($Sports as $i => $Data) {
			$id         = $Data['id'];
			$icon       = Icon::getSportIcon($id, $Data['img']);
			$iconSelect = HTML::selectBox('sport[img]['.$id.']', $IconOptions, $Data['img'], '', 'fip-select');
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
						<td> </td>
						<td>'.$iconSelect.'</td>
						<td>'.$name.'</td>
						<td><input type="text" size="3" name="sport[kcal]['.$id.']" value="'.$Data['kcal'].'"></td>
						<td><input type="text" size="3" name="sport[HFavg]['.$id.']" value="'.$Data['HFavg'].'"></td>
						<td><input type="text" size="1" name="sport[RPE]['.$id.']" value="'.$Data['RPE'].'"></td>
						<td><input type="checkbox" name="sport[distances]['.$id.']"'.($Data['distances'] == 1 ? ' checked' : '').'></td>
						<td>'.HTML::selectBox('sport[speed]['.$id.']', $PaceOptions, $Data['speed']).'</td>
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
