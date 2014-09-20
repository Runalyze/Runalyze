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
	'label'			=> __('Gender'),
	'options'		=> array('m' => __('male'), 'f' => __('female')),
	'onchange'		=> Ajax::$RELOAD_ALL
)));
$General->addConfigValue( new ConfigValueSelect('PULS_MODE', array(
	'default'		=> 'hfmax',
	'label'			=> __('Heart rate unit'),
	'options'		=> array('bpm' => __('absolute value'), 'hfmax' => '&#37; HFmax', 'hfres' => '&#37; HFreserve'),
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$General->addConfigValue( new ConfigValueSelectDb('MAINSPORT', array(
	'default'		=> 1,
	'label'			=> __('Main sport'),
	'table'			=> 'sport',
	'column'		=> 'name',
	'onchange'		=> Ajax::$RELOAD_PAGE
)));
$General->addConfigValue( new ConfigValueSelectDb('RUNNINGSPORT', array(
	'default'		=> 1,
	'label'			=> __('Running sport'),
	'table'			=> 'sport',
	'column'		=> 'name',
	'onchange'		=> Ajax::$RELOAD_PAGE
)));
$General->addConfigValue( new ConfigValueSelectDb('WK_TYPID', array(
	'default'		=> 5,
	'label'			=> __('Activity type: competition'),
	'table'			=> 'type',
	'column'		=> 'name',
	'onchange'		=> Ajax::$RELOAD_PLUGINS
)));
$General->addConfigValue( new ConfigValueSelectDb('LL_TYPID', array(
	'default'		=> 7,
	'label'			=> __('Activity type: long run'),
	'table'			=> 'type',
	'column'		=> 'name',
	'onchange'		=> Ajax::$RELOAD_PLUGINS
)));
$General->addToCategoryList();





$Training = new ConfigCategory('training', __('Activity view'));
$Training->setKeys(array(
	'TRAINING_PLOT_SMOOTH',
	'TRAINING_DECIMALS',
	'TRAINING_PLOT_PRECISION',
	'GMAP_PATH_PRECISION',
	'ELEVATION_METHOD',
	'GMAP_PATH_BREAK',
	'ELEVATION_MIN_DIFF',
	'TRAINING_MAP_COLOR',
	'PACE_Y_LIMIT_MAX',
	'PACE_Y_AXIS_REVERSE',
	'PACE_Y_LIMIT_MIN',
	'PACE_HIDE_OUTLIERS'
	//'TRAINING_PLOT_MODE', // Not supported anymore
	//'TRAINING_MAP_BEFORE_PLOTS', // Not supported anymore
));
$Training->addConfigValue( new ConfigValueSelect('TRAINING_PLOT_MODE', array(
	'default'		=> 'all',
	'label'			=> __('Plots: combination'),
	'options'		=> array(
		'all'			=> __('all separated'),
		'pacepulse'		=> __('Pace/Heart rate'),
		'collection'	=> __('Pace/Heart rate/Elevation')
	),
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue( new ConfigValueSelect('GMAP_PATH_BREAK', array(
	'default'		=> '15',
	'label'			=> __('Map: unterrupt route'),
	'tooltip'		=> __('The gps path can be interrupted in case of <em>jumps</em> (e.g. by car/train/...).'.
						'Finding these jumps is not easy. You can define up to what distance (in seconds by average pace)'.
						'between two data points the path should be continued.'),
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
	'label'			=> __('Map: precision'),
	'tooltip'		=> __('How many data points shoud be displayed?'),
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
$Training->addConfigValue( new ConfigValueBool('TRAINING_PLOT_SMOOTH', array(
	'default'		=> false,
	'label'			=> __('Plot: smooth curves'),
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue( new ConfigValueSelect('TRAINING_PLOT_PRECISION', array(
	'default'		=> '200points',
	'label'			=> __('Plots: precision'),
	'tooltip'		=> __('How many data points should be plotted?'),
	'options'		=> array( // see GpsData::nextStepForPlotData, GpsData::setStepSizeForPlotData
		'50m'			=> __('every 50m a data point'),
		'100m'			=> __('every 100m a data point'),
		'200m'			=> __('every 200m a data point'),
		'500m'			=> __('every 500m a data point'),
		'50points'		=> __('max. 50 data points'),
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
	'label'			=> __('Pace: y-axis-minimum'),
	'tooltip'		=> __('Data points below this limit will be ignored. (only for running)'),
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
	'label'			=> __('Pace: y-axis-maximum'),
	'tooltip'		=> __('Data points above this limit will be ignored. (only for running)'),
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
	'tooltip'		=> __('Reverse the y-axis such that a faster pace is at the top.'),
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue( new ConfigValueBool('PACE_HIDE_OUTLIERS', array(
	'default'		=> false,
	'label'			=> __('Pace: Ignore outliers'),
	'tooltip'		=> __('Try to ignore outliers in the pace plot.'),
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));
$Training->addConfigValue( new ConfigValueSelect('TRAINING_DECIMALS', array(
	'default'		=> '1',
	'label'			=> __('Number of decimals'),
	'options'		=> array('0', '1', '2'),
	'onchange'		=> Ajax::$RELOAD_DATABROWSER_AND_TRAINING
)));
$Training->addConfigValue( new ConfigValueString('TRAINING_MAP_COLOR', array(
	'default'		=> '#FF5500',
	'label'			=> __('Map: line color'),
	'tooltip'		=> __('as #RGB code'),
	'type'			=> 'color'
)));
$Training->addConfigValue( new ConfigValueSelect('ELEVATION_METHOD', array(
	'default'		=> 'treshold',
	'label'			=> __('Elevation: smoothing'),
	'tooltip'		=> __('Choose the algorithm to smooth the elevation data'),
	'options'		=> array(
		'none'				=> __('none'),
		'treshold'			=> __('Treshold method'),
		'douglas-peucker'	=> __('Douglas-Peucker-Algorithm'),
		//'reumann-witkamm'	=> __('Reumann-Witkamm-Algorithm')
	),
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("The tool <em>Datenbank-Cleanup</em> can be used to recalculate elevation values."));'
)));
$Training->addConfigValue( new ConfigValueInt('ELEVATION_MIN_DIFF', array(
	'default'		=> 3,
	'label'			=> __('Elevation: threshold'),
	'tooltip'		=> __('Treshold for the weeding algorithm'),
	'unit'			=> FormularUnit::$M,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("The tool <em>Datenbank-Cleanup</em> can be used to recalculate elevation values."));'
)));
// TODO: remove
/*$Training->addConfigValue( new ConfigValueBool('TRAINING_MAP_BEFORE_PLOTS', array(
	'default'		=> false,
	'label'			=> __('Map: before plots'),
	'onchange'		=> Ajax::$RELOAD_TRAINING
)));*/
$Training->addConfigValue(new ConfigValueString('TRAINING_LEAFLET_LAYER', array('default' => 'OpenStreetMap')));
/*$Training->addConfigValue(new ConfigValueBool('TRAINING_MAP_MARKER', array('default' => true)));
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
$Training->addConfigValue(new ConfigValueBool('FORMULAR_SHOW_GPS', array('default' => false)));*/
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
	'label'			=> __('Publish activities'),
	'tooltip'		=> __('Automatically mark every activity after its creation as public.')
)));
$Privacy->addConfigValue( new ConfigValueBool('TRAINING_LIST_PUBLIC', array(
	'default'		=> false,
	'label'			=> __('Public list: active'),
	'tooltip'		=> __('If activated: Everyone can see a list of all your (public) activities.'),
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$Privacy->addConfigValue( new ConfigValueBool('TRAINING_LIST_ALL', array(
	'default'		=> false,
	'label'			=> __('Public list: private workouts'),
	'tooltip'		=> __('If activated: Display a summary for each private activity in the public activity list.')
)));
$Privacy->addConfigValue( new ConfigValueBool('TRAINING_LIST_STATISTICS', array(
	'default'		=> false,
	'label'			=> __('Public list: general statistics'),
	'tooltip'		=> __('Show some general statistics above the activity list')
)));
$Privacy->addConfigValue( new ConfigValueSelect('TRAINING_MAP_PUBLIC_MODE', array(
	'default'		=> 'always',
	'label'			=> __('Public view: show map'),
	'tooltip'		=> __('You can hide the map for the public view'),
	'options'		=> array(
		'never'			=> __('never'),
		'race'			=> __('only for competitions'),
		'race-longjog'	=> __('only for competitions and long runs'),
		'always'		=> __('always')
	),
)));
$Privacy->addToCategoryList();





$Design = new ConfigCategory('design', __('Design'));
$Design->setKeys(array(
	'DB_DISPLAY_MODE',
	//'DB_HIGHLIGHT_TODAY',
	'DESIGN_BG_FILE',
	'DB_SHOW_DIRECT_EDIT_LINK',
	//'DESIGN_BG_FIX_AND_STRETCH',
	'DB_SHOW_CREATELINK_FOR_DAYS',
	''
));
// TODO: remove
/*$Design->addConfigValue( new ConfigValueBool('DB_HIGHLIGHT_TODAY', array(
	'default'		=> true,
	'label'			=> __('Calendar: highlight today'),
	'tooltip'		=> __('in the calendar'),
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));*/
$Design->addConfigValue( new ConfigValueSelect('DB_DISPLAY_MODE', array(
	'default'		=> 'week',
	'label'			=> __('Calendar: mode'),
	'options'		=> array(
		'week'			=> __('Week view'),
		'month'			=> __('Month view')
	),
	'tooltip'		=> __('Default mode for the calendar'),
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$Design->addConfigValue( new ConfigValueBool('DB_SHOW_CREATELINK_FOR_DAYS', array(
	'default'		=> true,
	'label'			=> __('Calendar: create button'),
	'tooltip'		=> __('Add a link for every day to create a new activity.'),
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
$Design->addConfigValue( new ConfigValueBool('DB_SHOW_DIRECT_EDIT_LINK', array(
	'default'		=> true,
	'label'			=> __('Calendar: edit button'),
	'tooltip'		=> __('Add an edit-link for every activity.'),
	'onchange'		=> Ajax::$RELOAD_DATABROWSER
)));
/*$Design->addConfigValue( new ConfigValueBool('DESIGN_BG_FIX_AND_STRETCH', array(
	'default'		=> true,
	'label'			=> __('Background image: scale'),
	'onchange'		=> Ajax::$RELOAD_PAGE
)));*/
$Design->addConfigValue( new ConfigValueSelectFile('DESIGN_BG_FILE', array(
	'default'		=> 'img/backgrounds/Default.jpg',
	'label'			=> __('Background image: file'),
	'folder'		=> 'img/backgrounds/',
	'onchange'		=> Ajax::$RELOAD_PAGE
)));
$Design->addToCategoryList();





$Calculations = new ConfigCategory('calculations', __('Experimental calculations'));
$Calculations->setKeys(array(
	'',//'RECHENSPIELE',
	'ATL_DAYS',
	'VDOT_HF_METHOD',
	'CTL_DAYS',
	'JD_USE_VDOT_CORRECTOR',
	'VDOT_DAYS',
	'VDOT_MANUAL_CORRECTOR',
	'',
	'VDOT_MANUAL_VALUE',
	'',
	'',
	'JD_USE_VDOT_CORRECTION_FOR_ELEVATION',
	'VDOT_CORRECTION_POSITIVE_ELEVATION',
	'VDOT_CORRECTION_NEGATIVE_ELEVATION'
));
/*$Calculations->addConfigValue( new ConfigValueBool('RECHENSPIELE', array(
	'default'		=> true,
	'label'			=> __('Use exp. calculations'),
	'tooltip'		=> __('Use TRIMP, ATL, CTL, TSB, VDOT, ...'),
	'onchange'		=> Ajax::$RELOAD_PLUGINS
)));*/
$Calculations->addConfigValue( new ConfigValueSelect('VDOT_HF_METHOD', array(
	'default'		=> 'logarithmic',
	'label'			=> __('VDOT: formula'),
	'options'		=> array(
		'logarithmic'	=> __('logarithmic (new method since v1.5)'),
		'linear'		=> __('linear (old method up to v1.4)')
	),
	'tooltip'		=> __('Formula to estimate the vdot value. The old method is only listed for compatibility reasons.'),
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("The tool <em>Datenbank-Cleanup</em> can be used to recalculate vdot values."));'
)));
$Calculations->addConfigValue( new ConfigValueBool('JD_USE_VDOT_CORRECTOR', array(
	'default'		=> true,
	'label'			=> __('VDOT: correction'),
	'tooltip'		=> __('Use a correction factor based on your best competition. (recommended)'),
	'onchange'		=> Ajax::$RELOAD_ALL,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("The tool <em>Datenbank-Cleanup</em> can be used to recalculate vdot values."));'
)));
$Calculations->addConfigValue( new ConfigValueInt('ATL_DAYS', array(
	'default'		=> 7,
	'label'			=> __('Days for ATL'),
	'tooltip'		=> __('Number of days to recognize for ATL'),
	'onchange'		=> Ajax::$RELOAD_PLUGINS,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("The tool <em>Datenbank-Cleanup</em> can be used to recalculate vdot values."));'
)));
$Calculations->addConfigValue( new ConfigValueInt('CTL_DAYS', array(
	'default'		=> 42,
	'label'			=> __('Days for CTL'),
	'tooltip'		=> __('Number of days to recognize for CTL'),
	'onchange'		=> Ajax::$RELOAD_PLUGINS,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("The tool <em>Datenbank-Cleanup</em> can be used to recalculate ctl values."));'
)));
$Calculations->addConfigValue( new ConfigValueInt('VDOT_DAYS', array(
	'default'		=> 30,
	'label'			=> __('Days for VDOT'),
	'tooltip'		=> __('Number of days to recognize for VDOT'),
	'onchange'		=> Ajax::$RELOAD_PLUGINS,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("The tool <em>Datenbank-Cleanup</em> can be used to recalculate vdot values."));'
)));
$Calculations->addConfigValue( new ConfigValueString('VDOT_MANUAL_CORRECTOR', array(
	'default'		=> '',
	'label'			=> __('VDOT: manual correction'),
	'tooltip'		=> __('Manual correction factor (e.g. 0.9), if the automatic factor does not fit. Can be left empty.'),
	'onchange'		=> Ajax::$RELOAD_PLUGINS,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("The tool <em>Datenbank-Cleanup</em> can be used to recalculate vdot values."));'
)));
$Calculations->addConfigValue( new ConfigValueString('VDOT_MANUAL_VALUE', array(
	'default'		=> '',
	'label'			=> 'VDOT: fixed value',
	'tooltip'		=> __('Fixed vdot value (e.g. 55), if the estimation does not fit. Can be left empty.'),
	'onchange'		=> Ajax::$RELOAD_PLUGINS
)));
$Calculations->addConfigValue( new ConfigValueBool('JD_USE_VDOT_CORRECTION_FOR_ELEVATION', array(
	'default'		=> false,
	'label'			=> __('VDOT: adapt for elevation'),
	'tooltip'		=> __('The distance can be corrected by a formula from Peter Greif to adapt for elevation.'),
	'layout'		=> FormularFieldset::$LAYOUT_FIELD_W100,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("The tool <em>Datenbank-Cleanup</em> can be used to recalculate vdot values."));'
)));
$Calculations->addConfigValue( new ConfigValueString('VDOT_CORRECTION_POSITIVE_ELEVATION', array(
	'default'		=> '2',
	'label'			=> __('VDOT: correction per positive elevation'),
	'tooltip'		=> __('Add for each meter upwards X meter to the distance. (Only for the vdot calculation)'),
	'unit'			=> FormularUnit::$M,
	'layout'		=> FormularFieldset::$LAYOUT_FIELD_W100,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("The tool <em>Datenbank-Cleanup</em> can be used to recalculate vdot values."));'
)));
$Calculations->addConfigValue( new ConfigValueString('VDOT_CORRECTION_NEGATIVE_ELEVATION', array(
	'default'		=> '-1',
	'label'			=> __('VDOT: correction per negative elevation'),
	'tooltip'		=> __('Add for each meter downwards X meter to the distance. (Only for the vdot calculation)'),
	'unit'			=> FormularUnit::$M,
	'layout'		=> FormularFieldset::$LAYOUT_FIELD_W100,
	'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("The tool <em>Datenbank-Cleanup</em> can be used to recalculate vdot values."));'
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





$TrainingForm = new ConfigCategory('trainingform', __('Form to create new activities'));
$TrainingForm->setKeys(array(
	'TRAINING_CREATE_MODE',
	'TRAINING_SHOW_AFTER_CREATE',
	'TRAINING_DO_ELEVATION',
	'TRAINING_ELEVATION_SERVER',
	'TRAINING_LOAD_WEATHER',
	'PLZ',
	'COMPUTE_KCAL',
	'TRAINING_SORT_SPORTS',
	'COMPUTE_POWER',
	'TRAINING_SORT_TYPES',
	'',
	'TRAINING_SORT_SHOES'
));
$TrainingForm->addConfigValue( new ConfigValueSelect('TRAINING_ELEVATION_SERVER', array(
	'default'		=> 'geonames',
	'label'			=> __('Elevation correction via'),
	'options'		=> array(
		'google'		=> 'maps.googleapis.com',
		'geonames'		=> 'ws.geonames.org'
	),
	'tooltip'		=> __('Elevation data can be retrieved via different services')
)));
$TrainingForm->addConfigValue( new ConfigValueBool('TRAINING_SHOW_AFTER_CREATE', array(
	'default'		=> false,
	'label'			=> __('Show activity after creation')
)));
$TrainingForm->addConfigValue( new ConfigValueSelect('TRAINING_CREATE_MODE', array(
	'default'		=> 'garmin',
	'label'			=> __('Default mode'),
	'options'		=> array(
		'upload'		=> __('Upload form'),
		'garmin'		=> __('Garmin-Communicator'),
		'form'			=> __('Standard form')
	)
)));
$TrainingForm->addConfigValue( new ConfigValueBool('COMPUTE_KCAL', array(
	'default'		=> true,
	'label'			=> __('Calculate calories'),
	'tooltip'		=> __('Recalculate calories after changing duration by hand')
)));
$TrainingForm->addConfigValue( new ConfigValueBool('COMPUTE_POWER', array(
	'default'		=> true,
	'label'			=> __('Calculate power'),
	'tooltip'		=> __('Calculate power by speed and grade for cycling')
)));
$TrainingForm->addConfigValue( new ConfigValueBool('TRAINING_DO_ELEVATION', array(
	'default'		=> true,
	'label'			=> __('Correct elevation data'),
	'tooltip'		=> __('Instead of using gps-elevation a correction via external services is possible.')
)));
$TrainingForm->addConfigValue( new ConfigValueString('PLZ', array(
	'default'		=> '',
	'label'			=> __('For weather: city'),
	'tooltip'		=> __('For loading weather data from openweathermap.org<br>e.g. <em>Berlin, de</em>'),
	'size'			=> FormularInput::$SIZE_MIDDLE
)));
$TrainingForm->addConfigValue( new ConfigValueBool('TRAINING_LOAD_WEATHER', array(
	'default'		=> true,
	'label'			=> __('Load weather'),
	'tooltip'		=> __('Load current weather conditions via openweathermap.org')
)));
$TrainingForm->addConfigValue( new ConfigValueSelect('TRAINING_SORT_SPORTS', array(
	'default'		=> 'id-asc',
	'label'			=> __('Sort: sport types'),
	'options'		=> array( // see SportFactory::getOrder()
		'id-asc'		=> __('id (oldest first)'),
		'id-desc'		=> __('id (newest first)'),
		'alpha'			=> __('alphabetical')
	)
)));
$TrainingForm->addConfigValue( new ConfigValueSelect('TRAINING_SORT_TYPES', array(
	'default'		=> 'id-asc',
	'label'			=> __('Sort: training types'),
	'options'		=> array( // see TypeFactory::getOrder()
		'id-asc'		=> __('id (oldest first)'),
		'id-desc'		=> __('id (newest first)'),
		'alpha'			=> __('alphabetical')
	)
)));
$TrainingForm->addConfigValue( new ConfigValueSelect('TRAINING_SORT_SHOES', array(
	'default'		=> 'id-asc',
	'label'			=> __('Sort: shoes'),
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