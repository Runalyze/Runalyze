<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractSingleParser;
use Runalyze\Parser\Activity\Common\Data\GpsDistanceCalculator;
use Runalyze\Parser\Activity\Common\Data\Pause\Pause;
use Runalyze\Parser\Activity\Common\Data\Round\Round;
use Runalyze\Parser\Activity\Common\PauseDetectionCapableParserInterface;
use Runalyze\Parser\Activity\Common\PauseDetectionCapableTrait;
use Runalyze\Util\LocalTime;
use SimpleXMLElement;

class TcxActivity extends AbstractSingleParser implements PauseDetectionCapableParserInterface
{
    use PauseDetectionCapableTrait;

    /** @var SimpleXMLElement */
    protected $Xml;

    /** @var int [s] */
    private static $IGNORE_NO_MOVE_UNTIL = 1;

    /** @var int [s] */
    protected $PauseInSeconds = 0;

    /** @var int [m] */
    protected $LastPoint = 0;

    /** @var float [m] */
    protected $LastDistance = -1;

    /** @var bool */
    protected $LastPointWasEmpty = false;

    /** @var bool */
    protected $IsWithoutDistance = false;

    /** @var bool */
    protected $DistancesAreEmpty = false;

    /** @var bool */
    protected $WasPause = false;

    /** @var int [s] */
    protected $PauseDuration = 0;

    public function __construct(SimpleXMLElement $activity)
    {
        parent::__construct();

        $this->checkThatActivityXmlIsValid($activity);

        $this->Xml = $activity;
    }

    protected function checkThatActivityXmlIsValid(SimpleXMLElement $xml)
    {
        if (!property_exists($xml, 'Id') || !property_exists($xml, 'Lap')) {
            throw new UnsupportedFileException('Given XML object is not a valid tcx activity. &lt;Id&gt;- or &lt;Lap&gt;-tag could not be located.');
        }
    }

    /**
     * Timestamps are given in UTC but local timezone offset has to be considered!
     *
     * @param string $string
     *
     * @return int
     */
    protected function strtotime($string)
    {
        if (substr($string, -1) == 'Z') {
            return LocalTime::fromServerTime(strtotime(substr($string, 0, -1).' UTC'))->getTimestamp();
        }

        return LocalTime::fromString($string)->getTimestamp();
    }

    public function parse()
    {
        $this->parseGeneralValues();
        $this->parseLaps();
    }

    protected function parseGeneralValues()
    {
        $this->Container->Metadata->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->Xml->Id);

        if (isset($this->Xml->Creator) && isset($this->Xml->Creator->Name)) {
            $this->Container->Metadata->setCreator('', (string)$this->Xml->Creator->Name);
        }

        if (isset($this->Xml->attributes()->Sport)) {
            $this->Container->Metadata->setSportName((string)$this->Xml->attributes()->Sport);
        }

        if (!empty($this->Xml->Notes)) {
            $this->Container->Metadata->setNotes((string)$this->Xml->Notes);
        }

        if (!empty($this->Xml->Training)) {
            $this->Container->Metadata->setDescription((string)$this->Xml->Training->Plan->Name);
        }
    }

    protected function parseLaps()
    {
        foreach ($this->Xml->Lap as $i => $lap) {
            if ($i == 0) {
                if (isset($lap['StartTime'])) {
                    $start = $this->strtotime((string)$lap['StartTime']);

                    if ($start < $this->Container->Metadata->getTimestamp()) {
                        $this->Container->Metadata->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$lap['StartTime']);
                    }
                }

                if (!empty($lap->Track) && !empty($lap->Track[0]->Trackpoint[0])) {
                    $start = $this->strtotime((string)$lap->Track[0]->Trackpoint[0]->Time);

                    if ($start < $this->Container->Metadata->getTimestamp()) {
                        $this->Container->Metadata->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$lap->Track[0]->Trackpoint[0]->Time);
                    }
                }
            }

            $this->parseLap($lap);
        }
    }

    protected function parseLap(SimpleXMLElement &$lap)
    {
        $this->parseLapValues($lap);
        $this->parseTrackpoints($lap);
    }

    protected function parseLapValues(SimpleXMLElement &$lap)
    {
        if (!empty($lap->Calories)) {
            $this->Container->ActivityData->EnergyConsumption += (int)$lap->Calories;
        }

        $this->Container->Rounds->add(new Round(
            round((int)$lap->DistanceMeters) / 1000,
            round((float)$lap->TotalTimeSeconds),
            'Active' == (string)$lap->Intensity
        ));

        $this->IsWithoutDistance = (
            (0 == (int)$lap->DistanceMeters && 10 < (int)$lap->TotalTimeSeconds) ||
            (
                isset($lap->Track[0]) && count($lap->Track[0]->Trackpoint) > 5 &&
                (double)$lap->Track[0]->Trackpoint[0]->DistanceMeters == (double)$lap->Track[0]->Trackpoint[count($lap->Track[0]->Trackpoint) - 2]->DistanceMeters
            )
        );
    }

    protected function parseTrackpoints(SimpleXMLElement &$trackPoints)
    {
        $this->LastPoint = 0;

        foreach ($trackPoints->Track as $track) {
            if ($this->LastPoint > 0) {
                $this->LastPointWasEmpty = true;
            }

            if (count($track->xpath('./Trackpoint')) > 0) {
                $this->DistancesAreEmpty = 0 == count($track->xpath('./Trackpoint/DistanceMeters'));

                if ($this->strtotime((string)$trackPoints['StartTime']) + 8 < $this->strtotime((string)$track->Trackpoint[0]->Time)) {
                    $this->LastPointWasEmpty = true;
                }

                foreach ($track->Trackpoint as $trackPoint) {
                    $this->parseTrackpoint($trackPoint);
                }
            }
        }
    }

    protected function parseTrackpoint(SimpleXMLElement &$trackPoint)
    {
        if ($this->DistancesAreEmpty) {
            $trackPoint->addChild('DistanceMeters', 1000 * $this->distanceToTrackpoint($trackPoint));
        }

        $thisBreakInMeter = (float)$trackPoint->DistanceMeters - $this->LastDistance;
        $thisBreakInSeconds = ($this->strtotime((string)$trackPoint->Time) - $this->Container->Metadata->getTimestamp() - end($this->Container->ContinuousData->Time)) - $this->PauseInSeconds;

        if ($thisBreakInSeconds <= 0) {
            return;
        }

        if ($this->DetectPauses && !$this->IsWithoutDistance) {
            $noMove = ($this->LastDistance == (float)$trackPoint->DistanceMeters);
            $tooSlow = !$this->LastPointWasEmpty && $thisBreakInMeter > 0 && ($thisBreakInSeconds / $thisBreakInMeter > 6);
        } else {
            $noMove = false;
            $tooSlow = false;
        }

        if ((empty($trackPoint->DistanceMeters) && !$this->IsWithoutDistance ) || $noMove || $tooSlow) {
            $ignored = false;

            if ($trackPoint->children()->count() == 1 || $noMove || $tooSlow) {
                if ($noMove && $thisBreakInSeconds <= self::$IGNORE_NO_MOVE_UNTIL) {
                    $ignored = true;
                } else {
                    $this->PauseInSeconds += $thisBreakInSeconds;
                    $this->WasPause = true;
                    $this->PauseDuration += $thisBreakInSeconds;
                }
            }

            if (!$ignored) {
                return;
            }
        }

        if (empty($trackPoint->DistanceMeters) && !empty($this->Container->ContinuousData->Distance)) {
            $trackPoint->DistanceMeters = end($this->Container->ContinuousData->Distance) * 1000;
        }

        if ($this->Container->Metadata->getTimestamp() == 0) {
            $this->Container->Metadata->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$trackPoint->Time);
        }

        if ($this->LastPointWasEmpty) {
            $OldPauseInSeconds = $this->PauseInSeconds;
            $this->PauseInSeconds = ($this->strtotime((string)$trackPoint->Time) - $this->Container->Metadata->getTimestamp() - end($this->Container->ContinuousData->Time));
            $this->PauseDuration += $this->PauseInSeconds - $OldPauseInSeconds;
            $this->WasPause = true;
        }

        if ($this->WasPause) {
            $pause = new Pause(end($this->Container->ContinuousData->Time), $this->PauseDuration);
            $pause->setHeartRateDetails(end($this->Container->ContinuousData->HeartRate), !empty($trackPoint->HeartRateBpm) ? round($trackPoint->HeartRateBpm->Value) : null);

            $this->Container->Pauses->add($pause);

            $this->WasPause = false;
            $this->PauseDuration = 0;
        }

        $this->LastPointWasEmpty = false;
        $this->LastPoint = (int)$trackPoint->DistanceMeters;
        $this->LastDistance = (float)$trackPoint->DistanceMeters;

        $this->Container->ContinuousData->Time[] = $this->strtotime((string)$trackPoint->Time) - $this->Container->Metadata->getTimestamp() - $this->PauseInSeconds;
        $this->Container->ContinuousData->Distance[] = (float)$trackPoint->DistanceMeters / 1000;
        $this->Container->ContinuousData->Altitude[] = (int)$trackPoint->AltitudeMeters;
        $this->Container->ContinuousData->HeartRate[] = (!empty($trackPoint->HeartRateBpm)) ? round($trackPoint->HeartRateBpm->Value) : null;

        if (!empty($trackPoint->Position)) {
            $this->Container->ContinuousData->Latitude[] = (double)$trackPoint->Position->LatitudeDegrees;
            $this->Container->ContinuousData->Longitude[] = (double)$trackPoint->Position->LongitudeDegrees;
        } elseif (!empty($this->Container->ContinuousData->Latitude)) {
            $this->Container->ContinuousData->Latitude[] = end($this->Container->ContinuousData->Latitude);
            $this->Container->ContinuousData->Longitude[] = end($this->Container->ContinuousData->Longitude);
        } else {
            $this->Container->ContinuousData->Latitude[] = 0;
            $this->Container->ContinuousData->Longitude[] = 0;
        }

        $this->parseExtensionValues($trackPoint);
    }

    protected function parseExtensionValues(SimpleXMLElement &$point)
    {
        $power = null;
        $rpm   = null;

        if (!empty($point->Cadence)) {
            $rpm = (int)$point->Cadence;
        }

        if (isset($point->Extensions)) {
            if (isset($point->Extensions->TPX) && isset($point->Extensions->TPX->RunCadence)) {
                $rpm = (int)$point->Extensions->TPX->RunCadence;
            }

            if (isset($point->Extensions->TPX) && isset($point->Extensions->TPX->Watts)) {
                $power = (int)$point->Extensions->TPX->Watts;
            }

            $this->parsePowerFromExtensionValues($point->Extensions[0], 'ns2', $power, $rpm);
            $this->parsePowerFromExtensionValues($point->Extensions[0], 'ns3', $power, $rpm);
        }

        $this->Container->ContinuousData->Power[] = $power;
        $this->Container->ContinuousData->Cadence[] = $rpm;
    }

    protected function parsePowerFromExtensionValues(SimpleXMLElement &$extensions, $namespace, &$power, &$rpm)
    {
        if ($extensions->children($namespace,true)->count() > 0) {
            if (isset($extensions->children($namespace,true)->TPX)) {
                $trackPointx = $extensions->children($namespace,true)->TPX[0];

                if ($trackPointx->children($namespace, true)->count() > 0 && isset($trackPointx->children($namespace,true)->Watts)) {
                    $power = (int)$trackPointx->children($namespace,true)->Watts;
                }

                if ($trackPointx->children($namespace,true)->count() > 0 && isset($trackPointx->children($namespace,true)->RunCadence)) {
                    $rpm = (int)$trackPointx->children($namespace,true)->RunCadence;
                }
            }
        }
    }

    protected function distanceToTrackpoint(SimpleXMLElement &$trackPoint)
    {
        if (empty($this->Container->ContinuousData->Distance)) {
            return empty($trackPoint->Position) ? 0.0 : 0.001;
        }

        $currentDistance = end($this->Container->ContinuousData->Distance);

        if (empty($trackPoint->Position)) {
            return $currentDistance;
        }

        $currentLatitude = end($this->Container->ContinuousData->Latitude);
        $currentLongitude = end($this->Container->ContinuousData->Longitude);

        if (0 == $currentLatitude && 0 == $currentLongitude) {
            return $currentDistance + 0.001;
        }

        return $currentDistance + GpsDistanceCalculator::gpsDistance(
            $currentLatitude,
            $currentLongitude,
            (double)$trackPoint->Position->LatitudeDegrees,
            (double)$trackPoint->Position->LongitudeDegrees
        );
    }
}
