<?php header('Content-type: text/html; charset=ISO-8859-1');
include_once('../../config/functions.php');
connect();

// TODO From DB / Input
$ziel = array('km' => 10, 'time' => mktime(15,0,0,4,24,2010), 'date' => '24.04.2010');

$trimp_week = trimp(0,7*CTL());
$pace_month = mysql_fetch_assoc(mysql_query('SELECT AVG(`dauer`/60/`distanz`) as `avg` FROM `ltb_training` WHERE `time` > '.(time()-30*24*60*60).' AND `sportid`=1 LIMIT 1'));
$wkm = $trimp_week / $pace_month['avg'];
$wkm = 50;
$heute = time();
$heute = mktime(15,0,0,2,8,2010);
$trainingstage = array('', 'Q1', 'L', 'Q2', '', 'L', 'Q3|L');
$wochente = 5;
$phase = 2;

$VDOT = $global['VDOT_form'];
$wochen_num = ceil(($ziel['time']-$heute)/60/60/24/7);
$wochen_max = 24;

function wochen_phasen_move($id) {
	global $wochen_phasen, $wochen_max;

	rsort($wochen_phasen[$id]);
	$wochen_max -= 6;
	foreach ($wochen_phasen[$id] as $week)
		foreach ($wochen_phasen as $pid => $wochen_phase)
			if ($pid != $id)
				foreach ($wochen_phase as $wid => $w)
					if ($w > $week) $wochen_phasen[$pid][$wid]--;
}

$wochen_phasen = array();
$wochen_phasen[] = array(1,2,3,13,21,23);
$wochen_phasen[] = array(10,11,12,18,19,20);
$wochen_phasen[] = array(7,8,9,14,15,16);
$wochen_phasen[] = array(4,5,6,17,22,24);
$phasen = array();
if ($phase <= 1) $phasen[] = array('kurz' => 'GR', 'text' => 'Grundlagenphase', 'wochen' => $wochen_phasen[0], 'Q1' => 2, 'Q2' => 1, 'Q3' => 1);
else wochen_phasen_move(0);
if ($phase <= 2) $phasen[] = array('kurz' => 'FQ', 'text' => 'Fr&uuml;he Qualit&auml;tsphase', 'wochen' => $wochen_phasen[1], 'Q1' => 6, 'Q2' => 4, 'Q3' => ($ziel['km'] <= 15) ? 5 : 2);
else wochen_phasen_move(1);
if ($phase <= 3) $phasen[] = array('kurz' => 'UQ', 'text' => '&Uuml;bergangsqualit&auml;tsphase', 'wochen' => $wochen_phasen[2], 'Q1' => 5, 'Q2' => ($ziel['km'] <= 15) ? 6 : 4, 'Q3' => 2);
else wochen_phasen_move(2);
if ($phase <= 4) $phasen[] = array('kurz' => 'AQ', 'text' => 'Abschlussqualit&auml;tsphase', 'wochen' => $wochen_phasen[3], 'Q1' => ($ziel['km'] <= 15) ? 4 : 3, 'Q2' => ($ziel['km'] <= 15) ? 5 : 4, 'Q3' => ($ziel['km'] <= 15) ? 6 : 2);
else wochen_phasen_move(3);

$t = array();
$t[] = array('kurz' => 'RL', 'name' => 'Regenerationslauf', 'pVDOT' => '59-64', 'pHF' => '65-70', 'km' => '2-8', 'pWKM' => 10, 'WA' => false, 'te' => array(0));
$t[] = array('kurz' => 'DL', 'name' => 'Dauerlauf', 'pVDOT' => '65-74', 'pHF' => '71-79', 'km' => '4-15', 'pWKM' => 30, 'WA' => false, 'te' => array(1));
$t[] = array('kurz' => 'LL', 'name' => 'Langer Lauf', 'pVDOT' => '65-74', 'pHF' => '71-79', 'km' => '16-35', 'pWKM' => 25, 'WA' => false, 'te' => array(2,3));
$t[] = array('kurz' => 'TDL', 'name' => 'Marathontempo', 'pVDOT' => '75-84', 'pHF' => '80-90', 'km' => '5-20', 'pWKM' => 12, 'WA' => true, 'te' => array(4,5));
$t[] = array('kurz' => 'TDL', 'name' => 'Schwellenlauf', 'pVDOT' => '83-88', 'pHF' => '88-92', 'km' => '5-15', 'pWKM' => 10, 'WA' => true, 'te' => array(6,7,8,9));
$t[] = array('kurz' => 'IT', 'name' => 'Intervalltraining', 'pVDOT' => '95-100', 'pHF' => '98-100', 'km' => '5-15', 'pWKM' => 8, 'WA' => true, 'te' => array(10,11));
$t[] = array('kurz' => 'WHL', 'name' => 'Wiederholungsl&auml;ufe', 'pVDOT' => '105-110', 'pHF' => '98-100', 'km' => '4-8', 'pWKM' => 5, 'WA' => true, 'te' => array(12,13,14));

$te = array();
$te[] = array('t' => 0, 'name' => '');
$te[] = array('t' => 1, 'name' => '');
$te[] = array('t' => 2, 'name' => '');
$te[] = array('t' => 2, 'name' => 'LL mit [3-15]km Endbeschleunigung');
$te[] = array('t' => 3, 'name' => '[1%-50%]km TDL in [100%v]');
$te[] = array('t' => 3, 'name' => '[51%-100%]km TDL in [1%v]');
$te[] = array('t' => 4, 'name' => '[1%-50%]km TDL in [50%v]');
$te[] = array('t' => 4, 'name' => '[51%-100%]km TDL in [1%v]');
$te[] = array('t' => 4, 'name' => '[2-6]x 2.000m in [100%v], 400m TP');
$te[] = array('t' => 4, 'name' => '[1-4]x 3.000m in [75%v], 600m TP');
$te[] = array('t' => 5, 'name' => '[3-10]x 1.000m in [100%v], 400m TP');
$te[] = array('t' => 5, 'name' => '[8-25]x 400m in [100%v], 200m TP');
$te[] = array('t' => 6, 'name' => '[10-20]x 200m in [100%v], 200m TP');
$te[] = array('t' => 6, 'name' => '[5-10]x 400m in [50%v], 400m TP');
$te[] = array('t' => 6, 'name' => '[20-40]x 100m in [100%v], 100m TP');

function te_replace($str) {
	$str = preg_replace('#\[([0-9]*)-([0-9]*)\]#e', "te_replace_x('\\1','\\2')", $str);
	$str = preg_replace('#\[([0-9]*)%-([0-9]*)%\]#e', "te_replace_p('\\1','\\2')", $str);
	$str = preg_replace('#\[([0-9]*)%v\]#e', "te_replace_v('\\1')", $str);
	return $str;
}

function te_replace_x($a,$b) {
	global $km;
	return $a + round($km['p']*($b-$a)/100);
}

function te_replace_p($a,$b) {
	global $km, $tid, $t;
	$km_dat = explode('-',$t[$tid]['km']);
	$p = $a + $km['p']*($b-$a)/100;
	return $km_dat[0] + round($p*($km_dat[1]-$km_dat[0])/100);
}

function te_replace_v($p) {
	global $t, $tid, $VDOT;
	$v_dat = explode('-',$t[$tid]['pVDOT']);
	$pVDOT = $v_dat[0] + round($p*($v_dat[1]-$v_dat[0])/100);
	return jd_pace(jd_vVDOT($VDOT)*$pVDOT/100);
}

$woche = array();
for ($wochen = $wochen_num; $wochen >= 1; $wochen--) {
	if ($wochen > $wochen_max)
		$woche[] = array('phase' => ($wochen%sizeof($phasen)));
	else {
		foreach ($phasen as $num => $array)
			if (in_array($wochen, $phasen[$num]['wochen'])) $woche[] = array('phase' => $num);
	}
}
sort($woche);
?>
<h1>
	Trainingsplan
	<small>
	- <?php echo $phasen[$woche[0]['phase']]['text']; ?>
	- <?php echo $ziel['km']; ?> km am <?php echo $ziel['date']; ?>
	- noch <?php echo $wochen_num; ?> Wochen
	</small>

	<small class="right">
	VDOT: <em><?php echo round($VDOT,2); ?></em>
	</small>
</h1>

<div class="right small r">
<?php
foreach ($t as $train) {
	$train_tempo = explode('-',$train['pVDOT']);
	$train_tempo_von = jd_pace(jd_vVDOT($VDOT)*$train_tempo[1]/100);
	$train_tempo_bis = jd_pace(jd_vVDOT($VDOT)*$train_tempo[0]/100);
	echo ('
'.$train['kurz'].': <em>'.$train_tempo_von.'</em> - <em>'.$train_tempo_bis.'</em>/km<br />');
}
?>
</div>
<em><?php echo round($wkm/5)*5; ?> km</em>/Woche, <em><?php echo $wochente; ?> TE</em>/Woche<br />
<br />
<?php
foreach ($woche as $num => $array) {
	$woche_km = round((0.9+0.1*(((($num+1)%4)-($wochen_num%4)+5)%4))*$wkm);
	echo ('
<table class="small left" cellspacing="0" width="40%">
	<tr class="b">
		<td>'.($num+1).'. Woche</td>
		<td colspan="2">'.$phasen[$array['phase']]['text'].'</td>
		<td class="r">'.$woche_km.' km</td>
	</tr>
	<tr class="space"><td colspan="4" /></tr>');

	$ws = wochenstart($ziel['time']-($wochen_num-$num-1)*7*day);
	
	$wochenplan = array();
	$km = array('min' => 0, 'max' => 0);
	foreach ($trainingstage as $w => $trainingstyp) {
		if ($trainingstyp == '')
			$wochenplan[] = '';
		else {
			$explode = explode('|',$trainingstyp);
			$typ = (sizeof($explode) > 1) ? $explode[$num%sizeof($explode)] : $explode[0];
			switch ($typ) {
				case 'L': $typid = 1; break;
				case 'R': $typid = 0; break;
				default: $typid = $phasen[$array['phase']][$typ]; break;
			}
			$wochenplan[] = $typid;
			$km_dat = explode('-',$t[$typid]['km']);
			$WA = ($t[$typid]['WA']) ? 6 : 0;
			$km['min'] += $km_dat[0] + $WA;
			$km['max'] += $km_dat[1] + $WA;
		}
	}

	if ($woche_km < $km['min']) $km['p'] = 1;
	elseif ($woche_km > $km['max']) $km['p'] = 100;
	else $km['p'] = round(100*(($woche_km-$km['min'])/($km['max']-$km['min'])));

	foreach ($wochenplan as $w => $tid) {
		$training = ($tid != '') ? $te[$t[$tid]['te'][$num%sizeof($t[$tid]['te'])]]: '';
		$tkm_dat = explode('-', $t[$tid]['km']);
		$tkm = round($tkm_dat[0]+($tkm_dat[1]-$tkm_dat[0])*$km['p']/100);
		if ($t[$tid]['WA']) $tkm += 6;
		echo ('
	<tr class="a'.($w%2+1).'">
		<td><small>'.date("d.m.", $ws+$w*day).'</small> '.substr(wochentag(date("w", $ws+$w*day)),0,2).'</td>
		<td>'.($tid != '' ? $t[$tid]['kurz'] : '').'</td>
		<td>'.te_replace($training['name']).'</td>
		<td class="r">'.($tkm != 0 ? $tkm.' km' : '').'</td>
	</tr>');
	}
	echo ('
</table>');
}
?>
<br class="clear" />