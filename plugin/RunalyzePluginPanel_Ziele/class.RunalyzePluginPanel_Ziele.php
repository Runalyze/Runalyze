<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Ziele".
 * @package Runalyze\Plugins\Panels
 */
$PLUGINKEY = 'RunalyzePluginPanel_Ziele';
/**
 * Class: RunalyzePluginPanel_Ziele
 * @author Ulrich Kiermayr <ulrich@kiermayr.at>
 * License: GPL 
 * @package Runalyze\Plugins\Panels
 */
class RunalyzePluginPanel_Ziele extends PluginPanel {
	/**
	 * Was there a run today?
	 * @var bool
	 */
	private $wasRunningToday = false;

	/**
	 * All lines
	 * @var array
	 */
	private $Lines = array();

	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->name = __('Goals');
		$this->description = __('Set your own goals for different time ranges and compare your current performance with them.');
	}

    /**
     * Display long description 
     */
    protected function displayLongDescription() {
		echo HTML::p( __('This plugin tracks your distances for chosen time ranges.') );
		echo HTML::p( __('You can set a goal (value &gt; 0) for every time range. If it is set, you will see how much you have to run to reach it.') );
		echo HTML::p( __('A virtual Pace Bunny <em>reaches</em> the goal with a steady performance - you can see how far you are ahead or behind.') );
		echo HTML::p( __('Note: &quot;Saison&quot; refers to the current saison in the german &quot;kmspiel&quot;.') );
    }

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();

        foreach ($this->getTimeset() as $i => $timeset) {
			$ShowHint = sprintf( __('Show a prognosis for the current %s'), $timeset['name'] );
			$GoalHint = sprintf( __('Current time range: %s to %s'), $timeset['start']->format("d.m.Y"), $timeset['end']->format("d.m.Y") );
	
			if (isset($timeset['note'])) {
				$ShowHint .= '<br>'.$timeset['note'];
				$GoalHint .= '<br>'.$timeset['note'];
			}

            $config['ziel_show_'.$i] = array(
				'type' => 'bool',
				'var' => true, 
				'description' => Ajax::tooltip( sprintf(__('%s: show'), $timeset['name'] ), $ShowHint)
			);

			$config['ziel_'.$i] = array(
				'type' => 'int',
				'var' => 0, 
				'description' => Ajax::tooltip( sprintf(__('%s: goal'), $timeset['name'] ), $GoalHint)
			);
        }
		return $config;
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		$Code = '<ul>';

		foreach ($this->getTimeset() as $i => $timeset) {
            if ( !$this->config['ziel_show_'.$i]['var'] )
				continue;

			$Code .= '<li>'.Ajax::change($timeset['name'], 'bunny', '#bunny_'.$i).'</li>';
		}

		return $Code.'</ul>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		echo '<div id="bunny">';

		$this->wasRunningToday = $this->wasRunningToday();

		$isFirst = true;
		foreach ($this->getTimeset() as $i => $timeset) {
			if (!$this->config['ziel_show_'.$i]['var'])
				continue;

			echo '<div id="bunny_'.$i.'" class="change"'.($isFirst ? '' : ' style="display:none;"').'>';
			$this->showTimeset($timeset, $this->config['ziel_'.$i]['var']);
			echo HTML::clearBreak();
			echo '</div>';

			$isFirst = false;
		}

		echo '</div>';
	}

	/**
	 * Show timeset
	 * @param array $Timeset
	 * @param int $goal
	 */
	private function showTimeset(&$Timeset, $goal) {
		$this->clearLines();

		// Some Numbers we need
		$start = $Timeset['start'];
		$now   = new DateTime(date('Y-m-d'));
		$end   = $Timeset['end'];
		$days  = $this->wasRunningToday + (int)date_diff($start, $now)->format('%a');
		$dauer = 1 + (int)date_diff($start, $end)->format('%a');
		$rest  = $dauer - $days;

		$dat = $this->fetchDataSince($start->getTimestamp());

		$this->addHeadline( __('Current'), $dat['distanz_sum'], $dat['anzahl'], true);
		$this->addLine( __('&oslash; Day'), $days > 0 ? $dat['distanz_sum']/$days : 0 );

		if ($days > 7)
			$this->addLine( __('&oslash; Week'), $days > 0 ? 7*$dat['distanz_sum']/$days : 0, $dat['anzahl']/$days*7 );

		$this->addHeadline( __('Prognosis'), $dat['distanz_sum']/$days*$dauer, $dat['anzahl']/$days*$dauer);

		if ($goal > 0) {
			$this->addHeadline( __('Goal'), $goal);

			if ($dat['distanz_sum'] < $goal) {
				$togo = $goal - $dat['distanz_sum'];

				$this->addLine( sprintf( __('%d days left'), $rest), $togo);
				$this->addLine( __('&oslash; Day'), $togo/$rest);

				if ($rest > 7)
					$this->addLine( __('&oslash; Week'), 7*$togo/$rest);
			} else {
				$this->addLine( __('Goal reached.') );
			}

			$this->addHeadline( __('Pace Bunny'), $goal/$dauer * $days );
			$this->addLine( __('&oslash; Day'), $goal/$dauer );

			$diff = $goal/$dauer * $days - $dat['distanz_sum'];

			if ($diff > 0)
				$this->addLine( __('Behind'), abs($diff) );
			else
				$this->addLine( __('Ahead'), abs($diff) );

			$this->addHeadline( __('Head 2 Head') );
			$this->addBar( __('Me'), $dat['distanz_sum'], $goal, $diff > 0 ? '#f99' : '#9f9');
			$this->addBar( __('Pace&nbsp;Bunny'), $goal/$dauer*$days, $goal, '#ccf');
		}

		foreach ($this->Lines as $Line)
			$this->showLineOrBar($Line);

		echo '<small class="right">'.sprintf( __('%s to %s (%d days)'), $start->format("d.m.Y"), $end->format("d.m.Y"), $dauer).'</small>';
	}

	/**
	 * Clear lines
	 */
	private function clearLines() {
		$this->Lines = array();
	}

	/**
	 * Add headline
	 * @param string $Name
	 * @param double $Distance
	 * @param int $Num
	 * @param bool $NoTopBorder
	 */
	private function addHeadline($name, $distance = 0, $num = 0, $noTopBorder = false) {
		$this->Lines[] = array(
			'name' => $name,
			'lvl' => 1,
			'sep' => !$noTopBorder,
			'km' => $distance,
			'anz' => $num
		);
	}

	/**
	 * Add line
	 * @param string $Name
	 * @param double $Distance
	 * @param int $Num
	 */
	private function addLine($name, $distance = 0, $num = 0) {
		$this->Lines[] = array(
			'name' => $name,
			'lvl' => 2,
			'km' => $distance,
			'anz' => $num
		);
	}

	/**
	 * Add bar
	 * @param string $name
	 * @param double $val
	 * @param double $max
	 * @param string $color
	 */
	private function addBar($name, $val, $max, $color) {
		$this->Lines[] = array(
			'name' => $name,
			'type' => 'bar',
			'val' => $val,
			'max' => $max,
			'color' => $color
		);
	}

	/**
	 * Show line or bar
	 * @param array $Array
	 */
	private function showLineOrBar(&$Array) {
		if (isset($Array['type']) && $Array['type'] == 'bar')
			$this->showBar($Array);
		else
			$this->showLine($Array);
	}

	/**
	 * Show bar
	 * @param array $Bar
	 */
	private function showBar(&$Bar) {
		$percentage = min(100, 100*$Bar['val']/$Bar['max']);

		echo '<div style="padding-left:5px; padding-right:7px; padding-bottom:2px">'.
				'<div style="border: 1px solid black; padding: 1px; background-color: none; width: 100%;">'.
				'<span class="right">'.round($percentage,1).'%&nbsp;</span>'.
				'<div id="progress_bar" style="background-color:'.$Bar['color'].'; width:'.round($percentage).'%;">&nbsp;'.$Bar['name'].'</div>'.
				'</div>'.
			'</div>';
	}

	/**
	 * Show line
	 * @param array $Line
	 */
	private function showLine(&$Line) {
		$span_format        = isset($Line['lvl']) ? $this->getLevelStyle($Line['lvl']) : 0;
		$p_format           = isset($Line['sep']) && $Line['sep'] ? ' style="border-top:1px solid #ccc;"' : '';

		$NumberOfActivities = isset($Line['anz']) && $Line['anz'] > 0 ? '<small><small>('.Helper::Unknown(round($Line['anz'],1), '0').'x)</small></small>' : '';
		$Distance           = isset($Line['km']) && $Line['km'] > 0 ? '<span '.($Line['lvl'] == 1 ? $span_format : '').'>'.Helper::Unknown(Running::Km(round($Line['km'],1)), '0,0 km').'</span>' : '';

		echo '<p'.$p_format.'>'.
			'<span class="right">'.
			$NumberOfActivities.' '.$Distance.
			'</span>'.
			'<span'.$span_format.'>'.$Line['name'].'</span>'.
			'</p>';
	}

	/**
	 * Get style for level
	 * @param int $level
	 * @return string
	 */
	private function getLevelStyle($level) {
		if ($level == 1)
			return ' style="font-weight:bold;"';

		if ($level == 2)
			return ' style="padding-left: 10px;"';

		return '';
	}

	/**
	 * Fetch data
	 * @param int $timestamp
	 * @return array
	 */
	private function fetchDataSince($timestamp) {
		$Data = DB::getInstance()->query('
				SELECT
					`sportid`,
					COUNT(`id`) as `anzahl`,
					SUM(`distance`) as `distanz_sum`,
					SUM(`s`) as `dauer_sum`
				FROM `'.PREFIX.'training`
				WHERE
					`time` >= '.$timestamp.' AND
					`sportid`='.CONF_RUNNINGSPORT.'
				GROUP BY `sportid`
				ORDER BY `distanz_sum` DESC, `dauer_sum` DESC
			')->fetch();

		if (!is_array($Data))
			return array('anzahl' => 0, 'distanz_sum' => 0, 'dauer_sum' => 0);

		return $Data;
	}

	/**
	 * Was there a run today?
	 * @return bool
	 */
	private function wasRunningToday() {
		return 0 < DB::getInstance()->query('
			SELECT
				`sportid`
			FROM `'.PREFIX.'training`
			WHERE
				DATE(FROM_UNIXTIME(`time`))=DATE(NOW()) AND
				`sportid`='.CONF_RUNNINGSPORT.'
			LIMIT 1
		')->rowCount();
	}

	/**
	 * Get the timeset as array for this panel
	 */
	private function getTimeset() {
		$timeset = array();

		// km-Spiel Saisonen
		$kmstart = new DateTime();
		$kmstart->setTime(0,0,0);
		$kmend   = new DateTime();
		$kmend->setTime(0,0,0);
		$now     = new DateTime("now");
		$kmstart->setISODate(date('Y'), 27, 1);

		if ($kmstart > $now) { 
			$kmstart->setISODate(date('Y'), 1, 1);
			$kmend->setISODate(date('Y'), 26, 7);
		} else { 
			$weeks = date('W', strtotime(date('Y'). '-12-31'));
			$kmend->setISODate(date('Y'), $weeks == 53 ? 53 : 52, 7);
		}

		// Zeitraeume fuer die Prognosen.
		$timeset['woche']  = array('name' => __('Week'), 'start' => new DateTime(date('o-\\WW')), 'end' => new Datetime(date('o-\\WW')." + 6 days"));
		$timeset['mon']    = array('name' => __('Month'), 'start' => new DateTime(date("Y-m-01")), 'end' => new Datetime(date('Y-m-t')));
		$timeset['hj']     = array('name' => __('Half-Year'), 'start' => new DateTime(date('m') < 7 ? date("Y-01-01") : date("Y-07-01")), 'end' => new Datetime(date('m') < 7 ? date("Y-06-30") : date('Y-12-31')));
		$timeset['saison'] = array('name' => __('Saison'), 'start' => $kmstart, 'end' => $kmend, 'note' => __('Note: Saison means the current saison in the german &quot;kmspiel&quot;'));
		$timeset['jahr']   = array('name' => __('Year'), 'start' => new DateTime(date("Y-01-01")), 'end' => new Datetime(date('Y-12-31')));

		return $timeset;
	}
}