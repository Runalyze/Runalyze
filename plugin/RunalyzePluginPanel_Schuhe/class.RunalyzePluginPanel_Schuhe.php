<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Schuhe".
 * @package Runalyze\Plugins\Panels
 */
$PLUGINKEY = 'RunalyzePluginPanel_Schuhe';
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
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$PANEL;
		$this->name = 'Schuhe';
		$this->description = 'Anzeige der gelaufenen Kilometer aller Schuhe.';
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p('Mit diesem Plugin k&ouml;nnen die eigenen Laufschuhen mit all ihren Kilometern protkolliert werden.');
		echo HTML::p('Im Panel werden alle aktuellen Laufschuhe mit ihrem derzeitigen Kilometerstand dargestellt.
					Au&szlig;erdem kann aber auch ein Extrafenster mit einer ausf&uuml;hrlichen Tabelle ge&ouml;ffnen werden.');
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();

		return $config;
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		$Links = '';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key.'/window.schuhe.php" '.Ajax::tooltip('', 'Laufschuh hinzuf&uuml;gen', true, true).'>'.Icon::$ADD.'</a>').'</li>';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key.'/window.schuhe.table.php" '.Ajax::tooltip('', 'Schuhe in Tabelle anzeigen', true, true).'>'.Icon::$TABLE.'</a>').'</li>';

		return '<ul>'.$Links.'</ul>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		echo $this->getStyle();
		echo '<div id="schuhe">';

		$inuse = true;
		$schuhe = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'shoe` ORDER BY `inuse` DESC, `km` DESC')->fetchAll();
		foreach ($schuhe as $schuh) {
			$Shoe = new Shoe($schuh);

			if ($inuse && $Shoe->isInUse() == 0) {
				echo '<div id="hiddenschuhe" style="display:none;">'.NL;
				$inuse = false;
			}

			echo '
			<p style="position:relative;">
				<span class="right">'.$Shoe->getKmString().'</span>
				<strong>'.ShoeFactory::getSearchLink($schuh['id']).'</strong>
				'.$this->getShoeUsageImage($Shoe->getKm()).'
			</p>';
		}

		if (empty($schuhe))
			echo HTML::em('Du hast noch keine Schuhe eingetragen.');

		if (!$inuse)
			echo '</div>';
		echo '</div>';

		if (!$inuse)
			echo Ajax::toggle('<a class="right" href="#schuhe" name="schuhe">Alte Schuhe anzeigen</a>', 'hiddenschuhe');

		echo HTML::clearBreak();
	}

	/**
	 * Get style
	 * @return string
	 */
	protected function getStyle() {
		return '<style type="text/css">.shoe-usage { position: absolute; bottom: 0; left: 0; background-image:url(plugin/'.$this->key.'/schuhbalken.png); background-position:left center; height: 2px; max-width: 100%; }</style>';
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

		echo '
		<table id="list-of-all-shoes" class="fullwidth zebra-style">
			<thead>
				<tr>
					<th class="{sorter: \'x\'} small">x-mal</th>
					<th class="{sorter: false}"></th>
					<th>Name</th>
					<th class="{sorter: \'germandate\'} small">seit</th>
					<th class="{sorter: \'distance\'}">&Oslash; km</th>
					<th>&Oslash; Pace</th>
					<th class="{sorter: \'distance\'} small"><small>max.</small> km</th>
					<th class="small"><small>min.</small> Pace</th>
					<th class="{sorter: \'resulttime\'}">Dauer</th>
					<th class="{sorter: \'distance\'}">Distanz</th>
				</tr>
			</thead>
			<tbody>';

		if (!empty($this->schuhe)) {
			foreach ($this->schuhe as $schuh) {
				$Shoe   = new Shoe($schuh);
				$in_use = $Shoe->isInUse() ? '' : ' unimportant';

				echo('
				<tr class="'.$in_use.' r" style="position: relative">
					<td class="small">'.$schuh['num'].'x</td>
					<td>'.$this->editLinkFor($schuh['id']).'</td>
					<td class="b l">'.ShoeFactory::getSearchLink($schuh['id']).'</td>
					<td class="small">'.$Shoe->getSince().'</td>
					<td>'.(($schuh['num'] != 0) ? Running::Km($Shoe->getKmInDatabase()/$schuh['num']) : '-').'</td>
					<td>'.(($schuh['num'] != 0) ? SportSpeed::minPerKm($Shoe->getKmInDatabase(), $Shoe->getTime()) : '-').'</td>
					<td class="small">'.Running::Km($schuh['dist']).'</td>
					<td class="small">'.  SportSpeed::minPerKm(1, $schuh['pace_in_s']).'/km'.'</td>
					<td>'.$Shoe->getTimeString().'</td>
					<td>'.$Shoe->getKmString().'</td>
				</tr>');
			}
		} else {
			echo('<tr><td colspan="9">Keine Schuhe vorhanden.</td></tr>');
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
		return Ajax::window('<a href="plugin/'.$this->get('key').'/window.schuhe.table.php">'.Icon::$TABLE.' Alle Laufschuhe anzeigen</a>');
	}

	/**
	 * Add link
	 * @return string
	 */
	public function addLink() {
		return Ajax::window('<a href="plugin/'.$this->get('key').'/window.schuhe.php">'.Icon::$ADD.' Einen neuen Schuh hinzuf&uuml;gen</a>');
	}

	/**
	 * Initialize internal data
	 */
	private function initTableData() {
		$this->schuhe   = array();
		$ShoeStatistics = array();
		$AllShoeStatistics = DB::getInstance()->query('
			SELECT
				shoeid,
				COUNT(*) as num,
				MIN(s/distance) as pace_in_s,
				MAX(distance) as dist
			FROM '.PREFIX.'training
			WHERE shoeid != 0
			GROUP BY shoeid
		')->fetchAll();

		foreach ($AllShoeStatistics as $Statistic)
			$ShoeStatistics[$Statistic['shoeid']] = $Statistic;

		$AllShoes = DB::getInstance()->query('
			SELECT
				*
			FROM '.PREFIX.'shoe
			ORDER BY inuse DESC, km DESC
		')->fetchAll();

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
		return Ajax::window('<a href="plugin/'.$this->key.'/window.schuhe.php?id='.$id.'">'.Ajax::tooltip(Icon::$EDIT, 'Bearbeiten').'</a>');
	}
}