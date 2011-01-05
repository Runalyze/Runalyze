<?php
header('Content-type: text/html; charset=ISO-8859-1');

include_once('../config/functions.php');
include_once('../config/dataset.php');
connect();
$heute = $_GET['heute'];
$start = $_GET['start'];
$ende = $_GET['ende'];
$dat = array( explode(".", date("d.m.Y", $heute)), explode(".", date("d.m.Y", $start)), explode(".", date("d.m.Y", $ende)) );

// L I N K - D E F I N I T I O N E N
$tage = round(($ende - $start) / 86400);
if ($tage == 7) { # Woche
	$heute_next = mktime(0,0,0,$dat[0][1],$dat[0][0]+7,$dat[0][2]);
	$start_next = mktime(0,0,0,$dat[1][1],$dat[1][0]+7,$dat[1][2]);
	$ende_next = mktime(23,59,50,$dat[2][1],$dat[2][0]+7,$dat[2][2]);
	$heute_prev = mktime(0,0,0,$dat[0][1],$dat[0][0]-7,$dat[0][2]);
	$start_prev = mktime(0,0,0,$dat[1][1],$dat[1][0]-7,$dat[1][2]);
	$ende_prev = mktime(23,59,50,$dat[2][1],$dat[2][0]-7,$dat[2][2]);
}
elseif ($tage > 360) { # Jahr
	$heute_next = mktime(0,0,0,$dat[0][1],$dat[0][0],$dat[0][2]+1);
	$start_next = mktime(0,0,0,1,1,$dat[1][2]+1);
	$ende_next = mktime(23,59,50,12,31,$dat[2][2]+1);
	$heute_prev = mktime(0,0,0,$dat[0][1],$dat[0][0],$dat[0][2]-1);
	$start_prev = mktime(0,0,0,1,1,$dat[1][2]-1);
	$ende_prev = mktime(23,59,50,12,31,$dat[2][2]-1);
}
else { # Monat
	$heute_next = mktime(0,0,0,$dat[0][1]+1,$dat[0][0],$dat[0][2]);
	$start_next = mktime(0,0,0,$dat[1][1]+1,1,$dat[1][2]);
	$ende_next = mktime(23,59,50,$dat[1][1]+2,0,$dat[2][2]);
	$heute_prev = mktime(0,0,0,$dat[0][1]-1,$dat[0][0],$dat[0][2]);
	$start_prev = mktime(0,0,0,$dat[1][1]-1,1,$dat[1][2]);
	$ende_prev = mktime(23,59,50,$dat[1][1],0,$dat[2][2]);
}

$monat_link = 'daten(\''.$heute.'\',\''.mktime(0,0,0,$dat[0][1],1,$dat[0][2]).'\',\''.mktime(23,59,50,$dat[0][1]+1,0,$dat[0][2]).'\');';
$jahr_link = 'daten(\''.$heute.'\',\''.mktime(0,0,0,1,1,$dat[0][2]).'\',\''.mktime(23,59,50,12,31,$dat[0][2]).'\');';
$woche_link = 'daten(\''.$heute.'\',\''.wochenstart($heute).'\',\''.wochenende($heute).'\');';

$set_db = mysql_query('SELECT * FROM `ltb_dataset` WHERE `modus`>=2 AND `position`!=0');
$num_dataset = mysql_num_rows($set_db);
$max_colspan = 2 + $num_dataset;
?>
<h1><span class="right"> <img class="link"
	title="Aktuelles Datenblatt neuladen"
	onClick="daten('<?php echo($heute.'\',\''.$start.'\',\''.$ende); ?>')"
	src="img/Refresh.png" /> <img class="link" title="Kalender-Auswahl"
	onClick="seite('calendar')" src="img/calendar_month.png" /> <img
	class="link" title="Monatskilometer"
	onClick="diagramm('monatskilometer','<?php echo(date("Y")); ?>')"
	src="img/mk.png" /> <img class="link" title="Wochenkilometer"
	onClick="diagramm('wochenkilometer','<?php echo(date("Y")); ?>')"
	src="img/wk.png" /> <img class="link" title="Trainings suchen"
	src="img/suche.png" onClick="submit_suche('')" /> <img class="link"
	title="Training eintragen" src="img/sticky_notes_plus.png"
	onClick="formular()" /> </span> <?php // L I N K S
echo('    <img class="link" onClick="daten(\''.$heute_prev.'\',\''.$start_prev.'\',\''.$ende_prev.'\')" title="vorherige'.$add.'" src="img/zurueck.png" />'.NL);
echo('
     <span class="link" onClick="'.$monat_link.'">'.monat(date("m", $heute)).'</span>,
     <span class="link" onClick="'.$jahr_link.'">'.date("Y", $heute).'</span>,
     <span class="link" onClick="'.$woche_link.'">'.strftime("%W", $heute).$woche.'. Woche</span>');
echo('    <img class="link" onClick="daten(\''.$heute_next.'\',\''.$start_next.'\',\''.$ende_next.'\')" title="n&auml;chste'.$add.'" src="img/vor.png" />'.NL);

?></h1>

<table cellspacing="0" width="100%">
	<tr class="space">
		<td colspan="<?php echo $max_colspan; ?>" />
	
	</tr>


	<?php
	// D A T E N
	$array_long = array();
	$array_short = array();
	$bilder = array();
	$db_sport = mysql_query('SELECT * FROM `ltb_sports`');
	while ($sport = mysql_fetch_assoc($db_sport)) {
		if ($sport['short'] == 1) $array_short[] = $sport['id'];
		else $array_long[] = $sport['id'];
		$bilder[$sport['id']] = $sport['bild'];
	}

	$i = 0;
	for ($w = 0; $w <= ($tage-1); $w++) {
		$tagbeginn = mktime(0,0,0,date("m",$start),date("d",$start)+$w,date("Y",$start));
		$tagende   = mktime(0,0,0,date("m",$start),date("d",$start)+$w+1,date("Y",$start));
		$trainings = false;
		// Kurze Versionen
		$shorts = '';
		$db_short = mysql_query('SELECT `id`, `dauer`, `sportid`, `time` FROM `ltb_training` WHERE `sportid` IN ('.implode(',',$array_short).') AND `time` BETWEEN '.($tagbeginn-10).' AND '.($tagende-10).' ORDER BY `time` ASC');
		while ($short = mysql_fetch_assoc($db_short))
		$shorts .= '<img class="link" onclick="seite(\'training\',\''.$short['id'].'\');" title="'.zeit($short['dauer']).'" src="img/sports/'.$bilder[$short['sportid']].'" /> ';

		$db = mysql_query('SELECT * FROM `ltb_training` WHERE `sportid` IN ('.implode(',',$array_long).') AND `time` BETWEEN '.($tagbeginn-10).' AND '.($tagende-10).' ORDER BY `time` ASC');
		while($training = mysql_fetch_array($db)) {
			$i++;
			$tr_style = $training['typid'] == $global['wettkampf_typid'] ? ' wk' : '';

			// Dataset
			$dataset = '';
			$set_db = mysql_query('SELECT * FROM `ltb_dataset` WHERE `modus`>=2 AND `position`!=0 ORDER BY `position` ASC');
			while ($set = mysql_fetch_assoc($set_db))
			$dataset .= dataset($set['id']);
			$dataset_more = ''; $dataset_more_left = ''; $dataset_more_right = ''; $j = -1;
			$setm_db = mysql_query('SELECT * FROM `ltb_dataset` WHERE `modus`=1 AND `position`!=0 ORDER BY `position` ASC');
			while ($setm = mysql_fetch_assoc($setm_db)) { $j++;
			if ($j == 0)
			$dataset_more .= dataset($setm['id']).'<td colspan="'.($max_colspan-2).'" class="l">';
			else {
				if ($j%2 == 0 && $dataset_more_right != '') $dataset_more_right .= '<br />';
				if ($j%2 == 0) $dataset_more_right .= dataset_more($setm['id']);
				if ($j%2 == 1 && $dataset_more_left != '') $dataset_more_left .= '<br />';
				if ($j%2 == 1) $dataset_more_left .= dataset_more($setm['id']);
			}
			}
			$dataset_more .= '<span class="right">'.$dataset_more_right.'</span> '.$dataset_more_left.'</td>';

			$day_info = (!$trainings) ? '
		<td class="l" style="width:24px;" onclick="training_tr(\''.$training['id'].'\')">'.($shorts == '' ? '&nbsp;' : $shorts).'</td>
		<td class="l" style=""><small>'.date("d.m.", $tagbeginn).'</small> '.wochentag(date("w", $tagbeginn),true).'</td>'
		: '
		<td colspan="2" />';
		echo('
	<tr class="a'.($i%2+1).$tr_style.' r training" rel="'.$training['id'].'">'.
		$day_info.
		$dataset.'
	</tr>');
		// TODO Dataset_more aus DB entfernen - wird nicht mehr genutzt
		$trainings = true;
		}
		$i++;
		if (!$trainings) // Leere Trainings
		echo('
	<tr class="a'.($i%2+1).'">
		<td>'.($shorts == '' ? '&nbsp;' : $shorts).'</td>
		<td><small>'.date("d.m.", $tagbeginn).'</small> '.wochentag(date("w", $tagbeginn),true).'</td>
		<td colspan="'.($max_colspan-2).'">&nbsp;</td>
	</tr>'.NL);
		else $i--;

		if (date("w", $tagbeginn) == 0 OR $w == ($tage-1))
		echo (NL.'
	<tr class="space">
		<td colspan="'.$max_colspan.'" />
	</tr>'.NL);
	}

	// Z U S A M M E N F A S S U N G
	$query_set_db = mysql_query('SELECT `name`, `zusammenfassung`, `zf_mode` FROM `ltb_dataset` WHERE `zusammenfassung`=1');
	while ($query_set = mysql_fetch_assoc($query_set_db))
	$query .= ', '.$query_set['zf_mode'].'(`'.$query_set['name'].'`) as `'.$query_set['name'].'`';

	$db = mysql_query('SELECT `id`, `time`, `sportid`, SUM(1) as `num`'.$query.' FROM `ltb_training` WHERE `time` BETWEEN '.($start-10).' AND '.($ende-10).' GROUP BY `sportid`');
	while($sport = mysql_fetch_assoc($db)) {
		$training = $sport;
		echo('
	<tr class="r">
		<td colspan="2">
			<small>'.$training['num'].'x</small>
			'.sport($training['sportid']).'
		</td>');
		$zf_set_db = mysql_query('SELECT `id`, `name`, `modus`, `position`, `zusammenfassung`, `zf_mode` FROM `ltb_dataset` WHERE `modus`>=2 AND `position`!=0 ORDER BY `position` ASC');;
		while ($zf_set = mysql_fetch_assoc($zf_set_db)) {
			if ($zf_set['zf_mode'] == 'AVG') {
				$zf_num_null_db = mysql_query('SELECT `time`, `sportid`, `'.$zf_set['name'].'` FROM `ltb_training` WHERE `time` BETWEEN '.($start-10).' AND '.($ende-10).' AND `'.$zf_set['name'].'`!=0 AND `'.$zf_set['name'].'`!="" AND `sportid`="'.$sport['sportid'].'"');
				$training[$zf_set['name']] = mysql_num_rows($zf_num_null_db) == 0 ? '' : $training[$zf_set['name']]*$sport['num']/mysql_num_rows($zf_num_null_db);
			}
			if ($zf_set['zusammenfassung'] == 1) echo(dataset($zf_set['id']));
			else echo('<td />');
		}
		echo('
	</tr>'.NL);
	}

	close();
	?>
</table>
