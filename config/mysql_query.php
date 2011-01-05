<?php
# Panels verschieben // Done for new version!
if ($_GET['action'] == "up") {
	mysql_query('UPDATE `ltb_modules` SET `order`=`order`+1 WHERE `order`=('.$_GET['order'].'-1) LIMIT 1');
	mysql_query('UPDATE `ltb_modules` SET `order`=`order`-1 WHERE `id`='.$_GET['id'].' LIMIT 1');
	close();
	header('Location: ?done');
}

elseif ($_GET['action'] == "down") {
	mysql_query('UPDATE `ltb_modules` SET `order`=`order`-1 WHERE `order`=('.$_GET['order'].'+1) LIMIT 1');
	mysql_query('UPDATE `ltb_modules` SET `order`=`order`+1 WHERE `id`='.$_GET['id'].' LIMIT 1');
	close();
	header('Location: ?done');
}

# User // Done for new vesrion!
elseif ($_GET['action'] == "do" && $_POST['type'] == "user") {
	mysql_query('INSERT INTO `ltb_user` (`time`, `gewicht`, `fett`, `wasser`, `muskeln`, `puls_ruhe`, `puls_max`, `blutdruck_min`, `blutdruck_max`)' .
			' VALUES ('.time().', "'.komma($_POST['gewicht']).'", "'.komma($_POST['fett']).'", "'.komma($_POST['wasser']).'", "'.komma($_POST['muskeln']).'", "'.$_POST['puls_ruhe'].'", "'.$_POST['puls_max'].'", "'.$_POST['blutdruck_min'].'", "'.$_POST['blutdruck_max'].'")') or die (mysql_error());
	close();
	header('Location: ?done');
}

# Schuh // Done for new version!
elseif ($_GET['action'] == "do" && $_POST['type'] == "schuh") {
	mysql_query('INSERT INTO `ltb_schuhe` (`name`, `marke`, `kaufdatum`, `inuse`)' .
			' VALUES ("'.$_POST['name'].'", "'.$_POST['marke'].'", "'.$_POST['kaufdatum'].'", 1)');
	close();
	header('Location: ?done');
}

elseif ($_GET['action'] == "do" && $_POST['type'] == "schuh_unuse") {
	mysql_query('UPDATE `ltb_schuhe` SET `inuse`=0 WHERE `id`='.$_POST['schuhid'].' LIMIT 1');
	close();
	header('Location: ?done');
}

# Training // Done for new version!
elseif ($_GET['action'] == "do" && $_POST['dauer'] != '0:00:00') {
	$send = array();
	$db = mysql_query('SELECT * FROM `ltb_sports` WHERE `id`='.$_POST['sportid'].' LIMIT 1');
	$sport = mysql_fetch_assoc($db);
	$send['sportid'] = $sport['id'];
	$send['kalorien'] = $_POST['kalorien'];
	$send['bemerkung'] = $_POST['bemerkung'];
	$send['trainingspartner'] = $_POST['trainingspartner'];

	$tag = explode(".", $_POST['datum']);
	$zeit = explode(":", $_POST['zeit']);
	$send['time'] = mktime($zeit[0], $zeit[1], 0, $tag[1], $tag[0], $tag[2]);
	$send['datum'] = date("d.m.Y H:i", $send['time']); // Zu Testzwecken
	$dauer = explode(":", $_POST['dauer']);
	$send['dauer'] = 3600 * $dauer[0] + 60 * $dauer[1] + $dauer[2];

	if ($sport['distanztyp'] == 1) {
		$send['distanz'] = komma($_POST['distanz']);
		$send['bahn'] = $_POST['bahn']==true ? 1 : 0;
		$send['pace'] = pace($send['distanz'], $send['dauer']);
	}
	else $send['distanz'] = 0;

	$send['temperatur'] = NULL;
	if ($sport['outside'] == 1) {
		$send['hm'] = $_POST['hm'];
		$send['kleidung'] = substr($_POST['kleidung'],0,-1);
		$send['temperatur'] = is_numeric($_POST['temperatur']) ? $_POST['temperatur'] : NULL;
		$send['wetterid'] = $_POST['wetterid'];
		$send['strecke'] = $_POST['strecke'];
	}

	if ($sport['pulstyp'] == 1) {
		$send['puls'] = $_POST['puls'];
		$send['puls_max'] = $_POST['puls_max'];
	}

	if ($sport['typen'] == 1) {
		$send['typid'] = $_POST['typid'];
		$send['schuhid'] = $_POST['schuhid'];
		$send['laufabc'] = $_POST['laufabc'] == true ? 1 : 0;
		if (typ($send['typid'],false,true) == 1)
		$send['splits'] = $_POST['splits'];
	}

	mysql_query('INSERT INTO `ltb_training` (`sportid`, `typid`, `time`, `distanz`,
		`bahn`, `dauer`, `pace`, `hm`, `kalorien`, `kleidung`, `temperatur`,
		`puls`, `puls_max`, `wetterid`, `strecke`, `splits`, `bemerkung`,
		`trainingspartner`, `laufabc`, `schuhid`)
		VALUES ("'.$send['sportid'].'",
		"'.$send['typid'].'",
		"'.$send['time'].'",
		"'.$send['distanz'].'",
		"'.$send['bahn'].'",
		"'.$send['dauer'].'",
		"'.$send['pace'].'",
		"'.$send['hm'].'",
		"'.$send['kalorien'].'",
		"'.$send['kleidung'].'",
		"'.$send['temperatur'].'",
		"'.$send['puls'].'",
		"'.$send['puls_max'].'",
		"'.$send['wetterid'].'",
		"'.$send['strecke'].'",
		"'.$send['splits'].'",
		"'.$send['bemerkung'].'",
		"'.$send['trainingspartner'].'",
		"'.$send['laufabc'].'",
		"'.$send['schuhid'].'")') or die(mysql_error());

	$insert_id = mysql_insert_id();
	mysql_query('UPDATE `ltb_training` SET `trimp`="'.trimp($insert_id).'" WHERE `id`='.$insert_id.' LIMIT 1');
	mysql_query('UPDATE `ltb_training` SET `vdot`="'.jd_VDOT_bereinigt($insert_id).'" WHERE `id`='.$insert_id.' LIMIT 1');

	if ($sport['typen'] == 1) mysql_query('UPDATE `ltb_schuhe` SET `km`=`km`+'.$send['distanz'].', `dauer`=`dauer`+'.$send['dauer'].' WHERE `id`='.$send['schuhid'].' LIMIT 1') or die(mysql_error());;
	mysql_query('UPDATE `ltb_sports` SET `distanz`=`distanz`+'.$send['distanz'].', `dauer`=`dauer`+'.$send['dauer'].' WHERE `id`='.$send['sportid'].' LIMIT 1') or die(mysql_error());;
	// max_atl, max_ctl, max_trimp
	if (atl($send['time']) > $config['max_atl']) mysql_query('UPDATE `ltb_config` SET `max_atl`="'.atl($send['time']).'"');
	if (ctl($send['time']) > $config['max_ctl']) mysql_query('UPDATE `ltb_config` SET `max_ctl`="'.ctl($send['time']).'"');
	if (trimp($insert_id) > $config['max_trimp']) mysql_query('UPDATE `ltb_config` SET `max_trimp`="'.trimp($insert_id).'"');

	close();
	header('Location: ?done');
}
?>