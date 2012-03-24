<?php
require '../../inc/class.Frontend.php';

new Frontend();

if (Request::sendId() === false) {
	$Header   = 'Laufschuh eintragen';
	$Mode     = StandardFormular::$SUBMIT_MODE_CREATE;
	$Shoe     = new Shoe( DataObject::$DEFAULT_ID );
} else {
	$Header   = 'Laufschuh bearbeiten';
	$Mode     = StandardFormular::$SUBMIT_MODE_EDIT;
	$Shoe     = new Shoe( Request::sendId() );
}

$Formular = new StandardFormular($Shoe, $Mode);

if ($Formular->submitSucceeded())
	header('Location: window.schuhe.table.php');

$Formular->setId('shoe');
$Formular->setHeader($Header);
$Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W33 );
$Formular->display();