<?php
/**
 * This file contains the class of the RunalyzePluginStat "Trainingszeiten".
 */
$PLUGINKEY = 'RunalyzePluginStat_Trainingszeiten';
/**
 * Class: RunalyzePluginStat_Trainingszeiten
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class RunalyzePluginStat_Trainingszeiten extends PluginStat {
	protected $dataIsMissing = false;

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
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
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayHeader('Trainingszeiten');
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
		$sports_not_short = '';
		$sports = Mysql::getInstance()->fetchAsArray('SELECT `id` FROM `'.PREFIX.'sport` WHERE `short`=0');
		foreach ($sports as $sport)
			$sports_not_short .= $sport['id'].',';
	
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
		
		echo '<table style="width:98%;" style="margin:0 5px 25px 5px;" class="small">';
		echo '<tr class="b c"><td colspan="8">N&auml;chtliches Training</td></tr>';
		echo HTML::spaceTR(8);

		foreach ($nights as $i => $night) {
			$Training = new Training($night['id'], $night);

			if ($i%2 == 0)
				echo('<tr class="a'.(round($i/2)%2+1).'">');
			echo('
				<td class="b">'.$Training->getDaytimeString().'</td>
				<td>'.$Training->trainingLinkWithSportIcon().'</td>
				<td>'.$Training->getKmOrTime().' '.$Training->Sport()->name().'</td>
				<td>'.$Training->getDateAsWeeklink().'</td>');
			if ($i%2 == 1)
				echo('</tr>');
		}

		echo '</table>';
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