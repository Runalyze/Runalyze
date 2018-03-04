<?php

namespace Runalyze\Parser\Common;

use Runalyze\Import\Exception\UnsupportedFileException;

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
     *
     * @throws UnsupportedFileException
     */
    public function setFileContent($content)
    {
        libxml_use_internal_errors(true);

        $this->Xml = simplexml_load_string(
            $this->correctXmlNamespace($this->removeBomFromFileContent($content)),
            null,
            LIBXML_PARSEHUGE
        );

        if (false === $this->Xml) {
            $errors = libxml_get_errors();

            throw new UnsupportedFileException($errors[0]->message);
        }
    }

    /**
     * @param string $content
     * @return string
     */
    protected function correctXmlNamespace($content)
    {
        return str_replace('xmlns=', 'ns=', $this->removeBomFromFileContent($content));
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
