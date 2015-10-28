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
		$Code = HTML::info( __('<em>&Oslash; HF</em> is necessary for the calculation of TRIMP.') );

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
						<th>'.__('Icon').'</th>
						<th>'.Ajax::tooltip(__('Name'), __('Name of the sport')).'</th>
						<th>'.Ajax::tooltip(__('kcal/h'), __('Average energy turnover in kilocalories per hour')).'</th>
						<th>'.Ajax::tooltip('&Oslash;&nbsp;'.__('HR'), __('Average heart rate (used for calculation of TRIMP)')).'</th>
						<th>'.Ajax::tooltip(__('km'), __('Has a distance')).'</th>   
						<th>'.Ajax::tooltip(__('Unit'), __('Unit for speed')).'</th>
						<th>'.Ajax::tooltip(__('Power'), __('Power is recorded or calculated.')).'</th>
						<th>'.Ajax::tooltip(__('Outside'), __('Sport is performed outdoor: activate route, weather, ...')).'</th>
						<th>'.Ajax::tooltip(__('Calendar view'), __('Mode for displaying activities in calendar')).'</th>
						<th>'.Ajax::tooltip(Icon::$CROSS_SMALL, __('A sport can only be deleted if no references exist.')).'</th>
					</tr>
				</thead>
				<tbody>';

		$Sports   = SportFactory::AllSports();
		$SportCount = SportFactory::CountArray();
		foreach($SportCount as $is => $SC) {
			if (isset($Sports[$is])) {
				$Sports[$is]['counts'] = $SC;
			}
		}

		usort($Sports, function($a, $b) {
			return (!isset($b['counts']) || (isset($a['counts']) && ((int)$a['counts'] > (int)$b['counts']))) ? -1 : 1;
		});

		$Sports[] = array('id' => -1, 'new' => true, 'name' => '', 'img' => 'unknown.gif', 'short' => 0, 'kcal' => '', 'HFavg' => '', 'distances' => 0, 'speed' => Pace::STANDARD, 'power' => 0, 'outside' => '');

		$IconOptions = SportFactory::getIconOptions();
		$PaceOptions = Pace::options();
		$ShortOptions = array(
			0 => __('complete row'),
			1 => __('only icon')
		);

		foreach ($Sports as $Data) {
			$id         = $Data['id'];
			$isRunning  = ($id == Runalyze\Configuration::General()->runningSport());
			$iconSelect = HTML::selectBox('sport[img]['.$id.']', $IconOptions, $Data['img'], '', 'fip-select');

			if ($id == -1)
				$delete = '';
			elseif (!isset($SportCount[$id]) || $SportCount[$id] == 0)
				$delete = '<input type="checkbox" name="sport[delete]['.$id.']">';
			else
				$delete = SearchLink::to('sportid', $id, '<small>('.$SportCount[$id].')</small>');

			$Code .= '
					<tr class="'.(isset($Data['new']) ? ' unimportant' : '').'">
						<td>'.$iconSelect.'</td>
						<td>'.($isRunning ? '<input type="hidden" name="sport[name]['.$id.']" value="'.$Data['name'].'">'.$Data['name'] : '<input type="text" name="sport[name]['.$id.']" value="'.$Data['name'].'">').'</td>
						<td><input type="text" size="3" name="sport[kcal]['.$id.']" value="'.$Data['kcal'].'"></td>
						<td><input type="text" size="3" name="sport[HFavg]['.$id.']" value="'.$Data['HFavg'].'"></td>
						<td><input type="checkbox" name="sport[distances]['.$id.']"'.($Data['distances'] == 1 ? ' checked' : '').'></td>
						<td>'.HTML::selectBox('sport[speed]['.$id.']', $PaceOptions, $Data['speed']).'</td>
						<td><input type="checkbox" name="sport[power]['.$id.']"'.($Data['power'] == 1 ? ' checked' : '').'></td>
						<td><input type="checkbox" name="sport[outside]['.$id.']"'.($Data['outside'] == 1 ? ' checked' : '').'></td>
						<td>'.($isRunning ? '<input type="hidden" name="sport[short]['.$id.']" value="0">-' : HTML::selectBox('sport[short]['.$id.']', $ShortOptions, $Data['short'])).'</td>
						<td>'.($isRunning ? '-' : $delete).'</td>
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
				'distances',
				'speed',
				'power',
				'outside',
			);

			$values  = array(
				$_POST['sport']['name'][$id],
				$_POST['sport']['img'][$id],
				$_POST['sport']['short'][$id],
				$_POST['sport']['kcal'][$id],
				$_POST['sport']['HFavg'][$id],
				isset($_POST['sport']['distances'][$id]),
				$_POST['sport']['speed'][$id],
				isset($_POST['sport']['power'][$id]),
				isset($_POST['sport']['outside'][$id]),
			);

			if (isset($_POST['sport']['delete'][$id]) && $id != Runalyze\Configuration::General()->runningSport())
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
