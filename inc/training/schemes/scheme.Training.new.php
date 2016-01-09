<?php
/**
 * Scheme for `runalyze_training`
 */
$TABLENAME = 'training';

$HIDDEN_KEYS = array(
	'creator', 'creator_details', 'activity_id',
	//'vdot', 'vdot_by_time', 'trimp', 'vdot_with_elevation', 'jd_intensity'
	'elapsed_time', 'groundcontact', 'vertical_oscillation', 'groundcontact_balance', 'total_strokes', 'swolf', 'pool_length'
);

$FIELDSETS = array(
	array(
		'id'		=> 'sports',
		'legend'	=> __('Sport'),
		'fields'	=> array('sportid', 'typeid'),
		'conf'		=> 'SPORT'
	),
	array(
		'id'		=> 'general',
		'legend'	=> __('General information'),
		'fields'	=> array('time', 's', 'kcal', 'pulse_avg', 'pulse_max'),
		'conf'		=> 'GENERAL'
	),
	array(
		'id'		=> 'distance',
		'legend'	=> __('Distance'),
		'fields'	=> array('distance', 'is_track', 'elevation', 'pace', 'power', 'cadence'),
            'conf'		=> 'DISTANCE',
		'css'		=> TrainingFormular::$ONLY_DISTANCES_CLASS
	),
	array(
		'id'		=> 'splits',
		'legend'	=> __('Laps'),
		'fields'	=> array('splits'),
		'conf'		=> 'SPLITS',
	),
	array(
		'id'		=> 'other',
		'legend'	=> __('Miscellaneous'),
		'fields'	=> array('use_vdot', 'shoeid', 'comment', 'partner', 'route'),
		'conf'		=> 'OTHER',
		'layout'	=> FormularFieldset::$LAYOUT_FIELD_W100_IN_W50
	),
	array(
		'id'		=> 'notes',
		'legend'	=> __('Notes'),
		'fields'	=> array('notes'),
		'conf'		=> 'NOTES',
		'layout'	=> FormularFieldset::$LAYOUT_FIELD_W100_IN_W50
	),
	array(
		'id'		=> 'weather',
		'legend'	=> __('Weather conditions'),
		'fields'	=> array('weatherid', 'temperature', 'clothes'),
		'conf'		=> 'WEATHER'
	),
	array(
		'id'		=> 'privacy',
		'legend'	=> __('Privacy'),
		'fields'	=> array('is_public'),
		'conf'		=> 'PUBLIC'
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
						'label'		=> __('Sport'),
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
						'label'		=> __('Activity type'),
						'class'		=> 'TrainingSelectType'
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
						'label'		=> __('Date'),
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
						'label'		=> __('Public'),
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
						'label'		=> __('Track'),
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
						'label'		=> __('Distance'),
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
						'label'		=> __('Duration'),
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
						'label'		=> __('Elapsed time'),
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
						'label'		=> __('Pace'),
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
						'label'		=> __('Elevation'),
						'unit'		=> FormularUnit::$ELEVATION,
						'css'		=> TrainingFormular::$ONLY_OUTSIDE_CLASS
					)
	),
	'elevation_calculated'	=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '5',
						'default'	=> '0'
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'kcal'				=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '5',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> __('Calories'),
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
						'label'		=> __('avg. HR'),
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
						'label'		=> __('max. HR'),
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
	'vdot_with_elevation'=> array(
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
						'label'		=> __('VDOT for form'),
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
						'label'		=> '<span class="'.TrainingFormular::$ONLY_NOT_RUNNING_CLASS.'">'.__('Cadence').'</span><span class="'.TrainingFormular::$ONLY_RUNNING_CLASS.'">'.__('Cadence (Running)').'</span>',
						'unit'		=> FormularUnit::$RPM,
						'tooltip'	=> __('Unit is always - also for running - <em>rpm</em>, i.e. rounds (or steps with one foot) per minute,'),
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
						'label'		=> __('Power'), // Leistung?
						'unit'		=> FormularUnit::$POWER,
						'css'		=> TrainingFormular::$ONLY_POWER_CLASS
					)
	),
	'groundcontact'		=> array(
					'database'	=> array(
						'type'		=> 'smallint',
						'precision'	=> '5',
						'default'	=> '0'
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'vertical_oscillation'	=> array(
					'database'	=> array(
						'type'		=> 'tinyint',
						'precision'	=> '3',
						'default'	=> '0'
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'temperature'		=> array(
					'database'	=> array(
						'type'		=> 'float',
						'null'		=> true
					),
					'formular'	=> array(
						'label'		=> __('Temperature'),
						'unit'		=> \Runalyze\Configuration::General()->temperatureUnit()->unit(),
                                                'parser'        => FormularValueParser::$PARSER_TEMPERATURE,
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
						'label'		=> __('Weather'),
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
						'label'		=> __('Route'),
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
						'label'		=> __('Clothing'),
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
						'label'		=> __('Laps'),
						'class'		=> 'TrainingInputSplits'
					)
	),
	'comment'			=> array(
					'database'	=> array(
						'type'		=> 'tinytext',
						'null'		=> true
					),
					'formular'	=> array(
						'label'		=> __('Title'),
						'size'		=> FormularInput::$SIZE_FULL_INLINE
					)
	),
	'partner'			=> array(
					'database'	=> array(
						'type'		=> 'tinytext',
						'null'		=> true
					),
					'formular'	=> array(
						'label'		=> __('Partner'),
						'size'		=> FormularInput::$SIZE_FULL_INLINE
					)
	),
	'shoeid'			=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '11',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> __('Shoe'),
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
						'label'		=> __('Notes'),
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
	'arr_geohashes'			=> array(
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
	'arr_alt_original'	=> array(
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
	'arr_groundcontact'	=> array(
					'database'	=> array(
						'type'		=> 'longtext',
						'null'		=> 'true',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'arr_vertical_oscillation'	=> array(
					'database'	=> array(
						'type'		=> 'longtext',
						'null'		=> 'true',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'pauses'	=> array(
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
	),
	'stroke'	=> array(
					'database'	=> array(
						'type'		=> 'longtext',
						'null'		=> 'true',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
	'stroketype'	=> array(
					'database'	=> array(
						'type'		=> 'longtext',
						'null'		=> 'true',
					),
					'formular'	=> array(
						'hidden'	=> true
					)
	),
    	'total_strokes'			=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '4',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> 'Total strokes'
					)
	),
    	'swolf' 			=> array(
					'database'	=> array(
						'type'		=> 'int',
						'precision'	=> '4',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> 'SWOLF'
					)
	),
    	'pool_length' 			=> array(
					'database'	=> array(
						'type'		=> 'smallint',
						'precision'	=> '4',
						'default'	=> '0'
					),
					'formular'	=> array(
						'label'		=> 'Pool length',
                                                'unit'		=> FormularUnit::$M
					)
	),  
    
);
