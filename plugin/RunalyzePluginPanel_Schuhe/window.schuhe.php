<?php
/**
 * Window: formular for shoes
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

if (Request::param('delete') == 'true') {
	Mysql::getInstance()->delete(PREFIX.'shoe', (int)Request::sendId());
	Mysql::getInstance()->updateWhere(PREFIX.'training', 'shoeid ='.(int)Request::sendId(), shoeid, 0);
	header('Location: window.schuhe.table.php?reload=true');
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


if (Request::sendId() > 0) {
	$DeleteText = '<strong>Schuh wieder l&ouml;schen &raquo;</strong>';
	$DeleteUrl  = $_SERVER['SCRIPT_NAME'].'?delete=true&id='.$Shoe->id();
	$DeleteLink = Ajax::link($DeleteText, 'ajax', $DeleteUrl);

	if ($Shoe->getKm() != $Shoe->getAdditionalKm())
		$DeleteLink = 'Der Schuh ist noch mit einigen Trainings verkn&uuml;pft und kann daher nicht gel&ouml;scht werden.';

	$DeleteFieldset = new FormularFieldset('Schuh l&ouml;schen');
	$DeleteFieldset->addWarning($DeleteLink);

	$Formular->addFieldset($DeleteFieldset);
}

echo '<div class="panel-heading">';
echo '<h1>'.$Header.'</h1>';
echo '</div>';
echo '<div class="panel-content">';
$Formular->setId('shoe');
$Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W33 );
$Formular->display();
echo '</div>';