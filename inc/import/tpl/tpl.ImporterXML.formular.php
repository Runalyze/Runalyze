<?php if ($this->inserted): ?>
	<em>Die Trainings wurden hinzugef&uuml;gt.</em>
	<br />
	<br />
	<?php if (!is_null($this->MultiEditor)) $this->MultiEditor->display(); ?>
</div>
<?php exit; ?>
<?php else: ?>
	<h1>RunningAhead Import</h1>
<?php
$Import = new FormularFieldset('Informationen');
$Import->addBlock('Falls du Sportarten oder Trainingstypen nicht zuordnen kannst, weil diese fehlen,
				solltest du sie zun&auml;chst manuell erstellen, bevor du diesen Import durchf&uuml;hrst.
				Lediglich die Schuhe k&ouml;nnen hier beim Import direkt erstellt werden.
				Sportarten und Trainingstypen ben&ouml;tigen zu viele Einstellungen.
				Du kannst sie in der Konfiguration selbst anlegen.');

$Sports  = new FormularFieldset('Sportarten zuordnen');
$Options = array('0' => '-- nicht importieren') + Sport::getNamesAsArray();

foreach ($this->FoundSports as $Sport) {
	$ID    = self::getIDforDatabaseString('sport', $Sport['name']);
	$Field = new FormularSelectBox('sport['.$Sport['id'].']', $Sport['name'], $ID);
	$Field->setOptions($Options);

	if ($ID == 0)
		$Field->addCSSclass(FormularField::$CSS_VALIDATION_FAILED);

	$Sports->addField($Field);
}


$Types   = new FormularFieldset('Trainingstypen zuordnen');
$Options = array('0' => '-- ignorieren') + Type::getNamesAsArray();

foreach ($this->FoundTypes as $Type) {
	$ID    = self::getIDforDatabaseString('type', $Type['name']);
	$Field = new FormularSelectBox('type['.$Type['id'].']', $Type['name'], $ID);
	$Field->setOptions($Options);

	if ($ID == 0)
		$Field->addCSSclass(FormularField::$CSS_VALIDATION_FAILED);

	$Types->addField($Field);
}


$Shoes   = new FormularFieldset('Equipment zuordnen');
$Options = array(0 => '-- NEU erstellen', -1 => '-- ignorieren') + Shoe::getNamesAsArray(false);

foreach ($this->FoundShoes as $Shoe) {
	$ID    = self::getIDforDatabaseString('shoe', $Shoe['name']);
	$Field = new FormularSelectBox('shoe['.$Shoe['id'].']', $Shoe['name'], $ID);
	$Field->setOptions($Options);

	if ($ID == 0)
		$Field->addCSSclass(FormularField::$CSS_VALIDATION_FAILED);

	$Shoes->addField($Field);
}


$Summary = new FormularFieldset('Zusammenfassung');
$Summary->addFileBlock('Es wurden <strong>'.$this->numTrainings.' Trainings</strong> zum Importieren gefunden.');
$Summary->addFileBlock('Es wurden <strong>'.count($this->FoundSports).' Sportarten</strong> zum Importieren gefunden.');
$Summary->addFileBlock('Es wurden <strong>'.count($this->FoundTypes).' Trainingstypen</strong> zum Importieren gefunden.');
$Summary->addFileBlock('Es wurden <strong>'.count($this->FoundShoes).' Equipments</strong> zum Importieren gefunden.');



$Formular = new Formular();
$Formular->setId('upload');
$Formular->addCSSclass('ajax');
$Formular->addHiddenValue('forceAsFileName', $this->fileName);
$Formular->addHiddenValue('insertNow', 'true');
$Formular->addFieldset($Import);
$Formular->addFieldset($Sports);
$Formular->addFieldset($Types);
$Formular->addFieldset($Shoes);
$Formular->addFieldset($Summary);
$Formular->setLayoutForFields(FormularFieldset::$LAYOUT_FIELD_W100);
$Formular->addSubmitButton('Trainings importieren');
$Formular->setSubmitButtonsCentered();
$Formular->display();

echo HTML::warning('Achtung: Nach dem Absenden des Formulars werden die Trainings ohne weitere &Uuml;berpr&uuml;fung in die Datenbank eingetragen.');
?>
<?php endif; ?>