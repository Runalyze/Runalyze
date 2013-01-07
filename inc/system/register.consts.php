<?php
/*
 * All registrations of CONST variables have to be done here
 * otherwise the Autoloader will not work correct
 */
$General = new ConfigCategory('general', 'Allgemein');
$General->setKeys(array(
	'GENDER',
	'PULS_MODE',
	'PLZ',
	'',
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
$General->addConfigValue( new ConfigValueString('PLZ', array(
	'default'		=> '',
	'label'			=> 'Postleitzahl',
	'tooltip'		=> 'zum Laden von Wetterdaten'
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
	'ELEVATION_MIN_DIFF',
	'TRAINING_MAPTYPE',
	'PACE_Y_LIMIT_MIN',
	'TRAINING_MAP_COLOR',
	'PACE_Y_LIMIT_MAX',
	'TRAINING_MAP_BEFORE_PLOTS',
	'PACE_Y_AXIS_REVERSE',
	'',
	'PACE_HIDE_OUTLIERS'
));
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
)));
$Training->addConfigValue( new ConfigValueBool('PACE_Y_AXIS_REVERSE', array(
	'default'		=> false,
	'label'			=> 'Pace: Y-Achse umkehren',
	'tooltip'		=> 'Standardm&auml;&szlig;ig wird ein h&ouml;heres Tempo im Diagramm weiter unten angezeigt als ein langsameres Tempo. Das kann mit dieser Einstellung umgekehrt werden.'
)));
$Training->addConfigValue( new ConfigValueBool('PACE_HIDE_OUTLIERS', array(
	'default'		=> false,
	'label'			=> 'Pace: Ausrei&szlig;er egal',
	'tooltip'		=> 'Wenn aktiviert, werden im Pace-Diagramm Ausrei&szlig;er nicht ber&uuml;cksichtigt.'
)));
$Training->addConfigValue( new ConfigValueSelect('TRAINING_DECIMALS', array(
	'default'		=> '1',
	'label'			=> 'Anzahl Nachkommastellen',
	'options'		=> array('0', '1', '2'),
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$Training->addConfigValue( new ConfigValueString('TRAINING_MAP_COLOR', array(
	'default'		=> '#FF5500',
	'label'			=> 'Karte: Linienfarbe',
	'tooltip'		=> 'als #RGB-Code'
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
)));
$Training->addConfigValue( new ConfigValueInt('ELEVATION_MIN_DIFF', array(
	'default'		=> 3,
	'label'			=> 'H&ouml;henmeterberechnung: minimale Differenz',
	'tooltip'		=> 'Ab welchem H&ouml;henunterschied zwischen zwei Datenpunkten soll dieser f&uuml;r die H&ouml;henmeter herangezogen werden?
						<br />(2 oder 3 liefert unserer Ansicht nach realistische Werte)'
)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_MAP_BEFORE_PLOTS', array('default' => false, 'label' => 'Karte: vor Diagrammen')));
$Training->addConfigValue(new ConfigValueBool('TRAINING_MAP_MARKER', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_DETAILS', array('default' => false)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_ZONES', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_ROUNDS', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_GRAPHICS', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_PLOT_PACE', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_PLOT_PULSE', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_PLOT_ELEVATION', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_PLOT_SPLITS', array('default' => true)));
$Training->addConfigValue(new ConfigValueBool('TRAINING_SHOW_MAP', array('default' => true)));
// TODO: remove
$Training->addConfigValue(new ConfigValueBool('TRAINING_PLOTS_BELOW', array('default' => false)));
$Training->addToCategoryList();





$Privacy = new ConfigCategory('privacy', 'Privatsph&auml;re');
$Privacy->setKeys(array(
	'TRAINING_LIST_PUBLIC',
	'TRAINING_MAKE_PUBLIC',
	'TRAINING_LIST_ALL'
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
$Privacy->addToCategoryList();





$Design = new ConfigCategory('design', 'Design');
$Design->setKeys(array(
	'DB_HIGHLIGHT_TODAY',
	'DB_DISPLAY_MODE',
	'DESIGN_BG_FIX_AND_STRETCH',
	'DESIGN_BG_FILE',
	'DB_SHOW_DIRECT_EDIT_LINK',
	'DB_SHOW_CREATELINK_FOR_DAYS',
	//'PLUGIN_SHOW_CONFIG_LINK',
	//'PLUGIN_SHOW_MOVE_LINK'
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
// TODO: remove
$Design->addConfigValue( new ConfigValueBool('PLUGIN_SHOW_CONFIG_LINK', array(
	'default'		=> true,
	'label'			=> 'Plugin: Konfiguration-Link anzeigen',
	'tooltip'		=> 'Wenn aktiv wird bei jedem Plugin vor dem Namen ein Link zur Plugin-Konfiguration angezeigt',
	'onchange'		=> Ajax::$RELOAD_PLUGINS
)));
// TODO: remove
$Design->addConfigValue( new ConfigValueBool('PLUGIN_SHOW_MOVE_LINK', array(
	'default'		=> false,
	'label'			=> 'Plugin: Verschieben-Link anzeigen',
	'tooltip'		=> 'Mit diesem Link lassen sich die Panel direkt verschieben.',
	'onchange'		=> Ajax::$RELOAD_PLUGINS
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
// TODO: remove
$Design->addConfigValue( new ConfigValueSelect('DESIGN_TOOLBAR_POSITION', array(
	'default'		=> 'top',
	'label'			=> 'Position der Toolbar',
	'options'		=> array(
		'top'			=> 'oben',
		'bottom'		=> 'unten'
	)
)));
// TODO: remove
$Design->addConfigValue( new ConfigValueBool('JS_USE_TOOLTIP', array('default' => true)));
$Design->addToCategoryList();





$Calculations = new ConfigCategory('calculations', 'Rechenspiele');
$Calculations->setKeys(array(
	'RECHENSPIELE',
	'ATL_DAYS',
	'',
	'CTL_DAYS',
	'JD_USE_VDOT_CORRECTOR',
	'VDOT_DAYS'
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
// Be careful: These values shouldn't be taken with CONF_MAX_ATL, use class::Trimp
$Calculations->addConfigValue(new ConfigValueInt('MAX_ATL', array('default' => 0)));
$Calculations->addConfigValue(new ConfigValueInt('MAX_CTL', array('default' => 0)));
$Calculations->addConfigValue(new ConfigValueInt('MAX_TRIMP', array('default' => 0)));
// Be careful: These values shouldn't be taken with CONF_VDOT_CORRECTOR, use class::JD (will create VDOT_CORRECTOR)
$Calculations->addConfigValue(new ConfigValueFloat('VDOT_CORRECTOR', array('default' => 1)));
$Calculations->addToCategoryList();





$TrainingForm = new ConfigCategory('trainingform', 'Eingabeformular');
$TrainingForm->setKeys(array(
	'TRAINING_CREATE_MODE',
	'COMPUTE_KCAL',
	'TRAINING_ELEVATION_SERVER',
	'TRAINING_DO_ELEVATION',
	'GARMIN_API_KEY'
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
	'tooltip'		=> 'Automatisch Kalorien f&uuml;r ein Training berechnen (falls nicht angegeben)'
)));
$TrainingForm->addConfigValue( new ConfigValueBool('TRAINING_DO_ELEVATION', array(
	'default'		=> true,
	'label'			=> 'H&ouml;henkorrektur verwenden',
	'tooltip'		=> 'Die H&ouml;hendaten k&ouml;nnen &uuml;ber externe APIs korrigiert werden. Das ist meist deutlich besser als GPS-Messungen'
)));
// TODO: Set as admin-config
$TrainingForm->addConfigValue( new ConfigValueString('GARMIN_API_KEY', array(
	'default'		=> '',
	'label'			=> 'Garmin API-Key',
	'tooltip'		=> 'Notwendig f&uuml;r den Garmin-Communicator<br />f&uuml;r http://'.$_SERVER['HTTP_HOST'],
)));
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