<?php
/**
 * File displaying the formular with new sportler information
 * Call:   call/call.Training.edit.php?id=
 */
require '../inc/class.Frontend.php';

new Frontend();

$Mysql    = Mysql::getInstance();
$id       = isset($_GET['id']) ? $_GET['id'] : 0;

if (isset($_GET['json'])) {
	Error::getInstance()->footer_sent = true;
	move_uploaded_file($_FILES['userfile']['tmp_name'], 'tmp.tcx');
	echo 'success';
	exit();
} elseif (isset($_GET['tmp'])) {
	ImporterTCX::addTCXdataToTraining($id, 'tmp.tcx');
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
	$Mysql->delete(PREFIX.'training', (int)$_GET['delete']);

	$values = Helper::calculateMaxValues();
	Config::update('MAX_ATL', $values[0]);
	Config::update('MAX_CTL', $values[1]);
	Config::update('MAX_TRIMP', $values[2]);

	$shoes = $Mysql->fetchAsArray('SELECT `id` FROM `'.PREFIX.'shoe`');
	foreach ($shoes as $shoe) {
		$data = $Mysql->fetchSingle('SELECT SUM(`distance`) as `km`, SUM(`s`) as `s` FROM `'.PREFIX.'training` WHERE `shoeid`="'.$shoe['id'].'" GROUP BY `shoeid`');

		if ($data === false)
			$data = array('km' => 0, 's' => 0);

		$Mysql->update(PREFIX.'shoe', $shoe['id'], array('km', 'time'), array($data['km'], $data['s']));
	}

	echo '<div id="submit-info" class="error">Das Training wurde gel&ouml;scht.</div>';
	echo '<script type="text/javascript">Runalyze.setTabUrlToFirstStatistic().reloadContent();</script>';
	exit();
}


if (isset($_POST['type']) && $_POST['type'] == "training") {
	$Editor = new Editor($id, $_POST);
	$Editor->performUpdate();

	$Errors = $Editor->getErrorsAsArray();
	if (!empty($Errors))
		echo HTML::error(implode('<br />', $Errors));
}

$Training = new Training($id);
$Training->overwritePostArray();

include '../inc/training/tpl/tpl.Training.edit.php';
?>