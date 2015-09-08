<?php
$TABLENAME = 'shoe';

$HIDDEN_KEYS = array();

$FIELDSETS = array(
	array(
		'id'		=> 'general',
		'legend'	=> __('Shoe'),
		'fields'	=> array('name', 'since')
	),
	array(
		'id'		=> 'analyse',
		'legend'	=> __('Miscellaneous'),
		'fields'	=> array('additionalKm', 'inuse', 'weight')
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
		'label'		=> __('Name'),
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
		'label'		=> __('Purchase date')
	)
);
$FIELDS['additionalKm'] = array(
	'database'	=> array(
		'type'		=> 'decimal',
		'precision'	=> '6,2',
	),
	'formular'	=> array(
		'label'		=> __('prev. distance'),
		'unit'		=> FormularUnit::$KM
	)
);
$FIELDS['km'] = array(
	'database'	=> array(
		'type'		=> 'decimal',
		'precision'	=> '6,2'
	)
);
$FIELDS['weight'] = array(
	'database'	=> array(
		'type'		=> 'smallint',
		'precision'	=> '3',
	),
	'formular'	=> array(
		'label'		=> __('weight'),
		'unit'		=> FormularUnit::$G
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
		'label'		=> __('In use'),
		'class'		=> 'FormularCheckbox',
		'parser'	=> FormularValueParser::$PARSER_BOOL
	)
);
?>