<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\Common\AbstractMultipleParser;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\XmlParserTrait;
use SimpleXMLElement;

class XmlPolar extends AbstractMultipleParser implements FileContentAwareParserInterface
{
    use XmlParserTrait;

    public function parse()
    {
        $this->checkThatXmlIsValid();

        if (isset($this->Xml->{'calendar-items'}->exercise)) {
            foreach ($this->Xml->{'calendar-items'}->exercise as $exercise) {
                $this->parseSingleExercise($exercise);
            }
        }
    }

    protected function parseSingleExercise(SimpleXMLElement $exercise)
    {
        $activityParser = new XmlPolarExercise($exercise);
        $activityParser->setLogger($this->logger);
        $activityParser->parse();

        $this->Container[] = $activityParser->getActivityDataContainer();
    }

    protected function checkThatXmlIsValid()
    {
        if (!property_exists($this->Xml, 'calendar-items')) {
            throw new UnsupportedFileException('Given XML object is not from Polar. &lt;calendar-items&gt;-tag could not be located.');
        }
    }
}
