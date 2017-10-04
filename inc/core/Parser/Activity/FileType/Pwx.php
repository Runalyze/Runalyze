<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Parser\Activity\Common\AbstractMultipleParser;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\XmlParserTrait;
use SimpleXMLElement;

class Pwx extends AbstractMultipleParser implements FileContentAwareParserInterface
{
    use XmlParserTrait;

    public function parse()
    {
        if (isset($this->Xml->workout)) {
            foreach ($this->Xml->workout as $workout) {
                $this->parseSingleWorkout($workout);
            }
        }
    }

    protected function parseSingleWorkout(SimpleXMLElement $workout)
    {
        $activityParser = new PwxWorkout($workout);
        $activityParser->setLogger($this->logger);
        $activityParser->parse();

        $this->Container[] = $activityParser->getActivityDataContainer();
    }
}
