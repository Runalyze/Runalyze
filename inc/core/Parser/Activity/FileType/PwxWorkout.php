<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractSingleParser;
use Runalyze\Parser\Activity\Common\Data\Pause\Pause;
use Runalyze\Parser\Activity\Common\Data\Round\Round;
use SimpleXMLElement;

class PwxWorkout extends AbstractSingleParser
{
    /** @var SimpleXMLElement */
    protected $Xml;

    public function __construct(SimpleXMLElement $workout)
    {
        parent::__construct();

        $this->checkThatActivityXmlIsValid($workout);

        $this->Xml = $workout;
    }

    protected function checkThatActivityXmlIsValid(SimpleXMLElement $workout)
    {
        if (!property_exists($workout, 'device') || !property_exists($workout, 'sample')) {
            throw new UnsupportedFileException('Given XML object is not from Peaksware/Trainingpeaks. &lt;device&gt;- or &lt;sample&gt;-tag could not be located.');
        }
    }

    public function parse()
    {
        $this->parseGeneralValues();
        $this->parseLaps();
        $this->parseLogEntries();
        $this->parseEvents();
    }

    /**
     * Parse general values
     *
     * "Time zones that aren't specified are considered undetermined."
     * That means: If the time string has no appendix (no '+01:00' and no 'Z'),
     * the offset must be treated as unknown (to prevent a timestamp change due to a coordinate-based time zone).
     *
     * @see http://books.xmlschemata.org/relaxng/ch19-77049.html
     */
    protected function parseGeneralValues()
    {
        $this->Container->Metadata->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->Xml->time);

        if (strlen((string)$this->Xml->time) == 19) {
            $this->Container->Metadata->setTimezoneOffset(null);
        }

        if (!empty($this->Xml->sportType)) {
            $this->Container->Metadata->setSportName((string)$this->Xml->sportType);
        }

        if (!empty($this->Xml->device)) {
            $string = (string)$this->Xml->device->make.', '.((string)$this->Xml->device->model);

            $this->Container->Metadata->setCreator('', $string.' ('.((string)$this->Xml->device['id']).')');
        }

        if (!empty($this->Xml->cmt)) {
            $this->Container->Metadata->setDescription((string)$this->Xml->cmt);
        }
    }

    protected function parseLaps()
    {
        if (!empty($this->Xml->segment)) {
            foreach ($this->Xml->segment as $segment) {
                foreach ($segment->summarydata as $lap) {
                    $this->parseLap($lap);
                }
            }
        }
    }

    protected function parseLap(SimpleXMLElement $lap)
    {
        if (!empty($lap->Calories)) {
            $this->Container->ActivityData->EnergyConsumption += (int)$lap->Calories;
        }

        $this->Container->Rounds->add(new Round(
            round((int)$lap->dist) / 1000,
            round((float)$lap->duration)
        ));
    }

    protected function parseLogEntries()
    {
        foreach ($this->Xml->sample as $log) {
            $this->parseLogEntry($log);
        }
    }

    protected function parseLogEntry(SimpleXMLElement $log)
    {
        if ((int)$log->timeoffset == 0 || (empty($log->dist) && (empty($log->lat) || empty($log->lon)))) {
            return;
        }

        $this->Container->ContinuousData->Time[] = (int)$log->timeoffset;
        $this->Container->ContinuousData->Latitude[] = (!empty($log->lat)) ? (double)$log->lat : null;
        $this->Container->ContinuousData->Longitude[] = (!empty($log->lon)) ? (double)$log->lon : null;
        $this->Container->ContinuousData->Altitude[] = (!empty($log->alt)) ? round((int)$log->alt) : null;
        $this->Container->ContinuousData->HeartRate[] = (!empty($log->hr)) ? round((int)$log->hr) : null;
        $this->Container->ContinuousData->Distance[] = (!empty($log->dist)) ? (float)$log->dist / 1000 : null;
        $this->Container->ContinuousData->Cadence[] = (!empty($log->cad)) ? (int)$log->cad : null;
        $this->Container->ContinuousData->Temperature[] = (!empty($log->temp)) ? round((int)$log->temp) : null;
        $this->Container->ContinuousData->Power[] = (!empty($log->pwr)) ? round((int)$log->pwr) : null;
    }

    protected function parseEvents()
    {
        $isStopped = false;
        $stopOffset = false;

        foreach ($this->Xml->event as $event) {
            if ((string)$event->type == 'Starting' && $isStopped && $stopOffset !== false) {
                $this->Container->PausesToApply->add(new Pause(
                    $stopOffset,
                    (int)$event->timeoffset - $stopOffset
                ));

                $isStopped = false;
                $stopOffset = false;
            } elseif ((string)$event->type == 'Stopping') {
                $isStopped = true;
                $stopOffset = (int)$event->timeoffset;
            }
        }
    }
}
