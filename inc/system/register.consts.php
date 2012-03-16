<?php
/*
 * All registrations of CONST variables have to be done here
 * otherwise the Autoloader will not work correct
 */
Config::register('Allgemein', 'GENDER', 'select', array('m' => true, 'f' => false), 'Geschlecht', array('m&auml;nnlich', 'weiblich'));
Config::register('Allgemein', 'PULS_MODE', 'select', array('bpm' => false, 'hfmax' => true), 'Pulsanzeige', array('absoluter Wert', '&#37; HFmax'));
Config::register('Allgemein', 'USE_PULS', 'bool', true, 'Pulsdaten speichern');
Config::register('Allgemein', 'USE_WETTER', 'bool', true, 'Wetter speichern');
Config::register('Allgemein', 'PLZ', 'int', 0, 'f&uuml;r Wetter-Daten: PLZ');

Config::register('Rechenspiele', 'RECHENSPIELE', 'bool', true, 'Rechenspiele aktivieren');
Config::register('Rechenspiele', 'JD_USE_VDOT_CORRECTOR', 'bool', true, 'Individuelle VDOT-Korrektur verwenden');

Config::register('Design', 'DB_HIGHLIGHT_TODAY', 'bool', '1', 'Heutigen Tag im Kalender hervorheben');
Config::register('Design', 'DB_SHOW_CREATELINK_FOR_DAYS', 'bool', '1', 'Training-Hinzuf&uuml;gen-Link f&uuml;r jeden Tag anzeigen');
Config::register('Design', 'DB_SHOW_DIRECT_EDIT_LINK', 'bool', '1', 'Training-Bearbeiten-Link im Kalender anzeigen');
Config::register('Design', 'JS_USE_TOOLTIP', 'bool', true, 'Tooltip f&uuml;r Icons');
Config::register('Design', 'DESIGN_BG_FILE', 'selectfile', 'img/backgrounds/Default.jpg', 'Hintergrundbild (Neuladen notwendig, eigene Bilder in img/backgrounds/)', array('img/backgrounds/'));
Config::register('Design', 'DESIGN_BG_FIX_AND_STRETCH', 'bool', true, 'Hintergrundbild skalieren (Neuladen notwendig)');
Config::register('Design', 'DESIGN_TOOLBAR_POSITION', 'select', array('top' => true, 'bottom' => false), 'Position der Toolbar', array('oben', 'unten'));

Config::register('Training', 'MAINSPORT', 'selectdb', 1, 'Haupt-Sportart', array('sport', 'name'));
Config::register('Training', 'RUNNINGSPORT', 'selectdb', 1, 'Lauf-Sportart', array('sport', 'name'));
Config::register('Training', 'WK_TYPID', 'selectdb', 5, 'Trainingstyp: Wettkampf', array('type', 'name'));
Config::register('Training', 'LL_TYPID', 'selectdb', 7, 'Trainingstyp: Langer Lauf', array('type', 'name'));
Config::register('Training', 'TRAINING_PLOTS_BELOW', 'bool', false, 'Diagramme untereinander anstatt im Wechsel anzeigen');
Config::register('Training', 'TRAINING_DECIMALS', 'select',
	array('0' => false, '1' => true, '2' => false), 'Anzahl angezeigter Nachkommastellen',
	array('0', '1', '2'));
Config::register('Training', 'TRAINING_MAP_COLOR', 'string', '#FF5500', 'Linienfarbe auf GoogleMaps-Karte (#RGB)');
Config::register('Training', 'TRAINING_MAP_MARKER', 'bool', true, 'Kilometer-Markierungen anzeigen');
Config::register('Training', 'TRAINING_MAPTYPE', 'select',
	array('G_NORMAL_MAP' => false, 'G_HYBRID_MAP' => true, 'G_SATELLITE_MAP' => false, 'G_PHYSICAL_MAP' => false), 'Typ der GoogleMaps-Karte',
	array('Normal', 'Hybrid', 'Satellit', 'Physikalisch'));

Config::register('Eingabeformular', 'TRAINING_ELEVATION_SERVER', 'select',
	array('google' => true, 'geonames' => false), 'Server f&uuml;r H&ouml;henkorrektur',
	array('maps.googleapis.com', 'ws.geonames.org'));
Config::register('Eingabeformular', 'COMPUTE_KCAL', 'bool', true, 'Kalorienverbrauch automatisch berechnen');
Config::register('Eingabeformular', 'TRAINING_CREATE_MODE', 'select',
	array('upload' => false, 'garmin' => true, 'form' => false), 'Standard-Eingabemodus',
	array('Datei hochladen', 'GarminCommunicator', 'Standard-Formular'));
Config::register('Eingabeformular', 'TRAINING_DO_ELEVATION', 'bool', true, 'H&ouml;henkorrektur verwenden');
Config::register('Eingabeformular', 'GARMIN_API_KEY', 'string', '', 'GarminCommunicator API-Key');

Config::register('Suchfenster', 'RESULTS_AT_PAGE', 'int', 15, 'Ergebnisse pro Seite');
