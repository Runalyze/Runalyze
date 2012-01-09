<?php
/**
 * File displaying the formular with new sportler information
 * Call:   call/call.Training.edit.php?id=
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend(true, __FILE__);
$Mysql    = Mysql::getInstance();
$id       = $_GET['id'];

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

	echo '<div id="submit-info" class="error">Das Training wurde gel&ouml;scht.</div>';
	echo '<script type="text/javascript">jReloadContent();</script>';
	exit();
}


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