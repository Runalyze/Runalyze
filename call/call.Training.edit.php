<?php
/**
 * File displaying the formular with new sportler information
 * Call:   call/call.Training.edit.php?id=
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend(true, __FILE__);
$Mysql = Mysql::getInstance();

// TODO CodeCleaning
// Upload tcx-file
if (isset($_GET['json'])) {
	Error::getInstance()->footer_sent = true;
	move_uploaded_file($_FILES['userfile']['tmp_name'], 'tmp.tcx');
	echo 'success';
	exit();
} elseif (isset($_GET['tmp'])) {
	$vars = array();
	$GPS  = Training::parseTcx(file_get_contents('tmp.tcx'));
	$Data = $Mysql->fetch(PREFIX.'training', $_GET['id']);
	$Training = new Training($_GET['id']);

	if (!($Data['elevation'] > 0))
		$vars[] = 'elevation';

	$vars[] = 'arr_time';
	$vars[] = 'arr_lat';
	$vars[] = 'arr_lon';
	$vars[] = 'arr_alt';
	$vars[] = 'arr_dist';
	$vars[] = 'arr_heart';
	$vars[] = 'arr_pace';

	if ($Data['pulse_avg'] != 0 || $Data['pulse_max'] != 0) {
		$vars[] = 'pulse_avg';
		$vars[] = 'pulse_max';
	}

	if ($Training->Type()->hasSplits() && strlen($Data['splits']) == 0)
		$vars[] = 'splits';

	foreach ($vars as $var)
		if (isset($GPS[$var])) {
			$columns[] = $var;
			$values[] = Helper::Umlaute(Helper::CommaToPoint($GPS[$var]));
		}

	if (!isset($GPS['error']) || $GPS['error'] == '') {
		$Mysql->update(PREFIX.'training', $_GET['id'], $columns, $values);
	
		$submit = '<em>Die Daten wurden hinzugef&uuml;gt.</em><br /><br />';
		$submit .= '<script type="text/javascript">jReloadContent();</script>';
	} else {
		$submit = '<em class="error">'.$GPS['error'].'</em><br /><br />';
	}

	unlink('tmp.tcx');
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
	$Mysql->delete(PREFIX.'training', (int)$_GET['delete']);

	echo '<div id="submit-info" class="error">Das Training wurde gel&ouml;scht.</div>';
	echo '<script type="text/javascript">jReloadContent();</script>';
	exit();
}

$id = $_GET['id'];

if (isset($_POST['type']) && $_POST['type'] == "training") {
	$error = '';

	$Sport = new Sport($_POST['sportid']);
	if ($Sport->hasTypes())
		$Type  = new Type($_POST['typeid']);

	$columns = array('sportid');
	$values = array($_POST['sportid']);
	// Short version for $columns['var'] and $values[$_POST['var']]
	// Helper::Umlaute() and Helper::CommaToPoint will be called automatically
	$vars = array('kcal', 'comment', 'partner');

	// Timestamp
	$tag = explode('.', $_POST['datum']);
	$zeit = explode(':', $_POST['zeit']);

	if (count($tag) != 3 || count($zeit) != 2)
		$error = 'Das Datum konnte nicht gelesen werden.';
	else {
		$timestamp = mktime($zeit[0], $zeit[1], 0, $tag[1], $tag[0], $tag[2]);
		$columns[] = 'time';
		$values[]  = $timestamp;
	}

	// Time in seconds
	$ms        = explode(".", Helper::CommaToPoint($_POST['s']));
	$dauer     = explode(":", $ms[0]);
	if (!isset($ms[1]))
		$ms[1] = 0;
	$time_in_s = round(3600 * $dauer[0] + 60 * $dauer[1] + $dauer[2] + ($ms[1]/100), 2);
	$columns[] = 's';
	$values[]  = $time_in_s;

	if ($time_in_s == 0)
		$error = 'Es muss eine Trainingszeit angegeben sein.';

	// save difference for typ/schuh
	$distanz = Helper::CommaToPoint($_POST['distance']);
	$dauer_dif = $time_in_s - $_POST['s_old'];
	$dist_dif = $distanz - $_POST['dist_old'];

	if ($Sport->usesDistance()) {
		$vars[]    = 'distance';
		$vars[]    = 'pace';
		$columns[] = 'is_track';
		$values[]  = isset($_POST['is_track']) && $_POST['is_track'] == 'on' ? 1 : 0;
	}

	if ($Sport->isOutside()) {
		if (strlen($_POST['temperature']) > 0)
			$vars[] = 'temperature';

		$vars[] = 'weatherid';
		$vars[] = 'route';

		// Kleidung
		$kleidung = array();
		$kleidungen = $Mysql->fetchAsArray('SELECT `id`, `short` FROM `'.PREFIX.'clothes`');
		foreach ($kleidungen as $kl) {
			if (isset($_POST[$kl['short']]) && $_POST[$kl['short']] == 'on')
				$kleidung[] = $kl['id'];
		}

		$columns[] = 'clothes';
		$values[]  = isset($_POST['clothes']) ? implode(',', array_keys($_POST['clothes'])) : '';
	}

	if ($Sport->usesDistance() && $Sport->isOutside())
		$vars[] = 'elevation';

	if ($Sport->usesPulse()) {
		$vars[] = 'pulse_avg';
		$vars[] = 'pulse_max';
	}

	if ($Sport->hasTypes()) {
		// Typid und Schuhid
		$vars[]    = 'typeid';
		$vars[]    = 'shoeid';
		$columns[] = 'abc';
		$values[]  = isset($_POST['abc']) && $_POST['abc'] == 'on' ? 1 : 0;

		if ($Type->hasSplits())
			$vars[] = 'splits';
	}

	foreach($vars as $var)
		if (isset($_POST[$var])) {
			$columns[] = $var;
			$values[] = Helper::Umlaute(Helper::CommaToPoint($_POST[$var]));
		}

	if ($error == '') {
		$Mysql->update(PREFIX.'training', $id, $columns, $values);

		if (isset($_POST['shoeid_old']) && isset($_POST['s_old']) && isset($_POST['dist_old']) && isset($_POST['shoeid']) && $_POST['shoeid_old'] != $_POST['shoeid'] && $_POST['shoeid'] != 0) {
			$Mysql->query('UPDATE `'.PREFIX.'shoe` SET `km`=`km`-"'.$_POST['dist_old'].'", `time`=`time`-'.$_POST['s_old'].' WHERE `id`='.$_POST['shoeid_old'].' LIMIT 1');
			$Mysql->query('UPDATE `'.PREFIX.'shoe` SET `km`=`km`+"'.$distanz.'", `time`=`time`+'.$time_in_s.' WHERE `id`='.$_POST['shoeid'].' LIMIT 1');
		}
		if ($Sport->hasTypes() && isset($_POST['shoeid']))
			$Mysql->query('UPDATE `'.PREFIX.'shoe` SET `km`=`km`+'.$dist_dif.', `time`=`time`+'.$dauer_dif.' WHERE `id`='.$_POST['shoeid'].' LIMIT 1');

		$Mysql->update(PREFIX.'training', $_POST['id'], 'trimp', Helper::TRIMP($_POST['id']));
		$Mysql->update(PREFIX.'training', $_POST['id'], 'vdot', JD::Training2VDOT($_POST['id']));

		$ATL = Helper::ATL($timestamp);
		$CTL = Helper::CTL($timestamp);
		$TRIMP = Helper::TRIMP($_POST['id']);

		if ($ATL > MAX_ATL)
			Config::update('MAX_ATL', $ATL);
		if ($CTL > MAX_CTL)
			Config::update('MAX_ATL', $CTL);
		if ($TRIMP > MAX_TRIMP)
			Config::update('MAX_ATL', $TRIMP);
	
		$submit = '<em>Die Daten wurden gespeichert!</em><br /><br />';
	} else {
		$submit = '<em class="error">'.$error.'</em><br /><br />';
	}
}

$Frontend->displayHeader();

$Training = new Training($id);
$Training->overwritePostArray();

include '../inc/tpl/tpl.Training.edit.php';

$Frontend->displayFooter();
$Frontend->close();
?>