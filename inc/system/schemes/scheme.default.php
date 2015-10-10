<?php
$HIDDEN_KEYS = array('id');

$FIELDSETS = array();

$FIELDS['id'] = array(
	'database'	=> array(
		'type'		=> 'int',
		'precision'	=> '11',
		'key'		=> true,
		'extra'		=> 'auto_increment'
		),
	'formular'	=> array(
		'label'		=> 'ID'
	)
);