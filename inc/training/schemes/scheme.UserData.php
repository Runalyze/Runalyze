<?php
$TABLENAME = 'user';

$HIDDEN_KEYS = array();

$FIELDSETS = array(
	array(
		'id'		=> 'general',
		'legend'	=> __('General Information'),
		'fields'	=> array('time', 'weight')
	),
	array(
		'id'		=> 'analyse',
		'legend'	=> __('Analysis'),
		'fields'	=> array('fat', 'water', 'muscles')
	),
	array(
		'id'		=> 'pulse',
		'legend'	=> __('Heartrate'),
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
		'label'		=> __('Date'),
		'required'	=> true,
		'class'		=> 'FormularInputDate',
		'parser'	=> FormularValueParser::$PARSER_DATE
	)
);
$FIELDS['weight'] = array(
	'database'	=> array(
		'type'		=> 'decimal',
		'precision'	=> '4,1',
		),
	'formular'	=> array(
		'label'		=> __('Weight'),
		'unit'		=> FormularUnit::$KG
	)
);
$FIELDS['pulse_rest'] = array(
	'database'	=> array(
		'type'		=> 'smallint',
		'precision'	=> '3',
		),
	'formular'	=> array(
		'label'		=> __('Resting HR'),
		'unit'		=> FormularUnit::$BPM
	)
);
$FIELDS['pulse_max'] = array(
	'database'	=> array(
		'type'		=> 'smallint',
		'precision'	=> '3',
		),
	'formular'	=> array(
		'label'		=> __('Maximal HR'),
		'unit'		=> FormularUnit::$BPM
	)
);
$FIELDS['fat'] = array(
	'database'	=> array(
		'type'		=> 'decimal',
		'precision'	=> '3,1',
		),
	'formular'	=> array(
		'label'		=> __('Body fat'),
		'unit'		=> FormularUnit::$PERCENT
	)
);
$FIELDS['water'] = array(
	'database'	=> array(
		'type'		=> 'decimal',
		'precision'	=> '3,1',
		),
	'formular'	=> array(
		'label'		=> __('Body water'),
		'unit'		=> FormularUnit::$PERCENT
	)
);
$FIELDS['muscles'] = array(
	'database'	=> array(
		'type'		=> 'decimal',
		'precision'	=> '3,1',
		),
	'formular'	=> array(
		'label'		=> __('Body muscle'),
		'unit'		=> FormularUnit::$PERCENT
	)
);