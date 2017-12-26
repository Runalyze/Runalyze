<?php

namespace Runalyze\Parser\Activity\FileType;

use Runalyze\Parser\Activity\Common\AbstractMultipleParser;
use Runalyze\Parser\Common\FileContentAwareParserInterface;
use Runalyze\Parser\Common\XmlParserTrait;
use SimpleXMLElement;

class Gpx extends AbstractMultipleParser implements FileContentAwareParserInterface
{
    use XmlParserTrait;

    public function parse()
    {
        if (isset($this->Xml->trk)) {
            foreach ($this->Xml->trk as $track) {
                $this->Container[] = $this->parseSingleTrack($track);
            }
        }
    }

    /**
     * @param SimpleXMLElement $track
     * @return \Runalyze\Parser\Activity\Common\Data\ActivityDataContainer
     */
    protected function parseSingleTrack(SimpleXMLElement $track)
    {
        $parser = new GpxTrack($track);
        $parser->setLogger($this->logger);

        if (isset($this->Xml['creator']) && $this->Xml['creator'] == 'GPS Master') {
            $parser->lookForPauses();
        }

        if (isset($this->Xml->extensions)) {
            $parser->setExtensionXml($this->Xml->extensions[0]);
        }

        if (isset($this->Xml->metadata)) {
            $parser->parseMetadata($this->Xml->metadata[0]);
        }

        $parser->parse();

        return $parser->getActivityDataContainer();
    }
}
