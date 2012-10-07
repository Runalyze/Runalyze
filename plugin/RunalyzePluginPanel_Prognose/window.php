<?php
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

if (!isset($_GET['y']))
	$_GET['y'] = date("Y");
?>
<h1>Prognose-Rechner</h1>

<?php
if (empty($_POST)) {
	$Plugin = Plugin::getInstanceFor('RunalyzePluginPanel_Prognose');

	$_POST['vdot'] = VDOT_FORM;
	$_POST['endurance'] = true;
	$_POST['distances'] = implode(', ', $Plugin->getDistances());
}

$Distances = array();
foreach (explode(',', $_POST['distances']) as $Dist) {
	$km         = trim($Dist);
	$PB         = Running::PersonalBest($km, true);
	$PrognosisA = Running::PrognosisAsArray($km, $_POST['vdot'], isset($_POST['endurance']));
	$Prognosis  = $PrognosisA['seconds'];

	if ($PB > 0)
		$PBdate = Mysql::getInstance()->fetchSingle('SELECT `time` FROM `'.PREFIX.'training` WHERE `typeid`="'.CONF_WK_TYPID.'" AND `distance`="'.$km.'" ORDER BY `s` ASC');

	$Distances[] = array(
		'distance'	=> Running::Km($km),
		'prognosis'		=> Time::toString($Prognosis),
		'prognosis-pace'=> Running::Pace($km, $Prognosis).'/km',
		'prognosis-vdot'=> round($PrognosisA['vdot'],2),
		'diff'			=> $PB == 0 ? '-' : ($PB>$Prognosis?'+ ':'- ').Time::toString(abs(round($PB-$Prognosis)),false,true),
		'diff-class'	=> $PB > $Prognosis ? 'plus' : 'minus',
		'pb'			=> $PB > 0 ? Time::toString($PB) : '-',
		'pb-pace'		=> $PB > 0 ? Running::Pace($km, $PB).'/km' : '-',
		'pb-vdot'		=> $PB > 0 ? round(JD::Competition2VDOT($km, $PB),2) : '-',
		'pb-date'		=> $PB > 0 ? date('d.m.Y', $PBdate['time']) : '-'
	);
}

$Table  = '
<table style="width:100%;">
	<thead>
		<tr>
			<th>Distanz</th>
			<th>Prognose</th>
			<th class="small">Pace</th>
			<th class="small">VDOT</th>
			<th>Differenz</th>
			<th>Bestzeit</th>
			<th class="small">Pace</th>
			<th class="small">VDOT</th>
			<th class="small">Datum</th>
		</tr>
	</thead>
	<tbody>';

foreach ($Distances as $i => $Dist) {
	$Table .= '
		<tr class="'.HTML::trClass($i).' r">
			<td class="small b l">'.$Dist['distance'].'</td>
			<td class="b">'.$Dist['prognosis'].'</td>
			<td class="small">'.$Dist['prognosis-pace'].'</td>
			<td class="small">'.$Dist['prognosis-vdot'].'</td>
			<td class="small '.$Dist['diff-class'].'">'.$Dist['diff'].'</td>
			<td class="b">'.$Dist['pb'].'</td>
			<td class="small">'.$Dist['pb-pace'].'</td>
			<td class="small">'.$Dist['pb-vdot'].'</td>
			<td class="small">'.$Dist['pb-date'].'</td>
		</tr>';
}

$Table .= '
	</tbody>
</table>';












$FieldsetInput = new FormularFieldset('Eingabe');
$FieldsetInput->addInfo('Dein aktueller VDOT: '.VDOT_FORM);

$FieldVdot = new FormularInput('vdot', Ajax::tooltip('Neuer VDOT', 'Statt deinem eigentlichen VDOT-Wert wird dieser zur Berechnung herangezogen.'));
$FieldVdot->setLayout( FormularFieldset::$LAYOUT_FIELD_W50_AS_W100 );

$FieldEndurance = new FormularCheckbox('endurance', Ajax::tooltip('Grundlagenausdauer-Faktor', 'Mit dieser Einstellung wird auch deine berechnete Grundlagenausdauer in die Berechnungen einflie&szlig;en.'));
$FieldEndurance->setLayout( FormularFieldset::$LAYOUT_FIELD_W50_AS_W100 );

$FieldDistances = new FormularInput('distances', Ajax::tooltip('Distanzen', 'Kommagetrennte Liste mit allen Distanzen, f&uuml;r die eine Prognose erstellt werden soll.'));
$FieldDistances->setLayout( FormularFieldset::$LAYOUT_FIELD_W50_AS_W100 );
$FieldDistances->setSize( FormularInput::$SIZE_FULL_INLINE );

$FieldsetInput->addField($FieldVdot);
$FieldsetInput->addField($FieldEndurance);
$FieldsetInput->addField($FieldDistances);

$FieldsetResult = new FormularFieldset('Prognose');
$FieldsetResult->addBlock($Table);
$FieldsetResult->addInfo('Die Prognosen basieren auf Formeln und Tabellen aus &quot;Die Laufformel&quot; von Jack Daniels.<br />');

$Formular = new Formular();
$Formular->setId('prognisis-calculator');
$Formular->addCSSclass('ajax');
$Formular->addCSSclass('no-automatic-reload');
$Formular->addFieldset($FieldsetInput);
$Formular->addFieldset($FieldsetResult);
$Formular->addSubmitButton('Prognose anzeigen');
$Formular->display();
?>