<?php
/**
 * This file contains the class of the RunalyzePluginStat "Strecken".
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_Strecken';

use Runalyze\Activity\Distance;

/**
 * Class: RunalyzePluginStat_Strecken
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Strecken extends PluginStat {
	/**
	 * City sperator
	 */
	const CITY_SEPERATOR = ' - ';

	/**
	 * Maximum number of routes on routenet
	 * @var int 
	 */
	const MAX_ROUTES_ON_NET = 50;

	/**
	 * Array with all cities
	 * @var array
	 */
	protected $Cities = array();

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Routes');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Some statistics for your most frequent routes.');
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p(
			__('The input field \'route\' expects different places separted by a \'-\', e.g. \'City A - City B\''.
				'This way the plugin will be able to count how often you visit each city or place.')
		);
	}

	/**
	 * Init configuration
	 */
	protected function initConfiguration() {
		$Configuration = new PluginConfiguration($this->id());
		$Configuration->addValue( new PluginConfigurationValueBool('analyze_cities', __('Show visited cities'), '', true) );

		$this->setConfiguration($Configuration);
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		$text = __('Open route network');
		$Link = Ajax::window('<a class="" href="plugin/'.$this->key().'/window.routenet.php?sport='.$this->sportid.'&y='.$this->year.'"><i class="fa fa-map-marker"></i> '.$text.'</a>', 'big');

		$this->setToolbarNavigationLinks(array('<li>'.$Link.'</li>'));
		$this->setYearsNavigation(true, true);
		$this->setSportsNavigation(true, true);

		$this->setHeaderWithSportAndYear();

		if ($this->Configuration()->value('analyze_cities')) {
			$this->initCities();
		}
	}

	/**
	 * Default sport
	 * @return int
	 */
	protected function defaultSport() {
		return -1;
	}

	/**
	 * Title for all years
	 * @return string
	 */
	protected function titleForAllYears() {
		return __('All years');
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayRoutes();

		if ($this->Configuration()->value('analyze_cities')) {
			$this->displayCities();

			echo HTML::clearBreak();
			echo HTML::clearBreak();

			$this->displayLonelyCities();
		}
	}

	/**
	 * Display routes
	 */
	private function displayRoutes() {
		if ($this->Configuration()->value('analyze_cities')) {
			echo '<table style="width:70%;" class="left zebra-style">';
		} else {
			echo '<table style="width:100%;" class="zebra-style">';		
		}

		echo '<thead><tr><th colspan="3">'.__('Most frequent routes').'</th></tr></thead>';
		echo '<tbody class="r">';

		$i = 0;
		$statement = DB::getInstance()->query('
			SELECT
				`'.PREFIX.'route`.`name`,
				SUM(`'.PREFIX.'training`.`distance`) as `km`,
				SUM(1) as `num`
			FROM `'.PREFIX.'training`
			LEFT JOIN `'.PREFIX.'route` ON `'.PREFIX.'training`.`routeid`=`'.PREFIX.'route`.`id`
			WHERE 1 '.$this->getSportAndYearDependenceForQuery().' AND `'.PREFIX.'training`.`accountid`='.SessionAccountHandler::getId().' AND `routeid`!=0 AND `name`!=""
			GROUP BY `name`
			ORDER BY `num` DESC
			LIMIT 10'
		);

		while ($data = $statement->fetch()) {
			echo '<tr>
					<td>'.$data['num'].'x</td>
					<td class="l">'.SearchLink::to('route', $data['name'], Helper::Cut($data['name'],100)).'</td>
					<td>'.Distance::format($data['km']).'</td>
				</tr>';

			$i++;
		}

		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Display most visited cities
	 */
	private function displayCities() {
		echo '<table style="width:25%;" class="right zebra-style">';
		echo '<thead><tr><th colspan="2">'.__('Most frequent places').'</th></tr></thead>';
		echo '<tbody>';
		
		$i = 1;

		if (empty($this->Cities)) {
			echo HTML::emptyTD(2, HTML::em( __('There are no routes.') ));
		}

		foreach ($this->Cities as $city => $num) {
			$i++;
			echo '<tr class="a'.($i%2+1).'">
					<td>'.$num.'x</td>
					<td>'.SearchLink::to('route', $city, $city, 'like').'</td>
				</tr>';

			if ($i == 11) {
				break;
			}
		}

		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Display less visited cities
	 */
	private function displayLonelyCities() {
		echo '<table class="margin-5 fullwidth zebra-style">';
		echo '<thead><tr><th colspan="2">'.__('Rare places').'</th></tr></thead>';
		echo '<tbody>';

		$num_x = 0;
		$LonelyCities = array_reverse($this->Cities);
		
		foreach ($LonelyCities as $city => $num) {
			if ($num_x <= 4) {
				if ($num_x != $num) {
					if ($num != 1)
						echo '</td></tr>';
					$num_x = $num;
					echo '<tr><td class="b">'.$num.'x</td><td>';
				} else
					echo ', ';

				echo SearchLink::to('route', $city, $city, 'like');
			}
			else {
				echo '</td></tr>';
				break;
			}
		}

		echo '
			<tr class="no-zebra">
				<td colspan="2" class="c">
					'.sprintf( __('You have visited %s different places.'), count($this->Cities) ).'
				</td>
			</tr>
		</tbody>
		</table>

		<p class="c"><em>'.__('Everything seperated by a \' - \' is considered as an individual place.').'</em></p>';
	}

	/**
	 * Initialize internal array for all cities
	 */
	private function initCities() {
		$this->Cities = array();
		$statement = DB::getInstance()->query($this->fetchCitiesQuery());

		while ($string = $statement->fetch(PDO::FETCH_COLUMN)) {
			$cities = explode(" - ", $string);
			foreach ($cities as $city) {
				$city = trim($city);

				if (!isset($this->Cities[$city]))
					$this->Cities[$city] = 1;
				else
					$this->Cities[$city]++;
			}
		}

		array_multisort($this->Cities, SORT_DESC);
	}

	/**
	 * Query to fetch cities
	 * @return string
	 */
	private function fetchCitiesQuery() {
		if ($this->sportid <= 0 && $this->year <= 0) {
			return 'SELECT `cities` FROM `'.PREFIX.'route` WHERE `cities`!=""';
		}

		$Query = 'SELECT `'.PREFIX.'route`.`cities` FROM `'.PREFIX.'training`';
		$Query .= ' RIGHT JOIN `'.PREFIX.'route` ON `'.PREFIX.'training`.`routeid` = `'.PREFIX.'route`.`id`';
		$Query .= ' WHERE ';

		if ($this->sportid > 0) {
			$Query .= '`sportid`='.(int) $this->sportid.' AND ';
		}

		$Query .= '`'.PREFIX.'training`.`accountid`='.SessionAccountHandler::getId().' AND ';

		if ($this->year > 0) {
			$Query .= '`time` BETWEEN UNIX_TIMESTAMP(\''.(int)$this->year.'-01-01\') AND UNIX_TIMESTAMP(\''.((int)$this->year+1).'-01-01\')-1 AND';
		}

		$Query .= '`routeid`!=0 AND `cities`!=""';

		return $Query;
	}

	/**
	 * Panel menu for routenet
	 * @param int $sportid
	 * @param int $year
	 * @return string
	 */
	static public function panelMenuForRoutenet($sportid, $year) {
		$Code = '<div class="panel-menu"><ul>';
		$Code .= '<li class="with-submenu"><span class="link">'.__('Choose sport').'</span><ul class="submenu">'.self::submenuForSport($sportid, $year).'</ul></li>';
		$Code .= '<li class="with-submenu"><span class="link">'.__('Choose year').'</span><ul class="submenu">'.self::submenuForYear($sportid, $year).'</ul></li>';
		$Code .= '</ul></div>';

		return $Code;
	}

	/**
	 * Submenu for sport
	 * @param int $sportid
	 * @param int $year
	 * @return string
	 */
	static private function submenuForSport($sportid, $year) {
		$Code = '<li'.(-1 == $sportid ? ' class="active"' : '').'>'.self::linkToRoutenet(__('All'), -1, $year).'</li>';

		$Sports = SportFactory::NamesAsArray();
		foreach ($Sports as $id => $name) {
			$Code .= '<li'.($id == $sportid ? ' class="active"' : '').'>'.self::linkToRoutenet($name, $id, $year).'</li>';
		}

		return $Code;
	}

	/**
	 * Submenu for sport
	 * @param int $sportid
	 * @param int $year
	 * @return string
	 */
	static private function submenuForYear($sportid, $year) {
		$Code = '<li'.(-1 == $year ? ' class="active"' : '').'>'.self::linkToRoutenet(__('All years'), $sportid, -1).'</li>';

		for ($y = date("Y"); $y >= START_YEAR; $y--) {
			$Code .= '<li'.($y == $year ? ' class="active"' : '').'>'.self::linkToRoutenet($y, $sportid, $y).'</li>';
		}

		return $Code;
	}

	/**
	 * Internal link to routenet
	 * @param string $text
	 * @param int $sportid
	 * @param int $year
	 * @return string
	 */
	static private function linkToRoutenet($text, $sportid, $year) {
		return Ajax::window('<a class="" href="plugin/RunalyzePluginStat_Strecken/window.routenet.php?sport='.$sportid.'&y='.$year.'">'.$text.'</a>', 'big');
	}
}