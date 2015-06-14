<?php
$TABLENAME = 'user';

$HIDDEN_KEYS = array();

$FIELDSETS = array(
	array(
		'id'		=> 'general',
		'legend'	=> __('General information'),
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
	),
    	array(
		'id'		=> 'sleep',
		'legend'	=> __('Sleep'),
		'fields'	=> array('sleep_duration')
	),
        array(
		'id'		=> 'notes',
		'legend'	=> __('Notes'),
		'fields'	=> array('notes'),
		'layout'	=> FormularFieldset::$LAYOUT_FIELD_W100_IN_W33
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
$FIELDS['sleep_duration'] = array(
	'database'	=> array(
		'type'		=> 'smallint',
		'precision'	=> '4',
		'default'	=> '0'
		),
	'formular'	=> array(
		'label'		=> __('Sleep Duration'),
		'unit'		=> FormularUnit::$HOUR,
		'parser'	=> FormularValueParser::$PARSER_TIME_MINUTES
	)
);
$FIELDS['notes'] = array(
	'database'	=> array(
		'type'		=> 'text',
		'default'	=> ''
		),
	'formular'	=> array(
		'label'		=> __('Notes'),
		'class'		=> 'FormularTextarea',
		'size'		=> FormularInput::$SIZE_FULL
	)
);