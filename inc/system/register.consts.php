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

Config::register('GENDER', 'select', array('m' => true, 'f' => false), 'Geschlecht', array('m&auml;nnlich', 'weiblich'));
Config::register('PULS_MODE', 'select', array('bpm' => false, 'hfmax' => true), 'Pulsanzeige', array('absoluter Wert', '&#37; HFmax'));
Config::register('USE_PULS', 'bool', true, 'Pulsdaten speichern');
Config::register('USE_WETTER', 'bool', true, 'Wetter speichern');
Config::register('PLZ', 'string', '', 'f&uuml;r Wetter-Daten: PLZ');


Config::addFieldset('Training', array(
	'MAINSPORT',
	'WK_TYPID',
	'RUNNINGSPORT',
	'LL_TYPID',
	//'TRAINING_PLOTS_BELOW',
	'TRAINING_MAPTYPE',
	'TRAINING_DECIMALS',
	'TRAINING_MAP_COLOR',
	'TRAINING_MAP_BEFORE_PLOTS',
	//'TRAINING_MAP_MARKER'
));

Config::register('MAINSPORT', 'selectdb', 1, 'Haupt-Sportart', array('sport', 'name'));
Config::register('RUNNINGSPORT', 'selectdb', 1, 'Lauf-Sportart', array('sport', 'name'));
Config::register('WK_TYPID', 'selectdb', 5, 'Trainingstyp: Wettkampf', array('type', 'name'));
Config::register('LL_TYPID', 'selectdb', 7, 'Trainingstyp: Langer Lauf', array('type', 'name'));
// TODO: remove
Config::register('TRAINING_PLOTS_BELOW', 'bool', false, 'Diagramme untereinander');
Config::register('TRAINING_DECIMALS', 'select',
	array('0' => false, '1' => true, '2' => false), 'Anzahl Nachkommastellen',
	array('0', '1', '2'));
Config::register('TRAINING_MAP_COLOR', 'string', '#FF5500', 'Karte: Linienfarbe');
Config::register('TRAINING_MAP_MARKER', 'bool', true, 'Karte: Markierungen');
Config::register('TRAINING_MAPTYPE', 'select',
	array('G_NORMAL_MAP' => false, 'G_HYBRID_MAP' => true, 'G_SATELLITE_MAP' => false, 'G_PHYSICAL_MAP' => false, 'OSM' => false), 'Karte: Typ',
	array('Normal', 'Hybrid', 'Satellit', 'Physikalisch', 'OpenStreetMap'));
Config::register('TRAINING_MAP_BEFORE_PLOTS', 'bool', false, 'Karte: vor Diagrammen');
// Hidden ones
Config::register('TRAINING_SHOW_ZONES', 'bool', true, 'Anzeige: Zonen');
Config::register('TRAINING_SHOW_ROUNDS', 'bool', true, 'Anzeige: Runden');
Config::register('TRAINING_SHOW_GRAPHICS', 'bool', true, 'Anzeige: Grafiken');
Config::register('TRAINING_SHOW_PLOT_PACE', 'bool', true, 'Grafik: Geschwindigkeit');
Config::register('TRAINING_SHOW_PLOT_PULSE', 'bool', true, 'Grafik: Herzfrequenz');
Config::register('TRAINING_SHOW_PLOT_ELEVATION', 'bool', true, 'Grafik: H&ouml;henprofil');
Config::register('TRAINING_SHOW_PLOT_SPLITS', 'bool', true, 'Grafik: Splits');
Config::register('TRAINING_SHOW_MAP', 'bool', true, 'Grafik: Karte');


Config::addFieldset('Privatsph&auml;re', array(
	'TRAINING_MAKE_PUBLIC',
	'TRAINING_LIST_PUBLIC',
	'TRAINING_LIST_ALL'
));

Config::register('TRAINING_MAKE_PUBLIC', 'bool', false, Ajax::tooltip('Trainings ver&ouml;ffentlichen', '&Ouml;ffentliche Trainings k&ouml;nnen von jedem betrachtet werden. Diese Standardeinstellung kann f&uuml;r jedes einzelne Training ver&auml;ndert werden.', false));
Config::register('TRAINING_LIST_PUBLIC', 'bool', false, Ajax::tooltip('Trainingsliste &ouml;ffentlich', 'Andere Nutzer k&ouml;nnen bei dieser Einstellung eine Liste mit all deinen (&ouml;ffentlichen) Trainings sehen.', true));
Config::register('TRAINING_LIST_ALL', 'bool', false, Ajax::tooltip('Liste: private Trainings', 'Bei dieser Einstellung werden in der &ouml;ffentlichen Liste auch private Trainings (ohne Link) angezeigt.', false));



Config::addFieldset('Design', array(
	'DB_HIGHLIGHT_TODAY',
	'', //'DESIGN_TOOLBAR_POSITION',
	'DESIGN_BG_FIX_AND_STRETCH',
	'DESIGN_BG_FILE',
	//'JS_USE_TOOLTIP',
	'DB_SHOW_DIRECT_EDIT_LINK',
	'DB_SHOW_CREATELINK_FOR_DAYS',
	'PLUGIN_SHOW_CONFIG_LINK',
	'PLUGIN_SHOW_MOVE_LINK'
));

Config::register('DB_HIGHLIGHT_TODAY', 'bool', '1', Ajax::tooltip('Heute hervorheben', 'im Kalender', true));
Config::register('DB_SHOW_CREATELINK_FOR_DAYS', 'bool', '1', 'Training-Hinzuf&uuml;gen-Link f&uuml;r jeden Tag anzeigen');
Config::register('DB_SHOW_DIRECT_EDIT_LINK', 'bool', '1', 'Training-Bearbeiten-Link im Kalender anzeigen');
// TODO: remove
Config::register('JS_USE_TOOLTIP', 'bool', true, 'Tooltip f&uuml;r Icons');
Config::register('DESIGN_BG_FILE', 'selectfile', 'img/backgrounds/Default.jpg', Ajax::tooltip('Hintergrundbild', 'Neuladen notwendig, eigene Bilder in img/backgrounds/', true), array('img/backgrounds/'));
Config::register('DESIGN_BG_FIX_AND_STRETCH', 'bool', true, Ajax::tooltip('Hintergrundbild skalieren', 'Neuladen notwendig', true));
Config::register('DESIGN_TOOLBAR_POSITION', 'select', array('top' => true, 'bottom' => false), 'Position der Toolbar', array('oben', 'unten'));
Config::register('PLUGIN_SHOW_CONFIG_LINK', 'bool', false, Ajax::tooltip('Plugin: Konfiguration-Link anzeigen', 'Wenn aktiv wird bei jedem Plugin vor dem Namen ein Link zur Plugin-Konfiguration angezeigt', true));
Config::register('PLUGIN_SHOW_MOVE_LINK', 'bool', false, Ajax::tooltip('Plugin: Verschieben-Link anzeigen', 'Mit diesem Link lassen sich die Panel direkt verschieben.', true));


Config::addFieldset('Rechenspiele', array(
	'RECHENSPIELE',
	'ATL_DAYS',
	'',
	'CTL_DAYS',
	'JD_USE_VDOT_CORRECTOR',
	'VDOT_DAYS'
));

Config::register('RECHENSPIELE', 'bool', true, 'Rechenspiele aktivieren');
Config::register('JD_USE_VDOT_CORRECTOR', 'bool', true, ' VDOT-Korrektur');
Config::register('ATL_DAYS', 'int', 7, 'Tage f&uuml;r ATL');
Config::register('CTL_DAYS', 'int', 42, 'Tage f&uuml;r CTL');
Config::register('VDOT_DAYS', 'int', 30, 'Tage f&uuml;r VDOT');

// Be careful: These values shouldn't be taken with CONF_MAX_ATL, use class::Trimp
Config::register('MAX_ATL', 'int', 0, 'Maximal value for ATL');
Config::register('MAX_CTL', 'int', 0, 'Maximal value for CTL');
Config::register('MAX_TRIMP', 'int', 0, 'Maximal value for TRIMP');


Config::addFieldset('Eingabeformular', array(
	'TRAINING_CREATE_MODE',
	'COMPUTE_KCAL',
	'TRAINING_ELEVATION_SERVER',
	'TRAINING_DO_ELEVATION',
	'GARMIN_API_KEY'
));

Config::register('TRAINING_ELEVATION_SERVER', 'select',
	array('google' => true, 'geonames' => false), 'H&ouml;henkorrektur &uuml;ber',
	array('maps.googleapis.com', 'ws.geonames.org'));
Config::register('COMPUTE_KCAL', 'bool', true, 'Kalorien berechnen');
Config::register('TRAINING_CREATE_MODE', 'select',
	array('upload' => false, 'garmin' => true, 'form' => false), 'Standard-Eingabemodus',
	array('Datei hochladen', 'GarminCommunicator', 'Standard-Formular'));
Config::register('TRAINING_DO_ELEVATION', 'bool', true, 'H&ouml;henkorrektur verwenden');
Config::register('GARMIN_API_KEY', 'string', '', Ajax::tooltip('Garmin API-Key', 'für http://'.$_SERVER['HTTP_HOST'], true));


Config::addFieldset('Suchfenster', array(
	'RESULTS_AT_PAGE'
));

Config::register('RESULTS_AT_PAGE', 'int', 15, 'Ergebnisse pro Seite');