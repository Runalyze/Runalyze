<?php
include('globals.php');
define('day', 24*60*60);

function sport($id, $array = false) {
	$db = mysql_query('SELECT * FROM `ltb_sports` WHERE `id`='.$id.' LIMIT 1');
	$dat = mysql_fetch_assoc($db);
	if ($array) return $dat;
	return $dat['name'];
}

function typ($id, $kurz = false, $splits = false, $array = false) {
	$db = mysql_query('SELECT * FROM `ltb_typ` WHERE `id`="'.$id.'" LIMIT 1');
	$dat = mysql_fetch_assoc($db);
	if ($splits) return $dat['splits'];
	if ($kurz) return $dat['abk'];
	if ($array) return $dat;
	else return $dat['name'];
}

function schuh($id) {
	$db = mysql_query('SELECT * FROM `ltb_schuhe` WHERE `id`='.$id.' LIMIT 1');
	$dat = mysql_fetch_assoc($db);
	return $dat['name'];
}

function wetter($id) {
	global $global;
	if ($id != 0) return '<img src="img/wetter/'.$global['wetter'][$id]['bild'].'" title="'.$global['wetter'][$id]['name'].'" style="vertical-align:bottom;" />';
	return '';
}

function tempo($km, $dauer, $sportid=0, $title=true) {
	if ($km == 0 || $dauer == 0) return '';
	$kmh_mode = 0;
	if ($sportid != 0) {
		$sport_db = mysql_query('SELECT * FROM `ltb_sports` WHERE `id`='.$sportid.' LIMIT 1');
		$sport = mysql_fetch_assoc($sport_db);
		$kmh_mode = $sport['kmh'];
	}
	if ($title) {
		if ($kmh_mode == 1) $title = ' title="'.pace($km, $dauer).'/km"';
		else $title = ' title="'.kmh($km, $dauer).' km/h"';
	}
	else $title = '';
	if ($kmh_mode == 1) return '<span'.$title.'>'.kmh($km, $dauer).' km/h</span>';
	else return '<span'.$title.'>'.pace($km, $dauer).'/km</span>';
}

function pace($km, $dauer) {
	return dauer(round($dauer/$km));
}

function kmh($km, $dauer) {
	return number_format($km*3600/$dauer, 1, ',', '.');
}

function km($km, $nachkommastellen = 1, $bahn = false) {
	if ($km == 0) return '';
	if ($bahn) return number_format($km*1000, 0, ',', '.').' m';
	return number_format($km, $nachkommastellen, ',', '.').' km';
}

function dauer($dauer, $tage = true) {
	$return = '';
	if ($dauer >= 86400 && $tage) $return = floor($dauer/86400).'d ';
	if ($dauer < 3600) $return .= (floor($dauer/60)%60).':'.zweinull($dauer%60);
	elseif ($tage) $return .= (floor($dauer/3600)%24).':'.zweinull(floor($dauer/60)%60).':'.zweinull($dauer%60);
	else $return .= floor($dauer/3600).':'.zweinull(floor($dauer/60)%60).':'.zweinull($dauer%60);
	return $return;
}

function zeit($dauer, $nullen = false) {
	if ($nullen) return floor($dauer/3600).':'.zweinull(floor($dauer/60)%60).':'.zweinull($dauer%60);
	if ($dauer < 60) return $dauer.'s';
	elseif ($dauer < 3600) return (floor($dauer/60)%60).':'.zweinull($dauer%60);
	else return floor($dauer/3600).':'.zweinull(floor($dauer/60)%60).':'.zweinull($dauer%60);
}

function bestzeit($dist, $dauer=false) {
	global $global;
	$db = mysql_query('SELECT `dauer`, `distanz` FROM `ltb_training` WHERE `typid`='.$global['wettkampf_typid'].' AND `distanz`="'.$dist.'" ORDER BY `dauer` ASC LIMIT 1');
	$bestzeit = mysql_fetch_assoc($db);
	if ($dauer) return ($bestzeit != '') ? $bestzeit['dauer'] : 0;
	if ($bestzeit != '') return zeit($bestzeit['dauer']);
	else return '<em>keine</em>';
}



/*** R E C H E N S P I E L E ***/

function trimp($id, $back = 0) {
	global $config, $global;
	if ($id != 0) {
		$db = mysql_query('SELECT * FROM `ltb_training` WHERE `id`='.$id.' LIMIT 1');
		$dat = mysql_fetch_assoc($db);
	}
	$faktor_a = ($config['geschlecht'] == 'm') ? 0.64 : 0.86;
	$faktor_b = ($config['geschlecht'] == 'm') ? 1.92 : 1.67;
	$sportid = ($dat['sportid'] != 0) ? $dat['sportid'] : 1;
	$sport = sport($sportid,true);
	$typ = ($dat['typid'] != 0) ? typ($dat['typid'],false,false,true) : 0;
	$HFavg = ($dat['puls'] != 0) ? $dat['puls'] : $sport['HFavg'];
	$RPE = ($typ != 0) ? $typ['RPE'] : $sport['RPE'];
	$HFperRest = ($HFavg - $global['HFrest']) / ($global['HFmax'] - $global['HFrest']);
	$TRIMP = $dat['dauer']/60 * $HFperRest * $faktor_a * exp($faktor_b * $HFperRest) * $RPE / 10;

	if ($back == 0)
	return round($TRIMP);
	// Anzahl der noetigen Minuten fuer $back als TRIMP-Wert
	return $back / ( $HFperRest * $faktor_a * exp($faktor_b * $HFperRest) * 5.35 / 10 );
}

function ATL($time = 0) {
	global $global;
	if ($time == 0) $time = time();
	$sum = mysql_fetch_assoc(mysql_query('SELECT SUM(`trimp`) as `sum` FROM `ltb_training` WHERE `time` BETWEEN '.($time-$global['atl_tage']*86400).' AND "'.$time.'"'));
	return round($sum['sum']/$global['atl_tage']);
}

function CTL($time = 0) {
	global $global;
	if ($time == 0) $time = time();
	$sum = mysql_fetch_assoc(mysql_query('SELECT SUM(`trimp`) as `sum` FROM `ltb_training` WHERE `time` BETWEEN '.($time-$global['ctl_tage']*86400).' AND "'.$time.'"'));
	return round($sum['sum']/$global['ctl_tage']);
}

function TSB($time = 0) {
	return CTL($time) - ATL($time);
}

function belastungscolor($belastung) {
	global $config;
	if ($belastung > 100) $belastung = 100;
	$gb = dechex(200 - 2*$belastung);
	if ((200 - 2*$belastung) < 16) $gb = '0'.$gb;
	return 'C8'.$gb.$gb;
}


function grundlagenausdauer($wert = false, $time = 0) {
	global $global;
	if ($time == 0) $time = time();
	$tagdauer = 24*60*60;
	$punkte = 0;
	// Wochenkilometer
	$wk_sum = 0;
	$db = mysql_query('SELECT `id`, `time`, `distanz` FROM `ltb_training` WHERE `time` BETWEEN '.($time-140*$tagdauer).' AND '.$time.' ORDER BY `time` DESC');
	while ($dat = mysql_fetch_array($db)) {
		$tage = round ( ($time - $dat['time']) / $tagdauer , 1 );
		$wk_sum += (2 - (1/70) * $tage) * $dat['distanz'];
	}
	$punkte += $wk_sum / 20;
	// Lange Läufe ...
	$db = mysql_query('SELECT `id`, `time`, `distanz` FROM `ltb_training` WHERE `typid`='.$global['ll_typid'].' AND `time` BETWEEN '.($time-70*$tagdauer).' AND '.$time.' ORDER BY `time` DESC');
	while ($dat = mysql_fetch_array($db)) {
		$punkte += ($dat['distanz']-15) / 2;
	}

	$punkte -= 50;
	if ($punkte < 0) $punkte = 0;
	if ($punkte > 100) $punkte = 100;

	if ($wert) return round($punkte);
	return round($punkte).' &#37;';
}

function prognose($dist, $bahn = false, $VDOT = 0) {
	global $global;

	$VDOT_neu = ($VDOT == 0) ? $global['VDOT_form'] : $VDOT;
	// Grundlagenausdauer
	if ($dist > 5)
		$VDOT_neu *= 1 - (1 - grundlagenausdauer(true)/100) * (exp(0.005*($dist-5)) - 1);
	$prognose_dauer = jd_prognose($VDOT_neu, $dist);
	$bisher_tag = ($prognose_dauer < bestzeit($dist,true)) ? 'del' : 'strong';
	$neu_tag = ($prognose_dauer > bestzeit($dist,true)) ? 'del' : 'strong';
	if ($VDOT != 0) return zeit($prognose_dauer);
	return '    <p><span><small>von</small> <'.$bisher_tag.' title="VDOT '.jd_VDOT($dist,bestzeit($dist,true)).'">'.bestzeit($dist).'</'.$bisher_tag.'> <small>auf</small> <'.$neu_tag.' title="VDOT '.$VDOT_neu.'">'.zeit($prognose_dauer).'</'.$neu_tag.'> <small>('.zeit($prognose_dauer/$dist).'/km)</small></span> <strong>'.km($dist, 0, $bahn).'</strong></p>'.NL;
}

function jd_VDOT($km, $dauer) { # Berechnung des VDOT aus Distanz und Dauer (^= Wettkampf)
	$min = $dauer/60;
	$m = 1000*$km;

	if ($min == 0)
		return 0;
	return (-4.6+0.182258*$m/$min+0.000104*pow($m/$min,2))/(0.8+0.1894393*exp(-0.012778*$min)+0.2989558*exp(-0.1932605*$min));
}

function jd_vVDOT($VDOT) { # Tempo bei 100% in m/min
	return 173.154+4.116*($VDOT-29);
}

function jd_pHFmax($pVDOT) { # %HFmax aus %VDOT
	return ($pVDOT+0.2812)/1.2812;
}

function jd_pVDOT($pHFmax) { # %VDOT aus %HFmax
	return 1.2812*$pHFmax-0.2812;
}

function jd_pace($mmin) { # Pace aus Tempo in m/min
	return dauer(60*1000/$mmin);
}

function jd_mmin($dauer) { # Tempo in m/min aus Pace in Sekunden/km
	return $dauer/1000/60;
}

function jd_VDOT_bereinigt($id) {
	global $global;
	$db = mysql_query('SELECT `sportid`, `distanz`, `dauer`, `puls` FROM `ltb_training` WHERE `id`='.$id.' LIMIT 1');
	$dat = mysql_fetch_assoc($db);
	return ($dat['puls'] != 0 && $dat['sportid'] == 1) ? round($global['VDOTfaktor']*jd_VDOT($dat['distanz'],$dat['dauer'])/(jd_pVDOT($dat['puls']/206)),2) : 0;
}

function jd_prognose($VDOT_neu,$distanz=5) {
	$dauer = 10*$distanz;
	$VDOTb = 150;
	$do = true;
	while ($do) {
		$dauer++;
		$VDOTa = $VDOTb;
		$VDOTb = jd_VDOT($distanz,$dauer);
		if ($VDOTa > $VDOT_neu && $VDOT_neu > $VDOTb) $do = false;
	}
	return $dauer;
}



/*** A L L G E M E I N E S ***/

function zweinull($zahl) {
	if ($zahl < 10) return '0'.$zahl;
	return $zahl;
}

function unbekannt($var, $char='?') {
	if (is_numeric($var) && $var != 0) return $var;
	if (!is_numeric($var) && $var != '') return $var;
	return $char;
}

function cut($text,$cut=29) {
	if (strlen($text) >= $cut) $text = substr ($text, 0, $cut-3).'...';
	return $text;
}

function wochenstart($heute) {
	$w = date("w", $heute); if ($w == 0) $w = 7; $w -= 1;
	return mktime(0,0,0,date("m",$heute),date("d",$heute)-$w,date("Y",$heute));
}

function wochenende($heute) {
	$start = wochenstart($heute);
	return mktime(23,59,50,date("m",$start),date("d",$start)+6,date("Y",$start));
}

function wochentag($w, $kurz=false) {
	switch($w%7) {
		case 0: return ($kurz) ? "So" : "Sonntag";
		case 1: return ($kurz) ? "Mo" : "Montag";
		case 2: return ($kurz) ? "Di" : "Dienstag";
		case 3: return ($kurz) ? "Mi" : "Mittwoch";
		case 4: return ($kurz) ? "Do" : "Donnerstag";
		case 5: return ($kurz) ? "Fr" : "Freitag";
		case 6: return ($kurz) ? "Sa" : "Samstag";
	}
}

function monat($m, $kurz = false) {
	switch($m) {
		case 1: return ($kurz) ? "Jan" :  "Januar";
		case 2: return ($kurz) ? "Feb" :  "Februar";
		case 3: return ($kurz) ? "Mrz" :  "M&auml;rz";
		case 4: return ($kurz) ? "Apr" :  "April";
		case 5: return ($kurz) ? "Mai" :  "Mai";
		case 6: return ($kurz) ? "Jun" :  "Juni";
		case 7: return ($kurz) ? "Jul" :  "Juli";
		case 8: return ($kurz) ? "Aug" :  "August";
		case 9: return ($kurz) ? "Sep" :  "September";
		case 10: return ($kurz) ? "Okt" :  "Oktober";
		case 11: return ($kurz) ? "Nov" :  "November";
		case 12: return ($kurz) ? "Dez" :  "Dezember";
	}
}

function komma($string) {
	return ereg_replace(",", ".", $string);
}

function textarea($text) {
	return stripslashes(ereg_replace("&","&amp;",$text));
}

function umlaute($text) {
	$text = ereg_replace("ÃŸ","ß",$text);
	$text = ereg_replace("Ã„","Ä",$text); $text = ereg_replace("Ã–","Ö",$text);
	$text = ereg_replace("Ãœ","Ü",$text); $text = ereg_replace("Ã¤","ä",$text);
	$text = ereg_replace("Ã¶","ö",$text); $text = ereg_replace("Ã¼","ü",$text);
	return $text;
}



/*** D A T E N B A N K ***/

function connect() {
	global $global, $config;
	mysql_connect ($global['db']['host'], $global['db']['user'], $global['db']['pass']);
	mysql_select_db ($global['db']['base']);

	config();
}

function close() {
	mysql_close();
}

function check_modus($row) {
	$db = mysql_query('SELECT `name`, `modus` FROM `ltb_dataset` WHERE `name`="'.$row.'" LIMIT 1');
	$dat = mysql_fetch_assoc($db);
	return $dat['modus'];
}

function config() {
	global $config, $global;
	// Allgemein
	$config_db = mysql_query('SELECT * FROM `ltb_config` LIMIT 1');
	$config = mysql_fetch_assoc($config_db);

	// Gewicht und Puls
	$user_db = mysql_query('SELECT MAX(`gewicht`) as `gewicht_max`, MIN(`gewicht`) as `gewicht_min`, MAX(`puls_ruhe`) as `puls_max`, MIN(`puls_ruhe`) as `puls_min` FROM `ltb_user` ORDER BY `id` DESC LIMIT 30');
	$config_dat = mysql_fetch_assoc($user_db);
	$gewicht_avg = ($config_dat['gewicht_max'] + $config_dat['gewicht_min']) / 2;
	$start = round($gewicht_avg);
	while ($start > $config_dat['gewicht_min']) $start -= 2;
	$ende = $start + 2 * (round($gewicht_avg) - $start);
	$config['gewicht_min'] = $start;
	$config['gewicht_max'] = $ende;
	$puls_avg = ($config_dat['puls_max'] + $config_dat['puls_min']) / 2;
	$start = round($puls_avg);
	while ($start > $config_dat['puls_min']) $start -= 2;
	$ende = $start + 2 * (round($puls_avg) - $start);
	$config['puls_min'] = $start;
	$config['puls_max'] = $ende;

	// Startjahr
	$jahr_db = mysql_query('SELECT MIN(`time`) as `time` FROM `ltb_training`');
	$jahr = mysql_fetch_assoc($jahr_db);
	$config['time-start'] = $jahr['time'];
	$config['startjahr'] = date("Y",$jahr['time']);

	// Global
	$user_db = mysql_query('SELECT * FROM `ltb_user` ORDER BY `time` DESC LIMIT 1');
	$global_dat = mysql_fetch_assoc($user_db);
	$global['HFmax'] = $global_dat['puls_max'];
	$global['HFrest'] = $global_dat['puls_ruhe'];
	$i = 0;
	$wetter_db = mysql_query('SELECT * FROM `ltb_wetter`');
	while($dat = mysql_fetch_assoc($wetter_db)) { $i++; $global['wetter'][$i] = $dat; }

	# Berechnung des besten VDOT-Wertes
	$VDOT_top = 0;
	$VDOT_top_distanz = 0;
	$distanzen = array(1.5, 3, 5, 10, 16.1, 21.1, 42.2);
	foreach ($distanzen as $distanz) {
		if (bestzeit($distanz,true) != 0) {
			if (jd_VDOT($distanz, bestzeit($distanz,true)) > $VDOT_top
				&& mysql_num_rows(mysql_query('SELECT 1 FROM `ltb_training` WHERE `typid`='.$global['wettkampf_typid'].' AND `puls`!=0 AND `distanz`="'.$distanz.'"')) > 0 ) {
				$VDOT_top = jd_VDOT($distanz,bestzeit($distanz,true));
				$VDOT_top_distanz = $distanz;
			}
		}
	}
	$VDOT_top_db = mysql_query('SELECT `dauer`, `distanz`, `puls` FROM `ltb_training` WHERE `typid`='.$global['wettkampf_typid'].' AND `puls`!=0 AND `distanz`="'.$VDOT_top_distanz.'" ORDER BY `dauer` ASC LIMIT 1');
	$VDOT_top_dat = mysql_fetch_assoc($VDOT_top_db);
	$VDOT_max = round(jd_VDOT($VDOT_top_dat['distanz'],$VDOT_top_dat['dauer'])/(jd_pVDOT($VDOT_top_dat['puls']/206)),2);

	$global['VDOTfaktor'] = $VDOT_top / $VDOT_max;

	$VDOT_form = 0; $num = 0;
	$VDOT_form_db = mysql_query('SELECT `id` FROM `ltb_training` WHERE `sportid`=1 && `puls`!=0 && `time`>'.(time()-30*24*60*60).' ');
	while ($VDOT_form_dat = mysql_fetch_array($VDOT_form_db)) { $VDOT_form += jd_VDOT_bereinigt($VDOT_form_dat['id']); $num++; }
	$global['VDOT_form'] = round($VDOT_form/$num,5);
}
?>
