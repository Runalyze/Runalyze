<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractMultipleParser;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\XmlParserTrait;
use SimpleXMLElement;

class Fitlog extends AbstractMultipleParser implements FileContentAwareParserInterface
{
    use XmlParserTrait;

    public function parse()
    {
        $this->checkThatXmlIsValid();

        if (isset($this->Xml->AthleteLog->Activity)) {
            foreach ($this->Xml->AthleteLog->Activity as $activity) {
                try {
                    $this->parseSingleActivity($activity);
                } catch (UnsupportedFileException $e) {
                    $this->logger->warning('Couldn\'t parse one of the fitlog activities: '.$e->getMessage());
                }
            }
        }
    }

    protected function parseSingleActivity(SimpleXMLElement $activity)
    {
        $activityParser = new FitlogActivity($activity);
        $activityParser->setLogger($this->logger);
        $activityParser->parse();

        $this->Container[] = $activityParser->getActivityDataContainer();
    }

    protected function checkThatXmlIsValid()
    {
        if (!property_exists($this->Xml, 'AthleteLog') || !property_exists($this->Xml->AthleteLog, 'Activity')) {
            throw new UnsupportedFileException('Given XML object is not from SportTracks. &lt;AthleteLog&gt;-tag or &lt;Activity&gt;-tag could not be located.');
        }
    }
}
