<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Schuhe".
 */
$PLUGINKEY = 'RunalyzePluginPanel_Schuhe';
/**
 * Class: RunalyzePluginPanel_Schuhe
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
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
		$Links = array();
		$Links[] = Ajax::window('<a href="plugin/'.$this->key.'/window.schuhe.php" '.Ajax::tooltip('', 'Laufschuh hinzuf&uuml;gen', true, true).'>'.Icon::$ADD.'</a>');
		$Links[] = Ajax::window('<a href="plugin/'.$this->key.'/window.schuhe.table.php" '.Ajax::tooltip('', 'Schuhe in Tabelle anzeigen', true, true).'>'.Icon::$TABLE.'</a>');

		return implode(' ', $Links);
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		echo('<div id="schuhe">');

		$inuse = true;
		$schuhe = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'shoe` ORDER BY `inuse` DESC, `km` DESC');
		foreach ($schuhe as $schuh) {
			$Shoe = new Shoe($schuh);

			if ($inuse && $Shoe->isInUse() == 0) {
				echo '<div id="hiddenschuhe" style="display:none;">'.NL;
				$inuse = false;
			}

			echo('
			<p style="background-image:url(plugin/'.$this->key.'/schuhbalken.php?km='.round($Shoe->getKm()).');">
				<span class="right">'.$Shoe->getKmString().'</span>
				<strong>'.Shoe::getSearchLink($schuh['id']).'</strong>
			</p>'.NL);	
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
	 * Display table
	 */
	public function displayTable() {
		if (is_null($this->schuhe))
			$this->initTableData();

		echo '
		<style type="text/css">
		tr.shoe { height:2px; }
		tr.shoe td { padding: 0; }
		</style>
		<table id="listOfAllShoes" class="fullWidth">
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
			foreach ($this->schuhe as $i => $schuh) {
				$Shoe   = new Shoe($schuh);
				$in_use = $Shoe->isInUse() ? '' : ' unimportant';

				echo('
				<tr class="'.HTML::trClass($i).$in_use.' r">
					<td class="small">'.$schuh['num'].'x</td>
					<td>'.$this->editLinkFor($schuh['id']).''.$this->getDeleteLinkFor($schuh['id']).'</td>
					<td class="b l">'.Shoe::getSearchLink($schuh['id']).'</td>
					<td class="small">'.$Shoe->getSince().'</td>
					<td>'.(($schuh['num'] != 0) ? Running::Km($Shoe->getKmInDatabase()/$schuh['num']) : '-').'</td>
					<td>'.(($schuh['num'] != 0) ? Running::Speed($Shoe->getKmInDatabase(), $Shoe->getTime()) : '-').'</td>
					<td class="small">'.Running::Km($schuh['dist']).'</td>
					<td class="small">'.$schuh['pace'].'/km'.'</td>
					<td>'.$Shoe->getTimeString().'</td>
					<td>'.$Shoe->getKmString().' '.$Shoe->getKmIcon().'</td>
				</tr>');
			}
		} else {
			echo('<tr class="a1"><td colspan="9">Keine Schuhe vorhanden.</td></tr>');
		}

		echo '</tbody>';
		echo '</table>';

		Ajax::createTablesorterFor("#listOfAllShoes");
	}

	/**
	 * Initialize internal data
	 */
	private function initTableData() {
		$this->schuhe = Mysql::getInstance()->fetchAsArray('
			SELECT
				COUNT('.PREFIX.'training.id) as num,
				MIN(pace) as pace,
				MAX(distance) as dist,
				'.PREFIX.'shoe.*
			FROM '.PREFIX.'shoe
				LEFT JOIN '.PREFIX.'training ON '.PREFIX.'training.shoeid='.PREFIX.'shoe.id
			WHERE
				'.PREFIX.'shoe.accountid="'.SessionAccountHandler::getId().'" AND
				'.PREFIX.'training.accountid="'.SessionAccountHandler::getId().'"
			GROUP BY shoeid
			ORDER BY inuse DESC, km DESC');
	}

	/**
	 * Get link for editing a shoe
	 * @param int $id
	 * @return string
	 */
	private function editLinkFor($id) {
		return Ajax::window('<a href="plugin/'.$this->key.'/window.schuhe.php?id='.$id.'">'.Ajax::tooltip(Icon::$EDIT, 'Bearbeiten').'</a>');
	}
	
	/**
	 * Get delete link for an entry
	 * @param int $id
	 * @return string
	 */
	private function getDeleteLinkFor($id) {
		return Ajax::window('<a href="plugin/'.$this->key.'/window.schuhe.php?id='.$id.'&delete=true">'.Ajax::tooltip(Icon::$DELETE, 'L&ouml;schen').'</a>');
	}
}