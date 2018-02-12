<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\ParserException;
use Runalyze\Parser\Activity\Common\AbstractSingleParser;
use Runalyze\Parser\Activity\Common\Data\Metadata;
use Runalyze\Parser\Activity\Common\Data\Pause\Pause;
use Runalyze\Parser\Activity\Common\Data\Round\Round;
use Runalyze\Parser\Activity\Common\StrtotimeWithLocalTimezoneOffsetTrait;
use Runalyze\Profile\Sport\Mapping\FitSdkMapping;

class FitActivity extends AbstractSingleParser
{
    use StrtotimeWithLocalTimezoneOffsetTrait;

    /** @var int [s] */
    const TIME_LIMIT_FOR_TIME_JUMP = 3600;

    /** @var string */
    const APP_ID_RUNSCRIBE_LIGHT = '80,32,146,179,176,221,69,173,137,179,71,42,178,6,10,182';

    /** @var string */
    const APP_ID_RUNSCRIBE_PLUS = '193,245,200,10,90,5,78,138,151,179,253,163,78,149,112,53';

    /** @var array */
    protected $Header = [];

    /** @var array */
    protected $Values = [];

    /** @var int */
    protected $PauseInSeconds = 0;

    /** @var int */
    protected $TimeJumpsInSeconds = 0;

    /** @var bool */
    protected $IsSwimming = false;

    /** @var bool */
    protected $IsPaused = false;

    /** @var bool */
    protected $WasPaused = false;

    /** @var int|bool */
    protected $LastStopTimestamp = false;

    /** @var float [s] */
    protected $CompressedTotalTime = 0.0;

    /** @var float [16m] */
    protected $CompressedTotalDistance16 = 0.0;

    /** @var float [16m] */
    protected $CompressedLastDistance16 = 0.0;

    /** @var string */
    protected $SoftwareVersion = '';

    /** @var array */
    protected $DeveloperFieldMappingForRecord = [
        'Power' => ['power', 1],
        'ContactTime' => ['stance_time', 10],
        'RP_Power' => ['power', 1],
        'RS_Power_AVG' => ['power', 1],
        'RS_ContactTime_L' => ['stance_time_left', 10],
        'RS_ContactTime_R' => ['stance_time_right', 10],
        'RS_Impact_GS_L' => ['impact_gs_left', 1],
        'RS_Impact_GS_R' => ['impact_gs_right', 1],
        'RS_Braking_GS_L' => ['braking_gs_left', 1],
        'RS_Braking_GS_R' => ['braking_gs_right', 1],
        'RS_FootStrike_L' => ['fs_type_left', 1],
        'RS_FootStrike_R' => ['fs_type_right', 1],
        'RS_Pronation_L' => ['pronation_left', 1],
        'RS_Pronation_R' => ['pronation_right', 1],
        'saturated_hemoglobin_percent' => ['smo2_0', 0.1],
        'total_hemoglobin_conc' => ['thb_0', 1]
    ];

    /** @var array */
    protected $NativeFieldMappingForRecord = [
        // TODO: native fields aller verwendeten Arrays
        3 => ['heart_rate'],
        4 => ['cadence', ['default' => 1, 'SPM' => 0.5]],
        5 => ['distance', ['default' => 1, 'm' => 100]],
        7 => ['power'],
        39 => ['vertical_oscillation', ['default' => 1, 'Centimeters' => 100]],
        40 => ['stance_time', ['default' => 1, 'Milliseconds' => 10]],
        54 => [['thb_0', 'thb_1'], ['default' => 100, 'base_type' => ['uint16' => 1]]],
        57 => [['smo2_0', 'smo2_1'], ['default' => 1, 'base_type' => ['uint16' => 0.1]]]
    ];

    /** @var array */
    protected $DeveloperFieldMappingForSession = [];

    /** @var array */
    protected $NativeFieldMappingForSession = [
        9 => ['total_distance', ['default' => 1, 'm' => 100]],
        11 => ['total_calories'],
        44 => ['pool_length', ['default' => 1, 'm' => 100]],
    ];

    /** @var array */
    protected $DeveloperFieldMappingForLap = [];

    /** @var array */
    protected $NativeFieldMappingForLap = [
        9 => ['total_distance', ['default' => 1, 'm' => 100]],
    ];

    /** @var array [developer_data_index => application_id] */
    protected $DeveloperDataAppIds = [];

    public function parse()
    {
        throw new \RuntimeException('FitActivity does not support parse().');
    }

	/**
	 * @see https://github.com/Runalyze/Runalyze/issues/1886
	 */
    public function finishParsing()
    {
        if ('suunto' == $this->Container->Metadata->getCreator()) {
            $this->Container->ActivityData->Duration = end($this->Container->ContinuousData->Time);
            $this->Container->Rounds->add(new Round(
                end($this->Container->ContinuousData->Distance) - $this->Container->Rounds->getTotalDistance(),
                $this->Container->ActivityData->Duration - $this->Container->Rounds->getTotalDuration()
            ));
        }
    }

    public function readMetadataForMultiSessionFrom(Metadata $metadata)
    {
        $this->Container->Metadata->setCreator($metadata->getCreator(), $metadata->getCreatorDetails());
        $this->Container->Metadata->setTimestamp(PHP_INT_MAX, $metadata->getTimezoneOffset());
    }

    protected function readDeveloperDataId()
    {
        if ($this->Values['application_id']) {
            $this->DeveloperDataAppIds[(int)$this->Values['developer_data_index'][0]] = $this->Values['application_id'][1];
        }
    }

    protected function readFieldDescription()
    {
        switch ($this->Values['native_mesg_num'][1]) {
            case 'record':
                $this->readFieldDescriptionFor($this->NativeFieldMappingForRecord, $this->DeveloperFieldMappingForRecord);
                break;

            case 'session':
                $this->readFieldDescriptionFor($this->NativeFieldMappingForSession, $this->DeveloperFieldMappingForSession);
                break;

            case 'lap':
                $this->readFieldDescriptionFor($this->NativeFieldMappingForLap, $this->DeveloperFieldMappingForLap);
                break;
        }
    }

    protected function readFieldDescriptionFor(array &$nativeFieldMapping, array &$fieldMapping)
    {
        if (isset($this->Values['developer_data_index']) && isset($this->DeveloperDataAppIds[(int)$this->Values['developer_data_index'][0]])) {
            $this->adjustFieldDescriptionForDeveloperApp($this->DeveloperDataAppIds[(int)$this->Values['developer_data_index'][0]]);
        }

        if (!isset($this->Values['developer_data_index']) || !isset($this->Values['field_name']) || !isset($this->Values['field_definition_number'])) {
            return;
        }

        $devFieldName = str_replace(['"', ' '], ['', '_'], $this->Values['field_name'][0]);
        $fieldName = $this->Values['developer_data_index'][0].'_'.$this->Values['field_definition_number'][0].'_'.$devFieldName;
        $fieldName = preg_replace_callback('/(\W)/i', function(array $char) {
            return sprintf('_%02x_', ord($char[0]));
        }, preg_replace('/(\s+)/i', '_', $fieldName));

        if (
            isset($this->Values['native_field_num']) &&
            isset($nativeFieldMapping[$this->Values['native_field_num'][0]]) &&
            !empty($nativeFieldMapping[$this->Values['native_field_num'][0]][0])
        ) {
            $nativeFieldNum = $this->Values['native_field_num'][0];
            $unitDefinition = str_replace('"', '', $this->Values['units'][0]);

            $mappingKey = $nativeFieldMapping[$nativeFieldNum][0];
            $mappingFactor = isset($nativeFieldMapping[$nativeFieldNum][1]) ? $nativeFieldMapping[$nativeFieldNum][1] : 1;

            if (is_array($mappingFactor)) {
                if (isset($mappingFactor[$unitDefinition])) {
                    $mappingFactor = $mappingFactor[$unitDefinition];
                } elseif (isset($this->Values['fit_base_type_id']) && isset($mappingFactor['base_type']) && isset($mappingFactor['base_type'][$this->Values['fit_base_type_id'][1]])) {
                    $mappingFactor = $mappingFactor['base_type'][$this->Values['fit_base_type_id'][1]];
                } else {
                    $mappingFactor = $mappingFactor['default'];
                }
            }

            if (is_array($mappingKey)) {
                if (empty($mappingKey)) {
                    return;
                }

                $mappingKey = array_shift($nativeFieldMapping[$nativeFieldNum][0]);
            }

            $fieldMapping[$fieldName] = [$mappingKey, $mappingFactor];
        } elseif (isset($fieldMapping[$devFieldName])) {
            $fieldMapping[$fieldName] = $fieldMapping[$devFieldName];
        }
    }

    protected function adjustFieldDescriptionForDeveloperApp($appId)
    {
        if (self::APP_ID_RUNSCRIBE_LIGHT == $appId || self::APP_ID_RUNSCRIBE_PLUS == $appId) {
            $this->adjustFieldDescriptionForRunScribeLight();
        }
    }

    /**
     * @see https://github.com/ScribeLabs/garmin-connect-iq-runscribe-light/blob/master/resources/resources.xml
     */
    protected function adjustFieldDescriptionForRunScribeLight()
    {
        $fieldName = $this->Values['developer_data_index'][0].'_'.$this->Values['field_definition_number'][0].'_';
        $resultingFieldNames = [
            0 => 'impact_gs_left',
            1 => 'braking_gs_left',
            2 => 'fs_type_left',
            3 => 'pronation_left',
            6 => 'impact_gs_right',
            7 => 'braking_gs_right',
            8 => 'fs_type_right',
            9 => 'pronation_right',
            12 => 'power'
        ];

        if (isset($resultingFieldNames[(int)$this->Values['field_definition_number'][0]])) {
            $this->DeveloperFieldMappingForRecord[$fieldName] = [
                $resultingFieldNames[(int)$this->Values['field_definition_number'][0]],
                1.0
            ];
        }
    }

    /**
     * @param string $line
     */
    public function parseLine($line)
    {
        if ('=' == $line[0]) {
            if (empty($this->Header)) {
                $this->setHeaderFrom($line);
            } else {
                $this->interpretCurrentValues();

                $this->Header = [];
                $this->Values = [];
            }
        } elseif ('-' == $line[0]) {
            $this->addValueFrom($line);
        }
    }

    /**
     * @param string $line
     */
    protected function setHeaderFrom($line)
    {
        foreach (explode(' ', substr($line, 2)) as $info) {
            $info = explode('=', $info);
            $this->Header[$info[0]] = $info[1];
        }
    }

    /**
     * @param string $line
     */
    protected function addValueFrom($line)
    {
        $line = substr($line, 4);
        $values = explode('=', $line);

        if (count($values) == 3) {
            $this->Values[$values[0]] = [$values[1], $values[2]];
        } elseif (count($values) == 2) {
            $this->Values[$values[0]] = [$values[1]];
        }
    }

    protected function interpretCurrentValues()
    {
        if (isset($this->Header['NAME'])) {
            switch ($this->Header['NAME']) {
                case 'file_id':
                    $this->readFileId();
                    break;

                case 'file_creator':
                    break;

                case 'device_info':
                    $this->readDeviceInfo();
                    break;

                case 'sport':
                    $this->readSport();
                    break;

                case 'event':
                    $this->readEvent();
                    break;

                case 'record':
                    $this->readRecord();
                    break;

                case 'hrv':
                    $this->readHRV();
                    break;

                case 'lap':
                    $this->readLap();
                    break;

                case 'session':
                    $this->readSession();
                    break;

                case 'length':
                    $this->readLength();
                    break;

                case 'developer_data_id':
                    $this->readDeveloperDataId();
                    break;

                case 'field_description':
                    $this->readFieldDescription();
                    break;

                case 'activity':
                    break;

                case 'user_profile':
                    $this->readUserProfile();
                    break;
            }
        } elseif (isset($this->Header['NUMBER'])) {
            switch ($this->Header['NUMBER']) {
                case 79:
                    $this->readUndocumentedUserData();
                    break;

                case 140:
                    $this->readUndocumentedDataBlob140();
                    break;
            }
        }
    }

    protected function readFileId()
    {
        if (isset($this->Values['type']) && $this->Values['type'][1] != 'activity') {
            throw new ParserException('FIT file is not specified as activity.');
        }

        if (isset($this->Values['time_created'])) {
            $this->Container->Metadata->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->Values['time_created'][1]);
        }

        if (isset($this->Values['manufacturer'])) {
            $this->Container->Metadata->setCreator($this->Values['manufacturer'][1]);
        }
    }

    protected function readDeviceInfo()
    {
        if (isset($this->Values['device_index']) && $this->Values['device_index'][0] == 0) {
            if (isset($this->Values['garmin_product'])) {
                $this->Container->Metadata->setCreator($this->Values['garmin_product'][1]);
            }

            if (isset($this->Values['software_version'])) {
                $this->SoftwareVersion = $this->Values['software_version'][1];

                $this->Container->Metadata->setCreator($this->Container->Metadata->getCreator(), 'firmware '.$this->SoftwareVersion);
            }
        }
    }

    protected function readSession()
    {
        $this->mapDeveloperFieldsToNativeFieldsFor($this->DeveloperFieldMappingForSession);

        if (isset($this->Values['total_timer_time'])) {
            $this->Container->ActivityData->Duration += round($this->Values['total_timer_time'][0] / 1e3);
        }

        if (isset($this->Values['total_elapsed_time'])) {
            $this->Container->ActivityData->ElapsedTime += round($this->Values['total_elapsed_time'][0] / 1e3);
        }

        if (isset($this->Values['total_distance'])) {
            $this->Container->ActivityData->Distance += round($this->Values['total_distance'][0] / 1e5, 3);
        }

        if (isset($this->Values['total_calories'])) {
            $this->Container->ActivityData->EnergyConsumption += $this->Values['total_calories'][0];
        }

        if (isset($this->Values['total_strokes'])) {
            $this->Container->ActivityData->TotalStrokes = $this->Values['total_strokes'][0];
        }

        if (isset($this->Values['avg_swimming_cadence'])) {
            $this->Container->ActivityData->AvgCadence = $this->Values['avg_swimming_cadence'][0];
        }

        if (isset($this->Values['pool_length'])) {
            $this->Container->ActivityData->PoolLength = $this->Values['pool_length'][0];
        }

        if (isset($this->Values['sport'])) {
            $this->Container->Metadata->setInternalSportId((new FitSdkMapping())->toInternal($this->Values['sport'][0]));
            $this->Container->Metadata->setSportName($this->Values['sport'][1]);
        }

        if (isset($this->Values['total_training_effect']) && $this->Values['total_training_effect'][0] >= 10.0 && $this->Values['total_training_effect'][0] <= 50.0) {
            $this->Container->FitDetails->TrainingEffect = $this->Values['total_training_effect'][0] / 10;
        }
    }

    protected function readSport()
    {
        if (isset($this->Values['sport'])) {
            $this->Container->Metadata->setInternalSportId((new FitSdkMapping())->toInternal($this->Values['sport'][0]));
            $this->Container->Metadata->setSportName($this->Values['sport'][1]);
        } elseif (isset($this->Values['name'])) {
            $this->Container->Metadata->setSportName(substr($this->Values['name'][0], 1, -1));
        }
    }

    protected function readUserProfile()
    {
        if (isset($this->Values['xxx39'])) {
            $this->Container->FitDetails->VO2maxEstimate = round((float)$this->Values['xxx39'][1] * 3.5, 2);
        }
    }

    protected function readUndocumentedUserData()
    {
        if (isset($this->Values['unknown0']) && 0.0 == $this->Container->FitDetails->VO2maxEstimate) {
            $this->Container->FitDetails->VO2maxEstimate = round((int)$this->Values['unknown0'][1] * 3.5 / 1024, 2);
        }
    }

    protected function readUndocumentedDataBlob140()
    {
        if (isset($this->Values['unknown17'])) {
            $this->Container->FitDetails->PerformanceConditionEnd = 100 + (float)$this->Values['unknown17'][1];
        }
    }

    protected function readEvent()
    {
        if (isset($this->Values['event']) && isset($this->Values['data'])) {
            switch ((int)$this->Values['event'][1]) {
                case 37:
                    $this->Container->FitDetails->VO2maxEstimate = (int)$this->Values['data'][1];
                    return;

                case 38:
                    $this->Container->FitDetails->RecoveryTime = (int)$this->Values['data'][1];
                    return;

                case 39:
                    $creator = $this->Container->Metadata->getCreator();

                    // TODO: this may need more device and firmware specific conditions
                    if (
                        substr($creator, 0, 5) == 'fr630' ||
                        substr($creator, 0, 7) == 'fr735xt' ||
                        substr($creator, 0, 6) == 'fenix3' ||
                        substr($creator, 0, 6) == 'fenix5'
                    ) {
                        if ((int)$this->Values['data'][1] >= 0 && (int)$this->Values['data'][1] <= 255) {
                            $this->Container->FitDetails->PerformanceCondition = (int)$this->Values['data'][1];
                        }
                    } else {
                        $this->Container->FitDetails->HrvAnalysis = (int)$this->Values['data'][1];
                    }

                    return;
            }
        }

        if (!isset($this->Values['event']) || $this->Values['event'][1] != 'timer' || !isset($this->Values['event_type'])) {
            return;
        }

        $thisTimestamp = $this->strtotime((string)$this->Values['timestamp'][1]);

        if (!empty($this->Container->ContinuousData->Time) && ($this->Values['event_type'][1] == 'stop_all' || $this->Values['event_type'][1] == 'stop')) {
            $this->IsPaused = true;
            $this->LastStopTimestamp = $thisTimestamp;
        } elseif ($this->Values['event_type'][1] == 'start') {
            if ($this->IsPaused && ($thisTimestamp - $this->Container->Metadata->getTimestamp()) < end($this->Container->ContinuousData->Time)) {
                $this->Container->PausesToApply->add(new Pause(
                    $this->LastStopTimestamp - $this->Container->Metadata->getTimestamp(),
                    $thisTimestamp - $this->LastStopTimestamp
                ));
            } elseif ($this->IsPaused) {
                $this->WasPaused = true;
            }

            $this->IsPaused = false;

            if ($this->LastStopTimestamp === false) {
                $this->Container->Metadata->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->Values['timestamp'][1]);
            } elseif ($thisTimestamp > $this->LastStopTimestamp) {
                $this->PauseInSeconds += $thisTimestamp - $this->LastStopTimestamp;
            }
        }
    }

    protected function readRecord()
    {
        if (
            $this->IsPaused || // Should not happen?
            $this->IsSwimming ||
            count($this->Values) == 1 ||
            (!isset($this->Values['compressed_speed_distance']) && !isset($this->Values['timestamp']))
        ) {
            return;
        }

        if ($this->IsSwimming) {
            return;
        }

        $this->mapDeveloperFieldsToNativeFieldsFor($this->DeveloperFieldMappingForRecord);

        if (isset($this->Values['compressed_speed_distance'])) {
            $time = $this->parseCompressedSpeedDistance();
            $last = -1;
        } else {
            if (empty($this->Container->ContinuousData->Time)) {
                $startTime = $this->strtotime((string)$this->Values['timestamp'][1]);

                if ($startTime < $this->Container->Metadata->getTimestamp()) {
                    $this->Container->Metadata->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->Values['timestamp'][1]);
                }
            }
            $time = $this->strtotime((string)$this->Values['timestamp'][1]) - $this->Container->Metadata->getTimestamp() - $this->PauseInSeconds - $this->TimeJumpsInSeconds;
            $last = end($this->Container->ContinuousData->Time);

            if ($this->WasPaused) {
                $pause = new Pause($last, $this->strtotime((string)$this->Values['timestamp'][1]) - $this->LastStopTimestamp);
                $pause->setHeartRateDetails(
                    end($this->Container->ContinuousData->HeartRate),
                    isset($this->Values['heart_rate']) ? (int)$this->Values['heart_rate'][0] : null
                );

                $this->Container->Pauses->add($pause);
                $this->WasPaused = false;
            } elseif ($time - $last > self::TIME_LIMIT_FOR_TIME_JUMP) {
                $this->TimeJumpsInSeconds += $time - $last;

                return;
            }
        }

        if ($time < $last) {
            return;
        }

        $this->Container->ContinuousData->Latitude[] = isset($this->Values['position_lat']) ? substr($this->Values['position_lat'][1], 0, -4) : null;
        $this->Container->ContinuousData->Longitude[] = isset($this->Values['position_long']) ? substr($this->Values['position_long'][1], 0, -4) : null;
        $this->Container->ContinuousData->Altitude[] = isset($this->Values['altitude']) && $this->Values['altitude'][0] != 0 ? substr($this->Values['altitude'][1], 0, -4) : null;
        $this->Container->ContinuousData->Distance[] = isset($this->Values['distance']) ? $this->Values['distance'][0] / 1e5 : end($this->Container->ContinuousData->Distance);
        $this->Container->ContinuousData->HeartRate[] = isset($this->Values['heart_rate']) ? (int)$this->Values['heart_rate'][0] : null;
        $this->Container->ContinuousData->Cadence[] = isset($this->Values['cadence']) ? (int)$this->Values['cadence'][0] : null;
        $this->Container->ContinuousData->Power[] = isset($this->Values['power']) ? (int)$this->Values['power'][0] : null;
        $this->Container->ContinuousData->LeftRightBalance[] = isset($this->Values['left_right_balance']) ? (int)$this->Values['left_right_balance'][0] : null;
        $this->Container->ContinuousData->Temperature[] = isset($this->Values['temperature']) ? (int)$this->Values['temperature'][0] : null;

        $this->Container->ContinuousData->Time[] = $time;

        //Running Dynamics
        $this->Container->ContinuousData->GroundContactTime[] = isset($this->Values['stance_time']) ? round($this->Values['stance_time'][0]/10) : (
            isset($this->Values['stance_time_left']) && isset($this->Values['stance_time_right']) ? round(($this->Values['stance_time_left'][0] + $this->Values['stance_time_right'][0]) / 2 / 10) : null
        );
        $this->Container->ContinuousData->VerticalOscillation[] = isset($this->Values['vertical_oscillation']) ? round($this->Values['vertical_oscillation'][0] / 10) : null;
        $this->Container->ContinuousData->GroundContactBalance[] = isset($this->Values['stance_time_balance']) ? (int)$this->Values['stance_time_balance'][0] : (
            isset($this->Values['stance_time_left']) && isset($this->Values['stance_time_right']) ? round(10000 * $this->Values['stance_time_left'][0] / ($this->Values['stance_time_left'][0] + $this->Values['stance_time_right'][0])) : null
        );

        // Fit developer fields
        $this->Container->ContinuousData->MuscleOxygenation[] = isset($this->Values['smo2_0']) ? (int)$this->Values['smo2_0'][0] : null;
        $this->Container->ContinuousData->MuscleOxygenation_2[] = isset($this->Values['smo2_1']) ? (int)$this->Values['smo2_1'][0] : null;
        $this->Container->ContinuousData->TotalHaemoglobin[] = isset($this->Values['thb_0']) ? (int)$this->Values['thb_0'][0] : null;
        $this->Container->ContinuousData->TotalHaemoglobin_2[] = isset($this->Values['thb_1']) ? (int)$this->Values['thb_1'][0] : null;

        // RunScribe fields
        $this->Container->ContinuousData->ImpactGsLeft[] = isset($this->Values['impact_gs_left']) ? round($this->Values['impact_gs_left'][0], 1) : null;
        $this->Container->ContinuousData->ImpactGsRight[] = isset($this->Values['impact_gs_right']) ? round($this->Values['impact_gs_right'][0], 1) : null;
        $this->Container->ContinuousData->BrakingGsLeft[] = isset($this->Values['braking_gs_left']) ? round($this->Values['braking_gs_left'][0], 1) : null;
        $this->Container->ContinuousData->BrakingGsRight[] = isset($this->Values['braking_gs_right']) ? round($this->Values['braking_gs_right'][0], 1) : null;
        $this->Container->ContinuousData->FootstrikeTypeLeft[] = isset($this->Values['fs_type_left']) ? (int)$this->Values['fs_type_left'][0] : null;
        $this->Container->ContinuousData->FootstrikeTypeRight[] = isset($this->Values['fs_type_right']) ? (int)$this->Values['fs_type_right'][0] : null;
        $this->Container->ContinuousData->PronationExcursionLeft[] = isset($this->Values['pronation_left']) ? round($this->Values['pronation_left'][0], 1) : null;
        $this->Container->ContinuousData->PronationExcursionRight[] = isset($this->Values['pronation_right']) ? round($this->Values['pronation_right'][0], 1) : null;

        if ($time === $last) {
            $this->mergeRecord();
        }
    }

    protected function mapDeveloperFieldsToNativeFieldsFor(array $developerFieldMapping)
    {
        foreach ($developerFieldMapping as $devFieldName => $nativeData) {
            $nativeFieldName = $nativeData[0];
            $nativeFactor = $nativeData[1];

            if (isset($this->Values[$devFieldName]) && ($this->Values[$devFieldName][0] != 0 || !isset($this->Values[$nativeFieldName]))) {
                $this->Values[$devFieldName][0] *= $nativeFactor;
                $this->Values[$nativeFieldName] = $this->Values[$devFieldName];
            }
        }
    }

    protected function mergeRecord()
    {
        end($this->Container->ContinuousData->Time);
        $i = key($this->Container->ContinuousData->Time);

        foreach ($this->Container->ContinuousData->getPropertyNamesOfArrays() as $key) {
            if (array_key_exists($i, $this->Container->ContinuousData->{$key})) {
                $last = $this->Container->ContinuousData->{$key}[$i - 1];
                $current = array_pop($this->Container->ContinuousData->{$key});

                if ($current != 0 && $last == 0) {
                    $this->Container->ContinuousData->{$key}[$i - 1] = $current;
                }
            }
        }
    }

    /**
     * @see FIT SDK, e.g. at https://github.com/dgaff/fitsdk/blob/7f38d911388b7cdc3db7bf0318239352928faa8b/c/examples/decode/decode.c#L132-L146
     * @return int current time
     */
    protected function parseCompressedSpeedDistance()
    {
        $values = explode(',', $this->Values['compressed_speed_distance'][1]);

        if (count($values) == 3) {
            $speed100 = $values[0] | (($values[1] & 0x0F) << 8);

            $distance16 = ($values[1] >> 4) | ($values[2] << 4);
            $distance16diff = ($distance16 - $this->CompressedLastDistance16) & 0x0FFF;
            $this->CompressedTotalDistance16 += $distance16diff;
            $this->CompressedLastDistance16 = $distance16;

            $this->CompressedTotalTime += ($distance16diff / 16.0) / ($speed100 / 100.0);
            $this->Values['distance'][0] = 100 * $this->CompressedTotalDistance16 / 16.0;
        }

        return round($this->CompressedTotalTime);
    }

    protected function readLap()
    {
        $this->mapDeveloperFieldsToNativeFieldsFor($this->DeveloperFieldMappingForLap);

        if (isset($this->Values['total_timer_time']) && isset($this->Values['total_distance']) && round($this->Values['total_timer_time'][0] / 1e3) > 0) {
            $this->Container->Rounds->add(new Round(
                $this->Values['total_distance'][0] / 1e5,
                $this->Values['total_timer_time'][0] / 1e3
            ));
        }
    }

    protected function readLength()
    {
        if (!$this->IsSwimming) {
            foreach ($this->Container->ContinuousData->getPropertyNamesOfArrays() as $key) {
                $this->Container->ContinuousData->{$key} = [];
            }

            $this->IsSwimming = true;
        }

        $this->Container->ContinuousData->Strokes[] = isset($this->Values['total_strokes']) ? (int)$this->Values['total_strokes'][0] : null;
        $this->Container->ContinuousData->StrokeType[] = isset($this->Values['swim_stroke']) ? (int)$this->Values['swim_stroke'][0] : null;
        $this->Container->ContinuousData->Cadence[] = isset($this->Values['avg_swimming_cadence']) ? (int)$this->Values['avg_swimming_cadence'][0] : null;

        if (empty($this->Container->ContinuousData->Time)) {
            $this->Container->Metadata->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->Values['start_time'][1]);
            $this->Container->ContinuousData->Time[] = round(((int)$this->Values['total_timer_time'][0]) / 1000);
        } else {
            $this->Container->ContinuousData->Time[] = end($this->Container->ContinuousData->Time) + round(((int)$this->Values['total_timer_time'][0]) / 1000);
        }
    }

    protected function readHRV()
    {
        if (!$this->IsPaused) {
            $values = explode(',', $this->Values['time'][1]);

            foreach ($values as $value) {
                if ($value != '65535') {
                    $this->Container->RRIntervals[] = 1000 * (double)substr($value, 0, -2);
                }
            }
        }
    }
}
