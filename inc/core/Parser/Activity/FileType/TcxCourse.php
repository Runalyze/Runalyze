<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractSingleParser;
use SimpleXMLElement;

class TcxCourse extends AbstractSingleParser
{
    /** @var SimpleXMLElement */
    protected $Xml;

    public function __construct(SimpleXMLElement $activity)
    {
        parent::__construct();

        $this->checkThatActivityXmlIsValid($activity);

        $this->Xml = $activity;
    }

    protected function checkThatActivityXmlIsValid(SimpleXMLElement $xml)
    {
        if (!property_exists($xml, 'Track')) {
            throw new UnsupportedFileException('Given XML object is not a valid tcx activity. &lt;Track&gt;-tag could not be located.');
        }
    }

    public function parse()
    {
        foreach ($this->Xml->Track as $track) {
            foreach ($track->Trackpoint as $TP) {
                $this->parseTrackpoint($TP);
            }
        }
    }

    protected function parseTrackpoint(SimpleXMLElement &$trackPoint)
    {
        if (!empty($trackPoint->Position)) {
            $this->Container->ContinuousData->Latitude[]  = (double)$trackPoint->Position->LatitudeDegrees;
            $this->Container->ContinuousData->Longitude[] = (double)$trackPoint->Position->LongitudeDegrees;
        } elseif (!empty($this->Container->ContinuousData->Latitude)) {
            $this->Container->ContinuousData->Latitude[] = end($this->Container->ContinuousData->Latitude);
            $this->Container->ContinuousData->Longitude[] = end($this->Container->ContinuousData->Longitude);
        } else {
            $this->Container->ContinuousData->Latitude[] = 0;
            $this->Container->ContinuousData->Longitude[] = 0;
        }

        $this->Container->ContinuousData->Distance[] = (float)$trackPoint->DistanceMeters / 1000;
        $this->Container->ContinuousData->Altitude[] = (int)$trackPoint->AltitudeMeters;
    }
}
