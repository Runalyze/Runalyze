<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractMultipleParser;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\XmlParserTrait;
use SimpleXMLElement;

class Logbook extends AbstractMultipleParser implements FileContentAwareParserInterface
{
    use XmlParserTrait;

    public function parse()
    {
        $this->checkThatXmlIsValid();

        if (isset($this->Xml->Activities->Activity)) {
            foreach ($this->Xml->Activities->Activity as $activity) {
                $this->parseSingleActivity($activity);
            }
        }
    }

    protected function parseSingleActivity(SimpleXMLElement $activity)
    {
        $activityParser = new LogbookActivity($activity);
        $activityParser->setLogger($this->logger);
        $activityParser->parse();

        $this->Container[] = $activityParser->getActivityDataContainer();
    }

    protected function checkThatXmlIsValid()
    {
        if (!property_exists($this->Xml, 'Activities')) {
            throw new UnsupportedFileException('Given XML object is not from SportTracks. &lt;Activities&gt;-tag could not be located.');
        }
    }
}
