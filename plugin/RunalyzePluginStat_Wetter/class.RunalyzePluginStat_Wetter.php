<?php
/**
 * This file contains the class of the RunalyzePluginStat "Wetter".
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_Wetter';

use \Runalyze\Data\Weather;
use Runalyze\Configuration;

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
	 * @var array
	 */
	protected $Clothes = array();

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
		return __('Statistics about weather conditions, temperatures and clothing.');
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p( __('There is no bad weather, there is only bad clothing.') );
		echo HTML::p( __('Are you a wimp or a tough runner?'. 
						'Have a look at these statistics about the weather conditions and your clothing while training.') );
	}

	/**
	 * Init configuration
	 */
	protected function initConfiguration() {
		$Configuration = new PluginConfiguration($this->id());
		$Configuration->addValue( new PluginConfigurationValueBool('for_weather', __('Show statistics about weather conditions'), '', true) );
		$Configuration->addValue( new PluginConfigurationValueBool('for_clothes', __('Show statistics about your clothing'), '', true) );

		$this->setConfiguration($Configuration);
	}

	/**
	 * Get own links for toolbar navigation
	 * @return array
	 */
	protected function getToolbarNavigationLinks() {
		$LinkList = array();

		if ($this->Configuration()->value('for_weather'))
			$LinkList[] = '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.php">'.Ajax::tooltip(Icon::$LINE_CHART, __('Show temperature plots')).'</a>').'</li>';

		return $LinkList;
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		$this->initData();

		$this->setYearsNavigation();
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
		$this->displayClothesTable();

		if (!$this->Configuration()->value('for_weather') && !$this->Configuration()->value('for_clothes'))
			echo HTML::warning( __('You have to activate some statistics in the plugin configuration.') );
	}

	/**
	 * Display month-table
	 */
	private function displayMonthTable() {
		echo '<table class="fullwidth zebra-style r">';
		echo '<thead>'.HTML::monthTR(8, 1).'</thead>';
		echo '<tbody>';

		if ($this->Configuration()->value('for_weather')) {
			$this->displayMonthTableTemp();
			$this->displayMonthTableWeather();
		}

		if ($this->Configuration()->value('for_clothes')) {
			$this->displayMonthTableClothes();
		}

		echo '</tbody>';
		echo '</table>';
	}

	/**
	* Display month-table for temperature
	*/
	private function displayMonthTableTemp() {
		echo '<tr class="top-spacer"><td>&#176;C</td>';

		$temps = DB::getInstance()->query('
			SELECT
				AVG(`temperature`) as `temp`,
				MONTH(FROM_UNIXTIME(`time`)) as `m`
			FROM `'.PREFIX.'training` WHERE
				`sportid`="'.Configuration::General()->mainSport().'" AND
				`temperature` IS NOT NULL
				'.($this->year != -1 ? ' AND `time` BETWEEN UNIX_TIMESTAMP(\''.(int)$this->year.'-01-01\') AND UNIX_TIMESTAMP(\''.((int)$this->year+1).'-01-01\')-1' : '').'
			GROUP BY MONTH(FROM_UNIXTIME(`time`))
			ORDER BY `m` ASC
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
				MONTH(FROM_UNIXTIME(`time`)) as `m`
			FROM `'.PREFIX.'training` WHERE
				`sportid`=?
				'.($this->year != -1 ? ' AND `time` BETWEEN UNIX_TIMESTAMP(\''.(int)$this->year.'-01-01\') AND UNIX_TIMESTAMP(\''.((int)$this->year+1).'-01-01\')-1' : '').' AND
				`weatherid`=?
			GROUP BY MONTH(FROM_UNIXTIME(`time`))
			ORDER BY `m` ASC
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
	* Display month-table for clothes
	*/
	private function displayMonthTableClothes() {
		$nums = DB::getInstance()->query('SELECT
				SUM(1) as `num`,
				MONTH(FROM_UNIXTIME(`time`)) as `m`
			FROM `'.PREFIX.'training` WHERE
				`sportid`="'.Configuration::General()->mainSport().'" AND
				`clothes`!=""
				'.($this->year != -1 ? ' AND `time` BETWEEN UNIX_TIMESTAMP(\''.(int)$this->year.'-01-01\') AND UNIX_TIMESTAMP(\''.((int)$this->year+1).'-01-01\')-1' : '').'
			GROUP BY MONTH(FROM_UNIXTIME(`time`))
			ORDER BY `m` ASC
			LIMIT 12')->fetchAll();
		
		if (!empty($nums)) {
			foreach ($nums as $dat)
				$num[$dat['m']] = $dat['num'];
		}

		$ClothesQuery = DB::getInstance()->prepare(
			'SELECT
				SUM(IF(FIND_IN_SET(:id, `clothes`)!=0,1,0)) as `num`,
				MONTH(FROM_UNIXTIME(`time`)) as `m`
			FROM `'.PREFIX.'training` WHERE
				`sportid`="'.Configuration::General()->mainSport().'"
				'.($this->year != -1 ? ' AND `time` BETWEEN UNIX_TIMESTAMP(\''.(int)$this->year.'-01-01\') AND UNIX_TIMESTAMP(\''.((int)$this->year+1).'-01-01\')-1' : '').'
			GROUP BY MONTH(FROM_UNIXTIME(`time`))
			HAVING `num`!=0
			ORDER BY `m` ASC
			LIMIT 12'
		);

		if (!empty($this->Clothes)) {
			foreach ($this->Clothes as $k => $kleidung) {
				echo '<tr class="'.($k == 0 ? 'top-spacer' : '').'"><td>'.$kleidung['name'].'</td>';
			
				$i = 1;
				$ClothesQuery->execute(array(':id' => $kleidung['id']));
				$data = $ClothesQuery->fetchAll();

				if (!empty($data)) {
					foreach ($data as $dat) {
						for (; $i < $dat['m']; $i++)
							echo HTML::emptyTD();
						$i++;

						if ($dat['num'] != 0)
							echo '
								<td class="r"><span title="'.$dat['num'].'x">
										'.round($dat['num']*100/$num[$dat['m']]).' &#37;
								</span></td>';
						else
							echo HTML::emptyTD();
					}

					for (; $i <= 12; $i++)
						echo HTML::emptyTD();
				} else {
					echo '<td colspan="12"></td>';
				}

				echo '</tr>';
			}
		}
	}

	/**
	 * Display table for clothes
	 */
	private function displayClothesTable() {
		if (!$this->Configuration()->value('for_clothes'))
			return;

		echo '<table class="fullwidth zebra-style">
			<thead><tr>
				<th></th>
				<th>'.__('Temperatures').'</th>
				<th>&Oslash;</th>
				<th colspan="2"></th>
				<th>'.__('Temperatures').'</th>
				<th>&Oslash;</th>
				<th colspan="2"></th>
				<th>'.__('Temperatures').'</th>
				<th>&Oslash;</th>
			</tr></thead>';
		echo '<tr class="r">';

		$ClothesQuery = DB::getInstance()->prepare(
			'SELECT
				AVG(`temperature`) as `avg`,
				MAX(`temperature`) as `max`,
				MIN(`temperature`) as `min`
			FROM `'.PREFIX.'training`
			WHERE
				`sportid`="'.Configuration::General()->mainSport().'" AND
				`temperature` IS NOT NULL AND
				FIND_IN_SET(:id,`clothes`) != 0
			'.($this->year != -1 ? ' AND `time` BETWEEN UNIX_TIMESTAMP(\''.(int)$this->year.'-01-01\') AND UNIX_TIMESTAMP(\''.((int)$this->year+1).'-01-01\')-1' : '')
		);

		$i = 0;

		if (!empty($this->Clothes)) {
			foreach ($this->Clothes as $kleidung) {
				echo ($i%3 == 0) ? '</tr><tr class="r">' : '<td>&nbsp;&nbsp;</td>';

				$ClothesQuery->execute(array(':id' => $kleidung['id']));
				$dat = $ClothesQuery->fetch();

				echo '<td class="l">'.$kleidung['name'].'</td>';

				if (isset($dat['min'])) {
					echo '<td>'.($dat['min']).'&deg;C '.__('to').' '.($dat['max']).'&deg;C</td>';
					echo '<td>'.round($dat['avg']).'&deg;C</td>';
				} else {
					echo '<td colspan="2" class="c"><em>-</em></td>';
				}

				$i++;
			}
		}

		for (; $i%3 != 2; $i++)
			echo HTML::emptyTD(3);

		echo '</tr>';
		echo '</table>';
	}

	/**
	 * Display extreme trainings
	 */
	private function displayExtremeTrainings() {
		$hot  = DB::getInstance()->query('SELECT `temperature`, `id`, `time` FROM `'.PREFIX.'training` WHERE '.($this->year != -1 ? '`time` BETWEEN UNIX_TIMESTAMP(\''.(int)$this->year.'-01-01\') AND UNIX_TIMESTAMP(\''.((int)$this->year+1).'-01-01\')-1 AND' : '').' `temperature` IS NOT NULL ORDER BY `temperature` DESC LIMIT 5')->fetchAll();
		$cold = DB::getInstance()->query('SELECT `temperature`, `id`, `time` FROM `'.PREFIX.'training` WHERE '.($this->year != -1 ? '`time` BETWEEN UNIX_TIMESTAMP(\''.(int)$this->year.'-01-01\') AND UNIX_TIMESTAMP(\''.((int)$this->year+1).'-01-01\')-1 AND' : '').' `temperature` IS NOT NULL ORDER BY `temperature` ASC LIMIT 5')->fetchAll();

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
		if ($this->year == -1) {
			$this->i      = 0;
			$this->jahr   = "Gesamt";
			$this->jstart = mktime(0,0,0,1,1,START_YEAR);
			$this->jende  = time();
		} else {
			$this->i      = $this->year;
			$this->jahr   = $this->year;
			$this->jstart = mktime(0,0,0,1,1,$this->i);
			$this->jende  = mktime(23,59,59,1,0,$this->i+1);
		}

		$this->Clothes = ClothesFactory::AllClothes();
	}

	/**
	 * Get header depending on config
	 */
	private function getHeader() {
		$header = 'Wetter';

		if ($this->Configuration()->value('for_clothes')) {
			$header = ($this->Configuration()->value('for_weather')) ? __('Weather and Clothing') : __('Clothing');
		}

		return $header.': '.$this->jahr;
	}
}