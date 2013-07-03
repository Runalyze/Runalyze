<?php
$TABLENAME = 'shoe';

$HIDDEN_KEYS = array();

$FIELDSETS = array(
	array(
		'id'		=> 'general',
		'legend'	=> 'Laufschuh',
		'fields'	=> array('name', 'since')
	),
	array(
		'id'		=> 'analyse',
		'legend'	=> 'Zus&auml;tzliches',
		'fields'	=> array('additionalKm', 'inuse')
	)
);

// Field for id is set always
// Default setting: precision='', null=false, key=false, extra='', default=''
$FIELDS = array();
$FIELDS['name'] = array(
	'database'	=> array(
		'type'		=> 'varchar',
		'precision'	=> '100',
	),
	'formular'	=> array(
		'label'		=> 'Name',
		'required'	=> true,
		'notempty'	=> true,
		'size'		=> FormularInput::$SIZE_MIDDLE
	)
);
$FIELDS['since'] = array(
	'database'	=> array(
		'type'		=> 'varchar',
		'precision'	=> '10',
	),
	'formular'	=> array(
		'label'		=> 'Kaufdatum'
	)
);
$FIELDS['additionalKm'] = array(
	'database'	=> array(
		'type'		=> 'decimal',
		'precision'	=> '6,2',
	),
	'formular'	=> array(
		'label'		=> 'bish. Leistung',
		'unit'		=> FormularUnit::$KM
	)
);
$FIELDS['km'] = array(
	'database'	=> array(
		'type'		=> 'decimal',
		'precision'	=> '6,2'
	)
);
$FIELDS['time'] = array(
	'database'	=> array(
		'type'		=> 'int',
		'precision'	=> '11',
		'default'	=> '0'
	)
);
$FIELDS['inuse'] = array(
	'database'	=> array(
		'type'		=> 'tinyint',
		'precision'	=> '1',
		'default'	=> '1'
		),
	'formular'	=> array(
		'label'		=> 'In Gebrauch',
		'class'		=> 'FormularCheckbox',
		'parser'	=> FormularValueParser::$PARSER_BOOL
	)
);
?>