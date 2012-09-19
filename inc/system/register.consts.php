<?php
/*
 * All registrations of CONST variables have to be done here
 * otherwise the Autoloader will not work correct
 */
Config::addFieldset('Allgemein', array(
	'GENDER',
	'',
	'PULS_MODE',
	'USE_PULS',
	'PLZ',
	'USE_WETTER'
));

Config::register('Allgemein', 'GENDER', 'select', array('m' => true, 'f' => false), 'Geschlecht', array('m&auml;nnlich', 'weiblich'));
Config::register('Allgemein', 'PULS_MODE', 'select', array('bpm' => false, 'hfmax' => true), 'Pulsanzeige', array('absoluter Wert', '&#37; HFmax'));
Config::register('Allgemein', 'USE_PULS', 'bool', true, 'Pulsdaten speichern');
Config::register('Allgemein', 'USE_WETTER', 'bool', true, 'Wetter speichern');
Config::register('Allgemein', 'PLZ', 'string', '', 'f&uuml;r Wetter-Daten: PLZ');


Config::addFieldset('Training', array(
	'MAINSPORT',
	'WK_TYPID',
	'RUNNINGSPORT',
	'LL_TYPID',
	//'TRAINING_PLOTS_BELOW',
	'TRAINING_MAPTYPE',
	'TRAINING_DECIMALS',
	'TRAINING_MAP_COLOR',
	'TRAINING_MAKE_PUBLIC'//,
	//'TRAINING_MAP_MARKER'
));

Config::register('Training', 'MAINSPORT', 'selectdb', 1, 'Haupt-Sportart', array('sport', 'name'));
Config::register('Training', 'RUNNINGSPORT', 'selectdb', 1, 'Lauf-Sportart', array('sport', 'name'));
Config::register('Training', 'WK_TYPID', 'selectdb', 5, 'Trainingstyp: Wettkampf', array('type', 'name'));
Config::register('Training', 'LL_TYPID', 'selectdb', 7, 'Trainingstyp: Langer Lauf', array('type', 'name'));
// TODO: remove
Config::register('Training', 'TRAINING_PLOTS_BELOW', 'bool', false, 'Diagramme untereinander');
Config::register('Training', 'TRAINING_DECIMALS', 'select',
	array('0' => false, '1' => true, '2' => false), 'Anzahl Nachkommastellen',
	array('0', '1', '2'));
Config::register('Training', 'TRAINING_MAP_COLOR', 'string', '#FF5500', 'Karte: Linienfarbe');
Config::register('Training', 'TRAINING_MAP_MARKER', 'bool', true, 'Karte: Markierungen');
Config::register('Training', 'TRAINING_MAPTYPE', 'select',
	array('G_NORMAL_MAP' => false, 'G_HYBRID_MAP' => true, 'G_SATELLITE_MAP' => false, 'G_PHYSICAL_MAP' => false), 'Karte: Typ',
	array('Normal', 'Hybrid', 'Satellit', 'Physikalisch'));
Config::register('Training', 'TRAINING_MAKE_PUBLIC', 'bool', false, Ajax::tooltip('Trainings ver&ouml;ffentlichen', '&Ouml;ffentliche Trainings k&ouml;nnen von jedem betrachtet werden. Diese Standardeinstellung kann f&uuml;r jedes einzelne Training ver&auml;ndert werden.', true));
// Hidden ones
Config::register('Training', 'TRAINING_SHOW_ZONES', 'bool', true, 'Anzeige: Zonen');
Config::register('Training', 'TRAINING_SHOW_ROUNDS', 'bool', true, 'Anzeige: Runden');
Config::register('Training', 'TRAINING_SHOW_GRAPHICS', 'bool', true, 'Anzeige: Grafiken');
Config::register('Training', 'TRAINING_SHOW_PLOT_PACE', 'bool', true, 'Grafik: Geschwindigkeit');
Config::register('Training', 'TRAINING_SHOW_PLOT_PULSE', 'bool', true, 'Grafik: Herzfrequenz');
Config::register('Training', 'TRAINING_SHOW_PLOT_ELEVATION', 'bool', true, 'Grafik: H&ouml;henprofil');
Config::register('Training', 'TRAINING_SHOW_PLOT_SPLITS', 'bool', true, 'Grafik: Splits');
Config::register('Training', 'TRAINING_SHOW_MAP', 'bool', true, 'Grafik: Karte');


Config::addFieldset('Design', array(
	'DB_HIGHLIGHT_TODAY',
	'DESIGN_TOOLBAR_POSITION',
	'DESIGN_BG_FIX_AND_STRETCH',
	'DESIGN_BG_FILE',
	//'JS_USE_TOOLTIP',
	'DB_SHOW_DIRECT_EDIT_LINK',
	'DB_SHOW_CREATELINK_FOR_DAYS'
));

Config::register('Design', 'DB_HIGHLIGHT_TODAY', 'bool', '1', Ajax::tooltip('Heute hervorheben', 'im Kalender', true));
Config::register('Design', 'DB_SHOW_CREATELINK_FOR_DAYS', 'bool', '1', 'Training-Hinzuf&uuml;gen-Link f&uuml;r jeden Tag anzeigen');
Config::register('Design', 'DB_SHOW_DIRECT_EDIT_LINK', 'bool', '1', 'Training-Bearbeiten-Link im Kalender anzeigen');
// TODO: remove
Config::register('Design', 'JS_USE_TOOLTIP', 'bool', true, 'Tooltip f&uuml;r Icons');
Config::register('Design', 'DESIGN_BG_FILE', 'selectfile', 'img/backgrounds/Default.jpg', Ajax::tooltip('Hintergrundbild', 'Neuladen notwendig, eigene Bilder in img/backgrounds/', true), array('img/backgrounds/'));
Config::register('Design', 'DESIGN_BG_FIX_AND_STRETCH', 'bool', true, Ajax::tooltip('Hintergrundbild skalieren', 'Neuladen notwendig', true));
Config::register('Design', 'DESIGN_TOOLBAR_POSITION', 'select', array('top' => true, 'bottom' => false), 'Position der Toolbar', array('oben', 'unten'));


Config::addFieldset('Rechenspiele', array(
	'RECHENSPIELE',
	'ATL_DAYS',
	'',
	'CTL_DAYS',
	'JD_USE_VDOT_CORRECTOR',
	'VDOT_DAYS'
));

Config::register('Rechenspiele', 'RECHENSPIELE', 'bool', true, 'Rechenspiele aktivieren');
Config::register('Rechenspiele', 'JD_USE_VDOT_CORRECTOR', 'bool', true, ' VDOT-Korrektur');
Config::register('Rechenspiele', 'ATL_DAYS', 'int', 7, 'Tage f&uuml;r ATL');
Config::register('Rechenspiele', 'CTL_DAYS', 'int', 42, 'Tage f&uuml;r CTL');
Config::register('Rechenspiele', 'VDOT_DAYS', 'int', 30, 'Tage f&uuml;r VDOT');

// Be careful: These values shouldn't be taken with CONF_MAX_ATL, use class::Trimp
Config::register('hidden', 'MAX_ATL', 'int', 0, 'Maximal value for ATL');
Config::register('hidden', 'MAX_CTL', 'int', 0, 'Maximal value for CTL');
Config::register('hidden', 'MAX_TRIMP', 'int', 0, 'Maximal value for TRIMP');


Config::addFieldset('Eingabeformular', array(
	'TRAINING_CREATE_MODE',
	'COMPUTE_KCAL',
	'TRAINING_ELEVATION_SERVER',
	'TRAINING_DO_ELEVATION',
	'GARMIN_API_KEY'
));

Config::register('Eingabeformular', 'TRAINING_ELEVATION_SERVER', 'select',
	array('google' => true, 'geonames' => false), 'H&ouml;henkorrektur &uuml;ber',
	array('maps.googleapis.com', 'ws.geonames.org'));
Config::register('Eingabeformular', 'COMPUTE_KCAL', 'bool', true, 'Kalorien berechnen');
Config::register('Eingabeformular', 'TRAINING_CREATE_MODE', 'select',
	array('upload' => false, 'garmin' => true, 'form' => false), 'Standard-Eingabemodus',
	array('Datei hochladen', 'GarminCommunicator', 'Standard-Formular'));
Config::register('Eingabeformular', 'TRAINING_DO_ELEVATION', 'bool', true, 'H&ouml;henkorrektur verwenden');
Config::register('Eingabeformular', 'GARMIN_API_KEY', 'string', '', Ajax::tooltip('Garmin API-Key', 'für http://'.$_SERVER['HTTP_HOST'], true));


Config::addFieldset('Suchfenster', array(
	'RESULTS_AT_PAGE'
));

Config::register('Suchfenster', 'RESULTS_AT_PAGE', 'int', 15, 'Ergebnisse pro Seite');