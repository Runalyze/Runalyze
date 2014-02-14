<?php
/**
 * This file contains the class of the RunalyzePluginStat "Strecken".
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_Strecken';
/**
 * Class: RunalyzePluginStat_Strecken
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Strecken extends PluginStat {
	/**
	 * Maximum number of routes on routenet
	 * @var int 
	 */
	static public $MAX_ROUTES_ON_NET = 100;

	/**
	 * Array with all cities
	 * @var array
	 */
	private $orte = array();

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = 'Strecken';
		$this->description = 'Auflistung der h&auml;ufigsten und seltensten Strecken/Orte.';
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p('Wenn f&uuml;r ein Training eine Strecke angegeben wird, wird davon ausgegangen,
					dass einzelne Orte durch einen Bindestricht voneinander getrennt werden.
					Dadurch kann dieses Plugin auswerten, welche Orte man wie oft besucht hat.');
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
	 * Init data 
	 */
	protected function prepareForDisplay() {
		$Link = Ajax::window('<a class="" href="plugin/'.$this->key.'/window.routenet.php"><i class="fa fa-map-marker"></i> Streckennetz &ouml;ffnen</a>', 'big');

		$this->setToolbarNavigationLinks(array('<li>'.$Link.'</li>'));
		$this->setYearsNavigation(true, true);

		$this->setHeaderWithSportAndYear();

		$this->initCities();
	}

	/**
	 * Title for all years
	 * @return string
	 */
	protected function titleForAllYears() {
		return 'Alle Jahre';
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayRoutes();
		$this->displayCities();

		echo HTML::clearBreak();
		echo HTML::clearBreak();

		$this->displayLonelyCities();
	}

	/**
	 * Display routes
	 */
	private function displayRoutes() {
		echo '<table style="width:70%;" class="left zebra-style">';
		echo '<thead><tr><th colspan="3">H&auml;ufigsten Strecken</th></tr></thead>';
		echo '<tbody class="r">';

		$strecken = DB::getInstance()->query('
			SELECT
				`route`,
				SUM(`distance`) as `km`,
				SUM(1) as `num`
			FROM `'.PREFIX.'training`
			WHERE `route`!="" '.$this->getSportAndYearDependenceForQuery().'
			GROUP BY `route`
			ORDER BY `num` DESC
			LIMIT 10')->fetchAll();

		if (empty($strecken))
			echo HTML::emptyTD(3, HTML::em('Keine Strecken vorhanden.'));

		foreach ($strecken as $i => $strecke) {
			echo('
				<tr class="a'.($i%2+1).'">
					<td>'.$strecke['num'].'x</td>
					<td class="l">	
						'.SearchLink::to('route', $strecke['route'], Helper::Cut($strecke['route'],100)).'
					</td>
					<td>'.Running::Km($strecke['km']).'</td>
				</tr>');
		}

		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Display most visited cities
	 */
	private function displayCities() {
		echo '<table style="width:25%;" class="right zebra-style">';
		echo '<thead><tr><th colspan="2">H&auml;ufigsten Orte</th></tr></thead>';
		echo '<tbody>';
		
		$i = 1;
		array_multisort($this->orte, SORT_DESC);

		if (empty($this->orte))
			echo HTML::emptyTD(2, HTML::em('Keine Strecken vorhanden.'));

		foreach ($this->orte as $ort => $num) {
			$i++;
			echo('
				<tr class="a'.($i%2+1).'">
					<td>'.$num.'x</td>
					<td>'.SearchLink::to('route', $ort, $ort, 'like').'</td>
				</tr>');

			if ($i == 11)
				break;
		}

		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Display less visited cities
	 */
	private function displayLonelyCities() {
		echo '<table class="margin-5 fullwidth zebra-style">';
		echo '<thead><tr><th colspan="2">Seltensten Orte</th></tr></thead>';
		echo '<tbody>';

		$num_x = 0;
		array_multisort($this->orte);
		
		foreach ($this->orte as $ort => $num) {
			if ($num_x <= 4) {
				if ($num_x != $num) {
					if ($num != 1)
						echo '</td></tr>';
					$num_x = $num;
					echo '<tr><td class="b">'.$num.'x</td><td>';
				} else
					echo(', ');

				echo SearchLink::to('route', $ort, $ort, 'like');
			}
			else {
				echo '</td></tr>';
				break;
			}
		}

		echo('
			<tr class="no-zebra">
				<td colspan="2" class="c">
					Insgesamt warst du in <strong>'.count($this->orte).' verschiedenen Orten</strong> unterwegs.
				</td>
			</tr>
		</tbody>
		</table>

		<p class="c"><em>Alles was bei der eingetragenen Strecke mit &quot; - &quot; getrennt wird, wird als eigener Ort betrachtet.</em></p>');
	}

	/**
	 * Initialize internal array for all cities
	 */
	private function initCities() {
		$this->orte = array();
		$strecken = DB::getInstance()->query('SELECT `route`, `distance` FROM `'.PREFIX.'training` WHERE `route`!="" '.$this->getSportAndYearDependenceForQuery())->fetchAll();
		foreach ($strecken as $strecke) {
			$streckenorte = explode(" - ", $strecke['route']);
			foreach ($streckenorte as $streckenort) {
				$streckenort = trim($streckenort);

				if (!isset($this->orte[$streckenort]))
					$this->orte[$streckenort] = 1;
				else
					$this->orte[$streckenort]++;
			}
		}
	}
}