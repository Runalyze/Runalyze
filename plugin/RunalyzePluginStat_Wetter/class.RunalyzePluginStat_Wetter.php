<?php
/**
 * This file contains the class of the RunalyzePluginStat "Wetter".
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_Wetter';

use Runalyze\Data\Weather;
use Runalyze\Configuration;
use Runalyze\Util\Time;

/**
 * Class: RunalyzePluginStat_Wetter
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Wetter extends PluginStat {
	private $i      = 0;
	private $jahr   = '';
	private $jstart = 0;
	private $jende  = 0;

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Weather');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Statistics about weather conditions and temperatures.');
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p( __('There is no bad weather, there is only bad clothing.') );
		echo HTML::p( __('Are you a wimp or a tough runner? '. 
						'Have a look at these statistics about the weather conditions.') );
	}

	/**
	 * Get own links for toolbar navigation
	 * @return array
	 */
	protected function getToolbarNavigationLinks() {
		$LinkList = array();
		$LinkList[] = '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.php">'.Ajax::tooltip(Icon::$LINE_CHART, __('Show temperature plots')).'</a>').'</li>';

		return $LinkList;
	}

	/**
	 * Timer for year or ordered months
	 * @return string
	 */
	protected function getTimerForOrderingInQuery() {
		if ($this->showsAllYears()) {
			// Ensure month-wise data
			return 'MONTH(FROM_UNIXTIME(`time`))';
		}

		return parent::getTimerForOrderingInQuery();
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		$this->initData();

		$this->setYearsNavigation(true, true, true);
		$this->setToolbarNavigationLinks($this->getToolbarNavigationLinks());

		$this->setHeader($this->getHeader());
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayExtremeTrainings();
		$this->displayMonthTable();
	}

	/**
	 * Display month-table
	 */
	private function displayMonthTable() {
		echo '<table class="fullwidth zebra-style r">';
		echo '<thead>';
		$this->displayTableHeadForTimeRange();
		echo '</thead>';
		echo '<tbody>';

		$this->displayMonthTableTemp();
		$this->displayMonthTableWeather();

		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Display an empty th and ths for chosen years/months
	 * @param bool $prependEmptyTag
	 * @param string $width
	 */
	protected function displayTableHeadForTimeRange($prependEmptyTag = true, $width = '8%') {
		echo '<th></th>';

		$width = ' width="'.$width.'"';
		$num = $this->showsLast6Months() ? 6 : 12;
		$add = $this->showsTimeRange() ? date('m') - $num - 1 + 12 : -1;

		for ($i = 1; $i <= 12; $i++) {
			echo '<th'.$width.'>'.Time::month(($i + $add)%12 + 1, true).'</th>';
		}
	}

	/**
	* Display month-table for temperature
	*/
	private function displayMonthTableTemp() {
		echo '<tr class="top-spacer"><td>&#176;C</td>';

		$temps = DB::getInstance()->query('
			SELECT
				AVG(`temperature`) as `temp`,
				'.$this->getTimerIndexForQuery().' as `m`
			FROM `'.PREFIX.'training` WHERE
				`temperature` IS NOT NULL
				'.$this->getSportAndYearDependenceForQuery().'
			GROUP BY '.$this->getTimerIndexForQuery().'
			ORDER BY '.$this->getTimerForOrderingInQuery().' ASC
			LIMIT 12')->fetchAll();

		$i = 1;

		if (!empty($temps)) {
			foreach ($temps as $temp) {
				for (; $i < $temp['m']; $i++)
					echo HTML::emptyTD();
				$i++;
		
				echo '<td>'.round($temp['temp']).' &deg;C</td>';
			}

			for (; $i <= 12; $i++)
				echo HTML::emptyTD();
		} else {
			echo HTML::emptyTD(12);
		}

		echo '</tr>';
	}

	/**
	* Display month-table for weather
	*/
	private function displayMonthTableWeather() {
		$Condition = new Weather\Condition(0);
		$Statement = DB::getInstance()->prepare(
			'SELECT
				SUM(1) as `num`,
				'.$this->getTimerIndexForQuery().' as `m`
			FROM `'.PREFIX.'training` WHERE
				`sportid`=?
				AND `weatherid`=?
				'.$this->getYearDependenceForQuery().'
			GROUP BY '.$this->getTimerIndexForQuery().'
			ORDER BY '.$this->getTimerForOrderingInQuery().' ASC
			LIMIT 12'
		);

		foreach (Weather\Condition::completeList() as $id) {
			$Condition->set($id);
			$Statement->execute(array(Configuration::General()->mainSport(), $id));
			$data = $Statement->fetchAll();
			$i = 1;

			echo '<tr><td>'.$Condition->icon()->code().'</td>';

			if (!empty($data)) {
				foreach ($data as $dat) {
					for (; $i < $dat['m']; $i++)
						echo HTML::emptyTD();
					$i++;
			
					echo ($dat['num'] != 0)
						? '<td>'.$dat['num'].'x</td>'
						: HTML::emptyTD();
				}
			
				for (; $i <= 12; $i++)
					echo HTML::emptyTD();
			} else {
				echo HTML::emptyTD(12);
			}
		}
	
		echo '</tr>';
	}


        /**
	 * Display extreme trainings
	 */
	private function displayExtremeTrainings() {
		$hot  = DB::getInstance()->query('SELECT `temperature`, `id`, `time` FROM `'.PREFIX.'training` WHERE `temperature` IS NOT NULL '.$this->getYearDependenceForQuery().' AND accountid = '.SessionAccountHandler::getId().' ORDER BY `temperature` DESC LIMIT 5')->fetchAll();
		$cold = DB::getInstance()->query('SELECT `temperature`, `id`, `time` FROM `'.PREFIX.'training` WHERE `temperature` IS NOT NULL '.$this->getYearDependenceForQuery().' AND accountid = '.SessionAccountHandler::getId().' ORDER BY `temperature` ASC LIMIT 5')->fetchAll();

		foreach ($hot as $i => $h)
			$hot[$i] = $h['temperature'].'&nbsp;&#176;C '.__('on').' '.Ajax::trainingLink($h['id'], date('d.m.Y', $h['time']));
		foreach ($cold as $i => $c)
			$cold[$i] = $c['temperature'].'&nbsp;&#176;C '.__('on').' '.Ajax::trainingLink($c['id'], date('d.m.Y', $c['time']));

		echo '<p>';
		echo '<strong>'.__('Hottest activities').':</strong> ';
		echo implode(', ', $hot).'<br>';
		echo '<strong>'.__('Coldest activities').':</strong> ';
		echo implode(', ', $cold).'<br>';
		echo '</p>';
	}

	/**
	 * Initialize internal data
	 */
	private function initData() {
		$this->sportid = Configuration::General()->mainSport();

		if ($this->showsAllYears()) {
			$this->i      = 0;
			$this->jahr   = __('In total');
			$this->jstart = mktime(0,0,0,1,1,START_YEAR);
			$this->jende  = time();
		} else {
			$this->i      = $this->year;
			$this->jahr   = $this->year;
			$this->jstart = mktime(0,0,0,1,1,$this->i);
			$this->jende  = mktime(23,59,59,1,0,$this->i+1);
		}

        }

	/**
	 * Get header depending on config
	 */
	private function getHeader() {
		return __('Weather').': '.$this->getYearString();
	}
}