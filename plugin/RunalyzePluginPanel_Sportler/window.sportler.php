<?php
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

if (Request::sendId() === false) {
	$Header   = 'K&ouml;rper-Daten eintragen';
	$Mode     = StandardFormular::$SUBMIT_MODE_CREATE;
	$UserData = new UserData( DataObject::$LAST_OBJECT );
	$UserData->setCurrentTimestamp();
} else {
	$Header   = 'K&ouml;rper-Daten bearbeiten';
	$Mode     = StandardFormular::$SUBMIT_MODE_EDIT;
	$UserData = new UserData( Request::sendId() );
}

$Formular = new StandardFormular($UserData, $Mode);

if ($Formular->submitSucceeded())
	header('Location: window.sportler.table.php');

$Formular->setId('sportler');
$Formular->setHeader($Header);
$Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W33 );
$Formular->display();