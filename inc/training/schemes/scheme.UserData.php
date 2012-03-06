<?php
$TABLENAME = 'user';

$HIDDEN_KEYS = array();

$FIELDSETS = array(
	array(
		'id'		=> 'general',
		'legend'	=> 'Allgemein',
		'fields'	=> array('time', 'weight')
	),
	array(
		'id'		=> 'analyse',
		'legend'	=> 'Analysewerte',
		'fields'	=> array('fat', 'water', 'muscles')
	),
	array(
		'id'		=> 'pulse',
		'legend'	=> 'Puls',
		'fields'	=> array('pulse_rest', 'pulse_max')
	)
);

// Field for id is set always
// Default setting: precision='', null=false, key=false, extra='', default=''
$FIELDS = array();
$FIELDS['time'] = array(
	'database'	=> array(
		'type'		=> 'int',
		'precision'	=> '11',
		),
	'formular'	=> array(
		'label'		=> 'Datum',
		'parser'	=> FormularValueParser::$PARSER_DATE
	)
);
$FIELDS['weight'] = array(
	'database'	=> array(
		'type'		=> 'decimal',
		'precision'	=> '4,1',
		),
	'formular'	=> array(
		'label'		=> 'Gewicht',
		'unit'		=> FormularInput::$UNIT_KG,
		'required'	=> true
	)
);
$FIELDS['pulse_rest'] = array(
	'database'	=> array(
		'type'		=> 'smallint',
		'precision'	=> '3',
		),
	'formular'	=> array(
		'label'		=> 'Ruhepuls',
		'unit'		=> FormularInput::$UNIT_BPM
	)
);
$FIELDS['pulse_max'] = array(
	'database'	=> array(
		'type'		=> 'smallint',
		'precision'	=> '3',
		),
	'formular'	=> array(
		'label'		=> 'Maximalpuls',
		'unit'		=> FormularInput::$UNIT_BPM
	)
);
$FIELDS['fat'] = array(
	'database'	=> array(
		'type'		=> 'decimal',
		'precision'	=> '3,1',
		),
	'formular'	=> array(
		'label'		=> 'Fettanteil',
		'unit'		=> FormularInput::$UNIT_PERCENT
	)
);
$FIELDS['water'] = array(
	'database'	=> array(
		'type'		=> 'decimal',
		'precision'	=> '3,1',
		),
	'formular'	=> array(
		'label'		=> 'Wasseranteil',
		'unit'		=> FormularInput::$UNIT_PERCENT
	)
);
$FIELDS['muscles'] = array(
	'database'	=> array(
		'type'		=> 'decimal',
		'precision'	=> '3,1',
		),
	'formular'	=> array(
		'label'		=> 'Muskelanteil',
		'unit'		=> FormularInput::$UNIT_PERCENT
	)
);
?>