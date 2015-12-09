<?php
/**
 * Display formular for editing a training
 * Call:   call/call.Training.edit.php?id=
 */
require '../inc/class.Frontend.php';

use Runalyze\Model\Activity;
use Runalyze\View\Activity\Linker;
use Runalyze\View\Activity\Dataview;

$Frontend = new Frontend();

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
	$Factory = Runalyze\Context::Factory();
	$Deleter = new Activity\Deleter(DB::getInstance(), $Factory->activity($_GET['delete']));
	$Deleter->setAccountID(SessionAccountHandler::getId());
	$Deleter->setEquipmentIDs($Factory->equipmentForActivity($_GET['delete'], true));
	$Deleter->delete();

	echo '<div class="panel-content"><p id="submit-info" class="error">'.__('The activity has been removed').'</p></div>';
	echo '<script>$("#multi-edit-'.((int)$_GET['delete']).'").remove();Runalyze.Statistics.resetUrl();Runalyze.reloadContent();</script>';
	exit();
}

$Training = new TrainingObject(Request::sendId());
$Activity = new Activity\Entity($Training->getArray());

$Linker = new Linker($Activity);
$Dataview = new Dataview($Activity);

echo $Linker->editNavigation();

echo '<div class="panel-heading">';
echo '<h1>'.$Dataview->titleWithComment().', '.$Dataview->dateAndDaytime().'</h1>';
echo '</div>';
echo '<div class="panel-content">';

$Formular = new TrainingFormular($Training, StandardFormular::$SUBMIT_MODE_EDIT);
$Formular->setId('training');
$Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W50 );
$Formular->display();

echo '</div>';