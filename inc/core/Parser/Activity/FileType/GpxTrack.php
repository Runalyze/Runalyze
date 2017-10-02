<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractSingleParser;
use Runalyze\Parser\Activity\Common\Data\Pause\Pause;
use SimpleXMLElement;

class GpxTrack extends AbstractSingleParser
{
    /** @var int factor to guess pauses */
    public static $PAUSE_FACTOR_FROM_AVERAGE_INTERVAL = 10;

    /** @var SimpleXMLElement */
    protected $Xml;

    /** @var int */
    protected $LastTimestamp = 0;

    /** @var SimpleXMLElement */
    protected $ExtensionXml = null;

    /** @var bool */
    protected $LookForPauses = false;

    /** @var int [s] gaps larger than this value will be recognized as pause if activated */
    protected $LimitForPauses = 0;

    /** @var bool */
    protected $WasPaused = false;

    /** @var int [s] */
    protected $PauseDuration = 0;

    /** @var int|null [bpm] */
    protected $LastValidHeartRate = null;

    public function __construct(SimpleXMLElement $track)
    {
        parent::__construct();

        $this->checkThatActivityXmlIsValid($track);

        $this->Xml = $track;
    }

    protected function checkThatActivityXmlIsValid(SimpleXMLElement $track)
    {
        if (!property_exists($track, 'trkseg')) {
            throw new UnsupportedFileException('Given XML object does not contain any track. &lt;trkseg&gt;-tag could not be located.');
        }
    }

    public function setExtensionXML(SimpleXMLElement $xml)
    {
        $this->ExtensionXml = $xml;
    }

    /**
     * @param bool $flag
     */
    public function lookForPauses($flag = true)
    {
        $this->LookForPauses = $flag;
    }

    public function parse()
    {
        $this->guessLimitForPauses();
        $this->parseGeneralValues();
        $this->parseTrack();
    }

    protected function parseGeneralValues()
    {
        $this->Container->Metadata->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->Xml->trkseg->trkpt[0]->time);

        if (!empty($this->Xml->desc)) {
            $this->Container->Metadata->setDescription(strip_tags((string)$this->Xml->desc));
        }
    }

    protected function guessLimitForPauses()
    {
        $totalPoints = 0;
        $totalTime = 0;
        $numSegments = count($this->Xml->trkseg);

        for ($seg = 0; $seg < $numSegments; ++$seg) {
            $numPoints = count($this->Xml->trkseg[$seg]->trkpt);
            $totalPoints += $numPoints;
            $totalTime += strtotime((string)$this->Xml->trkseg[$seg]->trkpt[$numPoints-1]->time) - strtotime((string)$this->Xml->trkseg[$seg]->trkpt[0]->time);
        }

        $this->LimitForPauses = round(self::$PAUSE_FACTOR_FROM_AVERAGE_INTERVAL * $totalTime / $totalPoints);
    }

    protected function parseTrack()
    {
        $i = 0;
        $j = 0;
        $this->LastTimestamp = 0;
        $lookForPauses = $this->LookForPauses;

        foreach ($this->Xml->trkseg as $trackSegment) {
            foreach ($trackSegment->trkpt as $point) {
                if ($i > 0 && !$lookForPauses) {
                    $this->LookForPauses = $j == 0;
                }

                $this->parseTrackPoint($point);

                ++$j;
            }

            ++$i;
            $j = 0;
        }

        $this->parseSpoQExtension();
    }

    protected function parseTrackPoint(SimpleXMLElement $point)
    {
        if ($this->LastTimestamp == 0) {
            $this->LastTimestamp = strtotime((string)$point->time);
        }

        if (!empty($point['lat'])) {
            $lat  = round((double)$point['lat'], 7);
            $lon  = round((double)$point['lon'], 7);
        } elseif (!empty($this->Container->ContinuousData->Latitude)) {
            $lat  = end($this->Container->ContinuousData->Latitude);
            $lon  = end($this->Container->ContinuousData->Longitude);
        } else {
            return;
        }

        $newTime = $this->getTimeOfPoint($point);

        if ($this->LookForPauses && $this->LimitForPauses > 0 && ($newTime - end($this->Container->ContinuousData->Time)) > $this->LimitForPauses) {
            $this->WasPaused = true;
            $this->PauseDuration += $newTime - end($this->Container->ContinuousData->Time);

            return;
        }

        $this->Container->ContinuousData->Time[] = $newTime;
        $this->Container->ContinuousData->Latitude[] = $lat;
        $this->Container->ContinuousData->Longitude[] = $lon;
        $this->Container->ContinuousData->Altitude[] = (isset($point->ele)) ? (int)$point->ele : null;

        $this->parseExtensionValues($point);

        if ($this->WasPaused) {
            $num = count($this->Container->ContinuousData->Time);

            if ($num >= 2) {
                $pause = new Pause($this->Container->ContinuousData->Time[$num - 2], $this->PauseDuration);

                if (!empty($this->Container->ContinuousData->HeartRate)) {
                    $pause->setHeartRateDetails($this->Container->ContinuousData->HeartRate[$num - 2], $this->Container->ContinuousData->HeartRate[$num - 1]);
                }

                $this->Container->Pauses->add($pause);
            }

            $this->WasPaused = false;
            $this->PauseDuration = 0;
        }
    }

    /**
     * @param SimpleXMLElement $point
     * @return int
     */
    private function getTimeOfPoint(SimpleXMLElement $point)
    {
        $newTimestamp = strtotime((string)$point->time);
        $timeToAdd = $newTimestamp - $this->LastTimestamp;
        $this->LastTimestamp = $newTimestamp;

        if (!empty($this->Container->ContinuousData->Time)) {
            return end($this->Container->ContinuousData->Time) + $timeToAdd;
        }

        return $timeToAdd;
    }

    public function parseMetadata(SimpleXMLElement $metadata)
    {
        if (isset($metadata->name)) {
            $this->Container->Metadata->setDescription((string)$metadata->name);
        }

        if (isset($metadata->desc)) {
            $this->Container->Metadata->setNotes((string)$metadata->desc);
        }
    }

    private function parseExtensionValues(SimpleXMLElement $point)
    {
        $currentNum = count($this->Container->ContinuousData->Time);
        $bpm  = 0;
        $rpm  = 0;
        $temp = 0;
        $altitude = 0;

        if (isset($point->extensions)) {
            foreach ($this->getExtensionNodesOf($point->extensions[0]) as $extensionNode) {
                if ($extensionNode instanceof SimpleXMLElement) {
                    if (isset($extensionNode->hr)) {
                        $bpm = (int)$extensionNode->hr;
                    }

                    if (isset($extensionNode->cad)) {
                        $rpm = (int)$extensionNode->cad;
                    } elseif (isset($extensionNode->cadence)) {
                        $rpm = (int)$extensionNode->cadence;
                    }

                    if (isset($extensionNode->temp)) {
                        $temp = (int)$extensionNode->temp;
                    } elseif (isset($extensionNode->atemp)) {
                        $temp = (int)$extensionNode->atemp;
                    }

                    if (isset($extensionNode->altitude)) {
                        $altitude = (int)$extensionNode->altitude;
                    }
                }
            }
        }

        if ($bpm > 0) {
            $this->LastValidHeartRate = $bpm;
        }

        $this->Container->ContinuousData->HeartRate[] = $this->LastValidHeartRate;
        $this->Container->ContinuousData->Cadence[] = $rpm;
        $this->Container->ContinuousData->Temperature[] = $temp;

        if (0 == $this->Container->ContinuousData->Altitude[$currentNum - 1]) {
            $this->Container->ContinuousData->Altitude[$currentNum - 1] = $altitude;
        }
    }

    /**
     * @param SimpleXMLElement $parentNode
     * @return SimpleXMLElement[]
     */
    protected function getExtensionNodesOf(SimpleXMLElement $parentNode) {
        $childNodes = [];

        foreach (['gpxtpx', 'ns3', 'ns2'] as $namespace) {
            if (
                $parentNode->children($namespace, true)->count() > 0 &&
                isset($parentNode->children($namespace, true)->TrackPointExtension) &&
                count($parentNode->children($namespace, true)->TrackPointExtension) > 0
            ) {
                $childNodes[] = $parentNode->children($namespace, true)->TrackPointExtension->children($namespace, true);
            }
        }

        if ($parentNode->children('gpxdata',true)->count() > 0) {
            $childNodes[] = $parentNode->children('gpxdata', true);
        }

        return $childNodes;
    }

    protected function parseSpoQExtension()
    {
        if (null !== $this->ExtensionXml && $this->ExtensionXml->children('st',true)->count() > 0) {
            $activity = $this->ExtensionXml->children('st',true)->activity[0];

            if (isset($activity) && count($activity->children('st',true)) > 0) {
                $track = $activity->children('st',true)->heartRateTrack[0];

                if (isset($track)) {
                    $num = count($this->Container->ContinuousData->Time);
                    $i = 0;

                    foreach ($track->children('st',true)->heartRate as $heartRate) {
                        $attr = $heartRate->attributes();

                        if ($i < $num) {
                            $this->Container->ContinuousData->HeartRate[$i] = (int)$attr->bpm;
                            $i++;
                        }
                    }
                }
            }
        }
    }
}
