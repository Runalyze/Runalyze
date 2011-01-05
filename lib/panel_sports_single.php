<?php 
if (!$include_sports) {
	header('Content-type: text/html; charset=ISO-8859-1');
	include_once('../config/functions.php');
	connect();
}

$db = mysql_query('SELECT * FROM `ltb_sports` WHERE `online`=1 ORDER BY `distanz` DESC, `dauer` DESC');
while($sport = mysql_fetch_array($db)) {
	if (!isset($_GET['start']) || !isset($_GET['ende']) || ($_GET['start']=='-1' && $_GET['ende']=='-1')) {
		$_GET['start'] = mktime(0,0,0,date("m"),1,date("Y"));
		$_GET['ende'] = mktime(23,59,59,(date("m")+1),0,date("Y"));		
	}
	$time_db = mysql_query('SELECT `sportid`, COUNT(`id`) as `anzahl`, SUM(`distanz`) as `distanz_sum`, SUM(`dauer`) as `dauer_sum`  FROM `ltb_training` WHERE `sportid`='.$sport['id'].' AND `time` BETWEEN '.$_GET['start'].' AND '.$_GET['ende'].' GROUP BY `sportid`');
	$time_dat = mysql_fetch_assoc($time_db);
	if ($sport['distanztyp'] == 1) $leistung = unbekannt(km($time_dat['distanz_sum']),'0,0 km');
	else $leistung = dauer($time_dat['dauer_sum']);
	$anzahl = $_GET['start']==0 ? '' : '<small><small>('.unbekannt($time_dat['anzahl'],'0').'-mal)</small></small> '; 		

	echo('    <p><span>'.$anzahl.$leistung.'</span> <img src="img/sports/'.$sport['bild'].'" alt="'.$sport['name'].'" /> <strong>'.$sport['name'].'</strong></p>'.NL);	
}

if (!$include_sports) close();
?>