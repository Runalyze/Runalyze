<?php

namespace Runalyze\Parser\Common;

trait XmlParserTrait
{
    /** @var \SimpleXMLElement */
    protected $Xml;

    /**
     * @param \SimpleXMLElement $xml
     */
    public function setXml(\SimpleXMLElement $xml)
    {
        $this->Xml = $xml;
    }

    /**
     * @param string $content
     */
    public function setFileContent($content)
    {
        $this->Xml = simplexml_load_string(
            $this->correctXmlNamespace($this->removeBomFromFileContent($content)),
            null,
            LIBXML_PARSEHUGE
        );
    }

    /**
     * @param string $content
     * @return string
     */
    protected function correctXmlNamespace($content)
    {
        return str_replace('xmlns=', 'ns=', removeBOMfromString($content));
    }

    /**
     * @param string $content
     * @return string
     */
    protected function removeBomFromFileContent($content)
    {
        return mb_substr($content, mb_strpos($content, "<"));
    }
}
