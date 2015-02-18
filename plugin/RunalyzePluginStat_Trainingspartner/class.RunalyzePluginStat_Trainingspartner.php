<?php
/**
 * This file contains the class of the RunalyzePluginStat "Trainingspartner".
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_Trainingspartner';
/**
 * Class: RunalyzePluginStat_Trainingspartner
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Trainingspartner extends PluginStat {
	/**
	 * Array for all trainingspartner
	 * @var array
	 */
	protected $Partner = array();

	/**
	 * Number of trainings without partner
	 * @var int
	 */
	protected $TrainingsWithPartner = 0;

	/**
	 * Number of trainings total
	 * @var int
	 */
	protected $TrainingsTotal = 0;

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Training partner');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('How often did you train with whom?');
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		$this->setSportsNavigation(true, true);
		$this->setYearsNavigation(true, true);

		$this->setHeaderWithSportAndYear();

		$this->initTrainingspartner();
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
		echo '<table class="fullwidth zebra-style">';
		echo '<thead><tr><th colspan="2">'.__('All training partners').'</th></tr></thead>';
		echo '<tbody>';

		if (empty($this->Partner))
			echo('
				<tr>
					<td class="b">0x</td>
					<td><em>'.__('You have only trained on your own.').'</em></td>
				</tr>');
		else {
			$row_num = INFINITY;
			$i = 0;
		
			foreach ($this->Partner as $name => $name_num) {
				if ($row_num == $name_num)
					echo(', ');
				else {
					if ($name_num != 1 && $row_num != INFINITY)
						echo '</td></tr>';
		
					$row_num = $name_num;
					$i++;
					echo '<tr><td class="b">'.$row_num.'x</td><td>';
				}

				echo SearchLink::to('partner', $name, $name, 'like');
			}
			echo '</td></tr>';
		}

		echo '</tobdy>';
		echo '</table>';

		echo '<p class="text">';
		echo sprintf( __('You have trained <strong>%sx</strong> in total and out of that <strong>%sx</strong> with a training partner, '), $this->TrainingsTotal, $this->TrainingsWithPartner );
		echo sprintf( __('that are <strong>%s</strong> &#37;.'), $this->TrainingsTotal == 0 ? 0 : round(100*$this->TrainingsWithPartner/$this->TrainingsTotal) );
		echo '</p>';
	}

	/**
	 * Init all trainingspartner
	 */
	protected function initTrainingspartner() {
		$Query = 'SELECT `partner` FROM `'.PREFIX.'training` WHERE `partner`!=""';
		$Query .= $this->getSportAndYearDependenceForQuery();

		$trainings = DB::getInstance()->query($Query)->fetchAll();

		$this->TrainingsWithPartner = count($trainings);
		$this->TrainingsTotal = DB::getInstance()->query('SELECT COUNT(*) FROM `'.PREFIX.'training` WHERE 1'.$this->getSportAndYearDependenceForQuery())->fetchColumn();

		if (empty($trainings))
			return;

		foreach ($trainings as $training) {
			$trainingspartner = explode(',', $training['partner']);
			foreach ($trainingspartner as $name) {
				$name = trim($name);

				if (!isset($this->Partner[$name]))
					$this->Partner[$name] = 1;
				else
					$this->Partner[$name]++;
			}
		}

		array_multisort($this->Partner, SORT_DESC);
	}
}