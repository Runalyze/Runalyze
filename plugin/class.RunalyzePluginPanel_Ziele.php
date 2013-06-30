<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Ziele".
 */
$PLUGINKEY = 'RunalyzePluginPanel_Ziele';
/**
 * Class: RunalyzePluginPanel_Ziele
 * @author Ulrich Kiermayr <ulrich@kiermayr.at>
 * License: GPL 
 */
class RunalyzePluginPanel_Ziele extends PluginPanel {
	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$PANEL;
		$this->name = 'Ziele';
		$this->description = 'Progonosen und Ziele f&uuml;r die Laufleistung in km in den ausgew&auml;hlten Zeitr&auml;men.';

		$this->textAsRightSymbol = true;
	}

    /**
     * Display long description 
     */

    protected function displayLongDescription() {
        echo HTML::p('Mit diesem Plugin k&auml;nnen Prognosen f&uuml; die Laufleistung in km in den ausgew&auml;hlten Zeitr&auml;men berechnet und angezeigt werden.');
        echo HTML::p('Wenn ein Ziel definiert ist (Wert &gt; 0) wird ausserdem angezeigt, wieviel noch f&uuml;r die Erreichung des Ziels notwendig ist.');
        echo HTML::p('Ein virtuelles Pace Bunny l&auml;uft gleichm&auml;ssig mit dem selben Ziel - der Vorsprung oder R&uuml;ckstand zum Pace Bunny wird ebenfalls angezeigt.');
        echo HTML::p('Hinweis: Saison bezieht sich auf die aktuelle Saison im <a href="http://www.kmspiel.de">kmspiel</a>.');
    }


	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();
        foreach ($this->getTimeset() as $i => $timeset) {
            $config['ziel_show_'.$i] = array('type' => 'bool', 'var' => true, 
                'description' => Ajax::tooltip($timeset['name'].' einblenden', 
                    $timeset['name'].' in der Liste der Ziele einblenden und die Prognose f&uuml;r diesen Zeitraum berechnen<br>'. ( isset($timeset['note'])  ? $timeset['note'] : '' ) ));
            $config['ziel_'.$i] = array('type' => 'int', 'var' => 0, 
                'description' => Ajax::tooltip('Ziel: '.$timeset['name'], 
                    'Das Ziel f&uuml;r den Zeitraum '.$timeset['start']->format("d.m.Y").' bis '.$timeset['end']->format("d.m.Y").'<br>0 um kein Ziel f&uuml; diesen Zeitraum zu definieren und das Pace Bunny auszublenden<br>'. ( isset($timeset['note'])  ? $timeset['note'] : '' )  ));
        }
		return $config;
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
        $buttons = array();
		foreach ($this->getTimeset() as $i => $timeset) {
            if ( ! $this->config['ziel_show_'.$i]['var'] ) { continue; }
			array_push($buttons, Ajax::change($timeset['name'], 'bunny', '#bunny_'.$i));
		}
		return '<span class="smallHeadNavi">'.implode(' | ',$buttons).'</span>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$Mysql = Mysql::getInstance();
	
		echo('<div id="bunny">');
        
        $first = true;    
        foreach ($this->getTimeset() as $i => $timeset) {
            if ( ! $this->config['ziel_show_'.$i]['var'] ) { continue; }
            echo('<div id="bunny_'.$i.'" class="change"'.($first == true ? '' : ' style="display:none;"').'>');
            $first = false;
/* Ziele
 *
 * Aktuell: (x-mal) km
 * Prognose: (y-mal) km
 * 
 * Ziel: km
 * Fehlende km
 * Notwendige leistung/Tag
 *
 * Ziele: km
 * Unterschied: 
 *
 */
            // Some Numbers we need
            $start = $timeset['start'];
            $now   = new DateTime(date('Y-m-d'));
            $end   = $timeset['end'];
            $days  = 1 + (int)date_diff($start, $now)->format('%a');
            $dauer = 1 + (int)date_diff($start, $end)->format('%a');

            //
            $ziel = $this->config['ziel_'.$i]['var'];

			$data = $Mysql->fetchAsArray('SELECT `sportid`, COUNT(`id`) as `anzahl`, SUM(`distance`) as `distanz_sum`, SUM(`s`) as `dauer_sum`  FROM `'.PREFIX.'training` WHERE `time` >= '.$start->getTimestamp().' AND `sportid`='.CONF_RUNNINGSPORT.' GROUP BY `sportid` ORDER BY `distanz_sum` DESC, `dauer_sum` DESC');
			foreach ($data as $dat) {
                $Sport = new Sport($dat['sportid']);


                // Do the Calculations for Pace Bunny at al.
                $ziele = array();
                $ziele['leistung'] = array( 'name' => 'Aktuell', 'lvl' => 1, 'km' => $dat['distanz_sum'], 'anz' => $dat['anzahl']);
		        $ziele['leistung_tag'] = array('name' => 'pro Tag', 'lvl' => 2, 'km' => $ziele['leistung']['km'] / $days ); 
                $ziele['leistung_monat'] = array('name' => 'pro Woche', 'lvl' => 2, 'km' => $ziele['leistung_tag']['km'] * 7, 'anz' => $dat['anzahl'] / $days * 7);

                $ziele['prognose'] = array('name' => 'Prognose', 'lvl' => 1, 'sep' => 1, 'km' => $ziele['leistung']['km'] / $days *  $dauer, 'anz' => $ziele['leistung']['anz'] / $days * $dauer );
                if ( $ziel > 0 ) {
                    $ziele['ziel'] = array('name' => 'Ziel', 'lvl' => 1, 'sep' => 1, 'km' => $ziel);
                    if ( $ziele['leistung']['km'] < $ziel ) {
                        $ziele['ziel_togo'] = array('name' => 'to go', 'lvl' => 2, 'km' => $ziel - $ziele['leistung']['km'] );
                        $ziele['ziel_togo_days'] = array('name' => 'pro Tag', 'lvl' => 2, 'km' => $ziele['ziel_togo']['km'] / ( $dauer - $days + 1) );
                        $ziele['ziel_togo_weeks'] = array('name' => 'pro Woche', 'lvl' => 2, 'km' => $ziele['ziel_togo_days']['km'] * 7 );
                    } else {
                        $ziele['ziel']['name'] = 'Ziel erreicht';
                    }
                    $ziele['bunny'] = array('name' => 'Pace Bunny', 'lvl' => 1, 'sep' => 1, 'km' => $ziel / $dauer * $days);
                    $ziele['bunny_diff'] = array( 'lvl' => 2, 'km' => abs( $ziele['bunny']['km'] - $ziele['leistung']['km'] ) );
                    if ( $ziele['bunny']['km'] > $ziele['leistung']['km'] ) {
                        $ziele['bunny_diff']['name'] = 'R&uuml;ckstand';
                    } else {
                        $ziele['bunny_diff']['name'] = 'Vorsprung';
                    }

                }
                foreach ( $ziele as $z ) {
                    switch ($z['lvl']) {
                        case 1: 
                            $format = 'style="font-weight:bold;"';
                            break;
                        case 2: 
                            $format = 'style="padding-left: 10px;"';
                            break;
                        default:
                            $format = '';
                    }
                    echo('<p' . ( isset($z['sep']) ? ' style="border-top:1px solid #ccc;"' : '' ). '>');
			        echo('<span class="right">');
                    if ( isset($z['anz'] ) ) {
                        echo('<small><small>('.Helper::Unknown(round($z['anz'],1), '0').'-mal) </small></small>');
                    }
                    echo('<span '.( $z['lvl'] == 1 ? $format : '').'>'.Helper::Unknown(Running::Km(round($z['km'],1)), '0,0 km').'</span>');
                    echo('</span>');
                    echo('<span '.$format.'>'.$z['name'].'</span>');
                    echo('</p>'.NL);

                }    
			}

			if (empty($data))
				echo('<p><em>Noch keine Daten vorhanden.</em></p>');
	
			echo('<small class="right">'.$start->format("d.m.Y").' bis '.$end->format("d.m.Y").' ('.$dauer.' Tage)</small>');
			echo HTML::clearBreak();
			echo('</div>');
		}
	
		echo('</div>');
	}

	/**
	 * Get the timeset as array for this panel
	 */
	private function getTimeset() {
        $timeset = array();

        // km-Spiel Saisonen
        $kmstart = new DateTime();
        $kmend   = new DateTime();
        $now     = new DateTime("now");
        $kmstart->setISODate(date('Y'), 1, 1);
        if ( $kmstart < $now ) { 
            $kmend->setISODate(date('Y'), 26, 7);
        } else { 
            $kmstart->setISODate(date('Y'), 27, 1);
            $weeks = date('W', strtotime(date('Y'). '-12-31'));
            $kmend->setISODate(date('Y'), $weeks == 53 ? 53 : 52, 7);
        }

        // Zeitraeume fuer die Prognosen.
        //
		$timeset['mon']    = array('name' => 'Monat', 'start' => new DateTime(date("Y-m-01")), 'end' => new Datetime(date('Y-m-t')));
		$timeset['hj']     = array('name' => 'Halbjahr', 'start' => new DateTime(date('m') < 7 ? date("Y-01-01") : date("Y-07-01")), 'end' => new Datetime(date('m') < 7 ? date("Y-06-30") : date('Y-12-31')));
        $timeset['saison'] = array('name' => 'Saison', 'start' => $kmstart, 'end' => $kmend, 'note' => 'Hinweis: Saison ist die aktuelle Saison im kmspiel');
		$timeset['jahr']   = array('name' => 'Jahr', 'start' => new DateTime(date("Y-01-01")), 'end' => new Datetime(date('Y-12-31')));
	
		return $timeset;
	}
}
