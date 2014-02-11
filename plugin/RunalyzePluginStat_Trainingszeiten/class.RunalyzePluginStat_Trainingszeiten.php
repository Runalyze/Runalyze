<?php
/**
 * This file contains the class::RunalyzePluginStat_Trainingszeiten
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_Trainingszeiten';
/**
 * Plugin "Trainingszeiten"
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Trainingszeiten extends PluginStat {
	protected $dataIsMissing = false;

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		if (!isset($_GET['sport']))
			$this->sportid = -1;

		$this->type = Plugin::$STAT;
		$this->name = 'Trainingszeiten';
		$this->description = 'Auflistung n&auml;chtlicher Trainings und Diagramme &uuml;ber die Trainingszeiten.';
	}

	/**
	 * Set default config-variables
	 * @see PluginStat::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();

		return $config;
	}

	/**
	 * Prepare
	 */
	protected function prepareForDisplay() {
		$this->setYearsNavigation(true, true);
		$this->setSportsNavigation(true, true);

		$this->setIndividualHeader();
	}

	/**
	 * Set individual header
	 */
	protected function setIndividualHeader() {
		$HeaderParts = array();

		if ($this->sportid > 0) {
			$Sport = new Sport($this->sportid);
			$HeaderParts[] = $Sport->name();
		}

		if ($this->year > 0)
			$HeaderParts[] = $this->year;

		if (!empty($HeaderParts))
			$this->setHeader($this->name.': '.implode(', ', $HeaderParts));
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayTable();

		if (!$this->dataIsMissing)
			$this->displayImages();
		else
			echo HTML::em('Es sind leider noch keine Trainingsdaten vorhanden.');
	}

	/**
	 * Display the images
	 */
	private function displayTable() {
		if ($this->sportid > 0) {
			$sports_not_short = $this->sportid.',';
		} else {
			$sports_not_short = '';
			$sports = Mysql::getInstance()->fetchAsArray('SELECT `id` FROM `'.PREFIX.'sport` WHERE `short`=0');
			foreach ($sports as $sport)
				$sports_not_short .= $sport['id'].',';
		}
	
		$nights = Mysql::getInstance()->fetchAsArray('SELECT * FROM (
			SELECT
				id,
				time,
				s,
				sportid,
				distance,
				is_track,
				HOUR(FROM_UNIXTIME(`time`)) as `H`,
				MINUTE(FROM_UNIXTIME(`time`)) as `MIN`
			FROM `'.PREFIX.'training`
			WHERE
				`sportid` IN('.substr($sports_not_short,0,-1).') AND
				(HOUR(FROM_UNIXTIME(`time`))!=0 OR MINUTE(FROM_UNIXTIME(`time`))!=0)
				'.($this->year > 0 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.(int)$this->year : '').'
			ORDER BY
				ABS(6-(`H`+4)%24-`MIN`/60) ASC,
				`MIN` DESC LIMIT 20
			) t
		ORDER BY
			(`H`+12)%24 ASC,
			`MIN` ASC');

		if (empty($nights)) {
			$this->dataIsMissing = true;
			return;
		}
		
		echo '<table class="fullwidth zebra-style small">';
		echo '<thead><tr class="b c"><th colspan="8">N&auml;chtliches Training</th></tr></thead>';
		echo '<tbody>';

		foreach ($nights as $i => $data) {
			$Training = new TrainingObject($data);

			if ($i%2 == 0)
				echo('<tr class="a'.(round($i/2)%2+1).'">');
			echo('
				<td class="b">'.$Training->DataView()->getDaytimeString().'</td>
				<td>'.$Training->Linker()->linkWithSportIcon().'</td>
				<td>'.$Training->DataView()->getKmOrTime().' '.$Training->Sport()->name().'</td>
				<td>'.$Training->DataView()->getDateAsWeeklink().'</td>');
			if ($i%2 == 1)
				echo('</tr>');
		}

		echo '</tbody></table>';
	}

	/**
	 * Display the images
	 */
	private function displayImages() {
		echo '<div style="max-width:750px;margin:0 auto;">';
		echo '<span class="right">';
		echo Plot::getDivFor('weekday', 350, 190);
		echo '</span>';
		echo '<span class="left">';
		echo Plot::getDivFor('daytime', 350, 190);
		echo '</span>';
		echo HTML::clearBreak();
		echo '</div>';

		include FRONTEND_PATH.'../plugin/'.$this->key.'/Plot.Daytime.php';
		include FRONTEND_PATH.'../plugin/'.$this->key.'/Plot.Weekday.php';
	}
}
?>