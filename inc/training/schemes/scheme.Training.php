<?php
/**
 * Scheme for `runalyze_training`
 */
$TABLENAME = 'training';

$HIDDEN_KEYS = array(
	//'created', 'edited',
	'creator', 'creator_details', 'activity_id',
	//'elevation_corrected', 'gps_cache_object',
	'arr_time', 'arr_lat', 'arr_lon', 'arr_alt', 'arr_heart', 'arr_dist', 'arr_pace', 'arr_cadence', 'arr_power', 'arr_temperature',
	//'vdot', 'vdot_by_time', 'trimp'
	'elapsed_time', // 'power', 'cadence',
	// TODO: already prepared attributes
	'jd_intensity'
);

$FIELDSETS = array(
	array(
		'id'		=> 'sports',
		'legend'	=> 'Sportart',
		'fields'	=> array('sportid', 'typeid'),
		'conf'		=> 'FORMULAR_SHOW_SPORT'
	),
	array(
		'id'		=> 'general',
		'legend'	=> 'Allgemeines',
		'fields'	=> array('time', 's', 'kcal', 'pulse_avg', 'pulse_max'),
		'conf'		=> 'FORMULAR_SHOW_GENERAL'
	),
	array(
		'id'		=> 'distance',
		'legend'	=> 'Distanz',
		'fields'	=> array('distance', 'is_track', 'elevation', 'abc', 'pace', 'power', 'cadence'),
		'conf'		=> 'FORMULAR_SHOW_DISTANCE',
		'css'		=> TrainingFormular::$ONLY_DISTANCES_CLASS
	),
	array(
		'id'		=> 'splits',
		'legend'	=> 'Zwischenzeiten',
		'fields'	=> array('splits'),
		'conf'		=> 'FORMULAR_SHOW_SPLITS',
		'css'		=> TrainingFormular::$ONLY_TYPES_CLASS
	),
	array(
		'id'		=> 'other',
		'legend'	=> 'Sonstiges',
		'fields'	=> array('use_vdot', 'shoeid', 'comment', 'partner', 'route'),
		'conf'		=> 'FORMULAR_SHOW_OTHER',
		'layout'	=> FormularFieldset::$LAYOUT_FIELD_W100_IN_W50
	),
	array(
		'id'		=> 'notes',
		'legend'	=> 'Notizen',
		'fields'	=> array('notes'),
		'conf'		=> 'FORMULAR_SHOW_NOTES',
		'layout'	=> FormularFieldset::$LAYOUT_FIELD_W100_IN_W50
	),
	array(
		'id'		=> 'weather',
		'legend'	=> 'Wetter',
		'fields'	=> array('weatherid', 'temperature', 'clothes'),
		'conf'		=> 'FORMULAR_SHOW_WEATHER'
	),
	array(
		'id'		=> 'privacy',
		'legend'	=> 'Privatsph&auml;re',
		'fields'	=> array('is_public'),
		'conf'		=> 'FORMULAR_SHOW_PUBLIC'
	)
);

// Field for id is set always
// Default setting: precision='', null=false, key=false, extra='', default=''
$FIELDS = array(
	'sportid'			=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '11',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> 'Sportart',
						'class'		=> 'TrainingSelectSport',
						'required'	=> true
					)
	),
	'typeid'			=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '11',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> 'Trainingstyp',
						'class'		=> 'TrainingSelectType',
						'css'		=> TrainingFormular::$ONLY_TYPES_CLASS
					)
	),
	'time'				=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '11',
						'default'	=> '0'
					),
					'formular'	=> array(
						'required'	=> true,
						'label'		=> 'Datum',
						'class'		=> 'FormularInputDayAndDaytime'
					)
	),
	'created'			=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '11',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'edited'			=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '11',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'is_public'			=> array(
					'database'	=> array(
						'type'		=> 'tinyint',
						'precision'	=> '1',
						'default'	=> '1'
					),
					'formular'	=> array(
						'label'		=> '&Ouml;ffentlich',
						'class'		=> 'FormularCheckbox'
					)
	),
	'is_track'			=> array(
					'database'	=> array(
						'type'		=> 'tinyint',
						'precision'	=> '1',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> 'Bahn',
						'class'		=> 'FormularCheckbox',
						'css'		=> TrainingFormular::$ONLY_RUNNING_CLASS
					)
	),
	'distance'			=> array(
					'database'	=> array(
						'type'		=> 'decimal',
						'precision'	=> '6,2',
						'default'	=> '0.00'
					),
					'formular'	=> array(
						'label'		=> 'Distanz',
						'unit'		=> FormularUnit::$KM
					)
	),
	's'					=> array(
					'database'	=> array(
						'type'		=> 'decimal',
						'precision'	=> '8,2',
						'default'	=> '0.00'
					),
					'formular'	=> array(
						'label'		=> 'Dauer',
						'required'	=> true,
						'parser'	=> FormularValueParser::$PARSER_TIME
					)
	),
	'elapsed_time'		=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '6',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> 'Gesamtdauer',
						'parser'	=> FormularValueParser::$PARSER_TIME
					)
	),
	'pace'				=> array(
					'database'	=> array(
						'type'		=> 'varchar',
						'precision'	=> '5',
						'default'	=> '-:--'
					),
					'formular'	=> array(
						//'class'	=> 'TrainingInputPace' // TODO: pace + speed
						'label'		=> 'Pace',
						'unit'		=> FormularUnit::$PACE
					)
	),
	'elevation'			=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '5',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> 'H&ouml;henmeter',
						'unit'		=> FormularUnit::$ELEVATION,
						'css'		=> TrainingFormular::$ONLY_OUTSIDE_CLASS
					)
	),
	'kcal'				=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '4',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> 'Kalorien',
						'unit'		=> FormularUnit::$KCAL
					)
	),
	'pulse_avg'			=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '3',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> '&oslash; Puls',
						'unit'		=> FormularUnit::$BPM
					)
	),
	'pulse_max'			=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '3',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> 'max. Puls',
						'unit'		=> FormularUnit::$BPM
					)
	),
	'vdot'				=> array(
					'database'	=> array(
						'type'		=> 'decimal',
						'precision'	=> '5,2',
						'default'	=> '0.00'
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'vdot_by_time'		=> array(
					'database'	=> array(
						'type'		=> 'decimal',
						'precision'	=> '5,2',
						'default'	=> '0.00'
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'use_vdot'			=> array(
					'database'	=> array(
						'type'		=> 'tinyint',
						'precision'	=> '1',
						'default'	=> '1'
					),
					'formular'	=> array(
						'label'		=> 'VDOT f&uuml;r Form',
						'class'		=> 'FormularCheckbox',
						'css'		=> TrainingFormular::$ONLY_RUNNING_CLASS
					)
	),
	'jd_intensity'		=> array(
					'database'	=> array(
						'type'		=> 'smallint',
						'precision'	=> '4',
						'default'	=> '0'
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'trimp'				=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '4',
						'default'	=> '0'
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'cadence'			=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '3',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> 'Trittfrequenz',
						'unit'		=> FormularUnit::$RPM,
						'css'		=> TrainingFormular::$ONLY_OUTSIDE_CLASS
					)
	),
	'power'				=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '4',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> 'Power', // Leistung?
						'unit'		=> FormularUnit::$POWER,
						'css'		=> TrainingFormular::$ONLY_POWER_CLASS
					)
	),
	'temperature'		=> array(
					'database'	=> array(
						'type'		=> 'float',
						'null'		=> true
					),
					'formular'	=> array(
						'label'		=> 'Temperatur',
						'unit'		=> FormularUnit::$CELSIUS,
						'css'		=> TrainingFormular::$ONLY_OUTSIDE_CLASS
					)
	),
	'weatherid'			=> array(
					'database'	=> array(
						'type'		=> 'smallint',
						'precision'	=> '6',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> 'Wetter',
						'class'		=> 'TrainingSelectWeather',
						'css'		=> TrainingFormular::$ONLY_OUTSIDE_CLASS
					)
	),
	'route'				=> array(
					'database'	=> array(
						'type'		=> 'tinytext',
						'null'		=> true
					),
					'formular'	=> array(
						'label'		=> 'Strecke',
						'size'		=> FormularInput::$SIZE_FULL_INLINE,
						'css'		=> TrainingFormular::$ONLY_OUTSIDE_CLASS
					)
	),
	'clothes'			=> array(
					'database'	=> array(
						'type'		=> 'varchar',
						'precision'	=> '100',
						'default'	=> ''
					),
					'formular'	=> array(
						'label'		=> 'Kleidung',
						'class'		=> 'TrainingSelectClothes',
						'css'		=> TrainingFormular::$ONLY_OUTSIDE_CLASS,
						'layout'	=> FormularFieldset::$LAYOUT_FIELD_W100_IN_W50
					)
	),
	'splits'			=> array(
					'database'	=> array(
						'type'		=> 'text',
						'null'		=> 'true',
						'default'	=> ''
					),
					'formular'	=> array(
						'label'		=> 'Zwischenzeiten',
						'class'		=> 'TrainingInputSplits'
					)
	),
	'comment'			=> array(
					'database'	=> array(
						'type'		=> 'tinytext',
						'null'		=> true
					),
					'formular'	=> array(
						'label'		=> 'Bemerkung',
						'size'		=> FormularInput::$SIZE_FULL_INLINE
					)
	),
	'partner'			=> array(
					'database'	=> array(
						'type'		=> 'tinytext',
						'null'		=> true
					),
					'formular'	=> array(
						'label'		=> 'Trainingspartner',
						'size'		=> FormularInput::$SIZE_FULL_INLINE
					)
	),
	'abc'				=> array(
					'database'	=> array(
						'type'		=> 'tinyint',
						'precision'	=> '1',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> 'Lauf-ABC',
						'class'		=> 'FormularCheckbox',
						'css'		=> TrainingFormular::$ONLY_RUNNING_CLASS
					)),
	'shoeid'			=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '11',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> 'Laufschuh',
						'class'		=> 'TrainingSelectShoe',
						'css'		=> TrainingFormular::$ONLY_RUNNING_CLASS
					)
	),
	'notes'				=> array(
					'database'	=> array(
						'type'		=> 'text',
						'default'	=> ''
					),
					'formular'	=> array(
						'label'		=> 'Notizen',
						'class'		=> 'FormularTextarea',
						'size'		=> FormularInput::$SIZE_FULL_INLINE
					)
	),
	'arr_time'			=> array(
					'database'	=> array(
						'type'		=> 'longtext',
						'null'		=> 'true',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'arr_lat'			=> array(
					'database'	=> array(
						'type'		=> 'longtext',
						'null'		=> 'true',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'arr_lon'			=> array(
					'database'	=> array(
						'type'		=> 'longtext',
						'null'		=> 'true',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'arr_alt'			=> array(
					'database'	=> array(
						'type'		=> 'longtext',
						'null'		=> 'true',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'arr_dist'			=> array(
					'database'	=> array(
						'type'		=> 'longtext',
						'null'		=> 'true',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'arr_heart'			=> array(
					'database'	=> array(
						'type'		=> 'longtext',
						'null'		=> 'true',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'arr_pace'			=> array(
					'database'	=> array(
						'type'		=> 'longtext',
						'null'		=> 'true',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'arr_cadence'		=> array(
					'database'	=> array(
						'type'		=> 'longtext',
						'null'		=> 'true',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'arr_power'			=> array(
					'database'	=> array(
						'type'		=> 'longtext',
						'null'		=> 'true',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'arr_temperature'	=> array(
					'database'	=> array(
						'type'		=> 'longtext',
						'null'		=> 'true',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'creator'			=> array(
					'database'	=> array(
						'type'		=> 'varchar',
						'precision'	=> '100',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'creator_details'	=> array(
					'database'	=> array(
						'type'		=> 'tinytext'
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'activity_id'		=> array(
					'database'	=> array(
						'type'		=> 'varchar',
						'precision'	=> '50',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'elevation_corrected' => array(
					'database'	=> array(
						'type'		=> 'tinyint',
						'precision'	=> '1',
						'default'	=> '0'
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'gps_cache_object'	=> array(
					'database'	=> array(
						'type'		=> 'mediumtext'
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	)
);
?>