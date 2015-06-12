<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Schuhe".
 * @package Runalyze\Plugins\Panels
 */
$PLUGINKEY = 'RunalyzePluginPanel_Schuhe';

use Runalyze\Activity\Distance;
use Runalyze\Activity\Pace;

/**
 * Class: RunalyzePluginPanel_Schuhe
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Panels
 */
class RunalyzePluginPanel_Schuhe extends PluginPanel {
	/**
	 * Internal array with all shoes from database and statistic values
	 * @var array 
	 */
	private $schuhe = null;

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Shoes');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Display statistics for your shoes.');
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		$Links = '';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.schuhe.php" '.Ajax::tooltip('', __('Add new shoe'), true, true).'>'.Icon::$ADD.'</a>').'</li>';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.schuhe.table.php" '.Ajax::tooltip('', __('Show all shoes'), true, true).'>'.Icon::$TABLE.'</a>').'</li>';

		return '<ul>'.$Links.'</ul>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		echo $this->getStyle();
		echo '<div id="schuhe">';

		// TODO: Use data from shoe factory
		$inuse = true;
		$schuhe = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'shoe` ORDER BY `inuse` DESC, `km` DESC')->fetchAll();
		foreach ($schuhe as $schuh) {
			$Shoe = new Shoe($schuh);

			if ($inuse && $Shoe->isInUse() == 0) {
				echo '<div id="hiddenschuhe" style="display:none;">';
				$inuse = false;
			}

			echo '<p style="position:relative;">
				<span class="right">'.$Shoe->getKmString().'</span>
				<strong>'.ShoeFactory::getSearchLink($schuh['id']).'</strong>
				'.$this->getShoeUsageImage($Shoe->getKm()).'
			</p>';
		}

		if (empty($schuhe))
			echo HTML::em( __('You don\'t have any shoes') );

		if (!$inuse)
			echo '</div>';
		echo '</div>';

		if (!$inuse)
			echo Ajax::toggle('<a class="right" href="#schuhe" name="schuhe">'.__('Show unused shoes').'</a>', 'hiddenschuhe');

		echo HTML::clearBreak();
	}

	/**
	 * Get style
	 * @return string
	 */
	protected function getStyle() {
		return '<style type="text/css">.shoe-usage { position: absolute; bottom: 0; left: 0; background-image:url(plugin/'.$this->key().'/schuhbalken.png); background-position:left center; height: 2px; max-width: 100%; }</style>';
	}

	/**
	 * Get shoe usage image
	 * @param float $km
	 * @return string
	 */
	protected function getShoeUsageImage($km) {
		return '<span class="shoe-usage" style="width:'.round(min(330,$km/4)).'px;"></span>';
	}

	/**
	 * Display table
	 */
	public function displayTable() {
		if (is_null($this->schuhe))
			$this->initTableData();

		echo '<table id="list-of-all-shoes" class="fullwidth zebra-style">
			<thead>
				<tr>
					<th class="{sorter: \'x\'} small">'.__('x-times').'</th>
					<th class="{sorter: false}"></th>
					<th>'.__('Name').'</th>
					<th class="{sorter: \'germandate\'} small">'.__('since').'</th>
					<th class="{sorter: \'distance\'}">&Oslash; km</th>
					<th>&Oslash; '.__('Pace').'</th>
					<th class="{sorter: \'distance\'} small"><small>'.__('max.').'</small> km</th>
					<th class="small"><small>'.__('min.').'</small> '.__('Pace').'</th>
					<th class="{sorter: \'resulttime\'}">'.__('Time').'</th>
					<th class="{sorter: \'distance\'}">'.__('Distance').'</th>
					<th>'.__('Weigth').'</th>
				</tr>
			</thead>
			<tbody>';

		if (!empty($this->schuhe)) {
			foreach ($this->schuhe as $schuh) {
				$Shoe   = new Shoe($schuh);
				$in_use = $Shoe->isInUse() ? '' : ' unimportant';

				$Pace = new Pace($Shoe->getTime(), $Shoe->getKmInDatabase());
				$MaxPace = new Pace($schuh['pace_in_s'], 1);

				echo '<tr class="'.$in_use.' r" style="position: relative">
					<td class="small">'.$schuh['num'].'x</td>
					<td>'.$this->editLinkFor($schuh['id']).'</td>
					<td class="b l">'.ShoeFactory::getSearchLink($schuh['id']).'</td>
					<td class="small">'.$Shoe->getSince().'</td>
					<td>'.(($schuh['num'] != 0) ? Distance::format($Shoe->getKmInDatabase()/$schuh['num']) : '-').'</td>
					<td>'.(($schuh['num'] != 0) ? $Pace->asMinPerKm().'/km' : '-').'</td>
					<td class="small">'.Distance::format($schuh['dist']).'</td>
					<td class="small">'.$MaxPace->asMinPerKm().'/km'.'</td>
					<td>'.$Shoe->getTimeString().'</td>
					<td>'.$Shoe->getKmString().'</td>
					<td class="small">'.$Shoe->getWeightString().'</td>
				</tr>';
			}
		} else {
			echo '<tr><td colspan="9">'.__('You don\'t have any shoes').'</td></tr>';
		}

		echo '</tbody>';
		echo '</table>';

		Ajax::createTablesorterFor("#list-of-all-shoes", true);
	}

	/**
	 * Table link
	 * @return string
	 */
	public function tableLink() {
		return Ajax::window('<a href="plugin/'.$this->key().'/window.schuhe.table.php">'.Icon::$TABLE.' '.__('Show all shoes').'</a>');
	}

	/**
	 * Add link
	 * @return string
	 */
	public function addLink() {
		return Ajax::window('<a href="plugin/'.$this->key().'/window.schuhe.php">'.Icon::$ADD.' '.__('Add a new shoe').'</a>');
	}

	/**
	 * Initialize internal data
	 */
	private function initTableData() {
		$this->schuhe   = array();
		$ShoeStatistics = array();
		$AllShoeStatistics = DB::getInstance()->query(
			'SELECT
				shoeid,
				COUNT(*) as num,
				MIN(s/distance) as pace_in_s,
				MAX(distance) as dist
			FROM '.PREFIX.'training
			WHERE shoeid != 0
			GROUP BY shoeid')->fetchAll();

		foreach ($AllShoeStatistics as $Statistic)
			$ShoeStatistics[$Statistic['shoeid']] = $Statistic;

		$AllShoes = DB::getInstance()->query(
			'SELECT
				*
			FROM '.PREFIX.'shoe
			ORDER BY inuse DESC, km DESC')->fetchAll();

		foreach ($AllShoes as $Shoe)
			if (isset($ShoeStatistics[$Shoe['id']]))
				$this->schuhe[] = array_merge($Shoe, $ShoeStatistics[$Shoe['id']]);
			else
				$this->schuhe[] = array_merge($Shoe, array('num' => 0, 'pace_in_s' => 0, 'dist' => 0));
	}

	/**
	 * Get link for editing a shoe
	 * @param int $id
	 * @return string
	 */
	private function editLinkFor($id) {
		return Ajax::window('<a href="plugin/'.$this->key().'/window.schuhe.php?id='.$id.'">'.Ajax::tooltip(Icon::$EDIT, __('Edit')).'</a>');
	}
}