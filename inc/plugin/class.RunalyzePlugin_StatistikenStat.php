<?php
/**
 * This file contains the class of the RunalyzePlugin "StatistikenStat".
 */
$PLUGINKEY = 'RunalyzePlugin_StatistikenStat';
/**
 * Class: RunalyzePlugin_StatistikenStat
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginStat
 * @uses class::Mysql
 * @uses class::Error
 * @uses class::Helper
 * @uses class:JD
 * @uses CONF_RECHENSPIELE
 * @uses START_YEAR
 *
 * Last modified 2011/07/10 13:00 by Hannes Christiansen
 */
class RunalyzePlugin_StatistikenStat extends PluginStat {
	private $sport     = array();
	private $colspan   = 0;
	private $num       = 0;
	private $num_start = 0;
	private $num_end   = 0;
	private $line      = 0;

	private $StundenData = array();
	private $KMData      = array();
	private $TempoData   = array();
	private $VDOTData    = array();
	private $TRIMPData   = array();

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = 'Statistiken';
		$this->description = 'Allgemeine Statistiken: Monatszusammenfassung in der Jahres&uuml;bersicht f&uuml;r alle Sportarten.';

		$this->initData();
		$this->initLineData();
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
		$this->displayHeader($this->sport['name'].' '.$this->getYearString());
		$this->displayYearNavigation();
		$this->displaySportsNavigation();
		echo Helper::clearBreak();

		echo '<table style="width:100%;" class="small r">';

		echo ($this->year == -1) ? Helper::yearTR(0, 1) : Helper::monthTR(8, 1);
		echo Helper::spaceTR($this->colspan);

		$this->displayLine('Stunden', $this->StundenData);

		if ($this->sport['distanztyp'] != 0) {
			$this->displayLine('KM', $this->KMData);
			$this->displayLine('&Oslash;Tempo', $this->TempoData);
		}

		if ($this->sportid == RUNNINGSPORT && CONF_RECHENSPIELE)
			$this->displayLine('VDOT', $this->VDOTData);

		if (CONF_RECHENSPIELE)
			$this->displayLine('TRIMP', $this->TRIMPData);

		echo '</table>';
	}

	/**
	 * Display one statistic line
	 * @param string $title
	 * @param array $data Array containing all $data[] = array('i' => i, 'text' => '...')
	 */
	private function displayLine($title, $data) {
		$this->line++;

		echo '<tr class="a'.($this->line%2+1).'">';
		echo '<td class="b">'.$title.'</td>';

		if (empty($data)) {
			echo Helper::emptyTD($this->colspan);
		} else {
			$td_i = 0;
			foreach ($data as $i => $dat) {
				for (; ($this->num_start + $td_i) < $dat['i']; $td_i++)
					echo Helper::emptyTD();
				$td_i++;

				echo '<td>'.$dat['text'].'</td>'.NL;
			}

			for (; $td_i < $this->num; $td_i++)
				echo Helper::emptyTD();
		}

		echo '</tr>';
		echo Helper::spaceTr($this->colspan);
	}

	/**
	 * Initialize internal data
	 */
	private function initData() {
		$this->sport = Mysql::getInstance()->fetch(''.PREFIX.'sports', $this->sportid);

		if ($this->year != -1) {
			$this->num = 12;
			$this->num_start = 1;
			$this->num_end   = 12;
		} else {
			$this->num = date("Y") - START_YEAR + 1;
			$this->num_start = START_YEAR;
			$this->num_end   = date("Y");
		}

		$this->colspan = $this->num + 1;
	}

	/**
	 * Initialize all line-data-arrays
	 */
	private function initLineData() {
		$this->initStundenData();
		$this->initKMData();
		$this->initTempoData();
		$this->initVDOTData();
		$this->initTRIMPData();
	}

	/**
	 * Initialize line-data-array for 'Stunden'
	 */
	private function initStundenData() {
		$result = ($this->year != -1)
			? Mysql::getInstance()->fetchAsArray('SELECT SUM(`dauer`) as `dauer`, MONTH(FROM_UNIXTIME(`time`)) as `i` FROM `'.PREFIX.'training` WHERE `sportid`='.$this->sportid.' && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' GROUP BY MONTH(FROM_UNIXTIME(`time`)) ORDER BY `i` LIMIT 12')
			: Mysql::getInstance()->fetchAsArray('SELECT SUM(`dauer`) as `dauer`, YEAR(FROM_UNIXTIME(`time`)) as `i` FROM `'.PREFIX.'training` WHERE `sportid`='.$this->sportid.' GROUP BY YEAR(FROM_UNIXTIME(`time`)) ORDER BY `i`');
		foreach ($result as $dat) {
			$text = ($dat['dauer'] == 0) ? '&nbsp;' : Helper::Time($dat['dauer'], false);
			$this->StundenData[] = array('i' => $dat['i'], 'text' => $text);
		}
	}

	/**
	 * Initialize line-data-array for 'KM'
	 */
	private function initKMData() {
		$result = ($this->year != -1)
			? Mysql::getInstance()->fetchAsArray('SELECT SUM(`distanz`) as `distanz`, MONTH(FROM_UNIXTIME(`time`)) as `i` FROM `'.PREFIX.'training` WHERE `sportid`='.$this->sportid.' && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' GROUP BY MONTH(FROM_UNIXTIME(`time`)) ORDER BY `i` LIMIT 12')
			: Mysql::getInstance()->fetchAsArray('SELECT SUM(`distanz`) as `distanz`, YEAR(FROM_UNIXTIME(`time`)) as `i` FROM `'.PREFIX.'training` WHERE `sportid`='.$this->sportid.' GROUP BY YEAR(FROM_UNIXTIME(`time`)) ORDER BY `i`');
		foreach ($result as $dat) {
			$text = ($dat['distanz'] == 0) ? '&nbsp;' : Helper::Km($dat['distanz'], 0);
			$this->KMData[] = array('i' => $dat['i'], 'text' => $text);
		}
	}

	/**
	 * Initialize line-data-array for 'Tempo'
	 */
	private function initTempoData() {
		$result = ($this->year != -1)
			? Mysql::getInstance()->fetchAsArray('SELECT SUM(`distanz`) as `distanz`, SUM(`dauer`) as `dauer`, MONTH(FROM_UNIXTIME(`time`)) as `i` FROM `'.PREFIX.'training` WHERE `sportid`='.$this->sportid.' && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' GROUP BY MONTH(FROM_UNIXTIME(`time`)) ORDER BY `i` LIMIT 12')
			: Mysql::getInstance()->fetchAsArray('SELECT SUM(`distanz`) as `distanz`, SUM(`dauer`) as `dauer`, YEAR(FROM_UNIXTIME(`time`)) as `i` FROM `'.PREFIX.'training` WHERE `sportid`='.$this->sportid.' GROUP BY YEAR(FROM_UNIXTIME(`time`)) ORDER BY `i`');
		foreach ($result as $dat) {
			$text = ($dat['dauer'] == 0) ? '&nbsp;' : Helper::Speed($dat['distanz'], $dat['dauer'], $this->sportid);
			$this->TempoData[] = array('i' => $dat['i'], 'text' => $text);
		}
	}

	/**
	 * Initialize line-data-array for 'VDOT'
	 */
	private function initVDOTData() {
		for ($i = $this->num_start; $i <= $this->num_end; $i++) {
			$result = ($this->year != -1)
				? Mysql::getInstance()->fetch('SELECT AVG(`vdot`) as `vdot` FROM `'.PREFIX.'training` WHERE `sportid`='.$this->sportid.' && `puls`!=0 && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' && MONTH(FROM_UNIXTIME(`time`))='.$i.' GROUP BY `sportid` LIMIT 1')
				: Mysql::getInstance()->fetch('SELECT AVG(`vdot`) as `vdot` FROM `'.PREFIX.'training` WHERE `sportid`='.$this->sportid.' && `puls`!=0 && YEAR(FROM_UNIXTIME(`time`))='.$i.' GROUP BY `sportid` LIMIT 1');
			if ($result !== false)
				$VDOT = JD::correctVDOT($result['vdot']);
			else
				$VDOT = 0;

			$text = ($VDOT == 0) ? '&nbsp;' : number_format($VDOT, 1);
			$this->VDOTData[] = array('i' => $i, 'text' => $text);
		}
	}

	/**
	 * Initialize line-data-array for 'TRIMP'
	 */
	private function initTRIMPData() {
		$result = ($this->year != -1)
			? Mysql::getInstance()->fetchAsArray('SELECT SUM(`trimp`) as `trimp`, MONTH(FROM_UNIXTIME(`time`)) as `i` FROM `'.PREFIX.'training` WHERE `sportid`='.$this->sportid.' && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' GROUP BY MONTH(FROM_UNIXTIME(`time`)) ORDER BY `i` LIMIT 12')
			: Mysql::getInstance()->fetchAsArray('SELECT SUM(`trimp`) as `trimp`, YEAR(FROM_UNIXTIME(`time`)) as `i` FROM `'.PREFIX.'training` WHERE `sportid`='.$this->sportid.' GROUP BY YEAR(FROM_UNIXTIME(`time`)) ORDER BY `i`');
		foreach ($result as $dat) {
			$avg_num = ($this->year != -1) ? 15 : 180;
			$text = ($dat['trimp'] == 0)
				? '&nbsp;'
				: '<span style="color:#'.Helper::Stresscolor($dat['trimp']/$avg_num).'">'.$dat['trimp'].'</span>';
			$this->TRIMPData[] = array('i' => $dat['i'], 'text' => $text);
		}
	}
}
?>