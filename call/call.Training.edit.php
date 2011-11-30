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
	$Editor = new Editor($id, $_POST);
	$Editor->performUpdate();

	$Errors = $Editor->getErrorsAsArray();
	if (!empty($Errors))
		echo HTML::error(implode('<br />', $Errors));
}

$Frontend->displayHeader();

$Training = new Training($id);
$Training->overwritePostArray();

include '../inc/tpl/tpl.Training.edit.php';

$Frontend->displayFooter();
$Frontend->close();
?>