<?php
/**
 * Display formular for editing a training
 * Call:   call/call.Training.edit.php?id=
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
	Mysql::getInstance()->delete(PREFIX.'training', (int)$_GET['delete']);

	Trimp::calculateMaxValues();
	ShoeFactory::recalculateAllShoes();

	echo '<p id="submit-info" class="error">Das Training wurde gel&ouml;scht.</p>';
	echo '<script type="text/javascript">$("#multi-edit-'.((int)$_GET['delete']).'").remove();Runalyze.setTabUrlToFirstStatistic().reloadContent();</script>';
	exit();
}

$Training = new TrainingObject(Request::sendId());
echo $Training->Linker()->editNavigation();

$Formular = new TrainingFormular($Training, StandardFormular::$SUBMIT_MODE_EDIT);
$Formular->setId('training');
$Formular->setHeader( $Training->DataView()->getTitleWithCommentAndDate() );
$Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W50 );
$Formular->display();