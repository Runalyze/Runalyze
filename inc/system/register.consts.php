<?php
/*
 * All registrations of CONST variables have to be done here
 * otherwise the Autoloader will not work correct
 */
$General = new ConfigCategory('general', __('General'));
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
	'label'			=> 'Gender',
	'options'		=> array('m' => __('male'), 'f' => __('female')),
	'onchange'		=> Ajax::$RELOAD_ALL
)));
$General->addConfigValue( new ConfigValueSelect('PULS_MODE', array(
	'default'		=> 'hfmax',
	'label'			=> __('Pulse notice'),
	'options'		=> array('bpm' => __('absolute value'), 'hfmax' => '&#37; HFmax', 'hfres' => '&#37; HFreserve'),
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$General->addConfigValue( new ConfigValueSelectDb('MAINSPORT', array(
	'default'		=> 1,
	'label'			=> __('Main Sport type'),
	'table'			=> 'sport',
	'column'		=> 'name',
	'onchange'		=> Ajax::$RELOAD_PAGE
)));
$General->addConfigValue( new ConfigValueSelectDb('RUNNINGSPORT', array(
	'default'		=> 1,
	'label'			=> __('Running Sporttype'),
	'table'			=> 'sport',
	'column'		=> 'name',
	'onchange'		=> Ajax::$RELOAD_PAGE
)));
$General->addConfigValue( new ConfigValueSelectDb('WK_TYPID', array(
	'default'		=> 5,
	'label'			=> __('Workout Type: competition'),
	'table'			=> 'type',
	'column'		=> 'name',
	'onchange'		=> Ajax::$RELOAD_PLUGINS
)));
$General->addConfigValue( new ConfigValueSelectDb('LL_TYPID', array(
	'default'		=> 7,
	'label'			=> __('Workout Type: Long run'),
	'table'			=> 'type',
	'column'		=> 'name',
	'onchange'		=> Ajax::$RELOAD_PLUGINS
)));
$General->addToCategoryList();





$Training = new ConfigCategory('training', __('Workout view'));
$Training->setKeys(array(
	'TRAINING_DECIMALS',
	'ELEVATION_METHOD',
	'TRAINING_PLOT_PRECISION',
	'ELEVATION_MIN_DIFF',
	'TRAINING_PLOT_MODE',
	'GMAP_PATH_BREAK',
	'',
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
	'label'			=> __('Graph combination'),
	'tooltip'		=> 'Normalerweise werden alle Diagramme einzeln angezeigt. Sie k&ouml;nnen aber auch kombiniert werden.',
	'options'		=> array(
		'all'			=> __('all separated'),
		'pacepulse'		=> __('Pace/Heart rate'),
		'collection'	=> __('Pace/Heart rate/altitude')
	),
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue( new ConfigValueSelect('GMAP_PATH_BREAK', array(
	'default'		=> '15',
	'label'			=> __('Interrupt Route'),
	'tooltip'		=> 'Wenn Teile der Strecke anders zur&uuml;ckgelegt wurden (Auto/Bahn/...),
						sollten diese in der Karte nicht eingezeichnet werden.
						Die Erkennung einer solchen Unterbrechung ist aber nicht immer eindeutig.<br>
						15s bedeutet: Distanz, die man bei dem Durchschnittstempo in 15s geschafft h&auml;tte.',
	'options'		=> array( // see LeafletTrainingRoute::findLimitForPauses
		'no'			=> __('never'),
		'15'			=> __('at too big distance (15s)'),
		'30'			=> __('at too big distance (30s)'),
		'60'			=> __('at too big distance (60s)'),
		'120'			=> __('at too big distance (120s)'),
		'240'			=> __('at too big distance (240s)'),
		'300'			=> __('at too big distance (300s)'),
		'600'			=> __('at too big distance (600s)'),
	),
	'onchange'		=> Ajax::$RELOAD_TRAINING,
	'onchange_eval'	=> 'System::clearTrainingCache();'
)));
$Training->addConfigValue( new ConfigValueSelect('GMAP_PATH_PRECISION', array(
	'default'		=> '5',
	'label'			=> __('Path Precision'),
	'tooltip'		=> 'Jeder wievielte Datenpunkt soll auf der Strecke angezeigt werden?<br>
						<em>Eine h&ouml;here Genauigkeit bedeutet auch immer l&auml;ngere Ladezeiten!</em>',
	'options'		=> array( // see LeafletTrainingRoute::prepareLoop
		'1'				=> __('every data point'),
		'2'				=> __('every second data point'),
		'5'				=> __('every fifth data point (recommended)'),
		'10'			=> __('every tenth data point'),
		'20'			=> __('every twentieth data point')
	),
	'onchange'		=> Ajax::$RELOAD_TRAINING,
	'onchange_eval'	=> 'System::clearTrainingCache();'
)));
$Training->addConfigValue( new ConfigValueSelect('TRAINING_PLOT_PRECISION', array(
	'default'		=> '200points',
	'label'			=> __('Graph precision'),
	'tooltip'		=> 'Wie viele Datenpunkte sollen in den Diagrammen enthalten sein?<br>
						<em>Eine h&ouml;here Genauigkeit bedeutet auch immer l&auml;ngere Ladezeiten!</em>',
	'options'		=> array( // see GpsData::nextStepForPlotData, GpsData::setStepSizeForPlotData
		'50m'			=> __('all 50m a data point'),
		'100m'			=> __('all 100m a data point'),
		'200m'			=> __('all 200m a data point'),
		'500m'			=> __('all 500m a data point'),
		'100points'		=> __('max. 100 data points'),
		'200points'		=> __('max. 200 data points (recommended)'),
		'300points'		=> __('max. 300 data points'),
		'400points'		=> __('max. 400 data points'),
		'500points'		=> __('max. 500 data points'),
		'750points'		=> __('max. 750 data points'),
		'1000points'	=> __('max. 1000 data points')
	),
	'onchange'		=> Ajax::$RELOAD_TRAINING,
	'onchange_eval'	=> 'System::clearTrainingCache();'
)));
$Training->addConfigValue( new ConfigValueSelect('PACE_Y_LIMIT_MIN', array(
	'default'		=> '0',
	'label'			=> __('Pace: Y-Axis-Minimum'),
	'tooltip'		=> 'Alternativ zum allgemeinen Ignorieren von Ausrei&szlig;ern kann hier eine maximale Grenze festgelegt werden.',
	'options'		=> array(
		0				=> __('automatic'),
		60				=> __('1:00/km'),
		120				=> __('2:00/km'),
		180				=> __('3:00/km'),
		240				=> __('4:00/km'),
		300				=> __('5:00/km'),
		360				=> __('6:00/km'),
		420				=> __('7:00/km'),
		480				=> __('8:00/km'),
		540				=> __('9:00/km'),
		600				=> __('10:00/km')
	),
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue( new ConfigValueSelect('PACE_Y_LIMIT_MAX', array(
	'default'		=> '0',
	'label'			=> __('Pace: Y-Axis-maximum'),
	'tooltip'		=> 'Alternativ zum allgemeinen Ignorieren von Ausrei&szlig;ern kann hier eine maximale Grenze festgelegt werden.',
	'options'		=> array(
		0				=> __('automatic'),
		240				=> __('4:00/km'),
		300				=> __('5:00/km'),
		360				=> __('6:00/km'),
		420				=> __('7:00/km'),
		480				=> __('8:00/km'),
		540				=> __('9:00/km'),
		600				=> __('10:00/km'),
		660				=> __('11:00/km'),
		720				=> __('12:00/km'),
		780				=> __('13:00/km'),
		840				=> __('14:00/km'),
		900				=> __('15:00/km')
	),
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue( new ConfigValueBool('PACE_Y_AXIS_REVERSE', array(
	'default'		=> false,
	'label'			=> __('Pace: Reverse y-axis'),
	'tooltip'		=> 'Standardm&auml;&szlig;ig wird ein h&ouml;heres Tempo im Diagramm weiter unten angezeigt als ein langsameres Tempo. Das kann mit dieser Einstellung umgekehrt werden.',
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue( new ConfigValueBool('PACE_HIDE_OUTLIERS', array(
	'default'		=> false,
	'label'			=> __('Pace: Outliers indifferent'),
	'tooltip'		=> 'Wenn aktiviert, werden im Pace-Diagramm Ausrei&szlig;er nicht ber&uuml;cksichtigt.',
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue( new ConfigValueSelect('TRAINING_DECIMALS', array(
	'default'		=> '1',
	'label'			=> __('Number of decimal places'),
	'options'		=> array('0', '1', '2'),
	'onchange'		=> Ajax::$RELOAD_DATABROWSER_AND_TRAINING
)));
$Training->addConfigValue( new ConfigValueString('TRAINING_MAP_COLOR', array(
	'default'		=> '#FF5500',
	'label'			=> __('Map: Linecolor'),
	'tooltip'		=> __('as #RGB-Code'),
	'type'			=> 'color'
)));
$Training->addConfigValue( new ConfigValueSelect('ELEVATION_METHOD', array(
	'default'		=> 'treshold',
	'label'			=> __('altitude equalisation'),
	'tooltip'		=> 'F&uuml;r Profis und altitudenmeterfanatiker: Mit welchem Algorithmus sollen die altitudenmeter vor der Berechnung gegl&auml;ttet werden?',
	'options'		=> array(
		'none'				=> __('none'),
		'treshold'			=> 'Schwellenwert-Methode',
		'douglas-peucker'	=> 'Douglas-Peucker-Algorithmus',
		//'reumann-witkamm'	=> 'Reumann-Witkamm-Algorithmus'
	),
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("&Uuml;ber das Tool <em>Datenbank-Cleanup</em> k&ouml;nnen jetzt alle altitudenmeter neuberechnet werden."));'
)));
$Training->addConfigValue( new ConfigValueInt('ELEVATION_MIN_DIFF', array(
	'default'		=> 3,
	'label'			=> __('Elevation: threshold'),
	'tooltip'		=> 'Schwellenwert zur altitudenmeterberechnung. Wird bei der Schwellenwert-Methode und beim Douglas-Peucker-Algorithmus verwendet.',
	'unit'			=> FormularUnit::$M,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("&Uuml;ber das Tool <em>Datenbank-Cleanup</em> k&ouml;nnen jetzt alle altitudenmeter neuberechnet werden."));'
)));
$Training->addConfigValue( new ConfigValueBool('TRAINING_MAP_BEFORE_PLOTS', array(
	'default'		=> false,
	'label'			=> __('Map: Before graphs'),
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue(new ConfigValueString('TRAINING_LEAFLET_LAYER', array('default' => 'OpenStreetMap')));
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





$Privacy = new ConfigCategory('privacy', __('Privacy'));
$Privacy->setKeys(array(
	'TRAINING_LIST_PUBLIC',
	'TRAINING_MAKE_PUBLIC',
	'TRAINING_LIST_ALL',
	'TRAINING_LIST_STATISTICS',
	'TRAINING_MAP_PUBLIC_MODE'
));
$Privacy->addConfigValue( new ConfigValueBool('TRAINING_MAKE_PUBLIC', array(
	'default'		=> false,
	'label'			=> __('Publish workouts'),
	'tooltip'		=> '&Ouml;ffentliche Trainings k&ouml;nnen von jedem betrachtet werden. Diese Standardeinstellung kann f&uuml;r jedes einzelne Training ver&auml;ndert werden.'
)));
$Privacy->addConfigValue( new ConfigValueBool('TRAINING_LIST_PUBLIC', array(
	'default'		=> false,
	'label'			=> __('Public workoutlist'),
	'tooltip'		=> 'Andere Nutzer k&ouml;nnen bei dieser Einstellung eine Liste mit all deinen (&ouml;ffentlichen) Trainings sehen.',
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$Privacy->addConfigValue( new ConfigValueBool('TRAINING_LIST_ALL', array(
	'default'		=> false,
	'label'			=> __('List: Private workouts'),
	'tooltip'		=> 'Bei dieser Einstellung werden in der &ouml;ffentlichen Liste auch private Trainings (ohne Link) angezeigt.'
)));
$Privacy->addConfigValue( new ConfigValueBool('TRAINING_LIST_STATISTICS', array(
	'default'		=> false,
	'label'			=> __('List: General Statistics'),
	'tooltip'		=> 'Sollen &uuml;ber der Trainingsliste allgemeine Statistiken angezeigt werden?'
)));
$Privacy->addConfigValue( new ConfigValueSelect('TRAINING_MAP_PUBLIC_MODE', array(
	'default'		=> 'always',
	'label'			=> __('Map in public view'),
	'tooltip'		=> __('This map can be hidden in public workouts.'),
	'options'		=> array(
		'never'			=> __('never'),
		'race'			=> __('by competition'),
		'race-longjog'	=> __('by competition and long runs'),
		'always'		=> __('show always')
	),
)));
$Privacy->addToCategoryList();





$Design = new ConfigCategory('design', __('Design'));
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
	'label'			=> __('Highlight today'),
	'tooltip'		=> __('in the calendar'),
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$Design->addConfigValue( new ConfigValueSelect('DB_DISPLAY_MODE', array(
	'default'		=> 'week',
	'label'			=> __('Calendar mode'),
	'options'		=> array(
		'week'			=> __('Week view'),
		'month'			=> __('Month view')
	),
	'tooltip'		=> 'Standardansicht f&uuml;r den Kalender',
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$Design->addConfigValue( new ConfigValueBool('DB_SHOW_CREATELINK_FOR_DAYS', array(
	'default'		=> true,
	'label'			=> __('Show Workout-Add button for every day'),
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$Design->addConfigValue( new ConfigValueBool('DB_SHOW_DIRECT_EDIT_LINK', array(
	'default'		=> true,
	'label'			=> __('Show Workout-Edit button in the calendar'),
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$Design->addConfigValue( new ConfigValueBool('DESIGN_BG_FIX_AND_STRETCH', array(
	'default'		=> true,
	'label'			=> __('Scale background image'),
	'onchange'		=> Ajax::$RELOAD_PAGE
)));
$Design->addConfigValue( new ConfigValueSelectFile('DESIGN_BG_FILE', array(
	'default'		=> 'img/backgrounds/Default.jpg',
	'label'			=> __('Background image'),
	'tooltip'		=> __('Own images in /img/backgrounds/'),
	'folder'		=> 'img/backgrounds/',
	'onchange'		=> Ajax::$RELOAD_PAGE
)));
$Design->addToCategoryList();





$Calculations = new ConfigCategory('calculations', __('Calculations'));
$Calculations->setKeys(array(
	'RECHENSPIELE',
	'VDOT_HF_METHOD',
	'JD_USE_VDOT_CORRECTOR',
	'ATL_DAYS',
	'VDOT_MANUAL_CORRECTOR',
	'CTL_DAYS',
	'VDOT_MANUAL_VALUE',
	'VDOT_DAYS',
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
$Calculations->addConfigValue( new ConfigValueSelect('VDOT_HF_METHOD', array(
	'default'		=> 'logarithmic',
	'label'			=> 'VDOT-Puls-Methode',
	'options'		=> array(
		'logarithmic'	=> 'logarithmisch (neue Methode ab v1.5)',
		'linear'		=> 'linear (alte Methode bis v1.4)'
	),
	'tooltip'		=> 'Methode zur Berechnung eines prozentualen VDOT-Werts aus einem Pulswert.
		Alte Methoden sind nur aus Kompatibilit&auml;tsgr&uuml;nden aufgelistet.
		Es wird sehr empfohlen, die neuste Methode zu verwenden.',
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("&Uuml;ber das Tool <em>Datenbank-Cleanup</em> k&ouml;nnen jetzt alle VDOT-Werte neuberechnet werden."));'
)));
$Calculations->addConfigValue( new ConfigValueBool('JD_USE_VDOT_CORRECTOR', array(
	'default'		=> true,
	'label'			=> 'VDOT-Korrektur',
	'tooltip'		=> 'VDOT-Werte anhand des &quot;besten&quot; Wettkampfes anpassen (empfohlen)',
	'onchange'		=> Ajax::$RELOAD_ALL,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("&Uuml;ber das Tool <em>Datenbank-Cleanup</em> k&ouml;nnen jetzt alle VDOT-Werte neuberechnet werden."));'
)));
$Calculations->addConfigValue( new ConfigValueInt('ATL_DAYS', array(
	'default'		=> 7,
	'label'			=> 'Tage f&uuml;r ATL',
	'tooltip'		=> 'Anzahl an Tagen, die zur Berechnung der ActualTrainingLoad genutzt werden',
	'onchange'		=> Ajax::$RELOAD_PLUGINS,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("&Uuml;ber das Tool <em>Datenbank-Cleanup</em> k&ouml;nnen jetzt alle ATL-Werte neuberechnet werden."));'
)));
$Calculations->addConfigValue( new ConfigValueInt('CTL_DAYS', array(
	'default'		=> 42,
	'label'			=> 'Tage f&uuml;r CTL',
	'tooltip'		=> 'Anzahl an Tagen, die zur Berechnung der ChronicalTrainingLoad genutzt werden',
	'onchange'		=> Ajax::$RELOAD_PLUGINS,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("&Uuml;ber das Tool <em>Datenbank-Cleanup</em> k&ouml;nnen jetzt alle CTL-Werte neuberechnet werden."));'
)));
$Calculations->addConfigValue( new ConfigValueInt('VDOT_DAYS', array(
	'default'		=> 30,
	'label'			=> __('Days for VDOT'),
	'tooltip'		=> 'Anzahl an Tagen, die zur Berechnung der VDOT-Form genutzt werden',
	'onchange'		=> Ajax::$RELOAD_PLUGINS,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("&Uuml;ber das Tool <em>Datenbank-Cleanup</em> k&ouml;nnen jetzt alle VDOT-Werte neuberechnet werden."));'
)));
$Calculations->addConfigValue( new ConfigValueString('VDOT_MANUAL_CORRECTOR', array(
	'default'		=> '',
	'label'			=> __('Manual VDOT-correction'),
	'tooltip'		=> 'Falls die automatische VDOT-Korrektur nicht passt, kannst du einen manuellen Faktor hier eingeben.',
	'onchange'		=> Ajax::$RELOAD_PLUGINS,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("&Uuml;ber das Tool <em>Datenbank-Cleanup</em> k&ouml;nnen jetzt alle VDOT-Werte neuberechnet werden."));'
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
	'tooltip'		=> 'Zur VDOT-Berechnung die Distanz nach Greif an die altitudenmeter anpassen.',
	'layout'		=> FormularFieldset::$LAYOUT_FIELD_W100,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("&Uuml;ber das Tool <em>Datenbank-Cleanup</em> k&ouml;nnen jetzt alle VDOT-Werte neuberechnet werden."));'
)));
$Calculations->addConfigValue( new ConfigValueString('VDOT_CORRECTION_POSITIVE_ELEVATION', array(
	'default'		=> '2',
	'label'			=> 'VDOT: Distanz-Korrektur pro pos. altitudenmeter',
	'tooltip'		=> 'Um bei der VDOT-Berechnung altitudenmeter zu beachten, kann die Distanz den altitudenmetern entsprechend angepasst werden.',
	'unit'			=> FormularUnit::$M,
	'layout'		=> FormularFieldset::$LAYOUT_FIELD_W100,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("&Uuml;ber das Tool <em>Datenbank-Cleanup</em> k&ouml;nnen jetzt alle VDOT-Werte neuberechnet werden."));'
)));
$Calculations->addConfigValue( new ConfigValueString('VDOT_CORRECTION_NEGATIVE_ELEVATION', array(
	'default'		=> '-1',
	'label'			=> 'VDOT: Distanz-Korrektur pro neg. altitudenmeter',
	'tooltip'		=> 'Um bei der VDOT-Berechnung altitudenmeter zu beachten, kann die Distanz den altitudenmetern entsprechend angepasst werden.',
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





$TrainingForm = new ConfigCategory('trainingform', __('Input form'));
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
	'label'			=> 'altitudenkorrektur &uuml;ber',
	'options'		=> array(
		'google'		=> 'maps.googleapis.com',
		'geonames'		=> 'ws.geonames.org'
	),
	'tooltip'		=> 'F&uuml;r die altitudenkorrektur k&ouml;nnen verschiedene Server verwendet werden'
)));
$TrainingForm->addConfigValue( new ConfigValueBool('TRAINING_SHOW_AFTER_CREATE', array(
	'default'		=> false,
	'label'			=> __('Show workout directly'),
	'tooltip'		=> 'Das Training nach dem Erstellen direkt &ouml;ffnen.'
)));
$TrainingForm->addConfigValue( new ConfigValueSelect('TRAINING_CREATE_MODE', array(
	'default'		=> 'garmin',
	'label'			=> __('Standard-Entry-Mode'),
	'options'		=> array(
		'upload'		=> __('Upload file'),
		'garmin'		=> __('Garmin-Communicator'),
		'form'			=> 'Standard-Formular'
	)
)));
$TrainingForm->addConfigValue( new ConfigValueBool('COMPUTE_KCAL', array(
	'default'		=> true,
	'label'			=> __('Calculate calories'),
	'tooltip'		=> 'Die Kalorien werden im Formular bei einer &Auml;nderung der Dauer automatisch angepasst. Dabei wird der f&uuml;r die Sportart hinterlegte Wert verwendet.'
)));
$TrainingForm->addConfigValue( new ConfigValueBool('COMPUTE_POWER', array(
	'default'		=> true,
	'label'			=> __('Calculate power'),
	'tooltip'		=> 'Beim Radfahren kann die jeweilige Power anhand einiger physikalischer Gr&ouml;&szlig;en automatisch berechnet werden.'
)));
$TrainingForm->addConfigValue( new ConfigValueBool('TRAINING_DO_ELEVATION', array(
	'default'		=> true,
	'label'			=> 'altitudenkorrektur verwenden',
	'tooltip'		=> 'Die altitudendaten k&ouml;nnen &uuml;ber externe APIs korrigiert werden. Das ist meist deutlich besser als GPS-Messungen'
)));
$TrainingForm->addConfigValue( new ConfigValueString('PLZ', array(
	'default'		=> '',
	'label'			=> __('Place'),
	'tooltip'		=> 'zum Laden von Wetterdaten von openweathermap.org<br><em>Ortsname, L&auml;nderk&uuml;rzel</em>',
	'size'			=> FormularInput::$SIZE_MIDDLE
)));
$TrainingForm->addConfigValue( new ConfigValueBool('TRAINING_LOAD_WEATHER', array(
	'default'		=> true,
	'label'			=> __('Load weather'),
	'tooltip'		=> 'Das aktuelle Wetter kann beim Eintragen eines neuen Trainings geladen und als Voreingabe eingef&uuml;gt werden.'
)));
$TrainingForm->addConfigValue( new ConfigValueSelect('TRAINING_SORT_SPORTS', array(
	'default'		=> 'id-asc',
	'label'			=> __('Sort: Sport Types'),
	'options'		=> array( // see SportFactory::getOrder()
		'id-asc'		=> __('id (oldest first)'),
		'id-desc'		=> __('id (newest first)'),
		'alpha'			=> __('alphabetical')
	)
)));
$TrainingForm->addConfigValue( new ConfigValueSelect('TRAINING_SORT_TYPES', array(
	'default'		=> 'id-asc',
	'label'			=> __('Sort: Types'),
	'options'		=> array( // see TypeFactory::getOrder()
		'id-asc'		=> __('id (oldest first)'),
		'id-desc'		=> __('id (newest first)'),
		'alpha'			=> __('alphabetical')
	)
)));
$TrainingForm->addConfigValue( new ConfigValueSelect('TRAINING_SORT_SHOES', array(
	'default'		=> 'id-asc',
	'label'			=> __('Sort: Shoes'),
	'options'		=> array( // see ShoeFactory::getOrder()
		'id-asc'		=> __('id (oldest first)'),
		'id-desc'		=> __('id (newest first)'),
		'alpha'			=> __('alphabetical')
	)
)));
$TrainingForm->addConfigValue(new ConfigValueArray('GARMIN_IGNORE_IDS', array('default' => array())));
$TrainingForm->addToCategoryList();





$SearchWindow = new ConfigCategory('searchwindow', __('Search window'));
$SearchWindow->setKeys(array(
	'RESULTS_AT_PAGE'
));
$SearchWindow->addConfigValue( new ConfigValueInt('RESULTS_AT_PAGE', array(
	'default'		=> 15,
	'label'			=> __('Results per page')
)));
$SearchWindow->addToCategoryList();