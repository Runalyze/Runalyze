<?php
function dataset($id, $cut = false) {
	global $training;
	$set_db = mysql_query('SELECT * FROM `ltb_dataset` WHERE `id`='.$id.' LIMIT 1');
	$set = mysql_fetch_assoc($set_db);
	$class = $set['class'] != '' ? ' class="'.$set['class'].'"' : '';
	$style = $set['style'] != '' ? ' style="'.$set['style'].'"' : '';
	$training['cut'] = $cut;
	$inhalt = eval('return '.$set['function']);
	return '<td'.$class.$style.'>'.($inhalt == '' ? '&nbsp;' : $inhalt).'</td>';
}

function dataset_more($id) {
	global $training;
	$set_db = mysql_query('SELECT * FROM `ltb_dataset` WHERE `id`='.$id.' LIMIT 1');
	$set = mysql_fetch_assoc($set_db);
	$style = $set['style'] != '' ? '<span style="'.$set['style'].'">' : '';
	$style_ende = $set['style'] != '' ? '</span>' : '';
	return $style.eval('return '.$set['function']).$style_ende;
}

function dataset_sport() {
	global $training;
	$sport_dat = sport($training['sportid'],true);
	return '<img class="link" onclick="seite(\'training\',\''.$training['id'].'\');training_tr(\''.$training['id'].'\')" title="'.$sport_dat['name'].'" src="img/sports/'.$sport_dat['bild'].'" />';
}

function dataset_typ() {
	global $training;
	$typ_array = typ($training['typid'],false, false,true);
	$text = typ($training['typid'], true);
	if ($typ_array['RPE'] > 4) return '<strong>'.$text.'</strong>';
	else return $text;
}

function dataset_time() {
	global $training;
	return date("H:i", $training['time']) != "00:00" ? date("H:i", $training['time']).' Uhr' : '';
}

function dataset_distanz() {
	global $training;
	return ($training['distanz'] != 0) ? km($training['distanz'],1,$training['bahn']) : '';
}

function dataset_dauer() {
	global $training;
	return zeit($training['dauer']);
}

function dataset_pace() {
	global $training;
	return tempo($training['distanz'], $training['dauer'], $training['sportid']);
}

function dataset_hm() {
	global $training;
	return ($training['hm'] != 0)
		? '<span title="&oslash; '.round($training['hm']/$training['distanz']/10,2).' &#37;">'.$training['hm'].' hm</span>'
		: '';
}

function dataset_kalorien() {
	global $training;
	return unbekannt($training['kalorien'],'').' kcal';
}

function dataset_puls_func($puls, $time) {
	global $config, $training;
	$puls = round($puls);
	if ($puls == 0) return '';
	if ($config['puls_mode'] != "bpm") {
		$HFmax_db = mysql_query('SELECT * FROM `ltb_user` ORDER BY ABS(`time`-'.$time.') ASC LIMIT 1');
		if (mysql_num_rows($HFmax_db) != 0) {
			$user_dat = mysql_fetch_assoc($HFmax_db);
			$HFmax = $user_dat['puls_max'];
			return '<span title="'.$puls.'bpm">'.round(100*$puls/$HFmax).' &#37;</span>';
		}
	}
	return $puls;
}

function dataset_puls() {
	global $training;
	return dataset_puls_func($training['puls'],$training['time']);
}

function dataset_puls_max() {
	global $training;
	return dataset_puls_func($training['puls_max'],$training['time']);
}

function dataset_trimp() {
	global $training;
	return '<span style="color:#'.belastungscolor($training['trimp']).';">'.$training['trimp'].'</span>';
}

function dataset_temperatur() {
	global $training;
	return ($training['temperatur'] != 0) ? $training['temperatur'].' &#176;C' : '';
}

function dataset_wetter() {
	global $training;
	return ($training['wetterid'] != 1) ? wetter($training['wetterid']) : '';
}

function dataset_strecke() {
	global $training;
	if (!$training['cut'])
	return ($training['strecke'] != '') ? 'Strecke: '.$training['strecke'] : '';
	elseif (strlen($training['strecke']) < 12)
	return $training['strecke'];
	else
	return  '<span title="'.$training['strecke'].'">'.substr($training['strecke'], 0, 10).'...</span>';
}

function dataset_kleidung() {
	global $training;
	if ($training['kleidung'] != '') {
		$kleidung_db = mysql_query('SELECT * FROM `ltb_kleidung` WHERE `id` IN ('.$training['kleidung'].') ORDER BY `order` ASC');
		while ($kleidung_dat = mysql_fetch_array($kleidung_db)) {
			if ($kleidung != '') $kleidung .= ', ';
			$kleidung .= $kleidung_dat['name'];
		}
		if (!$training['cut'] || strlen($kleidung) < 12)
		return $kleidung;
		else
		return  '<span title="'.$kleidung.'">'.substr($kleidung, 0, 10).'...</span>';
	}
	return '';
}

function dataset_splits() {
	global $training;
	return ($training['splits'] != '') ? '<img class="link" onclick="seite(\'splits\',\''.$training['id'].'\');training_tr(\''.$training['id'].'\')" src="img/clock.png" />' : '';
}

function dataset_bemerkung() {
	global $training;
	return '<span title="'.$training['bemerkung'].'">'.cut($training['bemerkung'], 20).'</span>';
}

function dataset_trainingspartner() {
	global $training;
	return $training['trainingspartner'] != '' ? 'mit '.$training['trainingspartner'] : '';
}

function dataset_laufabc() {
	global $training;
	return $training['laufabc'] == 1 ? '<img src="img/abc.png" alt="Lauf-ABC" />' : '';
}

function dataset_schuh() {
	global $training;
	if (!$training['cut'] || strlen(schuh($training['schuhid'])) < 12)
	return schuh($training['schuhid']);
	else
	return  '<span title="'.schuh($training['schuhid']).'">'.substr(schuh($training['schuhid']), 0, 10).'...</span>';
}

function dataset_vdot() {
	global $training, $global;
	$VDOT = $training['vdot'];
	if ($VDOT == 0) return '';
	if ( $VDOT > ($global['VDOT_form']+3) ) $form = '++';
	elseif ( $VDOT > ($global['VDOT_form']+1) ) $form = '+';
	elseif ( $VDOT < ($global['VDOT_form']-3) ) $form = '--';
	elseif ( $VDOT < ($global['VDOT_form']-1) ) $form = '-';
	else $form = '0';

	return '<img src="img/form'.$form.'.png" title="'.$VDOT.': 3.000m in '.prognose(3,0,$VDOT).', 5 km in '.prognose(5,0,$VDOT).', 10 km in '.prognose(10,0,$VDOT).', HM in '.prognose(21.1,0,$VDOT).', " />';
}

?>