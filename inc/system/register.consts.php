<?php
/*
 * All registrations of CONST variables have to be done here
 * otherwise the Autoloader will not work correct
 */
$General = new ConfigCategory('general', 'Allgemein');
$General->setKeys(array(
	'GENDER',
	'PULS_MODE',
	'MAINSPORT',
	'WK_TYPID',
	'RUNNINGSPORT',
	'LL_TYPID'
));
$General->addConfigValue( new ConfigValueSelect('GENDER', array(
	'default'		=> 'm',
	'label'			=> 'Geschlecht',
	'options'		=> array('m' => 'm&auml;nnlich', 'f' => 'weiblich'),
	'onchange'		=> Ajax::$RELOAD_ALL
)));
$General->addConfigValue( new ConfigValueSelect('PULS_MODE', array(
	'default'		=> 'hfmax',
	'label'			=> 'Pulsanzeige',
	'options'		=> array('bpm' => 'absoluter Wert', 'hfmax' => '&#37; HFmax', 'hfres' => '&#37; HFreserve'),
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$General->addConfigValue( new ConfigValueSelectDb('MAINSPORT', array(
	'default'		=> 1,
	'label'			=> 'Hauptsportart',
	'table'			=> 'sport',
	'column'		=> 'name',
	'onchange'		=> Ajax::$RELOAD_PAGE
)));
$General->addConfigValue( new ConfigValueSelectDb('RUNNINGSPORT', array(
	'default'		=> 1,
	'label'			=> 'Laufsportart',
	'table'			=> 'sport',
	'column'		=> 'name',
	'onchange'		=> Ajax::$RELOAD_PAGE
)));
$General->addConfigValue( new ConfigValueSelectDb('WK_TYPID', array(
	'default'		=> 5,
	'label'			=> 'Trainingstyp: Wettkampf',
	'table'			=> 'type',
	'column'		=> 'name',
	'onchange'		=> Ajax::$RELOAD_PLUGINS
)));
$General->addConfigValue( new ConfigValueSelectDb('LL_TYPID', array(
	'default'		=> 7,
	'label'			=> 'Trainingstyp: Langer Lauf',
	'table'			=> 'type',
	'column'		=> 'name',
	'onchange'		=> Ajax::$RELOAD_PLUGINS
)));
$General->addToCategoryList();





$Training = new ConfigCategory('training', 'Trainingsansicht');
$Training->setKeys(array(
	'TRAINING_DECIMALS',
	'ELEVATION_METHOD',
	'TRAINING_PLOT_PRECISION',
	'ELEVATION_MIN_DIFF',
	'TRAINING_PLOT_MODE',
	'GMAP_PATH_BREAK',
	'TRAINING_MAPTYPE',
	'GMAP_PATH_PRECISION',
	'TRAINING_MAP_COLOR',
	'PACE_Y_LIMIT_MAX',
	'TRAINING_MAP_BEFORE_PLOTS',
	'PACE_Y_LIMIT_MIN',
	'',
	'PACE_Y_AXIS_REVERSE',
	'',
	'PACE_HIDE_OUTLIERS'
));
$Training->addConfigValue( new ConfigValueSelect('TRAINING_PLOT_MODE', array(
	'default'		=> 'all',
	'label'			=> 'Diagrammkombination',
	'tooltip'		=> 'Normalerweise werden alle Diagramme einzeln angezeigt. Sie k&ouml;nnen aber auch kombiniert werden.',
	'options'		=> array(
		'all'			=> 'alle einzeln',
		'pacepulse'		=> 'Pace/Herzfrequenz',
		'collection'	=> 'Pace/Herzfrequenz/H&ouml;he'
	),
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue( new ConfigValueSelect('GMAP_PATH_BREAK', array(
	'default'		=> '15',
	'label'			=> 'Strecke unterbrechen',
	'tooltip'		=> 'Wenn Teile der Strecke anders zur&uuml;ckgelegt wurden (Auto/Bahn/...),
						sollten diese in der Karte nicht eingezeichnet werden.
						Die Erkennung einer solchen Unterbrechung ist aber nicht immer eindeutig.<br />
						15s bedeutet: Distanz, die man bei dem Durchschnittstempo in 15s geschafft h&auml;tte.',
	'options'		=> array( // see Gmap::getCodeForPolylines
		'no'			=> 'nie',
		'15'			=> 'bei zu gro&szlig;em Abstand (15s)',
		'30'			=> 'bei zu gro&szlig;em Abstand (30s)',
		'60'			=> 'bei zu gro&szlig;em Abstand (60s)',
		'120'			=> 'bei zu gro&szlig;em Abstand (120s)',
		'240'			=> 'bei zu gro&szlig;em Abstand (240s)',
		'300'			=> 'bei zu gro&szlig;em Abstand (300s)',
		'600'			=> 'bei zu gro&szlig;em Abstand (600s)',
	),
	'onchange'		=> Ajax::$RELOAD_TRAINING,
	'onchange_eval'	=> 'System::clearTrainingCache();'
)));
$Training->addConfigValue( new ConfigValueSelect('GMAP_PATH_PRECISION', array(
	'default'		=> '5',
	'label'			=> 'Streckengenauigkeit',
	'tooltip'		=> 'Jeder wievielte Datenpunkt soll auf der Strecke angezeigt werden?<br />
						<em>Eine h&ouml;here Genauigkeit bedeutet auch immer l&auml;ngere Ladezeiten!</em>',
	'options'		=> array( // see GpsData::getCodeForPolylines
		'1'				=> 'jeder Datenpunkt',
		'2'				=> 'jeder 2. Datenpunkt',
		'5'				=> 'jeder 5. Datenpunkt (empfohlen)',
		'10'			=> 'jeder 10. Datenpunkt',
		'20'			=> 'jeder 20. Datenpunkte'
	),
	'onchange'		=> Ajax::$RELOAD_TRAINING,
	'onchange_eval'	=> 'System::clearTrainingCache();'
)));
$Training->addConfigValue( new ConfigValueSelect('TRAINING_PLOT_PRECISION', array(
	'default'		=> '200points',
	'label'			=> 'Diagrammgenauigkeit',
	'tooltip'		=> 'Wie viele Datenpunkte sollen in den Diagrammen enthalten sein?<br />
						<em>Eine h&ouml;here Genauigkeit bedeutet auch immer l&auml;ngere Ladezeiten!</em>',
	'options'		=> array( // see GpsData::nextStepForPlotData, GpsData::setStepSizeForPlotData
		'50m'			=> 'alle 50m ein Datenpunkt',
		'100m'			=> 'alle 100m ein Datenpunkt',
		'200m'			=> 'alle 200m ein Datenpunkt',
		'500m'			=> 'alle 500m ein Datenpunkt',
		'100points'		=> 'max. 100 Datenpunkte',
		'200points'		=> 'max. 200 Datenpunkte (empfohlen)',
		'300points'		=> 'max. 300 Datenpunkte',
		'400points'		=> 'max. 400 Datenpunkte',
		'500points'		=> 'max. 500 Datenpunkte',
		'750points'		=> 'max. 750 Datenpunkte',
		'1000points'	=> 'max. 1000 Datenpunkte'
	),
	'onchange'		=> Ajax::$RELOAD_TRAINING,
	'onchange_eval'	=> 'System::clearTrainingCache();'
)));
$Training->addConfigValue( new ConfigValueSelect('PACE_Y_LIMIT_MIN', array(
	'default'		=> '0',
	'label'			=> 'Pace: Y-Achsen-Minimum',
	'tooltip'		=> 'Alternativ zum allgemeinen Ignorieren von Ausrei&szlig;ern kann hier eine maximale Grenze festgelegt werden.',
	'options'		=> array(
		0				=> 'automatisch',
		60				=> '1:00/km',
		120				=> '2:00/km',
		180				=> '3:00/km',
		240				=> '4:00/km',
		300				=> '5:00/km',
		360				=> '6:00/km',
		420				=> '7:00/km',
		480				=> '8:00/km',
		540				=> '9:00/km',
		600				=> '10:00/km'
	),
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue( new ConfigValueSelect('PACE_Y_LIMIT_MAX', array(
	'default'		=> '0',
	'label'			=> 'Pace: Y-Achsen-Maximum',
	'tooltip'		=> 'Alternativ zum allgemeinen Ignorieren von Ausrei&szlig;ern kann hier eine maximale Grenze festgelegt werden.',
	'options'		=> array(
		0				=> 'automatisch',
		240				=> '4:00/km',
		300				=> '5:00/km',
		360				=> '6:00/km',
		420				=> '7:00/km',
		480				=> '8:00/km',
		540				=> '9:00/km',
		600				=> '10:00/km',
		660				=> '11:00/km',
		720				=> '12:00/km',
		780				=> '13:00/km',
		840				=> '14:00/km',
		900				=> '15:00/km'
	),
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue( new ConfigValueBool('PACE_Y_AXIS_REVERSE', array(
	'default'		=> false,
	'label'			=> 'Pace: Y-Achse umkehren',
	'tooltip'		=> 'Standardm&auml;&szlig;ig wird ein h&ouml;heres Tempo im Diagramm weiter unten angezeigt als ein langsameres Tempo. Das kann mit dieser Einstellung umgekehrt werden.',
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue( new ConfigValueBool('PACE_HIDE_OUTLIERS', array(
	'default'		=> false,
	'label'			=> 'Pace: Ausrei&szlig;er egal',
	'tooltip'		=> 'Wenn aktiviert, werden im Pace-Diagramm Ausrei&szlig;er nicht ber&uuml;cksichtigt.',
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue( new ConfigValueSelect('TRAINING_DECIMALS', array(
	'default'		=> '1',
	'label'			=> 'Anzahl Nachkommastellen',
	'options'		=> array('0', '1', '2'),
	'onchange'		=> Ajax::$RELOAD_DATABROWSER_AND_TRAINING
)));
$Training->addConfigValue( new ConfigValueString('TRAINING_MAP_COLOR', array(
	'default'		=> '#FF5500',
	'label'			=> 'Karte: Linienfarbe',
	'tooltip'		=> 'als #RGB-Code',
	'type'			=> 'color'
)));
$Training->addConfigValue( new ConfigValueSelect('TRAINING_MAPTYPE', array(
	'default'		=> 'G_HYBRID_MAP',
	'label'			=> 'Karte: Typ',
	'options'		=> array(
		'G_NORMAL_MAP'		=> 'Normal',
		'G_HYBRID_MAP'		=> 'Hybrid',
		'G_SATELLITE_MAP'	=> 'Satellit',
		'G_PHYSICAL_MAP'	=> 'Physikalisch',
		'OSM'				=> 'OpenStreetMap'
	),
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue( new ConfigValueSelect('ELEVATION_METHOD', array(
	'default'		=> 'treshold',
	'label'			=> 'H&ouml;henmetergl&auml;ttung',
	'tooltip'		=> 'F&uuml;r Profis und H&ouml;henmeterfanatiker: Mit welchem Algorithmus sollen die H&ouml;henmeter vor der Berechnung gegl&auml;ttet werden?',
	'options'		=> array(
		'none'				=> 'keine',
		'treshold'			=> 'Schwellenwert-Methode',
		'douglas-peucker'	=> 'Douglas-Peucker-Algorithmus',
		//'reumann-witkamm'	=> 'Reumann-Witkamm-Algorithmus'
	),
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("&Uuml;ber das Tool <em>Datenbank-Cleanup</em> k&ouml;nnen jetzt alle H&ouml;henmeter neuberechnet werden."));'
)));
$Training->addConfigValue( new ConfigValueInt('ELEVATION_MIN_DIFF', array(
	'default'		=> 3,
	'label'			=> 'H&ouml;henmeter: Schwellenwert',
	'tooltip'		=> 'Schwellenwert zur H&ouml;henmeterberechnung. Wird bei der Schwellenwert-Methode und beim Douglas-Peucker-Algorithmus verwendet.',
	'unit'			=> FormularUnit::$M,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("&Uuml;ber das Tool <em>Datenbank-Cleanup</em> k&ouml;nnen jetzt alle H&ouml;henmeter neuberechnet werden."));'
)));
$Training->addConfigValue( new ConfigValueBool('TRAINING_MAP_BEFORE_PLOTS', array(
	'default'		=> false,
	'label'			=> 'Karte: vor Diagrammen',
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_MAP_MARKER', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_DETAILS', array('default' => false)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_ZONES', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_ROUNDS', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_GRAPHICS', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_PLOT_PACE', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_PLOT_PULSE', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_PLOT_ELEVATION', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_PLOT_SPLITS', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_PLOT_PACEPULSE', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_PLOT_COLLECTION', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_PLOT_CADENCE', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_PLOT_POWER', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_PLOT_TEMPERATURE', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_MAP', array('default' => true)));

$Training->addConfigValue(new ConfigValueBool('FORMULAR_SHOW_SPORT', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('FORMULAR_SHOW_GENERAL', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('FORMULAR_SHOW_DISTANCE', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('FORMULAR_SHOW_SPLITS', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('FORMULAR_SHOW_WEATHER', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('FORMULAR_SHOW_OTHER', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('FORMULAR_SHOW_NOTES', array('default' => false)));
$Training->addConfigValue(new ConfigValueBool('FORMULAR_SHOW_PUBLIC', array('default' => false)));
$Training->addConfigValue(new ConfigValueBool('FORMULAR_SHOW_ELEVATION', array('default' => false)));
$Training->addConfigValue(new ConfigValueBool('FORMULAR_SHOW_GPS', array('default' => false)));
$Training->addToCategoryList();





$Privacy = new ConfigCategory('privacy', 'Privatsph&auml;re');
$Privacy->setKeys(array(
	'TRAINING_LIST_PUBLIC',
	'TRAINING_MAKE_PUBLIC',
	'TRAINING_LIST_ALL',
	'TRAINING_LIST_STATISTICS',
	'TRAINING_MAP_PUBLIC_MODE'
));
$Privacy->addConfigValue( new ConfigValueBool('TRAINING_MAKE_PUBLIC', array(
	'default'		=> false,
	'label'			=> 'Trainings ver&ouml;ffentlichen',
	'tooltip'		=> '&Ouml;ffentliche Trainings k&ouml;nnen von jedem betrachtet werden. Diese Standardeinstellung kann f&uuml;r jedes einzelne Training ver&auml;ndert werden.'
)));
$Privacy->addConfigValue( new ConfigValueBool('TRAINING_LIST_PUBLIC', array(
	'default'		=> false,
	'label'			=> 'Trainingsliste &ouml;ffentlich',
	'tooltip'		=> 'Andere Nutzer k&ouml;nnen bei dieser Einstellung eine Liste mit all deinen (&ouml;ffentlichen) Trainings sehen.',
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$Privacy->addConfigValue( new ConfigValueBool('TRAINING_LIST_ALL', array(
	'default'		=> false,
	'label'			=> 'Liste: private Trainings',
	'tooltip'		=> 'Bei dieser Einstellung werden in der &ouml;ffentlichen Liste auch private Trainings (ohne Link) angezeigt.'
)));
$Privacy->addConfigValue( new ConfigValueBool('TRAINING_LIST_STATISTICS', array(
	'default'		=> false,
	'label'			=> 'Liste: allgemeine Statistiken',
	'tooltip'		=> 'Sollen &uuml;ber der Trainingsliste allgemeine Statistiken angezeigt werden?'
)));
$Privacy->addConfigValue( new ConfigValueSelect('TRAINING_MAP_PUBLIC_MODE', array(
	'default'		=> 'always',
	'label'			=> 'Karte in &ouml;ffentlicher Ansicht',
	'tooltip'		=> 'Die Karte kann bei &ouml;ffentlichen Trainings versteckt werden.',
	'options'		=> array(
		'never'			=> 'nie anzeigen',
		'race'			=> 'bei Wettk&auml;mpfen',
		'race-longjog'	=> 'bei Wettk&auml;mpfen und Langen L&auml;ufen',
		'always'		=> 'immer anzeigen'
	),
)));
$Privacy->addToCategoryList();





$Design = new ConfigCategory('design', 'Design');
$Design->setKeys(array(
	'DB_HIGHLIGHT_TODAY',
	'DB_DISPLAY_MODE',
	'DESIGN_BG_FIX_AND_STRETCH',
	'DESIGN_BG_FILE',
	'DB_SHOW_DIRECT_EDIT_LINK',
	'DB_SHOW_CREATELINK_FOR_DAYS'
));
$Design->addConfigValue( new ConfigValueBool('DB_HIGHLIGHT_TODAY', array(
	'default'		=> true,
	'label'			=> 'Heute hervorheben',
	'tooltip'		=> 'im Kalender',
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$Design->addConfigValue( new ConfigValueSelect('DB_DISPLAY_MODE', array(
	'default'		=> 'week',
	'label'			=> 'Kalender-Modus',
	'options'		=> array(
		'week'			=> 'Wochenansicht',
		'month'			=> 'Monatsansicht'
	),
	'tooltip'		=> 'Standardansicht f&uuml;r den Kalender',
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$Design->addConfigValue( new ConfigValueBool('DB_SHOW_CREATELINK_FOR_DAYS', array(
	'default'		=> true,
	'label'			=> 'Training-Hinzuf&uuml;gen-Link f&uuml;r jeden Tag anzeigen',
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$Design->addConfigValue( new ConfigValueBool('DB_SHOW_DIRECT_EDIT_LINK', array(
	'default'		=> true,
	'label'			=> 'Training-Bearbeiten-Link im Kalender anzeigen',
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$Design->addConfigValue( new ConfigValueBool('DESIGN_BG_FIX_AND_STRETCH', array(
	'default'		=> true,
	'label'			=> 'Hintergrundbild skalieren',
	'onchange'		=> Ajax::$RELOAD_PAGE
)));
$Design->addConfigValue( new ConfigValueSelectFile('DESIGN_BG_FILE', array(
	'default'		=> 'img/backgrounds/Default.jpg',
	'label'			=> 'Hintergrundbild',
	'tooltip'		=> 'Eigene Bilder in /img/backgrounds/',
	'folder'		=> 'img/backgrounds/',
	'onchange'		=> Ajax::$RELOAD_PAGE
)));
$Design->addToCategoryList();





$Calculations = new ConfigCategory('calculations', 'Rechenspiele');
$Calculations->setKeys(array(
	'RECHENSPIELE',
	'ATL_DAYS',
	'JD_USE_VDOT_CORRECTOR',
	'CTL_DAYS',
	'VDOT_MANUAL_CORRECTOR',
	'VDOT_DAYS',
	'VDOT_MANUAL_VALUE',
	'',
	'JD_USE_VDOT_CORRECTION_FOR_ELEVATION',
	'VDOT_CORRECTION_POSITIVE_ELEVATION',
	'VDOT_CORRECTION_NEGATIVE_ELEVATION'
));
$Calculations->addConfigValue( new ConfigValueBool('RECHENSPIELE', array(
	'default'		=> true,
	'label'			=> 'Rechenspiele aktivieren',
	'tooltip'		=> 'Berechnung von VDOT, TRIMP, ...',
	'onchange'		=> Ajax::$RELOAD_PLUGINS
)));
$Calculations->addConfigValue( new ConfigValueBool('JD_USE_VDOT_CORRECTOR', array(
	'default'		=> true,
	'label'			=> 'VDOT-Korrektur',
	'tooltip'		=> 'VDOT-Werte anhand des &quot;besten&quot; Wettkampfes anpassen (empfohlen)',
	'onchange'		=> Ajax::$RELOAD_ALL
)));
$Calculations->addConfigValue( new ConfigValueInt('ATL_DAYS', array(
	'default'		=> 7,
	'label'			=> 'Tage f&uuml;r ATL',
	'tooltip'		=> 'Anzahl an Tagen, die zur Berechnung der ActualTrainingLoad genutzt werden',
	'onchange'		=> Ajax::$RELOAD_PLUGINS
)));
$Calculations->addConfigValue( new ConfigValueInt('CTL_DAYS', array(
	'default'		=> 42,
	'label'			=> 'Tage f&uuml;r CTL',
	'tooltip'		=> 'Anzahl an Tagen, die zur Berechnung der ChronicalTrainingLoad genutzt werden',
	'onchange'		=> Ajax::$RELOAD_PLUGINS
)));
$Calculations->addConfigValue( new ConfigValueInt('VDOT_DAYS', array(
	'default'		=> 30,
	'label'			=> 'Tage f&uuml;r VDOT',
	'tooltip'		=> 'Anzahl an Tagen, die zur Berechnung der VDOT-Form genutzt werden',
	'onchange'		=> Ajax::$RELOAD_PLUGINS
)));
$Calculations->addConfigValue( new ConfigValueString('VDOT_MANUAL_CORRECTOR', array(
	'default'		=> '',
	'label'			=> 'manuelle VDOT-Korrektur',
	'tooltip'		=> 'Falls die automatische VDOT-Korrektur nicht passt, kannst du einen manuellen Faktor hier eingeben.',
	'onchange'		=> Ajax::$RELOAD_PLUGINS
)));
$Calculations->addConfigValue( new ConfigValueString('VDOT_MANUAL_VALUE', array(
	'default'		=> '',
	'label'			=> 'manueller VDOT-Wert',
	'tooltip'		=> 'Wenn du keine Pulsmessung verwendest oder der berechnete VDOT-Wert weit daneben liegt,
						kannst du f&uuml;r die Prognosen hier einen fixen Wert eingeben.',
	'onchange'		=> Ajax::$RELOAD_PLUGINS
)));
$Calculations->addConfigValue( new ConfigValueBool('JD_USE_VDOT_CORRECTION_FOR_ELEVATION', array(
	'default'		=> false,
	'label'			=> 'VDOT: Distanz-Korrektur verwenden',
	'tooltip'		=> 'Zur VDOT-Berechnung die Distanz nach Greif an die H&ouml;henmeter anpassen.',
	'layout'		=> FormularFieldset::$LAYOUT_FIELD_W100,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("&Uuml;ber das Tool <em>Datenbank-Cleanup</em> k&ouml;nnen jetzt alle VDOT-Werte neuberechnet werden."));'
)));
$Calculations->addConfigValue( new ConfigValueString('VDOT_CORRECTION_POSITIVE_ELEVATION', array(
	'default'		=> '2',
	'label'			=> 'VDOT: Distanz-Korrektur pro pos. H&ouml;henmeter',
	'tooltip'		=> 'Um bei der VDOT-Berechnung H&ouml;henmeter zu beachten, kann die Distanz den H&ouml;henmetern entsprechend angepasst werden.',
	'unit'			=> FormularUnit::$M,
	'layout'		=> FormularFieldset::$LAYOUT_FIELD_W100,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("&Uuml;ber das Tool <em>Datenbank-Cleanup</em> k&ouml;nnen jetzt alle VDOT-Werte neuberechnet werden."));'
)));
$Calculations->addConfigValue( new ConfigValueString('VDOT_CORRECTION_NEGATIVE_ELEVATION', array(
	'default'		=> '-1',
	'label'			=> 'VDOT: Distanz-Korrektur pro neg. H&ouml;henmeter',
	'tooltip'		=> 'Um bei der VDOT-Berechnung H&ouml;henmeter zu beachten, kann die Distanz den H&ouml;henmetern entsprechend angepasst werden.',
	'unit'			=> FormularUnit::$M,
	'layout'		=> FormularFieldset::$LAYOUT_FIELD_W100,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("&Uuml;ber das Tool <em>Datenbank-Cleanup</em> k&ouml;nnen jetzt alle VDOT-Werte neuberechnet werden."));'
)));
// Be careful: These values shouldn't be taken with CONF_MAX_..., use class::Trimp
$Calculations->addConfigValue(new ConfigValueInt('MAX_ATL', array('default' => 0)));
$Calculations->addConfigValue(new ConfigValueInt('MAX_CTL', array('default' => 0)));
$Calculations->addConfigValue(new ConfigValueInt('MAX_TRIMP', array('default' => 0)));
// Be careful: These values shouldn't be taken with CONF_VDOT_..., use class::JD (will create VDOT_...)
$Calculations->addConfigValue(new ConfigValueFloat('VDOT_FORM', array('default' => 0)));
$Calculations->addConfigValue(new ConfigValueFloat('VDOT_CORRECTOR', array('default' => 1)));
// Be careful: This value shouldn't be taken with CONF_BASIC_..., use class::Running (will create BASIC_ENDURANCE)
$Calculations->addConfigValue(new ConfigValueInt('BASIC_ENDURANCE', array('default' => 0)));
// Be careful: This value shouldn't be taken with CONF_..., class::Helper will create own consts
$Calculations->addConfigValue(new ConfigValueInt('START_TIME', array('default' => 0)));
$Calculations->addConfigValue(new ConfigValueInt('HF_MAX', array('default' => 200)));
$Calculations->addConfigValue(new ConfigValueInt('HF_REST', array('default' => 60)));
$Calculations->addToCategoryList();





$TrainingForm = new ConfigCategory('trainingform', 'Eingabeformular');
$TrainingForm->setKeys(array(
	'TRAINING_CREATE_MODE',
	'TRAINING_SHOW_AFTER_CREATE',
	'TRAINING_ELEVATION_SERVER',
	'TRAINING_DO_ELEVATION',
	'PLZ',
	'TRAINING_LOAD_WEATHER',
	'TRAINING_SORT_SPORTS',
	'COMPUTE_KCAL',
	'TRAINING_SORT_TYPES',
	'COMPUTE_POWER',
	'TRAINING_SORT_SHOES'
));
$TrainingForm->addConfigValue( new ConfigValueSelect('TRAINING_ELEVATION_SERVER', array(
	'default'		=> 'google',
	'label'			=> 'H&ouml;henkorrektur &uuml;ber',
	'options'		=> array(
		'google'		=> 'maps.googleapis.com',
		'geonames'		=> 'ws.geonames.org'
	),
	'tooltip'		=> 'F&uuml;r die H&ouml;henkorrektur k&ouml;nnen verschiedene Server verwendet werden'
)));
$TrainingForm->addConfigValue( new ConfigValueBool('TRAINING_SHOW_AFTER_CREATE', array(
	'default'		=> false,
	'label'			=> 'Training direkt anzeigen',
	'tooltip'		=> 'Das Training nach dem Erstellen direkt &ouml;ffnen.'
)));
$TrainingForm->addConfigValue( new ConfigValueSelect('TRAINING_CREATE_MODE', array(
	'default'		=> 'garmin',
	'label'			=> 'Standard-Eingabemodus',
	'options'		=> array(
		'upload'		=> 'Datei hochladen',
		'garmin'		=> 'Garmin-Communicator',
		'form'			=> 'Standard-Formular'
	)
)));
$TrainingForm->addConfigValue( new ConfigValueBool('COMPUTE_KCAL', array(
	'default'		=> true,
	'label'			=> 'Kalorien berechnen',
	'tooltip'		=> 'Die Kalorien werden im Formular bei einer &Auml;nderung der Dauer automatisch angepasst. Dabei wird der f&uuml;r die Sportart hinterlegte Wert verwendet.'
)));
$TrainingForm->addConfigValue( new ConfigValueBool('COMPUTE_POWER', array(
	'default'		=> true,
	'label'			=> 'Power berechnen',
	'tooltip'		=> 'Beim Radfahren kann die jeweilige Power anhand einiger physikalischer Gr&ouml;&szlig;en automatisch berechnet werden.'
)));
$TrainingForm->addConfigValue( new ConfigValueBool('TRAINING_DO_ELEVATION', array(
	'default'		=> true,
	'label'			=> 'H&ouml;henkorrektur verwenden',
	'tooltip'		=> 'Die H&ouml;hendaten k&ouml;nnen &uuml;ber externe APIs korrigiert werden. Das ist meist deutlich besser als GPS-Messungen'
)));
$TrainingForm->addConfigValue( new ConfigValueString('PLZ', array(
	'default'		=> '',
	'label'			=> 'Ort',
	'tooltip'		=> 'zum Laden von Wetterdaten von openweathermap.org<br /><em>Ortsname, L&auml;nderk&uuml;rzel</em>',
	'size'			=> FormularInput::$SIZE_MIDDLE
)));
$TrainingForm->addConfigValue( new ConfigValueBool('TRAINING_LOAD_WEATHER', array(
	'default'		=> true,
	'label'			=> 'Wetter laden',
	'tooltip'		=> 'Das aktuelle Wetter kann beim Eintragen eines neuen Trainings geladen und als Voreingabe eingef&uuml;gt werden.'
)));
$TrainingForm->addConfigValue( new ConfigValueSelect('TRAINING_SORT_SPORTS', array(
	'default'		=> 'id-asc',
	'label'			=> 'Sortierung: Sportarten',
	'options'		=> array( // see SportFactory::getOrder()
		'id-asc'		=> 'id (&auml;lteste zuerst)',
		'id-desc'		=> 'id (neueste zuerst)',
		'alpha'			=> 'alphabetisch'
	)
)));
$TrainingForm->addConfigValue( new ConfigValueSelect('TRAINING_SORT_TYPES', array(
	'default'		=> 'id-asc',
	'label'			=> 'Sortierung: Typen',
	'options'		=> array( // see TypeFactory::getOrder()
		'id-asc'		=> 'id (&auml;lteste zuerst)',
		'id-desc'		=> 'id (neueste zuerst)',
		'alpha'			=> 'alphabetisch'
	)
)));
$TrainingForm->addConfigValue( new ConfigValueSelect('TRAINING_SORT_SHOES', array(
	'default'		=> 'id-asc',
	'label'			=> 'Sortierung: Schuhe',
	'options'		=> array( // see ShoeFactory::getOrder()
		'id-asc'		=> 'id (&auml;lteste zuerst)',
		'id-desc'		=> 'id (neueste zuerst)',
		'alpha'			=> 'alphabetisch'
	)
)));
$TrainingForm->addConfigValue(new ConfigValueArray('GARMIN_IGNORE_IDS', array('default' => array())));
$TrainingForm->addToCategoryList();





$SearchWindow = new ConfigCategory('searchwindow', 'Suchfenster');
$SearchWindow->setKeys(array(
	'RESULTS_AT_PAGE'
));
$SearchWindow->addConfigValue( new ConfigValueInt('RESULTS_AT_PAGE', array(
	'default'		=> 15,
	'label'			=> 'Suchergebnisse pro Seite'
)));
$SearchWindow->addToCategoryList();