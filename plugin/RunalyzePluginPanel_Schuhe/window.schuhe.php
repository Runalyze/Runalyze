<?php
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

if (isset($_GET['delete'])) {
	Mysql::getInstance()->delete(PREFIX.'shoe', (int)$_GET['delete']);
	header('Location: window.schuhe.table.php');
}

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


if (Request::sendId() && $Shoe->getKm() == $Shoe->getAdditionalKm()) {
	$DeleteText = '<strong>Schuh wieder l&ouml;schen &raquo;</strong>';
	$DeleteUrl  = $_SERVER['SCRIPT_NAME'].'?delete='.$Shoe->id();
	$DeleteLink = Ajax::link($DeleteText, 'ajax', $DeleteUrl);

	$DeleteFieldset = new FormularFieldset('Schuh l&ouml;schen');
	$DeleteFieldset->addWarning($DeleteLink);

	$Formular->addFieldset($DeleteFieldset);
}

$Formular->setId('shoe');
$Formular->setHeader($Header);
$Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W33 );
$Formular->display();