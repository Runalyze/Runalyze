<?php
/**
 * File for displaying the formular for creating a new training.
 * Call:   call.Training.create.php
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend(isset($_GET['json']));

System::setMaximalLimits();

TrainingCreatorWindow::display(); exit;

$Mode     = StandardFormular::$SUBMIT_MODE_CREATE;
$Training = new TrainingObject( DataObject::$DEFAULT_ID );

$Formular = new TrainingFormular($Training, $Mode);

$Formular->setId('training');
$Formular->setHeader('Training hinzuf&uuml;gen');
$Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W50 );
$Formular->display();
?>